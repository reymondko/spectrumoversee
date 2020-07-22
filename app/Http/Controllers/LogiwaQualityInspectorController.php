<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use App\Models\Companies;
use App\Models\BatchesItems;
use Illuminate\Support\Facades\Log;
use App\Libraries\Logiwa\LogiwaAPI;
use Carbon\Carbon;
use App\Models\LogiwaDepositor;
use Illuminate\Support\Facades\Gate;
use App\Models\ShipPackage;
use App\Models\QualityInspector;
use App\Models\QualityInspectorLogs;
use Excel;



class LogiwaQualityInspectorController extends Controller
{
    /**
     * Show logiwa Quality Ispector page
     */
    public function index(){
        if(Gate::allows('can_quality_inspector', auth()->user())) {
            $package_sizes = [];
            $package_sizes_collection = ShipPackage::get();
            if($package_sizes_collection){
                foreach($package_sizes_collection as $key => $value){
                    $package_sizes[] = array(
                        'id' => $value->id,
                        'package_name' => $value->package_name,
                        'length' => $value->length,
                        'width' => $value->width,
                        'height' => $value->height,
                        'weight' => $value->weight,
                        'content_qty' => 1,
                        'selected' => false,
                    );
                }
            }
            return view('layouts/logiwa/logiwaqualityinspector',compact(['package_sizes']));
        }
        return redirect()->route('dashboard');
    }

