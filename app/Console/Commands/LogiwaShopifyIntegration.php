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
use App\Libraries\Logiwa\LogiwaAPI;
use Mail;
use App\Mail\ShopifyErrorReport;
use DB;
use Carbon\Carbon;


class LogiwaShopifyIntegration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'LogiwaShopifyIntegration:createOrders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull orders from shopify and push to Logiwa';

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
        // Fetch all shopify config
        $com_configs=ShopifyIntegrations::with('ignoredSkus')->where('integration_status',1)->get();
        if($com_configs){
            foreach($com_configs as $com_conf){
                $ignored_skus = array();
                if(isset($com_conf->ignoredSkus)){
                    $ignored_skus = explode(',',strtoupper($com_conf->ignoredSkus->skus));
                }
                $shopify_integration_id=$com_conf->id;
                $tpl_customer_id =$com_conf->tpl_customer_id;
                // Initialize shopify settings
                $config = array(
                    'ShopUrl' => $com_conf->shopify_url,
                    'ApiKey' => $com_conf->shopify_api_key,
                    'Password' => $com_conf->shopify_password,
                );
                // Connect to shopify
                try{
                    $shopify = new ShopifySDK($config);
                    // Get all pending orders from shopify
                    // Set parameters
                    $params = array(
                        'fulfillment_status' => 'null',
                        'fields' => 'id,line_items,name,total_price',
                        'financial_status' => 'paid',
                        'status' => 'open',
                        'created_at_min' => date('Y-m-d', time()-(86400*2)).'T00:00:00-00:00'
                    );

                    try{
                        $shopify_orders = $shopify->Order->get($params);
                        print "orders: ".count($shopify_orders)."\n";
                        // Populate orders from shopify
                        foreach($shopify_orders as $key=>$value){
                            // Initialize arrays for line items
                            $lineItems = array();
                            $tmpLineItems = array();
                            $orderDetail=$shopify->Order($value['id'])->get();
                            $refnum = str_replace('#', '', $orderDetail['name']);
                            print "refnum: $refnum\n";
                            $shopify_id=$orderDetail['id'];

                            // Check if shopify id already exist in the database (meaning was already pulled)
                            $order= ShopifyIntegrationOrders::where('shopify_order_id',"=",$refnum)->where('shopify_integration_id', $com_conf->id)->first();
                            if(!is_object($order)){
                                // Create new shopify integration order if does not exist in DB
                                $order = new ShopifyIntegrationOrders;
                                $order->shopify_integration_id = $shopify_integration_id;
                                $order->shopify_internal_order_id = $shopify_id;
                                $order->shopify_order_id = $refnum;
                                $order->status_shopify = 0;
                                $order->logiwa_depositor_id = $com_conf->logiwa_depositor_id;
                                $order->logiwa_depositor_code = $com_conf->logiwa_depositor_code;
                                $order->save();
                            } else {
                              // Skip this order if it is already created
                              print "order already created: $refnum\n";
                              continue;
                            }

                             // Check if 3pl order id exist
                             // meaning it was already pushed to 3pl
                            if($order->tpl_order_id==""){
                                if (empty($orderDetail['shipping_address'])){
                                    // See if the customer has an address
                                    if (!empty($orderDetail['customer']['default_address'])) {
                                      $orderDetail['shipping_address'] = $orderDetail['customer']['default_address'];
                                    } else {
                                      // Skip order if there is no shipping information
                                      \Log::info('No Shipping Info Found: '.$refnum);
                                      $order->delete();
                                      continue;
                                    }
                                }
                            }

                            // Check if sku is ignored
                            // Skip if true
                            foreach($value as $key2=>$value2){
                                if($key2=="line_items"){
                                    foreach($value2 as $key3=>$value3){
                                        if (empty($value3['sku'])) continue;
                                        // Check if sku is ignored
                                        // Skip if true
                                        if(in_array(strtoupper($value3['sku']),$ignored_skus)){
                                            $ignored_sku = $value3['sku'];
                                            \Log::info('============= IGNORE SKU ==============');
                                            \Log::info('IGNORED SKU: '.$ignored_sku);
                                            \Log::info('IGNORED SHOPIFY ORDER NUMBER: '.$orderDetail['order_number']);
                                            continue;
                                        }

                                        // Viome international SKU change to GI-INT
                                        if (($value3['sku'] == 'GI' || $value3['sku'] == 'ESSENTIAL') && ($com_conf->tpl_customer_id == 1 || $com_conf->tpl_customer_id == 34) && $orderDetail['shipping_address']['country_code'] != 'US') {
                                            $value3['sku'] = 'GI-INT';
                                        }

                                        $tmp = [
                                            'InventoryItem'=>$value3['sku'],
                                            'InventoryItemPackType'=>'EA',
                                            'PlannedPackQuantity'=>$value3['quantity'],
                                        ];

                                        // Save shopify line items on shopify_integration_order_items db
                                        $checkorderitem= ShopifyOrderItems::where('shopify_itemorder_id',"=",$value3['id'])->first();
                                        if(empty($checkorderitem)){
                                            $itemorders=new ShopifyOrderItems;
                                            $itemorders->shopify_integration_orders_id = $order->id;
                                            $itemorders->shopify_itemorder_id =  $value3['id'];
                                            $itemorders->variant_id =  $value3['variant_id'];
                                            $itemorders->sku =  $value3['sku'];
                                            $itemorders->quantity =  $value3['quantity'];
                                            $itemorders->save();
                                        }
                                        $tmpLineItems[] = $tmp;
                                    }
                                }
                            }

                            // Check if line items has quantity
                            // Otherwise Ignore
                            if(count($tmpLineItems) > 0){
                                $lineItems = $tmpLineItems;
                            }else{
                                $order->delete();
                                \Log::info('============= IGNORE ORDER ==============');
                                \Log::info('IGNORED SHOPIFY ORDER NUMBER: '.$orderDetail['order_number']);
                                continue;
                            }


                            // Fetch shopify order Details (Shipping details and email)
                            if(!empty($orderDetail['shipping_address'])){
                                $data = [];
                                $current_date = Carbon::now();
                                // Prepare data for logiwa integration
                                $tmpData = [
                                    'Code'=>$refnum,
                                    'CustomerOrderNo'=>$refnum,
                                    'Depositor'=>$com_conf->logiwa_depositor_code,
                                    'InventorySite'=>'Spectrum Solutions',
                                    'Warehouse' => 'Spectrum Solutions',
                                    'WarehouseOrderType' => 'Customer Order',
                                    'WarehouseOrderStatus' => 'Entered',
                                    'Customer' => $orderDetail['shipping_address']['name'],
                                    'CustomerAddress' =>$orderDetail['shipping_address']['name'],
                                    'OrderDate' => $current_date->format('m.d.Y H:i:s'),
                                    'State' =>$orderDetail['shipping_address']['province_code'],
                                    'Country'=>$orderDetail['shipping_address']['country_code'],
                                    'City'=>$orderDetail['shipping_address']['city'],
                                    'PostalCode' => $orderDetail['shipping_address']['zip'],
                                    'Phone'=>$orderDetail['shipping_address']['phone'],
                                    'Details' => $tmpLineItems,
                                    'AdressText'=>$orderDetail['shipping_address']['address1']
                                ];

                                if($orderDetail['shipping_address']['address2'] != null){
                                    $tmpData['AddressDirections'] = $orderDetail['shipping_address']['address2'];
                                }else{
                                    $tmpData['AddressDirections'] = " ";
                                }

                                $data[] = $tmpData;
                                $logiwa = new LogiwaAPI;
                                $result = $logiwa->insertShipmentOrder($data);

                                $orderdata = json_encode($data);
                                $order->shopify_order_data = $orderdata;
                                if(isset($result["success"])){
                                    if($result["success"] == true){
                                        if(isset($result['data']->Success)){
                                            if($result['data']->Success == true){
                                                $order->save();
                                            }
                                        }
                                    }
                                }

                                // Retrieve order ID
                                $order_data = [
                                    'WarehouseID'=>env('LOGIWA_WAREHOUSE_ID'),
                                    'Code'=>$refnum
                                ];

                                $logiwa = new LogiwaAPI;
                                $result = $logiwa->getWarehouseOrderSearch($order_data);
                                if(isset($result["success"])){
                                    if($result["success"] == true){
                                        if(!empty($result['data']->Data)){
                                            $order->tpl_order_id = $result['data']->Data[0]->ID;
                                            $order->save();
                                        }
                                    }
                                }

                            }else{
                                // Skip order if there is no shipping information
                                \Log::info('No Shipping Info Found: '.$refnum);
                                $order->delete();
                                continue;
                            }
                        }
                    }
                    catch(\PHPShopify\Exception\ApiException $e) {
                        // Send email of error
                        \Log::info("ERROR HANDLER3 :: ");
                        \Log::info($e);
                        $data = new \stdClass();
                        $data->sender = 'Spectrum';
                        $data->shopify_url = $com_conf->shopify_url;
                        $data->error_message= $e->getMessage();
                        // Mail::to(env('SHOPIFY_ERROR'))->send(new ShopifyErrorReport($data));
                        Log::channel('tpl_error_log')->info($e);
                    } catch (\Exception $e) {
                      \Log::info("ERROR HANDLER4 :: ");
                      \Log::info($e);
                    }
                }
                catch(\PHPShopify\Exception\ApiException $e)  {
                    // Send email of error
                    print $e->getMessage();
                    $data = new \stdClass();
                    $data->sender = 'Spectrum';
                    $data->error_message= $e->getMessage();
                    Mail::to(env('SHOPIFY_ERROR'))->send(new ShopifyErrorReport($data));
                    Log::channel('tpl_error_log')->info($e);
                } catch (\Exception $e) {
                  \Log::info("ERROR HANDLER3");
                  \Log::info($e);
                }
            }
        }
    }

}
