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

class LogiwaShopifyCancelOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'LogiwaShopifyIntegration:cancelOrders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull cancelled orders from shopify and push to Logiwa';

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
        // 24 hours ago in EST
        $previous_day = Carbon::now()->subDays(1);
        $previous_day->setTimezone('America/New_York');
        $previous_day_str = $previous_day->format('Y-m-d\TH:i:s');

        $cancelled_order_ids = [];

        $orderIDs=array();
        ///fetch all shopify config
        $com_configs=ShopifyIntegrations::where('integration_status',1)->get();

        if($com_configs){
            foreach($com_configs as $com_conf){
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
                        'status' => 'cancelled',
                        'fulfillment_status' => 'null',
                        'fields' => 'id,line_items,name,total_price',
                        'cancelled_at' => $previous_day_str
                    );
                    try{
                        $shopify_orders = $shopify->Order->get($params);
                        //populate orders from shopify
                        foreach($shopify_orders as $key=>$value){

                            // Get data on database where order has not been cancelled
                            $spectrum_order = ShopifyIntegrationOrders::where('shopify_internal_order_id',$value['id'])
                                                                      ->where('shopify_integration_id', $com_conf->id)
                                                                      ->where('status_shopify','<>','cancelled')
                                                                      ->first();
                            // Cancel 3pl Order and update data
                            if($spectrum_order){
                                $cancelLogiwa = $this->cancelLogiwaOrder($spectrum_order->tpl_order_id,$spectrum_order->shopify_order_id,$spectrum_order->shopify_internal_order_id,$com_conf->logiwa_depositor_code);
                                if($cancelLogiwa){
                                    $cancelled_order_ids[] = $spectrum_order->shopify_internal_order_id;
                                    $spectrum_order->status_shopify = 'cancelled';
                                    $spectrum_order->status_3pl = 'cancelled';
                                    $spectrum_order->save();
                                }
                            }
                        }



                        print "Orders Cancelled:".json_encode($cancelled_order_ids);
                    }
                    catch(\PHPShopify\Exception\ApiException $e) {
                        //send email of error
                        $data = new \stdClass();
                        $data->sender = 'Spectrum';
                        $data->error_message= $e->getMessage();
                        // Mail::to(env('SHOPIFY_ERROR'))->send(new ShopifyErrorReport($data));
                        \Log::info($e);
                    }

                }
                catch(\PHPShopify\Exception\ApiException $e)  {
                     print $e->getMessage();//send email of error
                     $data = new \stdClass();
                     $data->sender = 'Spectrum';
                     $data->error_message= $e->getMessage();
                    //  Mail::to(env('SHOPIFY_ERROR'))->send(new ShopifyErrorReport($data));
                    \Log::info($e);
                }
            }
        }
    }

    /**
     * Cancels Logiwa order
     *
     * @param $id int
     *  - logiwa order id of the order that needs to be cancelled
     * @param $refnum int
     *  - shopify order id
     * @param $shopify_id int
     *  - shopify internal order id
     *
     * @return bool
     */
    private function cancelLogiwaOrder($id,$refnum,$shopify_id,$depositor_code) {
        // Retrieve order
        // Cancel order
        try {
            $body['WarehouseID'] = env('LOGIWA_WAREHOUSE_ID');
            $body['ID'] = $id;
            $body['DepositorCode'] = $depositor_code;
            $logiwa = new LogiwaAPI;
            $request = $logiwa->getWarehouseOrderSearch($body);
            if($request['success'] == true){
                $cancel_request_data = [];
                $data = $request['data']->Data[0];
                // Map order values as they are requiered by Logiwa
                $cancel_request_data = [
                    'ID'=>$id,
                    'WarehouseID'=>env('LOGIWA_WAREHOUSE_ID'),
                    'WarehouseOrderTypeID'=>$data->WarehouseOrderTypeID,
                    'DepositorID' => $data->DepositorID,
                    'InventorySiteID'=> $data->InventorySiteID,
                    'CustomerID'=>$data->CustomerID,
                    'CustomerAddressID'=>$data->CustomerAddressID,
                    'OrderDate'=>$data->OrderDate,
                    'WarehouseOrderStatusID'=>99 // cancelled order status
                ];
                $logiwa = new LogiwaAPI;
                $result = $logiwa->updateOrder($cancel_request_data);
                if($request['success'] == true){
                    return true;
                }
            }
        } catch (\Exception $e) {
            $data = new \stdClass();
            $data->sender = 'Spectrum';
            $data->error_message= $e->getMessage();
            $data->refnum = $refnum;
            $data->shopify_id = $shopify_id;
            // Add exception for already cancelled orders
            if(isset($responseBodyAsArray)){
                if($responseBodyAsArray->ErrorCode == 'OrderConfirmed'){
                    return true;
                }
            }
            // Mail::to(env('SHOPIFY_ERROR'))->send(new ShopifyErrorReport($data));
            \Log::info($e->getMessage());
            return false;
        }
        return false;
    }
}
