<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PHPShopify\ShopifySDK;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use App\Models\ShopifyIntegrations;
use App\Models\ShopifyIntegrationOrders;
use App\Models\ShopifyOrderItems;
use App\Models\Companies;
use App\Models\ShopifyIgnoredSku;
use Mail;
use App\Mail\ShopifyErrorReport;

use Illuminate\Support\Facades\Log;
use DB;

class ShopifyController extends Controller
{
    public function index(){
        $getConfigs=ShopifyIntegrations::with('ignoredSkus')->select('shopify_integrations.*','c.company_name')
            ->join('companies as c', 'companies_id', '=', 'c.id')->get();
        $companies = Companies::get();
        $ctr = 0;
        foreach($getConfigs as $config){
            $current_ignored_skus = [];
            if(isset($config->ignoredSkus)){
                $current_ignored_skus = explode(',',$config->ignoredSkus->skus);
            }
            $getConfigs[$ctr]->ignored_skus = json_encode($current_ignored_skus);
            $ctr++;
        }

        $data = array(
            'getConfigs' => $getConfigs,
            'companies' =>$companies
        );
        return view('layouts.shopify.shopifysettings')->with('data',$data);
    }

    /* add new shopify configuration */
    public function addShopifyConfig(Request $r){
        $conf = new ShopifyIntegrations;
        $conf->companies_id = $_POST['company'];
        $conf->tpl_customer_id = $_POST['tpl_customer_id'];
        $conf->logiwa_depositor_code = $_POST['logiwa_depositor_code'];
        $conf->logiwa_depositor_id = $_POST['logiwa_depositor_id'];
        $conf->shopify_url = $_POST['shopify_url'];
        $conf->shopify_api_key = $_POST['shopify_api_key'];
        $conf->shopify_password = $_POST['shopify_password'];
        $conf->integration_status = $_POST['integration_status'];
        $conf->save();
        return back();
    }
    /* edit new shopify configuration */
    public function editShopifyConfig(Request $r){
        if($r->id_edit){
            $conf = ShopifyIntegrations::where('id',$r->id_edit)->first();
            $conf->companies_id = $_POST['company'];
            $conf->tpl_customer_id = $_POST['tpl_customer_id'];
            $conf->logiwa_depositor_code = $_POST['logiwa_depositor_code'];
            $conf->logiwa_depositor_id = $_POST['logiwa_depositor_id'];
            $conf->shopify_url = $_POST['shopify_url'];
            $conf->shopify_api_key = $_POST['shopify_api_key'];
            $conf->shopify_password = $_POST['shopify_password'];
            $conf->integration_status = $_POST['integration_status'];
            $conf->save();
            return back();
        }
    }

    /* delete shopify configuration */
    public function deleteShopifyConfig(Request $request){
        $delete=ShopifyIntegrations::where('id', $request->conf_id)->first();
        $delete->delete();
        return back();
    }

