<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Companies;
use App\Models\Locations;
use App\Models\ApiTokens;
use App\Models\InventoryFields;
use App\Models\Inventory;
use App\Models\HiddenInventoryFields;
use App\Models\InventoryItemScans;
use App\Models\Orders;
use App\Models\Batches;
use App\Models\BatchesItems;
use Carbon\Carbon;
use DB;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use App\Libraries\Logiwa\LogiwaAPI;
use App\Models\LogiwaDepositor;
#use Helper;

class ApiV1Controller extends Controller
{

    private $errNotFound = array("status" => "error","message" => "Not Found");
    private $errNotUnAuthorized = array('status' =>'error','message'=>'unauthorized');
    private $errError = array('status' =>'error','message'=>'error processing request');

    private static function AuthApiKey($key){

        if($key){
            $verify = ApiTokens::where('api_token',$key)->where('enabled',1)->first();
            if($verify){
                return $verify->companies_id;
            }
        }
        return false;
    }

    /**
     * Request method: POST
     * Request parameters: page=(int)
     * @desc gets all inventories
     * @returns JSON
     */
    public function getAllInventory(Request $request){
        //auth
        $companies_id = self::AuthApiKey($request->key);
        if($companies_id == false){
            return response()->json($this->errNotUnAuthorized,403);
        }
        if($request->page){
            $pageSize = 75;
            if($request->page <= 1){
                $recordsToSkip = 0;
            }else{
                $recordsToSkip = ($request->page * $pageSize) - $pageSize;
            }

            $inventoryValues = Inventory::with('latestScan','latestScan.user:id,name')
                                    ->where('companies_id',$companies_id)
                                    ->where('deleted',0)
                                    ->skip($recordsToSkip)
                                    ->take($pageSize)
                                    ->get();
        }else{
            $inventoryValues = Inventory::with('latestScan','latestScan.user:id,name')
                                    ->where('companies_id',$companies_id)
                                    ->where('deleted',0)
                                    ->get();
        }


        if($inventoryValues->count() > 0){
            $response = array(
                "status" =>"ok",
                "result" =>array("total"=>$inventoryValues->count(),"data" => $inventoryValues)
            );
            return response()->json($response,200);
        }else{
            return response()->json($this->errNotFound,200);
        }
    }

    /**
     * Request method: POST
     * Request parameters: barcode_id
     * @desc searches inventory by barcode
     * @returns JSON
     */
    public function searchByBarcode(Request $request){

        //auth
        $companies_id = self::AuthApiKey($request->key);
        if($companies_id == false){
            return response()->json($this->errNotUnAuthorized,403);
        }

        $inventoryValues = Inventory::where('companies_id',$companies_id)
                                    ->where('deleted',0)
                                    ->where('barcode_id',$request->barcode_id)
                                    ->get();
        //get all barcode custom fields
        $customBarcodeField = InventoryFields::where('companies_id',$companies_id)
                                             ->where('is_barcode',1)
                                             ->get();

        if($inventoryValues->count() == 0){
            if($customBarcodeField){
                foreach($customBarcodeField as $c){
                    $inventoryValues = Inventory::where('companies_id',$companies_id)->where('deleted',0);
                    $inventoryValues = $inventoryValues->where('custom_field'.$c->field_number,$request->barcode_id);
                    $inventoryValues =  $inventoryValues->get();
                    if($inventoryValues->count() > 0){
                        break;
                    }
                }
            }
        }

        if($inventoryValues->count() > 0){
            $response = array(
                "status" =>"ok",
                "result" =>$inventoryValues
            );
            return response()->json($response,200);
        }else{
            return response()->json($this->errNotFound,200);
        }
    }


