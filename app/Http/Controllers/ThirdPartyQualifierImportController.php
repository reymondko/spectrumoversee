<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Auth;
use Excel;
use Session;


class ThirdPartyQualifierImportController extends Controller
{
    /**
     * @session reference
     * customerItems | Array of Retrieved Customer Item Ids
     * customerId | Int Id of customer to have their Items updated with a qualifier
     * importItemQualifiers | Array of Imported Items
     * accessToken | Int API access Token
     */

     /**
      * Session Methods
      * $request->session()->put('key',$val) //store session
      * $request->session()->get('key') //get session
      * $request->session()->forget('key') //unset session
      */

    public function index(Request $request){
        $request->session()->put('accessToken',$this->getTplAccessToken());
        // dd($request->session()->get('accessToken'));
        $accessToken = $request->session()->get('accessToken');
        return view('layouts.thirdparty.thirdpartyqualifierimport');
    }

    public function import(Request $request){
        if (Gate::allows('admin-only', auth()->user())){

            $request->session()->forget('accessToken');
            $request->session()->forget('importItemQualifiers');
            $request->session()->forget('importCustomerId');

            if($request->session()->has('accessToken') === false){
                $request->session()->put('accessToken',$this->getTplAccessToken());
            }

            $path = $request->file('import_file')->getRealPath();
            $importData = Excel::load($path)->get();

            //verify sheet formatting
            $header = $importData->getHeading();
                if($header[0] == 'sku' && $header[1] == 'qualifier_1'){
                    $request->session()->put('importItemQualifiers',$importData->toArray());
                    $request->session()->put('importCustomerId',$request->customer_id);
                    
                    $result = array(
                        'totalCount' => count($importData),
                        'success' => true
                    );
                }else{
                    $result = array(
                        'success' => false
                    );
                }
                return response()->json($result,200);      
        }
    }

    public function apiImport(Request $request){
        if (Gate::allows('admin-only', auth()->user())){

            if(count(session('importItemQualifiers')) > 0){

                $importData = session('importItemQualifiers');
                $sku = null;
                $importRes = false;
                foreach($importData as $key => $value){
                    $sku = $value['sku'];
                    $importRes = $this->apiImportRequest($value,session('accessToken'),session('importCustomerId'));
                    if($importRes == true){
                        unset($importData[$key]);
                    }else{
                        $request->session()->put('accessToken',$this->getTplAccessToken());
                        $importRes = $this->apiImportRequest($value,session('accessToken'),session('importCustomerId'));
                    }
                    break;
                }

                $request->session()->put('importItemQualifiers',$importData);

                $result = array(
                    'totalCount' =>  count(session('importItemQualifiers')),
                    'sku' => $sku,
                    'import_result' => $importRes,
                    'success' => true,
                    'token' => session('accessToken')
                );
            }else{
                $result = array(
                    'totalCount' => 0,
                    'sku' => $sku,
                    'success' => true
                );
            }
            
            return response()->json($result,200);  
        } 
    }

    private function getTplAccessToken(){
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
           print $e->getMessage();
        }

        return $accessToken;
    }

    private function apiImportRequest($data,$accessToken,$customerId){
        //search for item
        $itemId = null;
        $sku = $data['sku'];
        $sku = str_replace(',','%252C',$sku);
        $apiRequest = 'https://secure-wms.com/customers/'.$customerId.'/items/?rql=sku=='.$sku;
        $client = new Client();
        try {
            $request = $client->request('GET', $apiRequest, [
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
                        if($c->sku == $data['sku']){
                            $itemId = $c->itemId;
                            break;
                        }
                    }
                }
            }
            
        }catch (\Exception $e) {
            //do something
        }

        if($itemId){
            
            $client = new Client();
            try {
                    $request = $client->request('GET', 'https://secure-wms.com/customers/'.$customerId.'/items/'.$itemId, [
                    'headers' => [
                        'Authorization' => 'Bearer '.$accessToken,
                        'Accept' => "application/hal+json"
                    ],
                    'json' => []
                    ]);
                
                    $headers = $request->getHeaders();
                    $etag = $headers['ETag'][0];
                    $response = json_decode($request->getBody());

            } catch (\Exception $e) {
                $response = $e->getResponse();
                $responseBodyAsString = $response->getBody()->getContents();
                print $responseBodyAsString;
            }

            if($response){
                $response->{'_embedded'} = array(
                    "item" => [
                        array('qualifier' => $data['qualifier_1']),
                        array('qualifier' => $data['qualifier_2']),
                        array('qualifier' => $data['qualifier_3']),
                        array('qualifier' => $data['qualifier_4']),
                        array('qualifier' => $data['qualifier_5']),
                        array('qualifier' => $data['qualifier_6']),
                    ]
                );

                $client = new Client();
                try {
                        $request = $client->request('PUT', 'https://secure-wms.com/customers/'.$customerId.'/items/'.$itemId, [
                        'headers' => [
                            'Authorization' => 'Bearer '.$accessToken,
                            'Accept' => "application/hal+json",
                            'Content-Type' => 'application/hal+json; charset=utf-8',
                            'If-Match' => $etag
                        ],
                        'json' => $response
                        ]);
        
                        $response = json_decode($request->getBody());

                    } catch (\Exception $e) {
                        $response = $e->getResponse();
                        $responseBodyAsString = $response->getBody()->getContents();
                        print $responseBodyAsString;
                    }
                if($response){
                    return true;
                }
            }
        }

        return false;
    }

   
}