    /**
     * Searches Logiwa for the specified transaction id,customer order or
     * transaction number
     *
     * @param Illuminate\Http\Request
    *  @return mixed
     */
    public function searchQITransactionID(Request $request){

        $address_data = null;
        $order = null;
        $order_found = false;
        $tmpLineItems = [];

        // Get customer codes and ids
        $depositors = $this->getDepositors();
        $depositor_codes = [];

        foreach($depositors as $depositor){
            $depositor_codes[] = $depositor->logiwa_depositor_code;
        }


        // Search by order id
        if(is_numeric($request->transaction_id)){
            $body = [];
            $body['WarehouseID'] = env('LOGIWA_WAREHOUSE_ID');
            $body['IsGetOrderDetails'] = true;
            $body['ID'] = $request->transaction_id;
            $logiwa = new LogiwaAPI;
            $order = $logiwa->getWarehouseOrderSearch($body);
            if(isset($order['data']->Data) && count($order['data']->Data) > 0){
                $order = $order['data']->Data[0];
                $order_found = true;
            }
        }

        // if not found try if customer order no
        if(!$order_found){
            $body = [];
            $order = null;
            $body['WarehouseID'] = env('LOGIWA_WAREHOUSE_ID');
            $body['IsGetOrderDetails'] = true;
            $body['CustomerOrderNo'] = $request->transaction_id;
            $logiwa = new LogiwaAPI;
            $order = $logiwa->getWarehouseOrderSearch($body);
            if(isset($order['data']->Data) && count($order['data']->Data) > 0){
                $order = $order['data']->Data[0];
                $order_found = true;
            }
        }

        // if not found try if tracking
        if(!$order_found){
            $body = [];
            $order = null;
            $body['WarehouseID'] = env('LOGIWA_WAREHOUSE_ID');
            $body['IsGetOrderDetails'] = true;
            $body['CarrierTrackingNumber'] = $request->transaction_id;
            $logiwa = new LogiwaAPI;
            $order = $logiwa->getWarehouseOrderSearch($body);
            if(isset($order['data']->Data) && count($order['data']->Data) > 0){
                $order = $order['data']->Data[0];
                $order_found = true;
            }
        }

        // if order is found
        if($order_found){

            // Get Order Address Details
            $body = [];
            $body['ID'] = $order->CustomerAddressID;
            $logiwa = new LogiwaAPI;
            $address_request = $logiwa->getAddressDataByID($body);
            if($address_request['success'] == true){
                $address_data = $address_request['data'];
                $order->address_data = $address_data;
            }

            // Get line items with serials
            $has_serials = false;
            $body = [];
            $body['WarehouseID'] = env('LOGIWA_WAREHOUSE_ID');
            $body['WarehouseOrderID'] = $order->ID;
            $logiwa = new LogiwaAPI;
            $serial_request = $logiwa->getShipmentInfoSerialSearch($body);

            if(count($serial_request['data']->Data) > 0){
                $has_serials = true;
                foreach($serial_request['data']->Data as $line_item){
                    $qi = QualityInspector::where('line_number', $line_item->ID)->first();
                    if($qi){
                        $status = $qi->status;
                        $line_number = $qi->line_number;
                        $qi_id = $qi->id;
                    }else{
                        $qi = new QualityInspector;
                        $qi->transaction_id = $line_item->WarehouseOrderID;
                        $qi->line_number = $line_item->ID;
                        $qi->company_id = $order->DepositorID;
                        $qi->reference_number = $line_item->WarehouseOrderCode;
                        $qi->save();
                        $line_number = $qi->line_number;
                        $qi_id = $qi->id;
                        $status = "";
                    }

                    if($status==1){
                        $statustext="PASS";
                    }
                    elseif($status==0){
                        $statustext="FAIL";
                    }
                    else{
                        $statustext="";
                    }

                    $tmpLineItems[] = array(
                        'item_id' => $line_number, //$item->readOnly->OrderItemId,
                        'sku' => $line_item->Barcode,
                        'upc' => null,
                        'description' => $line_item->InventoryItemDescription,
                        'serial_number' => $line_item->Serial,
                        'lot_number' => $line_item->LotBatchNo,
                        'expiration' => $line_item->ExpireDate != "" ? \DateTime::createFromFormat('m.d.Y H:i:s',$line_item->ExpireDate)->format('m/d/Y'):'N/A',
                        'qty' => $line_item->PackQuantity,
                        'qty_packed' => 0,
                        'qty_remaining' => $line_item->PackQuantity,
                        'status' => $status,
                        'statustext' => $statustext,
                        'done' => ($line_item->PackQuantity > 0 ? false:true),
                        'qi_id' =>$qi->id
                    );
                }
            }

            if(!$has_serials){
                foreach($order->DetailInfo as $line_item){
                    $qi = QualityInspector::where('line_number', $line_item->ID)->first();
                    if($qi){
                        $status = $qi->status;
                        $line_number = $qi->line_number;
                        $qi_id = $qi->id;
                    }else{
                        $qi = new QualityInspector;
                        $qi->transaction_id = $line_item->WarehouseOrderID;
                        $qi->line_number = $line_item->ID;
                        $qi->company_id = $order->DepositorID;
                        $qi->reference_number = $line_item->Code;
                        $qi->save();
                        $line_number = $qi->line_number;
                        $qi_id = $qi->id;
                        $status = "";
                        $failReason ="";
                    }

                    if($status==1){
                        $statustext="PASS";
                    }
                    elseif($status==0){
                        $statustext="FAIL";
                    }
                    else{
                        $statustext="";
                    }

                    $tmpLineItems[] = array(
                        'item_id' => $line_number, //$item->readOnly->OrderItemId,
                        'sku' => $line_item->Barcode,
                        'upc' => null,
                        'description' => $line_item->InventoryItemDescription,
                        'serial_number' => null,
                        'lot_number' => null,
                        'expiration' => null,
                        'qty' => $line_item->PackQuantity,
                        'qty_packed' => 0,
                        'qty_remaining' => $line_item->PackQuantity,
                        'status' => $status,
                        'statustext' => $statustext,
                        'done' => ($line_item->PackQuantity > 0 ? false:true),
                        'qi_id' =>$qi->id,
                        'failReason' => $failReason ?? null
                    );
                }
            }
        }

        if($order_found){
            $response = array(
                "status" =>"ok",
                "result" =>['order_details' => $order,'order_items' => $tmpLineItems,'order_address'=>$address_data]
            );
        }else{
            $response = array(
                "status" =>"other",
                'error_message' => 'Order not found'
            );
            return response()->json($response,200);
        }

        return response()->json($response,200);
    }

    /**
     * Retrieve user customer code for logiwa
     */
    public function getDepositors(){
        $depositor_array = [];
        $depositors = LogiwaDepositor::where('companies_id',\Auth::user()->companies_id)->get();
        foreach($depositors as $depositor){
            $depositor_array[] = $depositor;
        }
        return $depositor_array;
    }

    public function approve($qi_id){
        $qi = QualityInspector::where('id', $qi_id)->first();
        $qi->status = "1";
        $qi->qi_by = \Auth::user()->id;
        $qi->save();

        $qi_log = new QualityInspectorLogs;
        $qi_log->status = "1";
        $qi_log->qi_id = $qi->id;
        $qi_log->qi_by = \Auth::user()->id;
        $qi_log->status = "1";
        $qi_log->save();
        return "ok";
    }

