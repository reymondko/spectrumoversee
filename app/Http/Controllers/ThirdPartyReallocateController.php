<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use App\Models\Companies;
use App\Models\ShipPackage;
use App\Models\ShippingCarriers;
use App\Models\ShipPackSubmissions;
use App\Models\ShipRushAddress;
use App\Models\TplShipperAddress;
use App\Models\ShippingAutomationRules;
use App\Models\ShippingPrinter;
use App\Models\ShippingClient;
use App\Libraries\SecureWMS\SecureWMS;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;

class ThirdPartyReallocateController extends Controller
{
    private $etag = null;
    private $accessToken = null;
    private $currentTransactionOrderData = null;
    private $wms = null;
    private $oldReallocations = [];
    private $orderIdOfOldReallocation = null;
    private $orderItemIdOfOldReallocation = null;

    /**
     * Page handler
     * 
     * @return mixed
     */
    public function index(){

        if (
            Gate::allows('can_see_reallocate', auth()->user())
        ) {
            return view('layouts/reallocate/reallocate');
        }else{
            return redirect()->route('dashboard');
        }
    }

    /**
     * Search for order id
     * 
     * @return json
     */
    public function searchByTransactionId(Request $r)
    {
        $company_id = \Auth::user()->companies_id;
        $companies = Companies::select('fulfillment_ids')->where('id',$company_id)->first();
        $customer_id = null;

        if($companies->fulfillment_ids){
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
            //get recent orders
            try {
                $request = $client->request('GET', 'https://secure-wms.com/orders?detail=All&itemdetail=All&pgsiz=100&pgnum=1&rql=readonly.customeridentifier.id=in=('.$companies->fulfillment_ids.');readonly.OrderId=='.$r->transaction_id, [
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
                    $lineItems = $order->{'_embedded'}->{'http://api.3plCentral.com/rels/orders/item'};

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
                            }
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
                               $tmpLineItems[] = array(
                                   'item_id' => $item->readOnly->orderItemId,
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
                                   'new_serial_number' => null
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
                              $tmpLineItems[] = array(
                                  'item_id' => $item->readOnly->orderItemId,
                                  'order_id' => $order->readOnly->orderId,
                                  'sku' => $details->itemIdentifier->sku,
                                  'upc' => $upc,
                                  'description' => $item_desc,
                                  'serial_number' => $serial_number,
                                  'lot_number' => $lot_number,
                                  'expiration' => $expiration,
                                  'qty' => $allocation->qty,
                                  'qty_packed' => 0,
                                  'qty_remaining' => $allocation->qty,
                                  'done' => ($allocation->qty > 0 ? false:true),
                                  'new_serial_number' => null
                              );
                            }

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
        }

        $response = array(
            "status" =>"ok",
            "result" =>['order_details' => $returnOrder,'order_items' => $tmpLineItems]
        );
        return response()->json($response,200);
    }

    /**
     * Save Reallocation
     * 
     * @param Illuminate\Http\Request $request
     * 
     * @return JSON
     */
    public function saveReallocation(Request $request){
        $data = $request->data;
        $result = [];
        $this->defineAccessTokens($data['order_id']);

        $currentReceiverItemId =  $this->getSerialNumberReceiverItemId($data['current_serial_number'],$data['sku']);
        $newReceiverItemId = $this->getSerialNumberReceiverItemId($data['serial_number'],$data['sku']);
        

        if(!$newReceiverItemId){
            $response = array(
                "status" =>"error",
                'error_message' => "Serial Number/Sku Not Found"
            );
            return response()->json($response,200);
        }else{
            // Validate SKU and Deallocate if it exists on another order and is not yet picked
            // Otherwise throw an error
            $validateReceiverItemId = $this->validateSerialNumberUnallocate($data['sku'],$newReceiverItemId);
            if($validateReceiverItemId != 1){
                $response = array(
                    "status" =>"error",
                    'error_message' => $validateReceiverItemId
                );
                return response()->json($response,200);
            }

            
            $this->defineAccessTokens($data['order_id']);
            // Define allocation data
            $proposedAllocations = [];
            // Assign new allocation data
            $proposedAllocations[] = [
                'receiveItemId' => $newReceiverItemId,
                'qty' => 1
            ];
            // Get All Allocations Except for the current; and add the new allocation
            foreach($this->currentTransactionOrderData->{'_embedded'} as $lineItems){
                foreach($lineItems as $lineItem){
                    if(count($lineItem->readOnly->allocations) > 0){
                        foreach($lineItem->readOnly->allocations as $allocation){
                            if($allocation->receiveItemId != $currentReceiverItemId){
                                $proposedAllocations[] = [
                                    'receiveItemId' => $allocation->receiveItemId,
                                    'qty' => 1
                                ];
                            }
                        }
                    }
                }
            }
            
            // Unallocate
            $apiRoute = "https://secure-wms.com/orders/".$data['order_id']."/items/".$data['order_item_id']."/deallocator";
            try{
                $client = new Client();
                $req = $client->request('PUT', $apiRoute, [
                    'headers' => [
                        'Authorization' => 'Bearer '.$this->accessToken,
                        'Accept' => "application/hal+json",
                        'If-Match' => $this->etag
                    ],
                    'json' => []
                ]);
                $response = json_decode($req->getBody());
                if($response){
                    $result['deallocate_response'] = $response;
                }

            }catch(GuzzleHttp\Exception\ClientException $e){
                $response = array(
                    "status" =>"error",
                    'error_message' => "Unallocation Error".$e->getResponse()->getBody()->getContents()
                );
                return response()->json($response,200);
            }

            
            // Need to call precondition again otherwise it seems to cause an error
            $this->defineAccessTokens($data['order_id']);
            // Allocate 
            $apiRoute = "https://secure-wms.com/orders/".$data['order_id']."/items/".$data['order_item_id']."/allocator";
            try{
                $client = new Client();
                $req = $client->request('PUT', $apiRoute, [
                    'headers' => [
                        'Authorization' => 'Bearer '.$this->accessToken,
                        'Accept' => "application/hal+json",
                        'Content-Type' => 'application/hal+json; charset=utf-8',
                        'If-Match' => $this->etag
                    ],
                    'json' => [
                        'proposedAllocations' => $proposedAllocations
                    ]
                ]);

                $response = json_decode($req->getBody());
                if($response){
                    $result['allocate_response'] = $response;
                }
            }catch(GuzzleHttp\Exception\ClientException $e){
                $response = array(
                    "status" =>"error",
                    'error_message' => "Allocation Error".$e->getResponse()->getBody()->getContents()
                );
                return response()->json($response,200);
            }

            // Reallocate previous skus from the deallocated order item if it has any
            if(count($this->oldReallocations) > 0){
                // Need to call precondition again otherwise it seems to cause an error
                $this->defineAccessTokens($this->orderIdOfOldReallocation);
                // Allocate
                $apiRoute = "https://secure-wms.com/orders/".$this->orderIdOfOldReallocation."/items/".$this->orderItemIdOfOldReallocation."/allocator";
                try{
                    $client = new Client();
                    $req = $client->request('PUT', $apiRoute, [
                        'headers' => [
                            'Authorization' => 'Bearer '.$this->accessToken,
                            'Accept' => "application/hal+json",
                            'Content-Type' => 'application/hal+json; charset=utf-8',
                            'If-Match' => $this->etag
                        ],
                        'json' => [
                            'proposedAllocations' => $this->oldReallocations
                        ]
                    ]);

                    $response = json_decode($req->getBody());
                    if($response){
                        $result['allocate_response'] = $response;
                    }
                }catch(GuzzleHttp\Exception\ClientException $e){
                    $response = array(
                        "status" =>"error",
                        'error_message' => "Allocation Error".$e->getResponse()->getBody()->getContents()
                    );
                    return response()->json($response,200);
                }
            }
        }
        
        $response = array(
            "status" =>"success",
            'message' => $result
        );

        return response()->json($response,200);
    }

    /**
     * Main function to retrieve request headers for authentication
     * 
     * @param int $transactionId
     * 
     * @return null
     */
    public function defineAccessTokens($transactionId){
        // Access Token
        $this->accessToken = $this->getTPLAccessToken();
        // Etag
        $this->etag = $this->getEtag($transactionId,$this->accessToken);
    }

    /**
     * Retrieves access token
     * 
     * @return string
     */
    private function getTPLAccessToken()
    {
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
            //  print "Error1:";
         }

         return $accessToken;
    }

    /**
     * Retrieves Etag for HTTP requests
     * 
     * @param int $transactionId
     * @param string $accessToken
     * 
     * @return string
     */
    private function getEtag($transactionId,$accessToken)
    {
        $client = new Client();
        try {
            $req = $client->request('GET', 'https://secure-wms.com/orders/'.$transactionId.'/?detail=OrderItems&itemdetail=All', [
            'headers' => [
                'Authorization' => 'Bearer '.$accessToken,
                'Accept' => "application/hal+json"
            ],
            'json' => []
            ]);
            $headers = $req->getHeaders();
            $etag = $headers['ETag'][0];
            $response = json_decode($req->getBody());
            // Get currrent order data
            $this->currentTransactionOrderData = $response;
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            $errs[] = $responseBodyAsString;
        }

        return $etag;
    }

    /**
     * Retrieves receiver id for the given serial number and sku
     * 
     * @param int $serial_number
     * @param string $sku
     * 
     * @return mixed
     */
    private function getSerialNumberReceiverItemId($serial_number,$sku){
        $client = new Client();
        try {
            $request = $client->request('GET', 'https://secure-wms.com/inventory?pgsiz=3&pgnum=1&rql=serialNumber=='.$serial_number.';itemIdentifier.sku=='.$sku, [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->accessToken,
                    'Accept' => "application/hal+json"
                ],
                'json' => []
            ]);
            $response = json_decode($request->getBody());
            if($response){

            }
            $inventory = $response->{'_embedded'};
            if(isset($inventory->item)){
                if(count($inventory->item) > 0){
                   foreach($inventory->item as $item){
                       return $item->receiveItemId;
                   }
                }
            }
        }
        catch (\Exception $e) {
            return false;
        }
        return false;
    }