    public function integrate(){

        ///fetch all shopify config
        $com_configs=ShopifyIntegrations::where('integration_status',1)->get();
        if($com_configs){
            foreach($com_configs as $com_conf){
                $shopify_integration_id=$com_conf->id;
                //initialize shopify settings
                $config = array(
                    'ShopUrl' => $com_conf->shopify_url,
                    'ApiKey' => $com_conf->shopify_api_key,
                    'Password' => $com_conf->shopify_password,
                );
                // connect to shopify

                try{
                    $shopify = new ShopifySDK($config);
                    echo "connected";
                }
                catch(\GuzzleHttp\Exception\BadResponseException $e) {
                    $response = $e->getResponse();
                    $responseBodyAsString = $response->getBody()->getContents();
                    print "Error: $responseBodyAsString\n";
                    echo "not connected";
                }

                //get all orders from shopify

                $params = array(
                    'financial_status' => 'pending',
                    'fields' => 'id,line_items,name,total_price'
                );
                //get all orders from shopify
                $shopify_orders = $shopify->Order->get($params); //dd($shopify_orders);
                //dd($shopify_orders);
                $lineItems = array();
                $tmpLineItems = array();

                //populate orders from shopify
                foreach($shopify_orders as $key=>$value){
                    //$refnum=$value['id'];
                    $shipTo=array();
                    $orderDetail=$shopify->Order($value['id'])->get();
                    $refnum=$orderDetail['order_number'];
                    $shopify_id=$orderDetail['id'];
                    echo $refnum;

                    $data = new \stdClass();
                    $data->sender = 'Spectrum';
                    $data->refnum = $refnum;
                    $data->shopify_id= $shopify_id;
                    Mail::to(env('SHOPIFY_ERROR'))->send(new ShopifyErrorReport($data));

                    dd($orderDetail);
                    die();
                    if(!empty($orderDetail['shipping_address'])){
                        echo "not empty";
                        $shipTo = array(
                            'retailerId' => null,
                            'isQuickLookup' =>  false,
                            'companyName' => null,
                            'name' => $orderDetail['shipping_address']['name'],
                            'address1' => $orderDetail['shipping_address']['address1'],
                            'address2' => null,
                            'city' => $orderDetail['shipping_address']['city'],
                            'state' => $orderDetail['shipping_address']['province'],
                            'zip' => $orderDetail['shipping_address']['zip'],
                            'country' => $orderDetail['shipping_address']['country_code'],
                            'phoneNumber' => null,
                            'emailAddress' => $orderDetail['email'],
                            'dept' => null,
                        );
                    }
                    else{
                        echo "empty";
                    }
                    print_r($shipTo);
                        dd($orderDetail);
                        die();
                    //check if shopify id already exist in the database (meaning was already pulled)
                    $order= ShopifyIntegrationOrders::where('shopify_order_id',"=",$refnum)->first();
                    if(empty($order)){
                        //create new shopify integration order
                        $order = new ShopifyIntegrationOrders;
                        $order->shopify_integration_id = $shopify_integration_id;
                        $order->shopify_order_id = $refnum;
                        $order->status_shopify = 0;
                        $order->save();
                    }

                    //check if 3pl order id is saved (meaning it was already pushed to 3pl)
                    if($order->tpl_order_id==""){
                        //create order_data  for 3pl
                        foreach($value as $key2=>$value2){
                            if($key2=="line_items"){
                                foreach($value2 as $key3=>$value3){
                                    //save shopify line items on shopify_integration_order_items db
                                    $itemorders=new ShopifyOrderItems;
                                    $itemorders->shopify_integration_orders_id = $order->id;
                                    $itemorders->shopify_itemorder_id =  $value3['id'];
                                    $itemorders->variant_id =  $value3['variant_id'];
                                    $itemorders->sku =  $value3['sku'];
                                    $itemorders->quantity =  $value3['quantity'];
                                    $itemorders->save();

                                    $tmp = array(
                                        'readOnly' => array(
                                            'fullyAllocated' => false
                                        ),
                                        'itemIdentifier' => array(
                                            'sku' => $value3['sku']
                                        ),
                                        'qty' => $value3['quantity']
                                    );
                                }
                            }
                            $tmpLineItems[] = $tmp;
                        }


                        if($tmpLineItems){
                            $lineItems = $tmpLineItems;
                        }

                        /* Fetch shopify order Details (Shipping details and email) */
                        $shipTo=array();
                        if(!empty($orderDetail['shipping_address'])){
                            if(empty($orderDetail['shipping_address']['company'])){
                                $company=$orderDetail['shipping_address']['name'];
                            }
                            else{
                                $company=$orderDetail['shipping_address']['company'];
                            }
                            $shipTo = array(
                                'retailerId' => null,
                                'isQuickLookup' =>  false,
                                'companyName' => $company,
                                'name' => $orderDetail['shipping_address']['name'],
                                'address1' => $orderDetail['shipping_address']['address1'],
                                'address2' => $orderDetail['shipping_address']['address2'],
                                'city' => $orderDetail['shipping_address']['city'],
                                'state' => $orderDetail['shipping_address']['province'],
                                'zip' => $orderDetail['shipping_address']['zip'],
                                'country' => $orderDetail['shipping_address']['country_code'],
                                'phoneNumber' => $orderDetail['shipping_address']['phone'],
                                'emailAddress' => $orderDetail['email'],
                                'dept' => null,
                            );
                        }
                        /* end shipping part */
                        $requestArray = array(
                            'customerIdentifier' => array(
                                'name' => "(TEST Account)",
                                'id' => "1"
                            ),
                            'facilityIdentifier' => array(
                                'name' => "Spectrum Solutions",
                                'id' => "1"
                            ),
                            "referenceNum"=>$refnum,
                            "PoNum" =>  null,
                            'warehouseTransactionSourceEnum' => 7,
                            'transactionEntryTypeEnum' => 4,
                            'routingInfo' => array(
                                'isCod' => true,
                                'isInsurance' => false,
                                'carrier' => "USPS",
                                'mode' => "Standard",
                                'account' => null,
                                'shipPointZip' => null,
                            ),
                            'shipTo' => $shipTo,
                            'OrderItems' => $lineItems,
                            'billing' => array(
                                'billingCharges' => [array(
                                    'chargeType' => "3",
                                )]
                            )
                        );
                        $orderdata = json_encode($requestArray);
                        $order->shopify_order_data = $orderdata;
                        $order->shopify_shipTo_data = $shipTo;
                        $order->save();
                        $shopify_integration_order_id=$order->id;
                        /* 3pl part */
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
                        /* create the order to 3pl*/
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
                                $order_date = $responseBody->creationDate;
                                $order_status = $responseBody->status;

                                //save orderid and order date in ShopifyIntegrationOrders
                                $order= ShopifyIntegrationOrders::where("id",$shopify_integration_order_id)->first();
                                $order->tpl_order_id = $orderId;
                                $order->order_date = date("Y-m-d H:i:s",strtotime($order_date));
                                $order->status_3pl = $order_status;
                                $order->save();
                                $data = array(
                                    'status' => 'saved',
                                    'orderId' => $orderId
                                );
                                //return redirect()->route('layouts/shopify')->with('data',$data);
                            }

                        }catch (\GuzzleHttp\Exception\BadResponseException $e) {
                            $response = $e->getResponse();
                            $responseBodyAsString = $response->getBody()->getContents();
                            print "Error: $responseBodyAsString\n";
                            Log::channel('tpl_error_log')->info($e->getResponse()->getBody()->getContents());
                            //return view('layouts/shopify')->with('products',$shopify_orders);

                        }
                    }
                }
            }
                die();

        }


    }

    public function fullfill(){
        /* fetch orders status not closed */
        $tpl_orders=ShopifyIntegrationOrders::whereNotNull('tpl_order_id')->where('status_3pl',"!=","closed")->get();

        // connect to 3pl and check if status is closed
        foreach($tpl_orders as $orders){
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
            try {
                $request = $client->request('GET', 'https://secure-wms.com/orders/'.$orders->tpl_order_id, [
                    'headers' => [
                    'Authorization' => 'Bearer '.$accessToken,
                    'Accept' => "application/hal+json"
                    ],
                    'json' => []
                ]);
                $response = json_decode($request->getBody());
                //dd($response);

                //check if 3pl status is closed
                if($response->readOnly->isClosed == "true"){
                    if(!empty($response->routingInfo->trackingNumber)){
                        // dd($response);
                        $tracking_number = $response->routingInfo->trackingNumber;
                        //echo "<br><br>tracking = ".$tracking_number."<br>";die();
                            //update the shopify_integration_orders database
                        $updateorder=ShopifyIntegrationOrders::where('id',$orders->id)->first();
                        $updateorder->status_3pl="closed";
                        $updateorder->tpl_tracking_number = $tracking_number;

                            //fetch shopify config of company
                        $com_conf=ShopifyIntegrations::where('companies_id',1)->first();

                        if($com_conf){
                            $shopify_integration_id=$com_conf->id;
                            //initialize shopify settings
                            $config = array(
                                'ShopUrl' => $com_conf->shopify_url,
                                'ApiKey' => $com_conf->shopify_api_key,
                                'Password' => $com_conf->shopify_password,
                            );
                            // connect to shopify
                            try{
                                $shopify = new ShopifySDK($config);
                                // $shopify_orders = $shopify->Order->get($params); dd($shopify_orders);
                            }
                            catch(\Exception $e) {
                                echo "shopify connection error";
                            }
                            $lineItems = array();
                            $tmpLineItems=array();
                            // fetch all line items from database
                            $getOrderitems = ShopifyOrderItems::where('shopify_integration_orders_id',"=",$orders->id)->get();
                            //dd($getOrderitems);
                            if($getOrderitems){
                                foreach($getOrderitems as $items){
                                    $tmp = array(
                                        "id" =>$items->shopify_itemorder_id,
                                        "fulfillment_status" => "fulfilled",
                                        "tracking_number" => $tracking_number,
                                    );
                                    //update order items
                                    $updateOrderitems = ShopifyOrderItems::where('id',"=",$items->id)->first();
                                    $updateOrderitems->tracking_number = $tracking_number;
                                    $updateOrderitems->fulfillment_status = "fulfilled";
                                    $updateOrderitems->save();
                                    $tmpLineItems[] = $tmp;
                                }
                            }
                            if($tmpLineItems){
                                $lineItems = $tmpLineItems;
                            }
                            //dd($lineItems);
                            //Update  order in shopify
                            $updateInfo = array (
                                "fulfillment" => array(
                                    "tracking_number" => $tracking_number,
                                    "line_items" => $lineItems
                                    )
                            );
                            //dd( $updateInfo );
                            try{
                                echo $orders->shopify_order_id;
                                //check if fullfillmet already exist
                                $checkfullfillment = $shopify->Order($orders->shopify_order_id)->Fulfillment()->get();
                                if(empty($checkfullfillment)){
                                    echo "pass 1";
                                    $shopify_fulfillz = $shopify->Order($orders->shopify_order_id)->Fulfillment()->post($updateInfo);
                                    $fulfillment_id=$shopify_fulfillz[0]['id'];
                                }
                                else{
                                    echo "      fulfilid   =        ".$checkfullfillment[0]['id'];
                                    $fulfillment_id=$checkfullfillment[0]['id'];

                                    //$shopify_fulfillz = $shopify->Order($orders->shopify_order_id)->Fulfillment($fullfilment_id)->put($updateInfo);
                                }
                                //dd($shopify_fulfillz);
                                $updateorder->status_shopify= "fulfilled";

                                $updateorder->save();


                                //update fulfillment tracking number
                                $updateTracking = array(
                                "tracking_number" => $tracking_number
                                );
                                try{
                                    $fullfill = $shopify->Order($orders->shopify_order_id)->Fulfillment($fulfillment_id)->put($updateTracking);
                                    dd($fullfill);
                                }
                                catch(\Exception $e) {
                                    print $e->getMessage();
                                }
                            }
                            catch(\Exception $e) {
                                print $e->getMessage();
                            }

                        }
                    }
                }

            } catch (\Exception $e) {
                echo "<br>".$order->id. " skipped becaused not closed<br>";
            }

        }
        die();
    }



    /**
     * Save Edit Ignored Skus
     */
    public function saveIgnoredSkus(Request $request){
        $skus = null;
        if($request->ignored_skus){
            $skus = implode(',',$request->ignored_skus);
        }

        $ignored_skus = ShopifyIgnoredSku::where('shopify_integration_id',$request->id_edit_ignored_sku)
                                          ->where('companies_id',$request->company_edit_ignored_sku)
                                          ->first();
        if(!$ignored_skus){
            $ignored_skus = new ShopifyIgnoredSku;
        }

        $ignored_skus->companies_id = $request->company_edit_ignored_sku;
        $ignored_skus->shopify_integration_id = $request->id_edit_ignored_sku;
        $ignored_skus->skus = $skus;
        $ignored_skus->save();

        return redirect()->route('shopify')->with('status','saved');

    }

    /**
     * Retrieves all the skus related to the 3pl id given
     */
    private function getCompanyTplSkus($tpl_customer_id){

        $ctr = 0;
        $skus = array();

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

        // Get customer skus
        try {
            $apiRequestItems = 'https://secure-wms.com/customers/'.$tpl_customer_id.'/items?pgsiz=1&pgnum=1';
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
                    $req = 'https://secure-wms.com/customers/'.$tpl_customer_id.'/items?pgsiz=100&pgnum='.$initial;

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
                                        $skus[] = $c->sku;
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

            return $skus;

        }catch (\Exception $e) {
            //do something
        }

    }


}
