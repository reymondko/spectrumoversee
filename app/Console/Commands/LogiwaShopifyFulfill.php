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
use App\Libraries\Logiwa\LogiwaAPI;

class LogiwaShopifyFulfill extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'LogiwaShopifyIntegration:fulfillOrders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check orders in Logiwa and if shipped fulfill on shopify';

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
        $logiwa_orders = ShopifyIntegrationOrders::whereNotNull('tpl_order_id')
                      ->whereNotNull('shopify_integration_orders.logiwa_depositor_id')
                      ->whereNull('status_3pl')
                      ->leftjoin('shopify_integrations','shopify_integrations.id',"=",'shopify_integration_orders.shopify_integration_id')
                      ->get(['shopify_integration_orders.*','shopify_integrations.companies_id','shopify_integrations.logiwa_depositor_code','shopify_integrations.logiwa_depositor_id']);

        // connect to logiwa and check if status is shipped
        foreach($logiwa_orders as $orders){
            // Prepare order request API
            $body = [];
            $body['WarehouseID'] = env('LOGIWA_WAREHOUSE_ID');
            $body['ID'] = $orders->tpl_order_id;
            $logiwa = new LogiwaAPI;
            $request = $logiwa->getWarehouseOrderSearch($body);

            if(!empty($request['data']->Data[0])){
                $carrier = null;
                if(isset($request['data']->Data[0]->CarrierDescription)){
                    $carrier = $request['data']->Data[0]->CarrierDescription;
                    if(strpos($request['data']->Data[0]->CarrierDescription,'USPS') !== FALSE){
                        $carrier = 'USPS';
                    }
                }
                if($request['data']->Data[0]->WarehouseOrderStatusCode == 'Shipped'){
                    if(!empty($request['data']->Data[0]->CarrierTrackingNumber)){
                        $orderIDs[] = $orders->id;
                        $tracking_number = $request['data']->Data[0]->CarrierTrackingNumber;
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
                                    "tracking_company"=>$carrier,
                                    //"line_items" => $lineItems,
                                    "notify_customer" => false,
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
                                      $orders->status_3pl = 'unpaid';
                                      $orders->save();
                                      continue;
                                    }

                                    $shopify_fulfillz = $shopify->Order((string)$orders->shopify_internal_order_id)->Fulfillment()->post($updateInfo);
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
                            }
                        }
                    }
                }
            }

        }

        print "Fulfilled Logiwa Orders:".json_encode($orderIDs);
    }
}

