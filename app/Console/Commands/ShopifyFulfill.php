<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PHPShopify\ShopifySDK;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use App\Models\ShopifyIntegrations;
use App\Models\ShopifyIntegrationOrders;
use App\Models\ShopifyOrderItems;
use Illuminate\Support\Facades\Log;
use Mail;
use App\Mail\ShopifyErrorReport;
use DB;

class ShopifyFulfill extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ShopifyIntegration:fulfillOrders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check orders in 3pl  and if close fulfill on shopify';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $orderIDs=array();
        /* fetch orders status not closed */
        $tpl_orders = ShopifyIntegrationOrders::whereNotNull('tpl_order_id')
                      ->whereNotIn('status_3pl', ['closed', 'cancelled', 'unpaid'])
                      ->leftjoin('shopify_integrations','shopify_integrations.id',"=",'shopify_integration_orders.shopify_integration_id')
                      ->get(['shopify_integration_orders.*','shopify_integrations.companies_id','shopify_integrations.tpl_customer_id']);

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
                        $updateorder->status_3pl = "closed";
                        $updateorder->tpl_tracking_number = $tracking_number;

                        //fetch shopify config of company
                        //$com_conf=ShopifyIntegrations::where('companies_id',$orders->companies_id)->first();
                        $com_conf=ShopifyIntegrations::find($orders->shopify_integration_id);

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
                                //echo "shopify connection error";
                                //send email error
                                $data = new \stdClass();
                                $data->sender = 'Spectrum';
                                $data->error_message= $e->getMessage();
                                Mail::to(env('SHOPIFY_ERROR'))->send(new ShopifyErrorReport($data));
                                Log::channel('tpl_error_log')->info($e);
                            }

                            //get location
                            $locations = $shopify->Location()->get();
                            $location_id = $locations[0]['id'];

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
                                //"fulfillment" => array(
                                    "location_id" => (int)$location_id,
                                    "tracking_number" => $tracking_number,
                                    //"line_items" => $lineItems,
                                    "notify_customer" => (preg_match("/viome/", $com_conf->shopify_url) ? true : false),
                                  //)
                            );
                            //dd( $updateInfo );
                            try{
                                //check if fullfillmet already exist
                                //changed $orders->shopify_order_id to $orders->shopify_internal_order_id
                                $checkfullfillment = $shopify->Order((string)$orders->shopify_internal_order_id)->Fulfillment()->get();
                                if(empty($checkfullfillment)){
                                    $orderDetail=$shopify->Order($orders->shopify_internal_order_id)->get();
                                    if ($orderDetail['financial_status'] != 'paid') {
                                      //order not paid and fulfillment info can't be updated
                                      $orders->status_3pl = 'unpaid';
                                      $orders->save();
                                      continue;
                                    }

                                    $shopify_fulfillz = $shopify->Order((string)$orders->shopify_internal_order_id)->Fulfillment()->post($updateInfo);

                                    //print json_encode($updateInfo);
                                    //dd($shopify_fulfillz);
                                    $fulfillment_id=$shopify_fulfillz['id'];
                                }
                                else{
                                    $fulfillment_id=$checkfullfillment[0]['id'];
                                }
                                //echo "Fulfillment id:".$fulfillment_id;
                                $updateorder->fulfillment_id= $fulfillment_id;
                                $updateorder->status_shopify= "fulfilled";
                                $updateorder->save();

                                //update shopify fulfillment tracking number
                                $updateTracking = array(
                                "tracking_number" => $tracking_number
                                );
                                try{
                                    $fullfill = $shopify->Order((string)$orders->shopify_internal_order_id)->Fulfillment($fulfillment_id)->put($updateTracking);
                                   // dd($fullfill);
                                }
                                catch(\Exception $e) {
                                    //send email error
                                    $data = new \stdClass();
                                    $data->sender = 'Spectrum';
                                    $data->refnum = $orders->shopify_order_id;
                                    $data->shopify_id = $orders->shopify_integration_id;
                                    $data->error_message= $e->getMessage();
                                    Mail::to(env('SHOPIFY_ERROR'))->send(new ShopifyErrorReport($data));
                                    Log::channel('tpl_error_log')->info($e);
                                   // print $e->getMessage();
                                   //echo "error fullfill";
                                   /*$response = $e->getResponse();
                                    $responseBodyAsString = $response->getBody()->getContents();
                                    print "Error: $responseBodyAsString\n";*/
                                }
                                $orderIDs[]=$orders->tpl_order_id;
                            }
                            catch(\Exception $e) {
                                //send email error
                                $data = new \stdClass();
                                $data->sender = 'Spectrum';
                                $data->refnum = $orders->shopify_order_id;
                                $data->shopify_id = $orders->shopify_integration_id;
                                $data->error_message= $e->getMessage();
                                Mail::to(env('SHOPIFY_ERROR'))->send(new ShopifyErrorReport($data));
                                Log::channel('tpl_error_log')->info($e);
                                print "Error 3: ".$e->getMessage();
                               /* $response = $e->getResponse();
                                $responseBodyAsString = $response->getBody()->getContents();
                                print "Error: $responseBodyAsString\n";*/
                            }

                        }
                    }
                }
            }catch (\GuzzleHttp\Exception\BadResponseException $e) {
                \Log::info($e);
                //send error mail order number and  order url
                $response = $e->getMessage();
                //$responseBodyAsString = $response->getBody()->getContents();
                //send email error
                $data = new \stdClass();
                $data->sender = 'Spectrum';
                $data->refnum = $orders->shopify_order_id;
                $data->shopify_id = $orders->shopify_integration_id;
                $data->error_message= $response->getBody()->getContents();
                Mail::to(env('SHOPIFY_ERROR'))->send(new ShopifyErrorReport($data));
                Log::channel('tpl_error_log')->info($e);
                print "Error1: $responseBodyAsString\n";
            } catch (\Exception $e) {
                //send email error
                $data = new \stdClass();
                $data->sender = 'Spectrum';
                $data->refnum = $orders->shopify_order_id;
                $data->shopify_id = $orders->shopify_integration_id;
                $data->error_message= $e->getMessage();
                Mail::to(env('SHOPIFY_ERROR'))->send(new ShopifyErrorReport($data));
                Log::channel('tpl_error_log')->info($e);
                print "Error2: ".$e->getMessage()."\n";
            }

        }
        print "Fulfilled 3pl Orders:".json_encode($orderIDs);
    }
}
