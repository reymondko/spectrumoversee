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

class ShopifyIntegration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ShopifyIntegration:createOrders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull orders from shopify and push to 3pl';

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
        ///fetch all shopify config
        $com_configs=ShopifyIntegrations::with('ignoredSkus')->where('integration_status',1)->get();
        if($com_configs){
            foreach($com_configs as $com_conf){
                $ignored_skus = array();
                if(isset($com_conf->ignoredSkus)){
                    $ignored_skus = explode(',',strtoupper($com_conf->ignoredSkus->skus));
                }
                $shopify_integration_id=$com_conf->id;
                $tpl_customer_id =$com_conf->tpl_customer_id;
                //initialize shopify settings
                $config = array(
                    'ShopUrl' => $com_conf->shopify_url,
                    'ApiKey' => $com_conf->shopify_api_key,
                    'Password' => $com_conf->shopify_password,
                );
                // connect to shopify
                try{
                    $shopify = new ShopifySDK($config);
                    //get all pending orders from shopify
                    //set parameters
                    $params = array(
                        'fulfillment_status' => 'null',
                        'fields' => 'id,line_items,name,total_price',
                        'financial_status' => 'paid',
                        'status' => 'open',
                        'limit' => 250,
                        'created_at_min' => date('Y-m-d', time()-(86400*2)).'T00:00:00-00:00',
                    );
                    try{
                        $shopify_orders = $shopify->Order->get($params);

                        print "orders: ".count($shopify_orders)."\n";

                        //populate orders from shopify
                        foreach($shopify_orders as $key=>$value){
                            //initialize arrays for line items
                            $lineItems = array();
                            $tmpLineItems = array();

                            //$refnum=$value['id'];
                            $orderDetail=$shopify->Order($value['id'])->get();

                            //if ($orderDetail['order_number'] == '78673') {
                            //  print_r($orderDetail);
                            //  exit;
                            //}

                            $refnum = str_replace('#', '', $orderDetail['name']);

                            print "refnum: $refnum\n";

                            $shopify_id=$orderDetail['id'];
                            //check if shopify id already exist in the database (meaning was already pulled)
                            $order= ShopifyIntegrationOrders::where('shopify_order_id',"=",$refnum)->where('shopify_integration_id', $com_conf->id)->first();
                            if(!is_object($order)){

                                //create new shopify integration order if does not exist in DB
                                $order = new ShopifyIntegrationOrders;
                                $order->shopify_integration_id = $shopify_integration_id;
                                $order->shopify_internal_order_id = $shopify_id;
                                $order->shopify_order_id = $refnum;
                                $order->status_shopify = 0;
                                $order->save();
                            } else {
                              //skip this order if it is already created
                              print "order already created: $refnum\n";
                              continue;
                            }

                            //check if 3pl order id exist (meaning it was already pushed to 3pl)
                            if($order->tpl_order_id==""){
                                if (empty($orderDetail['shipping_address'])){
                                  //see if the customer has an address
                                  if (!empty($orderDetail['customer']['default_address'])) {
                                    $orderDetail['shipping_address'] = $orderDetail['customer']['default_address'];
                                  } else {
                                    //skip order if there is no shipping information
                                    \Log::info('No Shipping Info Found: '.$refnum);
                                    $order->delete();
                                    continue;
                                  }
                                }

                                /* end shopify shipping details */
                                //create order_data  for 3pl
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

                                            //viome international SKU change to GI-INT
                                            if (($value3['sku'] == 'GI' || $value3['sku'] == 'ESSENTIAL') && ($com_conf->tpl_customer_id == 1 || $com_conf->tpl_customer_id == 34) && $orderDetail['shipping_address']['country_code'] != 'US') {
                                              $value3['sku'] = 'GI-INT';
                                            }
                                            if ($value3['sku'] == 'HIS' && ($com_conf->tpl_customer_id == 1 || $com_conf->tpl_customer_id == 34) && $orderDetail['shipping_address']['country_code'] != 'US') {
                                              $value3['sku'] = 'HIS-INT';
                                            }

                                            $tmp = array(
                                                'readOnly' => array(
                                                    'fullyAllocated' => false
                                                ),
                                                'itemIdentifier' => array(
                                                    'sku' => $value3['sku']
                                                ),
                                                'qty' => $value3['quantity']
                                            );
                                            //save shopify line items on shopify_integration_order_items db

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
                                //line items
                                if(count($tmpLineItems) > 0){
                                    $lineItems = $tmpLineItems;
                                }else{
                                    $order->delete();
                                    \Log::info('============= IGNORE ORDER ==============');
                                    \Log::info('IGNORED SHOPIFY ORDER NUMBER: '.$orderDetail['order_number']);
                                    continue;
                                }
                                /* Fetch shopify order Details (Shipping details and email) */
                                $shipTo=array();
                                if(!empty($orderDetail['shipping_address'])){
                                    \Log::info(print_r($orderDetail, true));

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
                                        'state' => $orderDetail['shipping_address']['province_code'],
                                        'zip' => $orderDetail['shipping_address']['zip'],
                                        'country' => $orderDetail['shipping_address']['country_code'],
                                        'phoneNumber' => $orderDetail['shipping_address']['phone'],
                                        'emailAddress' => $orderDetail['email'],
                                        'dept' => null,
                                    );
                                } else {
                                  //skip order if there is no shipping information
                                  \Log::info('No Shipping Info Found: '.$refnum);
                                  $order->delete();
                                  continue;
                                }
                                /* end shipping part */
                                $requestArray = array(
                                    'customerIdentifier' => array(
                                        'id' => $tpl_customer_id
                                    ),
                                    'facilityIdentifier' => array(
                                        'name' => "Spectrum Solutions",
                                        'id' => "1"
                                    ),
                                    "referenceNum" => $refnum,
                                    "PoNum" =>  null,
                                    'warehouseTransactionSourceEnum' => 7,
                                    'transactionEntryTypeEnum' => 4,
                                    'routingInfo' => array(
                                        'isCod' => false,
                                        'isInsurance' => false,
                                        'carrier' => (isset($orderDetail['shipping_lines'][0]['title'])) ? $orderDetail['shipping_lines'][0]['title'] : 'USPS',
                                        'mode' => (isset($orderDetail['shipping_lines'][0]['title'])) ? $orderDetail['shipping_lines'][0]['title'] : 'USPS',
                                        'account' => null,
                                        'shipPointZip' => null,
                                    ),
                                    'Notes' => (isset($orderDetail['note'])) ? $orderDetail['note'] : null,
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
                                    \Log::info(print_r($requestArray, true));

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
                                        $orderIDs[]=$orderId;
                                        //fullfill the order in
                                        //return redirect()->route('layouts/shopify')->with('data',$data);
                                    }

                                }catch (\GuzzleHttp\Exception\BadResponseException $e) {
                                    //send error mail order number and  order url
                                    \Log::info("ERROR HANDLER1 :: ".$refnum);
                                    \Log::info($e);

                                    $data = new \stdClass();
                                    $data->sender = 'Spectrum';
                                    $data->refnum = $refnum;
                                    $data->shopify_id= $shopify_id;
                                    $data->shopify_url = $com_conf->shopify_url;
                                    $data->error_message= $e->getResponse()->getBody()->getContents();
                                    Mail::to(env('SHOPIFY_ERROR'))->send(new ShopifyErrorReport($data));
                                    //$response = $e->getResponse();
                                    //$responseBodyAsString = $response->getBody()->getContents();
                                    //print "Error: $responseBodyAsString\n";
                                    Log::channel('tpl_error_log')->info($e->getResponse()->getBody()->getContents());
                                    //return view('layouts/shopify')->with('orders',$shopify_orders);
                                    // remove the orders in the database
                                    $order = ShopifyIntegrationOrders::where('shopify_order_id', $refnum)->where('shopify_integration_id', $com_conf->id)->first();
                                    $orderId = $order->id;
                                    ShopifyOrderItems::where('shopify_integration_orders_id', $orderId)->delete();
                                    ShopifyIntegrationOrders::where('shopify_order_id', $refnum)->where('shopify_integration_id', $com_conf->id)->delete();
                                } catch (\Exception $e) {
                                  \Log::info("ERROR HANDLER2 :: ".$refnum);
                                  \Log::info($e);
                                }
                            }
                        }
                        print "Orders Created:".json_encode($orderIDs);
                    }
                    catch(\PHPShopify\Exception\ApiException $e) {
                        //send email of error
                        \Log::info("ERROR HANDLER3 :: ".$refnum);
                        \Log::info($e);

                        $data = new \stdClass();
                        $data->sender = 'Spectrum';
                        $data->shopify_url = $com_conf->shopify_url;
                        $data->error_message= $e->getMessage();
                        Mail::to(env('SHOPIFY_ERROR'))->send(new ShopifyErrorReport($data));
                        Log::channel('tpl_error_log')->info($e);
                    } catch (\Exception $e) {
                      \Log::info("ERROR HANDLER4 :: ".$refnum);
                      \Log::info($e);
                    }

                }
                catch(\PHPShopify\Exception\ApiException $e)  {
                     print $e->getMessage();//send email of error
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