    public function fail(Request $r){
        $fail = QualityInspector::where('id', $r->qi_id)->first();
        $fail->status = "0";
        $fail->notes = $r->reason;
        $fail->qi_by = \Auth::user()->id;
        $fail->save();

        $qi_log = new QualityInspectorLogs;
        $qi_log->status = "0";
        $qi_log->notes = $r->reason;
        $qi_log->qi_by = \Auth::user()->id;
        $qi_log->qi_id = $fail->id;
        $qi_log->save();
        if($qi_log){
            return "ok";
        }
        else{
            return "error";
        }
    }

    public function qualityinspector_details()
    {
        // $client = ShippingClient::IsCustomerRequiredScan( 77 )->first();
        //$client = ShippingClient::IsCustomerRequiredScan( request()->input('customer_id') )->first();
        $client = ShippingClient::where('tpl_client_id', request()->input('customer_id'))->first();
        if ($client) {
            return response()->json([
                'status' => 'success',
                'found' => true,
                'client' => $client
            ], 200);
        }

        return response()->json([
            'status' => 'success',
            'found' => false,
            'client' => null
        ], 200);
    }

    public function quality_inspect($transID,$item_id) {
        $qi = QualityInspector::where('transaction_id', $transID)->first();
        if($qi){
            return $qi->status;
        }
        else{
            $qi = new QualityInspector;
            $qi->status = '0';
            $qi->transaction_id = $transID;
            $qi->line_number = $item_id;
            $qi->save();
        }

    }

    public function qiReport(Request $request) {
        //DB::enableQueryLog();
        $qis = QualityInspectorLogs::
        join('quality_inspector', 'quality_inspector.id', '=', 'quality_inspector_logs.qi_id')
        ->join('users', 'users.id', '=', 'quality_inspector_logs.qi_by')

            ->orderBy('quality_inspector_logs.updated_at', 'desc')
            ->limit(50)
            ->get(['quality_inspector.line_number',
            'quality_inspector.company_id as company_name',
            'quality_inspector.transaction_id',
            'quality_inspector.reference_number',
            'quality_inspector_logs.status',
            'users.name',
            'quality_inspector_logs.updated_at',
            'quality_inspector_logs.notes']);

        //$query = DB::getQueryLog();
        //print_r($query);die();

      return view('layouts.qualityinspector.report', compact('qis'));
    }


    public function qiReportDownload(Request $request) {

        //DB::enableQueryLog();
        $qis = QualityInspectorLogs::
        join('quality_inspector', 'quality_inspector.id', '=', 'quality_inspector_logs.qi_id')
        ->join('users', 'users.id', '=', 'quality_inspector_logs.qi_by')
            ->orderBy('quality_inspector_logs.updated_at', 'desc')
            ->get(['quality_inspector.line_number AS Line Number',
            'quality_inspector.company_id as Company Name',
            'quality_inspector.transaction_id as Transaction ID',
            'quality_inspector.reference_number as Reference Number',
            DB::raw('(CASE WHEN quality_inspector_logs.status = 1 THEN "PASS" ELSE "FAIL" END) AS Status'),
            'users.name as QI By',
            'quality_inspector_logs.updated_at as Date QI',
            'quality_inspector_logs.notes as Fail Notes'])->toArray();
       Excel::create('quality_inspector', function($excel) use ($qis) {
            $excel->sheet('inventory', function($sheet) use ($qis)
            {
                $sheet->setFontFamily('Calibri');
                $sheet->setFontSize(9);
                $sheet->setStyle([
                    'borders' => [
                        'allborders' => [
                            'style' => 'thin',
                            'color' => [
                                'rgb' => 'FFFFFF'
                            ]
                        ]
                    ]
                ]);
                $sheet->fromArray($qis);
            });

            })->download('xlsx');

    }

    private function getSubkitIds($master_kit_id) {
        $subkits = [];
        if (strlen((string)$master_kit_id) > 1) {
          $subkitids = BatchesItems::where('master_kit_id', $master_kit_id)->get();
          foreach ($subkitids as $subkit) {
            $subkits[] = [
              'subkit_id' => $subkit->subkit_id,
              'return_tracking' => $subkit->return_tracking
            ];
          }
        }

        return $subkits;
    }

}
