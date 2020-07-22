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

use Excel;

class LogiwaReportController extends Controller
{
    /**
     * generates line item report from logiwa
     */
    public function lineItemReport(){
        // Get customer codes and ids
        $depositors = $this->getDepositors();

        if(!empty($depositors)){
            $from_date = null;
            $to_date = null;
            return view('layouts/logiwa/logiwalineitemreport',compact('from_date','to_date'));
        }

        return redirect()->route('dashboard');
    }

    /**
     * Generate Report
     *
     * @param Illuminate\Http\Request
     */
    public function generateLineItemReport(Request $request){
        // Get customer codes and ids
        $depositors = $this->getDepositors();

        if(!empty($depositors)){
            if($request->from_date != null && $request->to_date != null){
                $shipmentData = [];
                $shipmentParentData = [];
                $ctr = 0;
                foreach($depositors as $depositor){
                    $startDate = date('m.d.Y 00:00:00',strtotime($request->from_date));
                    $endDate = date('m.d.Y 23:59:59',strtotime($request->to_date));
                    $requestType = $request->request_type;
                    $body = [];
                    $body['DepositorID'] = $depositor->logiwa_depositor_id;
                    $body['DepositorCode'] = $depositor->logiwa_depositor_code;
                    $body['ShipmentDateTime_Start'] = $startDate;
                    $body['ShipmentDateTime_End'] = $endDate;

                    // Get Shipment Report From Logiwa
                    $logiwa = new LogiwaAPI;
                    $shipmentInfoRequest = $logiwa->getShipmentReportAllSearch($body);
                    if($shipmentInfoRequest['success'] == true){
                        if(isset($shipmentInfoRequest['data']->Data)){
                            foreach($shipmentInfoRequest['data']->Data as $data){
                                $shipmentParentData[$data->ID] = $data;
                                $shipmentData[$data->ID] = $data;
                            }
                        }
                    }

                    // Retrieve Line Items
                    $logiwa = new LogiwaAPI;
                    $shipmentSerialRequest = $logiwa->getShipmentInfoSerialSearch($body);

                    if(isset($shipmentSerialRequest['data']->Data)){
                        $existing_indices = [];
                        foreach($shipmentSerialRequest['data']->Data as $data){
                            // If serial numbers are found sort through them
                            // Mark the indices with match and remove afte the loop
                            if(isset($shipmentData[$data->ID]) && $data->Serial != NULL){
                                $existing_indices[] = $data->ID;
                                $shipmentData[$data->Serial] = $data;
                                $shipmentData[$data->Serial]->CustomerDescription = $shipmentParentData[$data->ID]->CustomerDescription ?? 'N/A';
                                $shipmentData[$data->Serial]->Company = $shipmentParentData[$data->ID]->Company ?? 'N/A';
                                $shipmentData[$data->Serial]->CustomerAddressDescription = $shipmentParentData[$data->ID]->CustomerAddressDescription ?? 'N/A';
                                $shipmentData[$data->Serial]->City = $shipmentParentData[$data->ID]->City ?? 'N/A';
                                $shipmentData[$data->Serial]->Zipcode = $shipmentParentData[$data->ID]->Zipcode ?? 'N/A';
                                $shipmentData[$data->Serial]->State = $shipmentParentData[$data->ID]->State ?? 'N/A';
                                $shipmentData[$data->Serial]->Country = $shipmentParentData[$data->ID]->Country ?? 'N/A';
                                $shipmentData[$data->Serial]->CarrierDescription = $shipmentParentData[$data->ID]->CarrierDescription ?? 'N/A';
                                $shipmentData[$data->Serial]->ShipmentMethod = $shipmentParentData[$data->ID]->ShipmentMethod ?? 'N/A';
                                $shipmentData[$data->Serial]->CarrierMarkupRate = $shipmentParentData[$data->ID]->CarrierMarkupRate ?? 'N/A';
                                $ctr++;
                            }
                        }

                        // Remove items with serials as they are already replaced
                        if(count($existing_indices) > 0){
                            foreach($existing_indices as $idx){
                                unset($shipmentData[$idx]);
                            }
                        }

                    }
                }

                //define header rows once
                $reportData[] = [];
                $reportData[] = ['Spectrum Solutions'];
                $reportData[] = ['Transactions From:',$request->from_date,'To:',$request->to_date];
                $reportData[] = [];
                $reportData[] = [
                    'Name',
                    //'captured Kit ID#',
                    'Kit ID#',
                    'Return Tracking',
                    'Trans #',
                    'ShipDate',
                    'Ref #',
                    'Name',
                    'Address',
                    'City',
                    'State',
                    'Zip',
                    'Country',
                    'SKU',
                    'Qty',
                    'Tracking #',
                    'Carrier',
                    //'Retailer ID',
                    //'Department ID',
                    'PO Number',
                    'Carrier',
                    'Carrier Method',
                    'Shipping Cost'
                ];

                if(!empty($shipmentData)){
                    foreach($shipmentData as $data){
                        $reportData[] = [
                            ($data->DepositorDescription != null ? $data->DepositorDescription : 'N/A'),
                            //($data->Serial ?? 'N/A'),
                            ($data->Serial ?? 'N/A'),
                            ($data->LotBatchNo != null ? $data->LotBatchNo : 'N/A'),
                            ($data->WarehouseOrderID != null ? $data->WarehouseOrderID : 'N/A'),
                            ($data->ShipmentDateTime != null ? $data->ShipmentDateTime : 'N/A'),
                            ($data->WarehouseOrderCode != null ? $data->WarehouseOrderCode : 'N/A'),
                            ($data->Company != null ? $data->Company : $data->CustomerDescription),
                            ($data->CustomerAddressDescription != null ? $data->CustomerAddressDescription : 'N/A'),
                            ($data->City != null ? $data->City : 'N/A'),
                            ($data->State != null ? $data->State : 'N/A'),
                            ($data->Zipcode != null ? $data->Zipcode : 'N/A'),
                            ($data->Country != null ? $data->Country : 'N/A'),
                            ($data->InventoryItemDescription != null ? $data->InventoryItemDescription : 'N/A'),
                            ($data->PackQuantity = 1),
                            ($data->CarrierTrackingNumber != null ? $data->CarrierTrackingNumber : 'N/A'),
                            ($data->CarrierDescription != null ? $data->CarrierDescription : 'N/A'),
                            //'N/A',
                            //'N/A',
                            'N/A',
                            ((isset($data->CarrierDescription)) ? $data->CarrierDescription : ''),
                            ((isset($data->ShipmentMethod)) ? $data->ShipmentMethod : ''),
                            ((isset($data->CarrierMarkupRate)) ? number_format($data->CarrierMarkupRate,2) : ''),
                        ];
                    }
                }

                if(isset($reportData)){

                    if($requestType == 'generate'){
                        $from_date = $request->from_date;
                        $to_date = $request->to_date;
                        return view('layouts/logiwa/logiwalineitemreport',compact('reportData','from_date','to_date'));
                    }else{
                        $currentTime = Carbon::now();
                        Excel::create('inventory_'.$currentTime, function($excel) use ($reportData) {
                        $excel->sheet('inventory', function($sheet) use ($reportData)
                        {

                            $sheet->setFontFamily('Calibri');
                            $sheet->setFontSize(9);
                            $sheet->cell('A1', function($cell) {$cell->setValue('Transaction By Line Item Report');   });
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
                            $sheet->mergeCells('A1:G1');
                            $sheet->cells('A1:G1', function ($cells) {
                                $cells->setFontWeight('bold');
                                $cells->setAlignment('center');
                                $cells->setValignment('center');
                                $cells->setFontSize(18);
                                $cells->setFontFamily('Century Gothic');
                                $cells->setBorder(array(
                                    'bottom'   => array(
                                        'style' => 'thick',
                                        'color' => [
                                            'rgb' => '000000'
                                        ]
                                    ),
                                ));
                            });

                            $sheet->setSize(array(
                                'A1' => array(
                                    'height'    => 25
                                )
                            ));

                            $sheet->mergeCells('A2:K2');
                            $sheet->setSize(array(
                                'A2' => array(
                                    'height'    => 6
                                )
                            ));


                            $sheet->mergeCells('A3:C3');

                            $sheet->cells('A3:C3', function ($cells) {
                                $cells->setFontWeight('bold');
                                $cells->setValignment('center');
                                $cells->setFontSize(12);
                                $cells->setFontColor('#FF0000');
                                $cells->setFontFamily('Century Gothic');
                            });
                            $sheet->cells('A4:D4', function ($cells) {
                                $cells->setValignment('center');
                                $cells->setAlignment('center');
                                $cells->setFontSize(10);
                                $cells->setFontFamily('Century Gothic');
                            });
                            $sheet->setSize(array(
                                'A5' => array(
                                    'height'    => 10
                                )
                            ));
                            $sheet->cells('A6:T6', function ($cells) {
                                $cells->setValignment('top');
                                $cells->setAlignment('center');
                                $cells->setFontSize(10);
                                $cells->setFontWeight('bold');
                                $cells->setFontColor('#FFFFFF');
                                $cells->setBorder(array(
                                    'allborders'   => array(
                                        'style' => 'thin',
                                        'color' => [
                                            'rgb' => '000000'
                                        ]
                                    ),
                                ));
                                $cells->setBackground('#808080');

                            });
                            $sheet->setSize(array(
                                'A6' => array(
                                    'height'    => 22
                                )
                            ));
                            $sheet->setAutoSize(true);
                            $sheet->cells('A7:T'.(count($reportData)+1), function ($cells) {
                                $cells->setBorder(array(
                                    'allborders'   => array(
                                        'style' => 'thin',
                                        'color' => [
                                            'rgb' => '000000'
                                        ]
                                    ),
                                ));
                            });

                            $sheet->cells('A'.(count($reportData)+2).':S'.(count($reportData)+2), function ($cells) {
                                $cells->setBorder(array(
                                    'top'   => array(
                                        'style' => 'thin',
                                        'color' => [
                                            'rgb' => '000000'
                                        ]
                                    ),
                                ));
                            });

                            $sheet->cells('Q6:T'.(count($reportData)+1), function ($cells) {
                                $cells->setBorder(array(
                                    'left'   => array(
                                        'style' => 'thin',
                                        'color' => [
                                            'rgb' => '000000'
                                        ]
                                    ),
                                ));
                            });

                            $sheet->fromArray($reportData);
                        });
                        })->download('xlsx');
                    }
                }


            }
            return view('layouts/logiwa/logiwalineitemreport');
        }

        return redirect()->route('dashboard');
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
}
