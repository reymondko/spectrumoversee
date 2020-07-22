<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use App\Models\Companies;
use App\Models\ShipPackage;
use App\Models\ShippingCarriers;
use App\Models\ShippingCarrierMethods;
use App\Models\ShipPackSubmissions;
use App\Models\ShipRushAddress;
use App\Models\TplShipperAddress;
use App\Models\ShippingAutomationRules;
use App\Models\ShippingPrinter;
use App\Models\ShippingClient;
use App\Models\User;

use App\Models\QualityInspector;
use App\Models\QualityInspectorLogs;

use App\Libraries\SecureWMS\SecureWMS;
use App\Libraries\ShipCaddie\ShipCaddie;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;
Use DB;

use Excel;

class QualityInspectorController extends Controller
{
   
    protected $wms = null;
    public $allowedShipcadieAffiliates = [
        'USPS'
    ];

    public function index()
    {
        if (
            //Gate::allows('can_see_ship_pack_tpl', auth()->user()) ||
            Gate::allows('can_quality_inspector', auth()->user())
           // ||Gate::allows('company-with-ship-pack', auth()->user())
        ) {
            $package_sizes = array();
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
            return view('layouts/qualityinspector/qualityinspector',compact(['package_sizes']));
        }else{
            return redirect()->route('dashboard');
        }
    }