     /**
     * Request method: POST
     * Request parameters: field (sku,barcode_id,created,last_scan_date,last_scan_location,last_scanned_by,custom_fields),
     *                     operator v(is,has,is_not,greater_than,less_than)
     *                     value
     * @desc searches inventory by specified field
     * @returns JSON
     */
    public function searchByCustom(Request $request){

         $errInvalidOperator = array("status" => "error","message" => "Invalid Operator");

        //auth
        $companies_id = self::AuthApiKey($request->key);
        if($companies_id == false){
            return response()->json($this->errNotUnAuthorized,403);
        }

        if(!isset($request->field)){
            return response()->json(array("status" => "error","message" => "'field' is required"),200);
        }

        if(!isset($request->operator)){
            return response()->json(array("status" => "error","message" => "'operator' is required"),200);
        }

        if(!isset($request->value)){
            return response()->json(array("status" => "error","message" => "'value' is required"),200);
        }

        $hasScanFilter = false;
        $scanFilterArray = array();

        $inventoryValues = Inventory::with('latestScan','latestScan.user:id,name')
                                    ->where('companies_id',$companies_id)
                                    ->where('deleted',0);

        if($request->field == 'created_at'){
            $date = Carbon::parse($request->value);
            if($request->operator == 'greater_than'){
                $inventoryValues =  $inventoryValues->where($request->field, '>=',$date->format('Y-m-d'));
            }elseif($request->operator == 'less_than'){
                $inventoryValues =  $inventoryValues->where($request->field, '<=',$date->format('Y-m-d'));
            }else{
                return response()->json($errInvalidOperator,200);
            }
        }elseif($request->field == 'last_scan_date'){
            $hasScanFilter = true;
            $scanFilterArray['last_scan_date']['value'] = $request->value;
            $scanFilterArray['last_scan_date']['type'] = $request->operator;
        }elseif($request->field == 'last_scan_location'){
            $hasScanFilter = true;
            $scanFilterArray['last_scan_location']['value'] = $$request->value;
            $scanFilterArray['last_scan_location']['type'] = $request->operator;
        }elseif($request->field == 'last_scan_by'){
            $hasScanFilter = true;
            $scanFilterArray['last_scan_by']['value'] = $request->value;
            $scanFilterArray['last_scan_by']['type'] = $request->operator;
        }elseif($request->field == 'sku' || $request->field == 'barcode_id'){
            if( $request->operator == 'is'){
                $inventoryValues =  $inventoryValues->where($request->field,$request->value);
            }elseif($request->operator == 'is_not'){
              $inventoryValues =  $inventoryValues->where($request->field,'<>',$request->value);
            }elseif($request->operator == 'has'){
              $inventoryValues =  $inventoryValues->where($request->field,'like','%'.$request->value.'%');
            }else{
                return response()->json($errInvalidOperator,200);
            }
        }else{
            $customField = InventoryFields::where('companies_id',$companies_id)->where('field_name',$request->field)->first();

            if($customField){
                $fieldNumber = 'custom_field'.$customField->field_number;
                if( $request->operator == 'is'){
                    $inventoryValues =  $inventoryValues->where($fieldNumber,$request->value);
                }elseif($request->operator == 'is_not'){
                  $inventoryValues =  $inventoryValues->where($fieldNumber,'<>',$request->value);
                }elseif($request->operator == 'has'){
                  $inventoryValues =  $inventoryValues->where($fieldNumber,'like','%'.$request->value.'%');
                }else{
                    return response()->json($errInvalidOperator,200);
                }
            }else{
                return response()->json(array("status" => "error","message" => "custom field does not exist"),200);
            }
        }

        $inventoryValues = $inventoryValues->get();

        if($inventoryValues && $hasScanFilter){
            $tmpInvValues = array();
            foreach($inventoryValues as $i){
                if($i['latestScan']){
                    $match = 1;
                    if(isset($scanFilterArray['last_scan_date'])){
                        $search_date =Carbon::parse($scanFilterArray['last_scan_date']['value']);

                        if($scanFilterArray['last_scan_date']['type'] == 'greater_than'){
                            if(($i['latestScan']['created_at'] >= $search_date) == false){
                                $match = 0;
                            }
                        }else{
                            if(($i['latestScan']['created_at'] <= $search_date) == false){
                                $match = 0;
                            }
                        }
                    }

                    if(isset($scanFilterArray['last_scan_location'])){
                        if($scanFilterArray['last_scan_location']['type'] == 'is'){
                            if(($i['latestScan']['scanned_location'] == $scanFilterArray['last_scan_location']['value']) == false){
                                $match = 0;
                            }
                        }else{
                            if(($i['latestScan']['scanned_location'] != $scanFilterArray['last_scan_location']['value']) == false){
                                $match = 0;
                            }
                        }
                    }

                    if(isset($scanFilterArray['last_scan_by'])){
                        if($scanFilterArray['last_scan_by']['type'] == 'is'){
                            if(($i['latestScan']['user']['name'] == $scanFilterArray['last_scan_by']['value']) == false){
                                $match = 0;
                            }
                        }else{
                            if(($i['latestScan']['user']['name'] != $scanFilterArray['last_scan_by']['value']) == false){
                                $match = 0;
                            }
                        }
                    }

                    if($match == 1){
                        $tmpInvValues[] = $i;
                    }
                }
            }
            $inventoryValues = $tmpInvValues;

       }

       if(count($inventoryValues) > 0){
            $response = array(
                "status" =>"ok",
                "result" =>array("total"=>count($inventoryValues),"data" => $inventoryValues)
            );
            return response()->json($response,200);
        }

        return response()->json($this->errNotFound,200);

    }

