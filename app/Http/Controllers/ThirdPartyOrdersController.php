<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use App\Models\Companies;
use App\Models\BatchesItems;
use Illuminate\Support\Facades\Log;
use App\Libraries\SecureWMS\SecureWMS;
use DB;

class ThirdPartyOrdersController extends Controller
{

    public $wms;

    public function index(){

        $companies = Companies::select('fulfillment_ids')->where('id',\Auth::user()->companies_id)->first();
        if($companies->fulfillment_ids){
            $orders  = [];
            $client = new Client();
            /* get access token */
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
              print "Error1:";
            }

           // echo $accessToken."<br>";
            //echo 'https://secure-wms.com/orders?detail=All&pgsiz=100&pgnum=1&rql=readonly.CreationDate=gt='.date('Y-m-d', time()-(86400*5)).'T00:00:00;readonly.customeridentifier.id=in=('.$companies->fulfillment_ids.')&sort=-readOnly.CreationDate';die();

            //get recent orders
            try {
              $request = $client->request('GET', 'https://secure-wms.com/orders?detail=All&pgsiz=100&pgnum=1&rql=readonly.CreationDate=gt='.date('Y-m-d', time()-(86400*5)).'T00:00:00;readonly.customeridentifier.id=in=('.$companies->fulfillment_ids.')&sort=-readOnly.CreationDate', [
                'headers' => [
                  'Authorization' => 'Bearer '.$accessToken,
                  'Accept' => "application/hal+json"
                ],
                'json' => []
              ]);
              $response = json_decode($request->getBody());
              $orderz = $response->{'_embedded'}->{'http://api.3plCentral.com/rels/orders/order'};
              foreach ($orderz as $order) {
                $o = $order->readOnly;
                $orders[] = $order;
              }
            } catch (\Exception $e) {
            //   print "Error2:";
            //   print $e->getMessage();
            }

            #print_r(compact('orders'));die();
            return view('layouts/thirdparty/thirdpartyorders', compact('orders'));
        }
        return redirect()->route('dashboard');
    }

    public function searchOrders(Request $req) {
        $companies = Companies::select('fulfillment_ids')->where('id',\Auth::user()->companies_id)->first();
        if($companies->fulfillment_ids){
            $orders  = [];
            $client = new Client();
            /* get access token */
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
            }
        //echo $accessToken;
        /*
         $searchordernum="";
        $searchrefnum="";
        $searchshipto="";
        if($req->input('search-order-num')!=""){
            $searchordernum='readonly.OrderId=='.$req->input('search-order-num').',';
        }
        if($req->input('search-ref-num')!=""){
            $searchrefnum='ReferenceNum==*'.$req->input('search-ref-num').'*';
        }
        if($req->input('search-ship-to')!=""){

            if($searchrefnum!=""){
                $searchshipto=',';
            }
            $searchshipto.='ShipTo.CompanyName==*'.$req->input('search-ship-to').'*';
        }*/
        $searchz="";
        if(is_numeric($req->input('search'))){
            $searchz.='readonly.OrderId=='.$req->input('search').',';
        }
        $searchz.='ReferenceNum==*'.$req->input('search').'*,ShipTo.CompanyName==*'.$req->input('search').'*';
            //get recent orders
            try {
            $request = $client->request('GET', 'https://secure-wms.com/orders?detail=All&pgsiz=100&pgnum=1&rql=readonly.customeridentifier.id=in=('.$companies->fulfillment_ids.');('.$searchz.')', [
                'headers' => [
                'Authorization' => 'Bearer '.$accessToken,
                'Accept' => "application/hal+json"
                ],
                'json' => []
            ]);

            $response = json_decode($request->getBody());

            $orderz = $response->{'_embedded'}->{'http://api.3plCentral.com/rels/orders/order'};
            #var_dump($orderz);die();
            foreach ($orderz as $order) {
                $o = $order->readOnly;
                $orders[] = $order;
            }
            } catch (\Exception $e) {
            }
            //print_r($orders);
            return view('layouts/thirdparty/thirdpartyorders', compact('orders'))->with('search',$req->search);
        }
        return redirect()->route('dashboard');
    }

    public function filterOrders(Request $req) {

        $startDateQ = null;
        $endDateQ = null;
        $statusQ = null;
        $refQ = null;
        $shipToNameQ = null;
        $search = array();

        if(!isset($req->from_date) &&
           !isset($req->tpl_status) &&
           !isset($req->ref) &&
           !isset($req->ship_to_name)){
            return redirect()->route('thirdparty_orders');
        }

        if(isset($req->from_date) && isset($req->to_date)){
            $startDate = date('Y-m-d',strtotime($req->from_date));
            $endDate = date('Y-m-d',strtotime($req->to_date));

            $search['from_date'] = $req->from_date;
            $search['to_date'] = $req->to_date;


            $startDateQ = 'readonly.CreationDate=gt='.$startDate.'T00:00:00;';
            $endDateQ = 'readonly.CreationDate=lt='.$endDate.'T23:59:59;';
        }

        if(isset($req->ref)){
            $refQ = 'ReferenceNum==*'.$req->ref.'*;';
            $search['ref'] = $req->ref;
        }

        if(isset($req->ship_to_name)){
            $shipToNameQ ='ShipTo.companyName==*'.$req->ship_to_name.'*;';
            $search['ship_to_name'] = $req->ship_to_name;
        }

        if(isset($req->tpl_status)){
            if($req->tpl_status == 1){
                $statusQ = 'readonly.isClosed==false;';
            }else{
                $statusQ = 'readonly.isClosed==true;';
            }

            $search['status'] = $req->tpl_status;
        }

        $companies = Companies::select('fulfillment_ids')->where('id',\Auth::user()->companies_id)->first();
        if($companies->fulfillment_ids){
            $orders  = [];
            $client = new Client();
            /* get access token */
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
            }
            //get recent orders
            try {
                $request = $client->request('GET', 'https://secure-wms.com/orders?detail=All&pgsiz=1000&pgnum=1&rql='.$statusQ.$startDateQ.$endDateQ.$refQ.$shipToNameQ.'readonly.customeridentifier.id=in=('.$companies->fulfillment_ids.')&sort=-readOnly.CreationDate', [
                    'headers' => [
                      'Authorization' => 'Bearer '.$accessToken,
                      'Accept' => "application/hal+json",
                      'Content-Type' => 'application/hal+json; charset=utf-8',
                    ],
                    'json' => []
                  ]);

            $response = json_decode($request->getBody());
            $orderz = $response->{'_embedded'}->{'http://api.3plCentral.com/rels/orders/order'};
            foreach ($orderz as $order) {
                $o = $order->readOnly;
                $orders[] = $order;
            }
            } catch (\Exception $e) {
            }

            return view('layouts/thirdparty/thirdpartyorders', compact('orders'))->with('filter',$search);
        }

        return redirect()->route('dashboard');
    }

    public function orderDetails(Request $request, $id) {
        $companies = Companies::select('fulfillment_ids','can_manual_fulfill')->where('id',\Auth::user()->companies_id)->first();
        if($companies->fulfillment_ids){
            $client = new Client();
            /* get access token */
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
            }

            //get recent orders
            try {
                $request = $client->request('GET', 'https://secure-wms.com/orders?detail=All&itemdetail=All&pgsiz=100&pgnum=1&rql=readonly.customeridentifier.id=in=('.$companies->fulfillment_ids.');readonly.OrderId=='.$id, [
                    'headers' => [
                    'Authorization' => 'Bearer '.$accessToken,
                    'Accept' => "application/hal+json"
                    ],
                    'json' => []
                ]);
            $response = json_decode($request->getBody());
            $orderz = $response->{'_embedded'}->{'http://api.3plCentral.com/rels/orders/order'};
            foreach ($orderz as $order) {
                $returnOrder = $order;
            }
            } catch (\Exception $e) {
                //
            }

            $tmpLineItems = array();
            $lineItems = $order->{'_embedded'}->{'http://api.3plCentral.com/rels/orders/item'};
            foreach($lineItems as $item) {
                    $request = $client->request('GET', 'https://secure-wms.com/inventory?pgsiz=1&pgnum=1&rql=ItemIdentifier.sku=='.$item->itemIdentifier->sku, [
                            'headers' => [
                            'Authorization' => 'Bearer '.$accessToken,
                            'Accept' => "application/hal+json"
                            ],
                            'json' => []
                    ]);
                    $item_desc = 'N/A';
                    $responseInv = json_decode($request->getBody());
                    if (isset($responseInv->{'_embedded'})) {
                      $responseLineItem = $responseInv->{'_embedded'};
                      if(isset($responseLineItem->item[0]->itemDescription))
                        $item_desc = $responseLineItem->item[0]->itemDescription;
                    }

                    $request = $client->request('GET', 'https://secure-wms.com/orders/'.$order->readOnly->orderId.'/items/'.$item->readOnly->orderItemId.'?detail=AllocationsWithDetail', [
                        'headers' => [
                        'Authorization' => 'Bearer '.$accessToken,
                        'Accept' => "application/hal+json"
                        ],
                        'json' => []
                    ]);

                    $responseItems = json_decode($request->getBody());
                    if (count($responseItems->readOnly->allocations) == 0) {
                        if(isset($item->expirationDate)){
                            $expiration = explode('T',$i->expirationDate);
                            $expiration = $expiration[0];
                        }else{
                            $expiration = 'N/A';
                        }
                       $tmpLineItems[] = array(
                           'item_id' => $item->readOnly->orderItemId,
                           'sku' => $item->itemIdentifier->sku,
                           'description' => $item_desc,
                           'serial_number' => null,
                           'lot_number' => null,
                           'expiration' => (isset($item->expirationDate)) ? $item->expirationDate : 'N/A',
                           'qty' => $item->qty,
                           'qty_packed' => 0,
                           'qty_remaining' => $item->qty,
                           'done' => ($item->qty > 0 ? false:true),
                           'subkits' => []
                       );

                    }
                    foreach($responseItems->readOnly->allocations as $allocation){

                      $details = $allocation->detail->itemTraits;

                      $serial_number = 'N/A';
                      $expiration = 'N/A';
                      $lot_number = 'N/A';
                      if(isset($details->serialNumber)){
                          $serial_number =(($details->serialNumber != "") ? $details->serialNumber:'N/A');
                      }
                      if(isset($details->lotNumber)){
                          $lot_number = (($details->lotNumber != "") ? $details->lotNumber:'N/A');
                      }
                      if (isset($details->expirationDate)) {
                          $expiration = date('Y-m-d', strtotime($details->expirationDate));
                      }

                      $tmpLineItems[] = array(
                          'item_id' => $order->readOnly->orderId,
                          'sku' => $details->itemIdentifier->sku,
                          'description' => $item_desc,
                          'serial_number' => $serial_number,
                          'lot_number' => $lot_number,
                          'expiration' => $expiration,
                          'qty' => $allocation->qty,
                          'qty_packed' => 0,
                          'qty_remaining' => $allocation->qty,
                          'done' => ($allocation->qty > 0 ? false:true),
                          'subkits' => $this->getSubkitIds($serial_number)
                      );

//                                   if(isset($responseLineItem->item[0]->expirationDate)){
//                                       $expiration = (isset($responseLineItem->item[0]->expirationDate) ? $responseLineItem->item[0]->expirationDate:'N/A');
//                                   }
                    }
                    //\Log::info(json_decode(json_encode($responseItems), true));
            }
            return view('layouts/thirdparty/thirdpartyordersdetails', ['order' => $returnOrder,'line_items'=>$tmpLineItems,'company'=>$companies]);
        }
        return redirect()->route('dashboard');
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

      public function cancelOrder(Request $request, $id) {
            $companies = Companies::select('fulfillment_ids')->where('id',\Auth::user()->companies_id)->first();
            if($companies->fulfillment_ids){
                $client = new Client();
                /* get access token */
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
                }
                //cancel order
                try {
                    $request = $client->request('GET', 'https://secure-wms.com/orders/'.$id, [
                    'headers' => [
                        'Authorization' => 'Bearer '.$accessToken,
                        'Accept' => "application/hal+json"
                    ],
                    'json' => []
                    ]);
                    $headers = $request->getHeaders();
                    $etag = $headers['ETag'][0];
                    $request = $client->request('POST', 'https://secure-wms.com/orders/'.$id.'/canceler', [
                    'headers' => [
                        'Authorization' => 'Bearer '.$accessToken,
                        'Accept' => "application/hal+json",
                        'If-Match' => $etag
                    ],
                    'json' => ['reason' => 'cancelled by customer']
                    ]);
                    $response = json_decode($request->getBody());
                } catch (GuzzleHttp\Exception\ClientException $e) {
                    $response = $e->getResponse();
                    $responseBodyAsString = $response->getBody()->getContents();
                }
                return redirect('thirdparty/orders/details/'.$id);
            }

            return redirect()->route('dashboard');
      }

      public function createOrder(){
        $companies = Companies::select('fulfillment_ids')->where('id',\Auth::user()->companies_id)->first();

        if($companies){

            $ctr = 0;
            $customerItemData = array();
            $customerItemDataSku = array();
            $customerItemDataCid = array();
            $facilityData = array();
            $customerData = array();
            $customers = array();

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
                print "Error1:";
            }
            /** End get access token **/

            $customerIds = explode(',',$companies->fulfillment_ids);

            foreach($customerIds as $customer){
                $apiRequestItems = 'https://secure-wms.com/customers/'.$customer.'/items?pgsiz=1&pgnum=1';
                $apiRequestCustomer = 'https://secure-wms.com/customers/'.$customer;

                try {
                    $request = $client->request('GET', $apiRequestCustomer, [
                        'headers' => [
                            'Authorization' => 'Bearer '.$accessToken,
                            'Accept' => "application/hal+json"
                        ],
                        'json' => []
                    ]);
                    $response = json_decode($request->getBody());
                    if($response){
                       $facilityData = array(
                            'name' =>  $response->facilities[0]->name,
                            'id' =>  $response->facilities[0]->id,
                       );

                       $customerData = array(
                           'name' => $response->companyInfo->companyName,
                           'customeerId' => $response->readOnly->customerId,
                       );
                    }


                }catch (\Exception $e) {
                    //do something
                }

                //get total of requested items
                try {
                    $request = $client->request('GET', $apiRequestItems, [
                        'headers' => [
                            'Authorization' => 'Bearer '.$accessToken,
                            'Accept' => "application/hal+json"
                        ],
                        'json' => []
                    ]);
                    $response = json_decode($request->getBody());
                    if($response){
                        $totalPages = ceil($response->totalResults/100);
                        $initial = 1;

                        while($initial <= $totalPages){
                            $req = 'https://secure-wms.com/customers/'.$customer.'/items?pgsiz=100&pgnum='.$initial;

                            try {
                                $request = $client->request('GET', $req, [
                                    'headers' => [
                                        'Authorization' => 'Bearer '.$accessToken,
                                        'Accept' => "application/hal+json"
                                    ],
                                    'json' => []
                                ]);

                                $response = json_decode($request->getBody());
                                if($response){
                                    $customerItems = $response->{'_embedded'}->{'http://api.3plCentral.com/rels/customers/item'};
                                    if($customerItems){
                                        foreach($customerItems as $c){

                                            if($c->readOnly->deactivated == false){
                                                $customerItemData[$ctr]['id'] = $c->itemId;
                                                $customerItemData[$ctr]['sku'] = $c->sku;
                                                $customerItemData[$ctr]['description'] = $c->description;
                                                $ctr++;
                                                $customerItemDataSku[$c->sku] = $c->description;
                                                $customerItemDataCid[$c->sku]['customer_id'] = $c->readOnly->customerIdentifier->id;
                                                $customerItemDataCid[$c->sku]['customer_name'] = $c->readOnly->customerIdentifier->name;
                                                $customers[$c->readOnly->customerIdentifier->name] = $c->readOnly->customerIdentifier->id;
                                            }


                                        }
                                    }
                                }
                            }catch (\Exception $e) {
                                    //do something
                            }
                            $initial++;
                        }
                    }

                }catch (\Exception $e) {
                    //do something
                }
            }

            $customers = array_unique($customers);

            return view('layouts/thirdparty/thirdpartycreateorder',compact('customerItemData','facilityData','customerData','customerItemDataSku','customerItemDataCid','customers'));
        }

        return redirect()->route('dashboard');
    }

    public function createOrderSave(Request $request){
        $companies = Companies::select('fulfillment_ids')->where('id',\Auth::user()->companies_id)->first();
        if($companies->fulfillment_ids){

            $lineItems = array();
            $tmpLineItems = array();

            foreach($request->line_item_id as $key=>$value){
                $tmp = array(
                    'readOnly' => array(
                        'fullyAllocated' => false
                    ),
                    'itemIdentifier' => array(
                        'sku' => $request->line_item_sku[$key],
                        'id' => $value
                    ),
                    'qty' => $request->line_item_qty[$key]
                );

                $tmpLineItems[] = $tmp;
            }

            if($tmpLineItems){
                $lineItems = $tmpLineItems;
            }


            $requestArray = array(
                'customerIdentifier' => array(
                    'name' => $request->customer_name,
                    'id' => $request->customer_id
                ),
                'facilityIdentifier' => array(
                    'name' => $request->facility_name,
                    'id' => $request->facility_id
                ),
                "referenceNum"=>$request->ref_number,
                "PoNum" => (strlen($request->po_number)) ? $request->po_number : null,
                'warehouseTransactionSourceEnum' => 7,
                'transactionEntryTypeEnum' => 4,
                'routingInfo' => array(
                    'isCod' => ($request->cod == 1 ? true:false),
                    'isInsurance' => ($request->insurance == 1 ? true:false),
                    'carrier' => ($request->carrier ? $request->carrier:null),
                    'mode' => ($request->service ? $request->service:null),
                    'account' => ($request->account ? $request->account:null),
                    'shipPointZip' => ($request->account_zip ? $request->account_zip:null),
                ),
                'shipTo' => array(
                    'retailerId' => null,
                    'isQuickLookup' =>  ($request->show_quick_lookup == 1 ? true:false),
                    'companyName' => ($request->company ? $request->company:null),
                    'name' => ($request->name ? $request->name:null),
                    'address1' => ($request->address1 ? $request->address1:null),
                    'address2' => ($request->address2 ? $request->address2:null),
                    'city' => ($request->city ? $request->city:null),
                    'state' => ($request->street ? $request->street:null),
                    'zip' => ($request->zip ? $request->zip:null),
                    'country' => ($request->country ? $request->country:null),
                    'phoneNumber' => ($request->phone ? $request->phone:null),
                    'emailAddress' => ($request->ziemailp ? $request->ziemailp:null),
                    'dept' => ($request->dept_no ? $request->dept_no:null),
                ),
                'OrderItems' => $lineItems,
                'billing' => array(
                    'billingCharges' => [array(
                        'chargeType' => ($request->billing ? $request->billing:null),
                    )]
                )
            );
            \Log::info(json_encode($requestArray));
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
                print "Error1:";
            }
            /** End get access token **/
            try {
                $request = $client->request('POST', 'https://secure-wms.com/orders', [
                    'headers' => [
                        'Authorization' => 'Bearer '.$accessToken,
                        'Accept' => "application/hal+json"
                    ],
                    'json' => $requestArray
                ]);

                $response = json_decode($request->getBody());
                if($response){
                    $responseBody = $response->readOnly;
                    $orderId = $responseBody->orderId;

                    $data = array(
                        'status' => 'saved',
                        'orderId' => $orderId
                    );

                    return redirect()->route('thirdparty_orders')->with('data',$data);
                }


            }catch (\GuzzleHttp\Exception\BadResponseException $e) {
                // $response = $e->getResponse();
                // $responseBodyAsString = $response->getBody()->getContents();
                // print "Error: $responseBodyAsString\n";
                Log::channel('tpl_error_log')->info($e->getResponse()->getBody()->getContents());
                return redirect()->route('thirdparty_orders_create')->with('status','error_saving');

            }
        }
        return redirect()->route('dashboard');
    }

    private function formatApiDate($date){
        $formatted = date("Y-m-d", strtotime($date));
        return $formatted.'T00:00:00';
    }

    public function manualFulfillOrder(Request $request, $orderid){


        $companies = Companies::select('fulfillment_ids','can_manual_fulfill')->where('id',\Auth::user()->companies_id)->first();

        if(!$companies){
            return redirect()->back()->with('status', 'error_saving');
        }

        if($companies->can_manual_fulfill != 1){
            return redirect()->back()->with('status', 'error_saving');
        }

        $response = array(
            "status" =>"err",
        );

        $this->wms = new SecureWMS();
        $order_id = $orderid; //file_get_contents("php://input");

        $tpl_company_id = 0;
        $thisOrder = null;
        $apires = array();

        // Confirm and Close the order
        try {
            $parameters = [
                'detail' => 'All',
                'itemdetail' => 'All',
                'pgsiz' => 1,
                'pgnum' => 1,
                'rql' => 'readonly.OrderId=='.$order_id
            ];
            $response = $this->wms->sendRequest('/orders', $parameters, 'GET');

            $orders = $response->{'_embedded'}->{'http://api.3plCentral.com/rels/orders/order'};

            if (isset($orders[0])) {
                $tpl_company_id = $orders[0]->readOnly->customerIdentifier->id;

                //get the etag
                $response = $this->wms->sendRequest("/orders/$order_id", $parameters, 'GET', true);
                $eTag = $response['headers']['ETag'][0];
                //fake tracknumber
                $faketrackingnum = 'FAKE-FULFILL-'.time();
                //order confirmer
                $confirmer = [
                    'OrderConfirmInfo' => [
                    ],
                    'trackingNumber' => $faketrackingnum, //$shipRushRecord['trackingNo'],
                    'recalcAutoCharges' => true,
                    'billing' => ['billingCharges' => [
                        [
                            'chargeType' => 3,
                            'details' => [],
                            //'subtotal' => 0.00 //total shipping cost goes here plus the markup
                        ]
                        ]
                    ]
                ];

                //confirm order
                $response = $this->wms->sendRequest("/orders/$order_id/confirmer", $confirmer, 'POST', false, ['If-Match' => $eTag]);

                return redirect()->back()->with('status', 'saved');


            }
        } catch (\Exception $e) {
            $errs[] = "Error: ".$e->getMessage();
            //print "Error: ".$e->getMessage();
            \Log::info("Errors with Confirming Order - VOID VOID :: ".$request->transaction_id);
            \Log::info($e);
            $response = [];
            $response['message'] = "PLEASE VOID LABEL IN SHIPRUSH - Error while confirming order :: ".$e->getMessage();
            return redirect()->back()->with('status', 'error_saving');
        }

    }
}