    public function searchQITransactionID(Request $r)
    {
        $company_id = \Auth::user()->companies_id;
        $companies = Companies::select('fulfillment_ids')->where('id',$company_id)->first();
        $customer_id = null;

            $client = new Client();
            /* get access token */
            $accessToken = null;
            $tmpLineItems = array();
            try {
                $request = $client->request('POST', 'https://secure-wms.com/AuthServer/api/Token', [
                    'headers' => ['Authorization' => 'Basic Yzc5YWVjNjktNmE4ZC00ZDIyLTg2NmUtZGI3NjQ2ZTFiYTYxOnU1WEMrRTBWQVlZMGtDZnVYZFNtbTFheFhtcW5ZUnA4'],
                    'json' => [
                        'grant_type' => 'client_credentials',
                        'tpl' => '{e55a580d-29d1-43d0-9b7b-448c5602a223}',
                        'user_login_id' => '1'
                    ]]);
                $response = json_decode($request->getBody());
                $accessToken = $response->access_token;
            }
            
            catch (\Exception $e) {}
            //echo $accessToken;die();
            //get recent orders
            try {
                $request = $client->request('GET', 'https://secure-wms.com/orders?detail=All&itemdetail=All&pgsiz=100&pgnum=1&rql=readonly.OrderId=='.$r->transaction_id, [
                    'headers' => [
                        'Authorization' => 'Bearer '.$accessToken,
                        'Accept' => "application/hal+json"
                    ],
                    'json' => []
                ]);

                $response = json_decode($request->getBody());
               // print_r($response); die();
                $orderz = $response->{'_embedded'}->{'http://api.3plCentral.com/rels/orders/order'};
                foreach ($orderz as $order) {
                    $returnOrder = $order;
                    $lineItems = $order->{'_embedded'}->{'http://api.3plCentral.com/rels/orders/item'};
                    $customer_id = $order->readOnly->customerIdentifier->name;
                    $reference_num=$order->referenceNum;
                    foreach($lineItems as $item) {
                        if ($item->qty <= 0)
                            continue;
                        $request = $client->request('GET', 'https://secure-wms.com/customers/'.$order->readOnly->customerIdentifier->id.'/items?pgsiz=100&pgnum=1&rql=Upc==*'.$item->itemIdentifier->sku.'*,sku==*'.$item->itemIdentifier->sku.'*', [
                            'headers' => [
                            'Authorization' => 'Bearer '.$accessToken,
                            'Accept' => "application/hal+json"
                            ],
                            'json' => []
                        ]);
                        $item_desc = 'N/A';
                        $responseInv = json_decode($request->getBody());
                        $responseLineItem = $responseInv->{'_embedded'};
                        
                        
                        foreach($responseLineItem as $LineItem) {
                            if (isset($LineItem[0]->upc)) {
                                $upc = $LineItem[0]->upc;
                            } else {
                                $upc = null;
                            }
                            if(!empty($LineItem)){
                                foreach($LineItem as $li){
                                    if($li->sku == $item->itemIdentifier->sku){
                                        $item_desc = $li->description;
                                    }
                                }
                            }
                        }
                        //echo 'https://secure-wms.com/orders/'.$order->readOnly->orderId.'/items/'.$item->readOnly->orderItemId.'?detail=AllocationsWithDetail';die();
                        if(isset($responseLineItem->item[0]->itemDescription))
                            $item_desc = $responseLineItem->item[0]->itemDescription;

                            $request = $client->request('GET', 'https://secure-wms.com/orders/'.$order->readOnly->orderId.'/items/'.$item->readOnly->orderItemId.'?detail=AllocationsWithDetail', [
                            'headers' => [
                            'Authorization' => 'Bearer '.$accessToken,
                            'Accept' => "application/hal+json"
                            ],
                            'json' => []
                        ]);

                        $responseItems = json_decode($request->getBody());
                        if (count($responseItems->readOnly->allocations) == 0) {
                            
                            $qi = QualityInspector::where('line_number', $item->readOnly->orderItemId)->first();
                            if($qi){
                                $status = $qi->status;
                                $qi_id = $qi->id;
                                $line_number = $qi->line_number;
                                $failReason = $qi->notes;
                            }
                            else{
                                $qi = new QualityInspector;
                                //$qi->status = '0';
                                $qi->transaction_id = $r->transaction_id;
                                $qi->line_number = $item->readOnly->orderItemId;
                                $qi->company_id = $customer_id;
                                $qi->reference_number = $reference_num;
                                $qi->save();
                                $qi_id = $qi->id;
                                $line_number = $qi->line_number;
                                $status = "";
                                $failReason ="";
                            }
                            
                            $statustext="";
                            if($status==1){
                                $statustext="PASS";
                            }
                            elseif($status==0){
                                $statustext=="FAIL";
                            }
                            else{
                                $statustext="";
                            }
                            $tmpLineItems[] = array(
                                'item_id' => $line_number, //$item->readOnly->OrderItemId,
                                'sku' => $item->itemIdentifier->sku,
                                'upc' => $upc,
                                'description' => $item_desc,
                                'serial_number' => null,
                                'lot_number' => null,
                                'expiration' => null,
                                'qty' => $item->qty,
                                'qty_packed' => 0,
                                'qty_remaining' => $item->qty,
                                'done' => ($item->qty > 0 ? false:true),
                                'status' => $status,
                                'statustext' => $statustext,
                                'qi_id' => $qi->id,
                                'failReason' => $failReason
                            );
                        }
                        foreach($responseItems->readOnly->allocations as $allocation){

                            $details = $allocation->detail->itemTraits;
                            
                            $qi = QualityInspector::where('line_number', $item->readOnly->orderItemId)->first();
                            if($qi){
                                $status = $qi->status;
                                $line_number = $qi->line_number;
                                $qi_id = $qi->id;
                            }
                            else{
                                $qi = new QualityInspector;
                               // $qi->status = '0';
                                $qi->transaction_id = $r->transaction_id;
                                $qi->line_number = $item->readOnly->orderItemId;
                                $qi->company_id = $customer_id;
                                $qi->reference_number = $reference_num;
                                $qi->save();
                                $line_number = $qi->line_number;
                                $qi_id = $qi->id;
                                $status = "";
                            }
                            if(isset($details->serialNumber)){
                                $serial_number =(($details->serialNumber != "") ? $details->serialNumber:'N/A');
                            }

                            if(isset($details->lotNumber)){
                                $lot_number = (($details->lotNumber != "") ? $details->lotNumber:'N/A');
                            }

                            
                            $serial_number = 'N/A';
                            $expiration = 'N/A';
                            $lot_number = 'N/A';
                            if(isset($details->serialNumber)){
                                $serial_number =(($details->serialNumber != "") ? $details->serialNumber:'N/A');
                            }
                            $statustext="";
                            if(isset($details->lotNumber)){
                                $lot_number = (($details->lotNumber != "") ? $details->lotNumber:'N/A');
                            }
                            if($status==1){
                                $statustext="PASS";
                            }
                            elseif($status==0){
                                $statustext=="FAIL";
                            }
                            else{
                                $statustext="";
                            }
                            $tmpLineItems[] = array(
                                'item_id' => $line_number, //$item->readOnly->OrderItemId,
                                'sku' => $details->itemIdentifier->sku,
                                'upc' => $upc,
                                'description' => $item_desc,
                                'serial_number' => $serial_number,
                                'lot_number' => $lot_number,
                                'expiration' => $expiration,
                                'qty' => $allocation->qty,
                                'qty_packed' => 0,
                                'qty_remaining' => $allocation->qty,
                                'status' => $status,
                                'statustext' => $statustext,
                                'done' => ($allocation->qty > 0 ? false:true),
                                'qi_id' =>$qi->id
                            );                
                        }
                            //\Log::info(json_decode(json_encode($responseItems), true));
                    }

                }
            }
            catch (\Exception $e) {
                $response = array(
                    "status" =>"other",
                    'error_message' => $e->getMessage()
                );
                return response()->json($response,200);
            }

        $response = array(
            "status" =>"ok",
            "result" =>['order_details' => $returnOrder,'order_items' => $tmpLineItems]
        );
        return response()->json($response,200);
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

    

}