     /**
     * Request method: POST
     * Request parameters: barcode_id,page
     * @desc returns scan history of inventory
     * @returns JSON
     */
     public function getInventoryDetail(Request $request){
        //auth
        $companies_id = self::AuthApiKey($request->key);
        if($companies_id == false){
            return response()->json($this->errNotUnAuthorized,403);
        }

        $inventoryValues = Inventory::where('barcode_id',$request->barcode_id)->where('companies_id',$companies_id)->first();

        if($inventoryValues){

            if($request->page){
                $pageSize = 75;
                if($request->page <= 1){
                    $recordsToSkip = 0;
                }else{
                    $recordsToSkip = ($request->page * $pageSize) - $pageSize;
                }

                $inventoryScans = InventoryItemScans::with('user:id,name')
                                        ->where('inventory_item_id',$inventoryValues->id)
                                        ->orderBy('created_at')
                                        ->skip($recordsToSkip)
                                        ->take($pageSize)
                                        ->get();
            }else{
                $inventoryScans = InventoryItemScans::with('user:id,name')
                                                    ->where('inventory_item_id',$inventoryValues->id)
                                                    ->orderBy('created_at')
                                                    ->get();
            }

            if(count($inventoryScans) > 0){
                $response = array(
                    "status" =>"ok",
                    "result" =>array("page"=>$request->page,"total"=>count($inventoryScans),"data" => $inventoryScans)
                );
                return response()->json($response,200);
            }
        }

        return response()->json($this->errNotFound,200);
     }

    /**
     * Request method: POST
     * Request parameters: barcode_id,page
     * @desc returns orders
     * @returns JSON
     */
    public function getOrders(Request $request){
         //auth
         $companies_id = self::AuthApiKey($request->key);
         if($companies_id == false){
             return response()->json($this->errNotUnAuthorized,403);
         }

         if($request->page){
            $pageSize = 75;
            if($request->page <= 1){
                $recordsToSkip = 0;
            }else{
                $recordsToSkip = ($request->page * $pageSize) - $pageSize;
            }

            $orders = Orders::with('orderItems')
                            ->where('companies_id',$companies_id)
                            ->skip($recordsToSkip)
                            ->take($pageSize)
                            ->get();
        }else{
            $orders = Orders::with('orderItems')
                            ->where('companies_id',$companies_id)
                            ->get();
        }

        if(count($orders) > 0){
            $response = array(
                "status" =>"ok",
                "result" =>array("page"=>$request->page,"total"=>count($orders),"data" => $orders)
            );
            return response()->json($response,200);
        }

         return response()->json($this->errNotFound,200);
    }

