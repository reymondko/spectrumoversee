<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Libraries\SecureWMS\SecureWMS;
use App\Models\ApiTokens;
use App\Models\TplShipperAddress;
use GuzzleHttp\Exception\GuzzleException;
use Guzzle\Http\Exception\ClientException;
use GuzzleHttp\Client;
use App\Models\Companies;

class ApiProxyController extends Controller
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

    private static function checkFulfill($companyid,$customerIdentifier){
        if($companyid){
            $verify = Companies::where('id',$companyid)->first();
            
            if($verify['fulfillment_ids']){
                $ids = explode(',',$verify['fulfillment_ids']);
                foreach($ids as $id){
                    if($id == $customerIdentifier){
                        return $verify->id;
                    }
                }
                return false;
            }
        }
        return false;
    }
    public function createOrder(Request $request){
        /* POSTMAN Authorization Key:key, value: api key from spectrum */  
        $header = $request->header('key');
        //check if api key exist
        $companies_id = self::AuthApiKey($header);
        if($companies_id == false){
            \Log::info('=============  Auth Key Failed  ==============');
            return response()->json($this->errNotUnAuthorized,403);
        }
        else{
            try {
                $wms = new SecureWMS();
                $orderdata = file_get_contents("php://input");
                $check=json_decode($orderdata);
                $fulfill = self::checkFulfill($companies_id,$check->customerIdentifier->id);
                if($fulfill == false){
                    \Log::info('=============  customerIdentifier not Authorized  ==============');
                    return response()->json($this->errNotUnAuthorized,403);
                }
                $response = $wms->sendOrderPostRequest('/orders', json_decode($orderdata), 'POST');
                
                return response()->json($response,200);
            
            } catch (\Exception $e) {
                \Log::info($e);
                return response()->json(json_decode($e->getMessage(), true), 500);
            }
        }
    }

    public function retrieveOrder(Request $request, $id){
        /* POSTMAN Authorization Key:key, value: api key from spectrum */ 
        /* postman body = 3pl_order_id */ 
        $header = $request->header('key');
        //check if api key exist
        $companies_id = self::AuthApiKey($header);
        if($companies_id == false){
            \Log::info('=============  Auth Key Failed  ==============');
            return response()->json($this->errNotUnAuthorized,403);
        }
        else{
            try {
                $wms = new SecureWMS();
                // $orderid = file_get_contents("php://input");
                //checker if user has
                $detail = $request->query('detail');
                $response = $wms->sendRequestProxy("/orders/$id", $detail);
                
                $fulfillment_id = $response->readOnly->customerIdentifier->id;
                
                //check if auth key has permision to view this order
                $fulfill = self::checkFulfill($companies_id,$fulfillment_id);
                if($fulfill == false){
                    \Log::info('=============  customerIdentifier not Authorized  ==============');
                    return response()->json($this->errNotUnAuthorized,403);
                }
                return response()->json($response,200);;
            
            } catch (\Exception $e) {
                \Log::info($e);
                return response()->json(json_decode($e->getMessage(), true), 500);
            }
        }     
    }

    // for multiple orders
    public function retrieveOrders(Request $request){
        /* POSTMAN Authorization Key:key, value: api key from spectrum */ 
        /* postman body = 3pl_order_id */ 
        $header = $request->header('key');
        //check if api key exist
        $companies_id = self::AuthApiKey($header);
        if($companies_id == false){
            \Log::info('=============  Auth Key Failed  ==============');
            return response()->json($this->errNotUnAuthorized,403);
        }
        else{
            $companies = Companies::where('id',$companies_id)->first();
            if($companies->fulfillment_ids){
                try {
                    $wms = new SecureWMS();
                    // $orderid = file_get_contents("php://input");   
                    //check if more that 1 fulfillmentids
                    $ful_arr=explode(",",$companies->fulfillment_ids);
                     if(count($ful_arr) > 1){
                        $add = "=in=(".$companies->fulfillment_ids.")";
                    }
                    else{
                        $add = "==".$companies->fulfillment_ids;
                    }
                    //set default customerIdentifier based on auth key
                    $rql_string = "readonly.customerIdentifier.id".$add;
                    $details = $request->query();
                    /* check if rql has customerIdentifier*/
                   
                    if(isset($details['rql'])){
                        if (strpos($details['rql'], 'readonly.customerIdentifier.id')!== false){
                            $rql_arr=explode(";",$details['rql']);
                            foreach($rql_arr as $value){
                                if (strpos($value, 'readonly.customerIdentifier.id')!== false){}
                                else{
                                    $rql_string.=";".$value;
                                }
                            }
                        }
                    }
                    //assign new rql string
                    $details['rql']=$rql_string;
                    //echo $params;die();
                    $response = $wms->sendRequestProxy("/orders", $details, $method = "GET", $returnHeaders = false, $additionalHeaders = null);
                    
                    return response()->json($response,200);
                    
                } catch (\Exception $e) {
                    \Log::info($e);
                    return response()->json(json_decode($e->getMessage(), true), 500);
                }
            }
            else{
                return response()->json($this->errNotFound,403);
            }
        }
    }

    public function retrieveOrderItems(Request $request, $id){
        /* POSTMAN Authorization Key:key, value: api key from spectrum */ 
        /* postman body = 3pl_order_id */ 
        $header = $request->header('key');
        //check if api key exist
        $companies_id = self::AuthApiKey($header);
        if($companies_id == false){
            \Log::info('=============  Auth Key Failed  ==============');
            return response()->json($this->errNotUnAuthorized,403);
        }
        else{
            try {
                $wms = new SecureWMS();
                // $orderid = file_get_contents("php://input");
                $detail = $request->query('detail');
                
                //check if auth key has permission for customerIdentifier
                $response = $wms->sendRequestProxy("/orders/$id", $detail);                
                $fulfillment_id = $response->readOnly->customerIdentifier->id;                
                $fulfill = self::checkFulfill($companies_id,$fulfillment_id);
                if($fulfill == false){
                    \Log::info('=============  customerIdentifier not Authorized  ==============');
                    return response()->json($this->errNotUnAuthorized,403);
                }

                //fetch the order items details
                $details['detail']="All";
                $response = $wms->sendRequestProxy("/orders/$id/items", $details);
                
                return response()->json($response,200);
            
            } catch (\Exception $e) {
                \Log::info($e);
                return response()->json(json_decode($e->getMessage(), true), 500);
            }
        }
    }

    /* Confirm an Order */
    /* POSTMAN Authorization Key:key, value: api key from spectrum */ 
    /* postman body form-data order_id = 3pl_order_id, customer_id = customeridentifierID */ 
    public function confirmOrder(Request $request, $id){
        $response = array(
            "status" =>"err",
        );
       
        $header = $request->header('key');
        //check if api key exist
        $companies_id = self::AuthApiKey($header);
        
        if($companies_id == false){
            \Log::info('=============  Auth Key Failed  ==============');
            return response()->json($this->errNotUnAuthorized,403);
        }
        else{
            $wms = new SecureWMS();
            
            $detail = $request->query('detail');
            $itemdetail = $request->query('itemdetail');
            $details="detail=$detail&itemdetail=$itemdetail";
            $response = $wms->sendRequestProxy("/orders/$id", $details);
            
            $fulfillment_id = $response->readOnly->customerIdentifier->id;
            
            $fulfill = self::checkFulfill($companies_id,$fulfillment_id);
            if($fulfill == false){
                \Log::info('=============  customerIdentifier not Authorized  ==============');
                return response()->json($this->errNotUnAuthorized,403);
            }

            $this->wms = new SecureWMS();
            $order_id = $id; //file_get_contents("php://input");
            
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
                    'rql' => 'readonly.OrderId=='.$id
                ];
                $response = $this->wms->sendRequestProxy('/orders', $parameters, 'GET');
                
                $orders = $response->{'_embedded'}->{'http://api.3plCentral.com/rels/orders/order'};

                if (isset($orders[0])) {
                    $tpl_company_id = $orders[0]->readOnly->customerIdentifier->id;

                    //get the etag
                    $response = $this->wms->sendRequestProxy("/orders/$id", $parameters, 'GET', true);
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
                    $response = $this->wms->sendRequestProxy("/orders/$id/confirmer", $confirmer, 'POST', false, ['If-Match' => $eTag]);
                    
                    return response()->json($response,200);
                }
            }  catch  (\Exception $e) {
                $response = $e->getMessage();
                $response = json_decode($response, true);                
                return response()->json($response,500);
            }
        }     
    }

}