    /**
     * Checks open orders containing the receiveitem id and sku
     * 
     * @param string $sku
     * @param int $receiveItemId
     * 
     * @return mixed (1 = true; string = error_message)
     */
    private function validateSerialNumberUnallocate($sku,$receiveItemId){
        $pgnum = 1;
        $pgsize = 1000;
        $pgnumbers = 1;
        $endloop = false;

        $orders = $this->getOpenOrdersBySku($sku,$pgnum,$pgsize);
        // Check if total results if greater than page size
        if($orders->totalResults > $pgsize){
            // Set pagination
            $pgnumbers  = ceil($orders->totalResults/$pgsize);
        }
        if($orders){
            // Loop through orders
            do{
                // Recheck if proceeding to next page
                if($pgnum > 1){
                    $orders = $this->getOpenOrdersBySku($sku,$pgnum,$pgsize);
                }
                foreach($orders->{'_embedded'} as $orders){
                    foreach($orders as $order){
                        foreach($order->{'_embedded'} as $lineItems){
                            foreach($lineItems as $lineItem){
                                if(count($lineItem->readOnly->allocations) > 0){
                                    foreach($lineItem->readOnly->allocations as $allocation){
                                        if($allocation->receiveItemId == $receiveItemId){ //Check for receive item id match
                                            if($allocation->properlyPickedPrimary != 0){
                                                return 'Item has already been picked';
                                            }else{
                                                //Deallocate
                                                $this->defineAccessTokens($order->readOnly->orderId);
                                                $this->orderIdOfOldReallocation = $order->readOnly->orderId;
                                                $this->orderItemIdOfOldReallocation = $lineItem->readOnly->orderItemId;
                                                if(count($lineItem->readOnly->allocations) > 1){
                                                    // Define allocation data
                                                    $this->oldReallocations = [];
                                                    // Get All old allocations the one being removed
                                                    foreach($this->currentTransactionOrderData->{'_embedded'} as $lineItems){
                                                        foreach($lineItems as $lineItem){
                                                            if(count($lineItem->readOnly->allocations) > 0){
                                                                foreach($lineItem->readOnly->allocations as $allocation){
                                                                    if($allocation->receiveItemId != $receiveItemId){
                                                                        $this->oldReallocations[] = [
                                                                            'receiveItemId' => $allocation->receiveItemId,
                                                                            'qty' => 1
                                                                        ];
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }

                                                $apiRoute = "https://secure-wms.com/orders/".$order->readOnly->orderId."/items/".$lineItem->readOnly->orderItemId."/deallocator";
                                                try{
                                                    $client = new Client();
                                                    $req = $client->request('PUT', $apiRoute, [
                                                        'headers' => [
                                                            'Authorization' => 'Bearer '.$this->accessToken,
                                                            'Accept' => "application/hal+json",
                                                            'If-Match' => $this->etag
                                                        ],
                                                        'json' => []
                                                    ]);
                                                    $response = json_decode($req->getBody());
                                                    if($response){
                                                        $result['deallocate_response'] = $response;
                                                    }

                                                    return 1;
        
                                                }catch(GuzzleHttp\Exception\ClientException $e){
                                                    $response = array(
                                                        "status" =>"error",
                                                        'error_message' => "Unallocation Error".$e->getResponse()->getBody()->getContents()
                                                    );
                                                    return response()->json($response,200);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                if($pgnum < $pgnumbers){
                    $pgnum++;
                }else{
                    $endloop = true;
                }
            }while($endloop == false);
        }
        return 1; // assume one as true otherwise return error string 
    }

    /**
     * Gets all open orders by sku
     * 
     * @param string $sku
     * @param int $pgnum
     * @param int $pgsize
     * 
     * @return mixed
     */
    private function getOpenOrdersBySku($sku,$pgnum,$pgsize){
        $client = new Client();
        try {
            $request = $client->request('GET', 'https://secure-wms.com/orders/?skucontains='.$sku.'&pgsiz='.$pgsize.'&pgnum='.$pgnum.'&detail=OrderItems&itemdetail=All&rql=readonly.isclosed==false', [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->accessToken,
                    'Accept' => "application/hal+json"
                ],
                'json' => []
            ]);
            $response = json_decode($request->getBody());
            return $response;
        }
        catch (\Exception $e) {
            return false;
        }
    }
}