     /**
     * Request method: POST
     * Request parameters: location
     * @desc returns total inventories by location
     * @returns JSON
     */
    public function getInventoryTotalByLocation(Request $request){
         //auth
         $companies_id = self::AuthApiKey($request->key);
         if($companies_id == false){
             return response()->json($this->errNotUnAuthorized,403);
         }

         if(!isset($request->location)){
            return response()->json(array("status" => "error","message" => "location is required"),200);
         }

         $inventoryByLocation = DB::select('SELECT t1.id,t1.scanned_location
                        FROM inventory_item_scans t1
                        WHERE t1.id = (SELECT MAX(t2.id)
                        FROM inventory_item_scans t2
                        WHERE t2.inventory_item_id = t1.inventory_item_id)
                        AND `companies_id` = ?
                        AND `scanned_location` = ?
                        ORDER BY `id`
                        DESC', array($companies_id,$request->location) );


            $response = array(
                "status" =>"ok",
                "result" =>array("location"=>$request->location,"total"=>count($inventoryByLocation)));

        return response()->json($response,200);

    }


     /**
     * Request method: POST
     * Request parameters: key
     * @desc returns list of available locations
     * @returns JSON
     */
    public function getAllLocation(Request $request){
        //auth
        $companies_id = self::AuthApiKey($request->key);
        if($companies_id == false){
            return response()->json($this->errNotUnAuthorized,403);
        }

        $locations = Locations::select('id','name')->where('companies_id',$companies_id)->get();

        if(empty($locations)){
           return response()->json(array("status" => "ok","message" => "no locations found"),200);
        }


        $response = array(
            "status" =>"ok",
            "result" =>array("locations"=>$locations));

       return response()->json($response,200);

   }

    /**
     * Request method: POST
     * Request parameters: key
     * @desc returns list of available locations
     * @returns JSON
     */
    public function getAllLocationWithTotalInventory(Request $request){
        //auth
        $companies_id = self::AuthApiKey($request->key);
        if($companies_id == false){
            return response()->json($this->errNotUnAuthorized,403);
        }

        $locations = Locations::select('id','name')->where('companies_id',$companies_id)->withCount('inventory')->get();

        if(empty($locations)){
           return response()->json(array("status" => "ok","message" => "no locations found"),200);
        }


        $response = array(
            "status" =>"ok",
            "result" =>array("locations"=>$locations));

       return response()->json($response,200);

   }

    /**
     * Request method: POST
     * Request parameters: key,tracking number
     * @desc returns shipment tracking status
     * @returns JSON
     */
    public function getTrackStatus(Request $request){
        //auth
        $companies_id = self::AuthApiKey($request->key);
        if($companies_id == false){
            return response()->json($this->errNotUnAuthorized,403);
        }

       return getTrackingStatus($request->tracking_number);

   }

   /**
     * Request method: POST
     * Request parameters: key,master kit id
     * @desc returns kit boxing subkit ids and return tracking
     * @returns JSON
     */
    public function getSubKits(Request $httpRequest){
        //auth
        $companies_id = self::AuthApiKey($httpRequest->key);
        if($companies_id == false){
            return response()->json($this->errNotUnAuthorized,403);
        }

        $kitz = array('kits' => []);

        try {
          if ($httpRequest->has('master_kit_id')) {
            //get subkits by master kit id

            $kits = BatchesItems::where('master_kit_id', $httpRequest->master_kit_id)
                    ->join('batches', 'batches_items.batch_id', '=', 'batches.id')
                    ->join('skus', 'batches.sku', '=', 'skus.id')
                    ->where('companies_id', $companies_id)
            ->get(['subkit_id AS kit_id', 'return_tracking AS return_tracking_number', 'master_kit_id AS master_kit_id'])
            ->toArray();

            $kitz["kits"]=$kits;
          } elseif ($httpRequest->has('order_id')) {
            //get subkits by transaction id

            $companies = Companies::find($companies_id);

            //get master kit id's from
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
              \Log::info($e);
              return response()->json($this->errError, 500);
            }

            //get recent orders
            try {
              $request = $client->request('GET', 'https://secure-wms.com/orders?detail=All&itemdetail=All&pgsiz=100&pgnum=1&rql=readonly.customeridentifier.id=in=('.$companies->fulfillment_ids.');readonly.OrderId=='.$httpRequest->order_id, [
                  'headers' => [
                  'Authorization' => 'Bearer '.$accessToken,
                  'Accept' => "application/hal+json"
                  ],
                  'json' => []
              ]);
              $response = json_decode($request->getBody());
              if (!isset($response->{'_embedded'})) {
                return response()->json(['status' => 'error', 'message' => 'unable to find requested order']);
              }
              $orderz = $response->{'_embedded'}->{'http://api.3plCentral.com/rels/orders/order'};
              foreach ($orderz as $order) {
                  $returnOrder = $order;
              }
            } catch (\Exception $e) {
              \Log::info($e);
              return response()->json($this->errError, 500);
            }

            if (isset($order->{'_embedded'}->{'http://api.3plCentral.com/rels/orders/item'})) {
              $lineItems = $order->{'_embedded'}->{'http://api.3plCentral.com/rels/orders/item'};
              foreach($lineItems as $item) {
                $request = $client->request('GET', 'https://secure-wms.com/orders/'.$order->readOnly->orderId.'/items/'.$item->readOnly->orderItemId.'?detail=AllocationsWithDetail', [
                    'headers' => [
                    'Authorization' => 'Bearer '.$accessToken,
                    'Accept' => "application/hal+json"
                    ],
                    'json' => []
                ]);
                $responseItems = json_decode($request->getBody());

                //loop through the items
                foreach($responseItems->readOnly->allocations as $allocation){
                  $details = $allocation->detail->itemTraits;
                  if (isset($details->serialNumber) && $details->serialNumber != ''){
                    $kits = BatchesItems::where('master_kit_id', $details->serialNumber)
                            ->join('batches', 'batches_items.batch_id', '=', 'batches.id')
                            ->join('skus', 'batches.sku', '=', 'skus.id')
                            ->where('companies_id', $companies_id)
                    ->get(['subkit_id AS kit_id', 'return_tracking AS return_tracking_number', 'master_kit_id AS master_kit_id'])
                    ->toArray();

                    array_push($kitz["kits"], $kits);
                  }
                }
              }
            }
          }

          return response()->json($kitz);
        } catch (\Exception $e) {
          //throw an error
          \Log::info($e);
          return response()->json($this->errError, 500);
        }
   }

