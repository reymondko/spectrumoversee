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
use Carbon\Carbon;
use Mail;
use App\Mail\ShopifyErrorReport;
use DB;

class ShopifyCancelOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ShopifyIntegration:cancelOrders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull cancelled orders from shopify and push to 3pl';

    /**
     * The 3pl access token
     *
     * @var string
     */
    protected $accessToken;

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

        // Get 3pl Access Token
        $this->accessToken = $this->get3plAccessToken();

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
                        'updated_at_min' => $previous_day_str
                    );
                    try{
                        $shopify_orders = $shopify->Order->get($params);
                        //populate orders from shopify
                        foreach($shopify_orders as $key=>$value){

                            // Get data on database where order has not been cancelled
                            $spectrum_order = ShopifyIntegrationOrders::where('shopify_internal_order_id',$value['id'])
                                                                      ->where('shopify_integration_id', $com_conf->id)
                                                                      ->where('status_shopify','<>','cancelled')
                                                                      ->where('status_3pl','<>','cancelled')
                                                                      ->first();

                            // Cancel 3pl Order and update data
                            if($spectrum_order){
                                $cancel3pl = $this->cancel3plOrder($spectrum_order->tpl_order_id,$spectrum_order->shopify_order_id,$spectrum_order->shopify_internal_order_id);
                                if($cancel3pl){
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
                        Mail::to(env('SHOPIFY_ERROR'))->send(new ShopifyErrorReport($data));
                        Log::channel('tpl_error_log')->info($e);
                    }

                }
                catch(\PHPShopify\Exception\ApiException $e)  {
                     print $e->getMessage();//send email of error
                     $data = new \stdClass();
                     $data->sender = 'Spectrum';
                     $data->error_message= $e->getMessage();
                     Mail::to(env('SHOPIFY_ERROR'))->send(new ShopifyErrorReport($data));
                     Log::channel('tpl_error_log')->info($e);
                }
            }
        }
    }

    /**
     * Cancels 3pl order
     *
     * @param $id int
     *  - 3pl order id of the order that needs to be cancelled
     * @param $refnum int
     *  - shopify order id
     * @param $shopify_id int
     *  - shopify internal order id
     *
     * @return bool
     */
    private function cancel3plOrder($id,$refnum,$shopify_id) {
        $client = new Client();
        // Cancel order
        try {
            // Get HTTP precondition
            $request = $client->request('GET', 'https://secure-wms.com/orders/'.$id, [
            'headers' => [
                'Authorization' => 'Bearer '.$this->accessToken,
                'Accept' => "application/hal+json"
            ],
                'json' => []
            ]);
            $headers = $request->getHeaders();
            $etag = $headers['ETag'][0];

            // Cancel Order
            $request = $client->request('POST', 'https://secure-wms.com/orders/'.$id.'/canceler', [
            'headers' => [
                'Authorization' => 'Bearer '.$this->accessToken,
                'Accept' => "application/hal+json",
                'If-Match' => $etag
            ],
                'json' => ['reason' => 'cancelled by customer']
            ]);

            $response = json_decode($request->getBody());
            if($response){
                return true;
            }

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            $responseBodyAsArray = json_decode($responseBodyAsString);
            $statusCode = $response->getStatusCode();
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
            Mail::to(env('SHOPIFY_ERROR'))->send(new ShopifyErrorReport($data));
            Log::channel('tpl_error_log')->info($e);
            return false;
        }
        return false;
    }

    /**
     * Retrieves 3pl access token
     *
     * @return bool
     */
    private function get3plAccessToken(){
        $client = new Client();
        try {
            $request = $client->request('POST', 'https://secure-wms.com/AuthServer/api/Token', [
            'headers' => ['Authorization' => 'Basic Yzc5YWVjNjktNmE4ZC00ZDIyLTg2NmUtZGI3NjQ2ZTFiYTYxOnU1WEMrRTBWQVlZMGtDZnVYZFNtbTFheFhtcW5ZUnA4'],
            'json' => [
                'grant_type' => 'client_credentials',
                'tpl' => '{e55a580d-29d1-43d0-9b7b-448c5602a223}',
                'user_login_id' => '1'
                ]]);
            $response = json_decode($request->getBody());
            return $response->access_token;
        } catch (\Exception $e) {

        }
    }
}
