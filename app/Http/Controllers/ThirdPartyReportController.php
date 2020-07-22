<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use App\Models\Companies;
use App\Models\BatchesItems;
use Carbon\Carbon;
use Excel;

class ThirdPartyReportController extends Controller
{
    public function index(){

        $companies = Companies::select('fulfillment_ids')->where('id',\Auth::user()->companies_id)->first();

        if($companies->fulfillment_ids){
            return view('layouts/thirdparty/thirdpartyreport');
        }

        return redirect()->route('dashboard');
    }

    public function generateLineItemReport(Request $request){
        $companies = Companies::select('fulfillment_ids')->where('id',\Auth::user()->companies_id)->first();
        $requestType = $request->request_type;
        if($companies->fulfillment_ids){

            $startDateQ = null;
            $endDateQ = null;

            if(isset($request->from_date) && isset($request->to_date)){
                $startDate = date('Y-m-d',strtotime($request->from_date));
                $endDate = date('Y-m-d',strtotime($request->to_date));

                $from_date = $request->from_date;
                $to_date = $request->to_date;

                $startDateQ = 'readonly.processDate=ge='.$startDate.'T00:00:00;';
                $endDateQ = 'readonly.processDate=le='.$endDate.'T23:59:59;';
            }


             /* get access token */
             $client = new Client();
             $accessToken = null;
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
             } catch (\Exception $e) {
                print $e->getMessage();
             }
             //get recent orders

             try {
                $request = $client->request('GET', 'https://secure-wms.com/orders?detail=All&pgsiz=100&pgnum=1&rql='.$startDateQ.$endDateQ.'readonly.customeridentifier.id=in=('.$companies->fulfillment_ids.')&sort=-readOnly.CreationDate', [
                    'headers' => [
                      'Authorization' => 'Bearer '.$accessToken,
                      'Accept' => "application/hal+json"
                    ],
                    'json' => []
                  ]);
                $response = json_decode($request->getBody());
                $orderz = $response->{'_embedded'}->{'http://api.3plCentral.com/rels/orders/order'};
                $totalResults = $response->totalResults;
                $pageSize = 100;
                $totalPages = ceil($totalResults/$pageSize);
                $initial = 1;

                foreach ($orderz as $order) {
                    $o = $order->readOnly;
                    $name = $o->customerIdentifier->name;
                    $warehouseName = $o->facilityIdentifier->name;
                    break;
                }

                //define header rows once
                $reportData[] = array();
                $reportData[] = array('Warehouse: '.$warehouseName);
                $reportData[] = array('Transactions From:',$from_date,'To:',$to_date);
                $reportData[] = array();
                $reportData[] = array(
                    'Name',
                    'captured Kit ID#',
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
                    'Retailer ID',
                    'Department ID',
                    'PO Number'
                );
                while($initial <= $totalPages) { //handle report paginating

                  $request = $client->request('GET', 'https://secure-wms.com/orders?detail=All&pgsiz=100&pgnum='.$initial.'&rql='.$startDateQ.$endDateQ.'readonly.customeridentifier.id=in=('.$companies->fulfillment_ids.')&sort=-readOnly.CreationDate', [
                      'headers' => [
                        'Authorization' => 'Bearer '.$accessToken,
                        'Accept' => "application/hal+json"
                      ],
                      'json' => []
                    ]);
                  $response = json_decode($request->getBody());
                  if (isset($response->{'_embedded'})) {
                    $orderz = $response->{'_embedded'}->{'http://api.3plCentral.com/rels/orders/order'};
                  } else {
                    $orderz = [];
                  }

                  //print 'https://secure-wms.com/orders?detail=All&pgsiz=1000&pgnum=1&rql='.$startDateQ.$endDateQ.'readonly.customeridentifier.id=in=('.$companies->fulfillment_ids.')&sort=-readOnly.CreationDate';
                  //print "<br />".count($orderz);
                  //exit;

                  foreach ($orderz as $order) {

                      // echo '<pre>';var_dump($order);


                      $o = $order->readOnly;

                      /*if ($o->orderId == 152891) {
                        print_r($order);
                        exit;
                      }
                      continue;*/


                      // $orders[] = $order;
                      $name = $o->customerIdentifier->name;
                      $transId = $o->orderId;
                      $refNum = $order->referenceNum;
                      $address = (isset($order->shipTo->address1) ? $order->shipTo->address1:'N/A');
                      $shipToName = (isset($order->shipTo->companyName) ? $order->shipTo->companyName:(isset($order->shipTo->name) ? $order->shipTo->name:'N/A'));
                      $city = (isset($order->shipTo->city) ? $order->shipTo->city:'N/A');
                      $state = (isset($order->shipTo->state) ? $order->shipTo->state:'N/A');
                      $zip = (isset($order->shipTo->zip) ? $order->shipTo->zip:'N/A');
                      $country = (isset($order->shipTo->country) ? $order->shipTo->country:'N/A');
                      $itemObject = (isset($order->{'_embedded'}) ? $order->{'_embedded'}->{'http://api.3plCentral.com/rels/orders/item'} : null) ;
                      $carrier = (isset($order->routingInfo->carrier) ? $order->routingInfo->carrier : 'N/A');
                      $trackingNo = (isset($order->routingInfo->trackingNumber) ? $order->routingInfo->trackingNumber : 'N/A');
                      $retailerId = (isset($order->shipTo->retailerId) ? $order->shipTo->retailerId:'N/A');
                      $dept = (isset($order->shipTo->dept) ? $order->shipTo->dept:'N/A');
                      $poNum = 'N/A';

                      if(isset($order->poNum)){
                          if($order->poNum != ''){
                              $poNum = $order->poNum;
                          }
                      }
                        //try to get line items from inventory allocation detail
                        if (isset($order->{'_embedded'})) {
                          $lineItems = $order->{'_embedded'}->{'http://api.3plCentral.com/rels/orders/item'};
                        } else {
                          $lineItems = [];
                        }
                        $foundItems = false;
                        foreach($lineItems as $item) {
                          if ($item->qty <= 0)
                            continue;

                          $request = $client->request('GET', 'https://secure-wms.com/orders/'.$order->readOnly->orderId.'/items/'.$item->readOnly->orderItemId.'?detail=AllocationsWithDetail', [
                              'headers' => [
                              'Authorization' => 'Bearer '.$accessToken,
                              'Accept' => "application/hal+json"
                              ],
                              'json' => []
                          ]);
                          $responseItems = json_decode($request->getBody());

                          foreach($responseItems->readOnly->allocations as $allocation){
                            //check local db if lot number is empty
                            if (!isset($allocation->detail->itemTraits->lotNumber) && isset($allocation->detail->itemTraits->itemIdentifier->sku) && isset($allocation->detail->itemTraits->serialNumber)) {
                              $result = BatchesItems::select('return_tracking')
                                                    ->where('master_kit_id', $allocation->detail->itemTraits->serialNumber)
                                                    ->where('skus.sku', $allocation->detail->itemTraits->itemIdentifier->sku)
                                                    ->join('batches', 'batches_items.batch_id', '=', 'batches.id')
                                                    ->join('skus', 'batches.sku', '=', 'skus.id')
                                                    ->first();
                              if (is_object($result)) {
                                $allocation->detail->itemTraits->lotNumber = $result->return_tracking;
                              }
                            }

                            $foundItems = true;
                            $dateStr = explode('T',$o->processDate);
                            $shipDate = Carbon::parse($dateStr[0])->format('Y/m/d');
                            $reportData[] = array(
                                 $name,
                                 (isset($allocation->detail->itemTraits->serialNumber) ? $allocation->detail->itemTraits->serialNumber:'N/A'),
                                 (isset($allocation->detail->itemTraits->serialNumber) ? $allocation->detail->itemTraits->serialNumber:'N/A'),
                                 (isset($allocation->detail->itemTraits->lotNumber) ? $allocation->detail->itemTraits->lotNumber:'N/A'),
                                 $transId,
                                 $shipDate,
                                 $refNum,
                                 $shipToName,
                                 $address,
                                 $city,
                                 $state,
                                 $zip,
                                 $country,
                                $allocation->detail->itemTraits->itemIdentifier->sku,
                                $allocation->qty,
                                $trackingNo,
                                $carrier,
                                $retailerId,
                                $dept,
                                $poNum
                            );
                          }

                        }

                        if (!$foundItems) {
                          foreach($o->packages as $package){
                              if(count($package->packageContents) > 0){
                                  $foundItems = true;
                                  foreach($package->packageContents as $key=>$value){
                                      //check local db if lot number is empty
                                      if (!isset($value->lotNumber) && isset($itemObject[0]->itemIdentifier->sku) && isset($value->serialNumber)) {
                                        $result = BatchesItems::select('return_tracking')
                                                              ->where('master_kit_id', $value->serialNumber)
                                                              ->where('skus.sku', $itemObject[0]->itemIdentifier->sku)
                                                              ->join('batches', 'batches_items.batch_id', '=', 'batches.id')
                                                              ->join('skus', 'batches.sku', '=', 'skus.id')
                                                              ->first();
                                        if (is_object($result)) {
                                          $value->lotNumber = $result->return_tracking;
                                        }
                                      }

                                      $dateStr = explode('T',$o->processDate);
                                      $shipDate = Carbon::parse($dateStr[0])->format('Y/m/d');
                                      $reportData[] = array(
                                           $name,
                                           (isset($value->serialNumber) ? $value->serialNumber:'N/A'),
                                           (isset($value->serialNumber) ? $value->serialNumber:'N/A'),
                                           (isset($value->lotNumber) ?  $value->lotNumber:'N/A'),
                                           $transId,
                                           $shipDate,
                                           $refNum,
                                           $shipToName,
                                           $address,
                                           $city,
                                           $state,
                                           $zip,
                                           $country,
                                          ($itemObject ? $itemObject[0]->itemIdentifier->sku:'N/A'),
                                          $value->qty,
                                          $trackingNo,
                                          $carrier,
                                          $retailerId,
                                          $dept,
                                          $poNum
                                      );
                                  }
                              }
                          }
                        }

                        if (!$foundItems) {
                          if (isset($order->{'_embedded'})) {
                            $lineItems = $order->{'_embedded'}->{'http://api.3plCentral.com/rels/orders/item'};
                          } else {
                            $lineItems = [];
                          }
                          $foundItems = false;
                          foreach($lineItems as $item) {
                            if ($item->qty <= 0)
                              continue;

                            $dateStr = explode('T',$o->processDate);
                            $shipDate = Carbon::parse($dateStr[0])->format('Y/m/d');
                            $reportData[] = array(
                                 $name,
                                 '',
                                 '',
                                 '',
                                 $transId,
                                 $shipDate,
                                 $refNum,
                                 $shipToName,
                                 $address,
                                 $city,
                                 $state,
                                 $zip,
                                 $country,
                                ($itemObject ? $item->itemIdentifier->sku:'N/A'),
                                $item->qty,
                                $trackingNo,
                                $carrier,
                                $retailerId,
                                $dept,
                                $poNum
                            );
                          }
                        }


                      //}
                  }

                  $initial++;
                }
                // die();
            } catch (\Exception $e) {
                \Log::info($e);
                print $e->getMessage();
                exit;
            }

            if(isset($reportData)){

                if($requestType == 'generate'){
                    return view('layouts/thirdparty/thirdpartyreport',compact('reportData'));
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

            return redirect()->route('thirdparty_report');
        }

        return redirect()->route('dashboard');
    }

}