   /**
     * Request method: POST
     * Request parameters: key,master kit id
     * @desc returns kit boxing subkit ids and return tracking
     * uses logiwa API if order id is passed as parameter
     * @returns JSON
     */
    public function getLogiwaSubKits(Request $httpRequest){
        //auth
        $companies_id = self::AuthApiKey($httpRequest->key);
        if($companies_id == false){
            return response()->json($this->errNotUnAuthorized,403);
        }

        $kitz = array('kits' => []);

        try {
          if ($httpRequest->has('master_kit_id')) {
            //get subkits by master kit id

            $kits = BatchesItems::where('master_kit_id', $httpRequest->master_kit_id)
                    ->join('batches', 'batches_items.batch_id', '=', 'batches.id')
                    ->join('skus', 'batches.sku', '=', 'skus.id')
                    ->where('companies_id', $companies_id)
            ->get(['subkit_id AS kit_id', 'return_tracking AS return_tracking_number', 'master_kit_id AS master_kit_id'])
            ->toArray();

            $kitz["kits"]=$kits;
          } elseif ($httpRequest->has('order_id')) {
            $order_id = $httpRequest->order_id;
            //get subkits by transaction id
            $companies = Companies::find($companies_id);

            // Get Order Serial Numbers
            $body = [];
            $body['WarehouseID'] = env('LOGIWA_WAREHOUSE_ID');
            $body['WarehouseOrderID'] = $order_id;
            $logiwa = new LogiwaAPI;
            $serial_request = $logiwa->getShipmentInfoSerialSearch($body);

            if(!empty($serial_request['data']->Data)){
                foreach($serial_request['data']->Data as $serial){
                    $kits = BatchesItems::where('master_kit_id', $serial->Serial)
                    ->join('batches', 'batches_items.batch_id', '=', 'batches.id')
                    ->join('skus', 'batches.sku', '=', 'skus.id')
                    ->where('companies_id', $companies_id)
                    ->get(['subkit_id AS kit_id', 'return_tracking AS return_tracking_number', 'master_kit_id AS master_kit_id'])
                    ->toArray();
                    array_push($kitz["kits"], $kits);
                }
            }
          }
          return response()->json($kitz);
        } catch (\Exception $e) {
          //throw an error
          \Log::info($e);
          return response()->json($this->errError, 500);
        }
   }

    /**
     * Retrieve user customer code for logiwa
     */
    public function getDepositors($cid){
        $depositor_array = [];
        $depositors = LogiwaDepositor::where('companies_id',$cid)->get();
        foreach($depositors as $depositor){
            $depositor_array[] = $depositor;
        }
        return $depositor_array;
    }


}
