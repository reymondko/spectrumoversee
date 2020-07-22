<?php

/**
 * Base class for Logiwa API
 */
namespace App\Libraries\Logiwa;

use Guzzle\Http\Exception\ClientException;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class Logiwa{

    const TOKEN_ERROR = 'LOGIWA_ERROR: ERROR RETRIEVING ACCESS TOKEN - ';
    const LOG_REFRESH = 'REFRESHING TOKEN: ';
    const LOG_ENDPOINT = 'LOGIWA REQUEST ENDPOINT: ';
    const LOG_BODY = 'LOGIWA REQUEST BODY: ';
    const LOG_RESPONSE = 'LOGIWA REQUEST RESPONSE: ';
    const LOGIWA_CACHE_KEY = 'spectrum_logiwa_access_token';

    // Logiwa Access Credentials
    private $user;
    private $password;


    // Logiwa access token
    private $accessToken;

    // Constructor
    public function __construct(){
        $this->user = env('LOGIWA_USERNAME');
        $this->password = env('LOGIWA_PASSWORD');
        $this->accessToken = $this->getAccessToken();
    }

    /**
     * Checks the cache for the acess token
     * If none is found or access token has expired form cache
     * Will retrieve and store a new Access token on the cache
     *
     * @return String
     *  - The access token
     */
    private function getAccessToken(){
        if (Cache::has(self::LOGIWA_CACHE_KEY)) {
            return Cache::get(self::LOGIWA_CACHE_KEY);
        }else{
            return $this->getAccessTokenFromAPI();
        }
    }

    /**
     * Retrieves access token from Logiwa API
     * @documentation - http://developer.logiwa.com/?id=5df0da39e6466c2eec992f3f
     *
     * @return String
     *  - The access token
     */
    private function getAccessTokenFromAPI(){

        $client = new Client([
          'verify' => false
        ]);

        $accessToken = null;
        try{
            $request = $client->request('POST', env('LOGIWA_API_URL') . 'token', [
                'headers' => ['Content-Type'=>'application/x-www-form-urlencoded'],
                'form_params' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'grant_type' => 'password',
                    'username' => $this->user,
                    'password' =>  $this->password
                ]
            ]);
            $response = json_decode($request->getBody());
            $accessToken = $response->access_token;
            // cache access token
            $this->cacheAccessToken($accessToken);
        } catch (\Exception $e) {
            \Log::info(self::TOKEN_ERROR . $e->getMessage());
        }

        return $accessToken;
    }

    /**
     * Store logiwa access token to cache set expiration to 24 hours
     *
     * @param String - $accessToken
     *  - The access token
     *
     */
    private function cacheAccessToken($accessToken){
        Cache::put(self::LOGIWA_CACHE_KEY, $accessToken, 1440);
    }

    /**
     * Creates a POST request to the specified endpoint
     *
     * @param Array - $body
     *  - Request Body
     * @param String - $endpoint
     *  - Request Endpoint
     * @param Array - $headers
     *  - Reqeust Headers
     */
    public function postRequest($body,$endpoint,$headers = []){

        $client = new Client([
          'verify' => false
        ]);

        $result = [
            'success'=>false,
            'data'=>[]
        ];
        $error_code = null;

        if (preg_match("/InsertShipmentOrder/", $endpoint)) {
          \Log::info('Starting InsertShipmentOrder');
        }
        //if (preg_match("/InsertShipmentOrder/", $endpoint)) {
        //  $url = env('LOGIWA_API_URL2');
        //  $headers['Authorization'] = 'Bearer '.env('LOGIWA_API_TOKEN2');
          //\Log::info(self::LOG_ENDPOINT . $endpoint);
          //\Log::info(self::LOG_BODY . json_encode($body));
        //  \Log::info('Starting InsertShipmentOrder');
        //} else {
          $url = env('LOGIWA_API_URL');
          $headers['Authorization'] = 'Bearer '.$this->accessToken;
        //}

        try{
            $request = $client->request('POST', $url . $endpoint, [
                'headers' => $headers,
                'json' => $body
            ]);
            $response = json_decode($request->getBody());
            $success = true;
            if(isset($response->Errors)){
                if(count($response->Errors)>0){
                    $success = false;
                    $response = $response->Errors;
                }
            }
            $result = [
                'success' => true,
                'data' => $response
            ];
        } catch (\Exception $e) {
            $success = false;
            $result['data'] = $e->getMessage();
            $error_code = $e->getCode();
        }

        // Update the token and try the
        // request again if unauthorized
        if($error_code == 401){
            \Log::info(self::LOG_REFRESH);
            $this->accessToken = $this->getAccessTokenFromAPI();
            $headers['Authorization'] = 'Bearer '.$this->accessToken;
            try{
                $request = $client->request('POST', env('LOGIWA_API_URL') . $endpoint, [
                    'headers' => $headers,
                    'json' => $body
                ]);
                $response = json_decode($request->getBody());
                $success = true;
                if(isset($response->Errors)){
                    if(count($response->Errors)>0){
                        $success = false;
                        $response = $response->Errors;
                    }
                }
                $result = [
                    'success' => true,
                    'data' => $response
                ];
            } catch (\Exception $e) {
                $success = false;
                $result['data'] = $e->getMessage();
                $error_code = $e->getCode();
            }

        }

        // Log API response
        if (preg_match("/InsertShipmentOrder/", $endpoint)) {
            \Log::info('Ending InsertShipmentOrder');
            \Log::info(self::LOG_ENDPOINT . $endpoint);
            \Log::info(self::LOG_BODY . json_encode($body));
            \Log::info(self::LOG_RESPONSE . json_encode($result));
        }

        return $result;
    }

}

?>
