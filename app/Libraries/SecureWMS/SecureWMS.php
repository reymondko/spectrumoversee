<?php
namespace App\Libraries\SecureWMS;
use Guzzle\Http\Exception\ClientException;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
class SecureWMS
{
  protected $accessToken = null;
  public function __construct()
  {
    $client = new Client();
    try {
      $request = $client->request('POST', env('WMS_URL').'/AuthServer/api/Token', [
        'headers' => ['Authorization' => 'Basic '.env('WMS_AUTH_TOKEN')],
        'json' => [
            'grant_type' => 'client_credentials',
            'tpl' => '{'.env('WMS_TPL').'}',
            'user_login_id' => '1'
          ]]);
      $response = json_decode($request->getBody());
      $this->accessToken = $response->access_token;
    } catch (\Exception $e) {
      throw new \Exception("Error getting WMS access token: ".$e->getMessage());
    }
  }
  public function sendRequest($endpoint, $parameters = null, $method = "GET", $returnHeaders = false, $additionalHeaders = null)
  {
    $client = new Client();
    try {
      //format GET query string parameters
      $get_parameters = '';
      if ($method == 'GET' && is_array($parameters)) {
        $get_parameters = '?'.http_build_query($parameters);
      } elseif ($method == 'POST' || $method == 'PUT') {
        $get_parameters = '';
      }
      $headers = [
        'Authorization' => 'Bearer '.$this->accessToken,
        'Accept' => "application/hal+json",
        'Content-Type' => "application/hal+json; charset=utf-8",
      ];
      //add additional headers if needed
      if (is_array($additionalHeaders)) {
        $headers = array_merge($headers, $additionalHeaders);
      }
      //send the request using guzzle
      $request = $client->request($method, env('WMS_URL').$endpoint.$get_parameters, [
        'headers' => $headers,
        'json' => ($method == 'POST' || $method == 'PUT') ? $parameters : []
      ]);
      //decode the response
      $body = json_decode($request->getBody());
      if ($returnHeaders) {
        //return decoded response with headers
        return ['body' => $body, 'headers' => $request->getHeaders()];
      } else {
        //return decoded response only
        return $body;
      }
    } catch (\Exception $e) {
      \Log::info("Error message 2");
      \Log::info($e);
      $response = $e->getResponse();
      //var_dump($response->getStatusCode()); // HTTP status code
      //var_dump($response->getReasonPhrase()); // Message
      //var_dump((string) $response->getBody()); // Body
      \Log::info((string) $response->getBody());
      throw new \Exception($e->getMessage());
    }
  }
  public function sendPostRequest($endpoint, $parameters = null, $method = "POST")
  {
    $client = new Client();
    try {
      // print("\n");
      // print_r($this->accessToken);
      // exit();
      //send the request using guzzle
      $request = $client->request($method, env('WMS_URL').$endpoint, [
        'headers' => [
          'Authorization' => 'Bearer '.$this->accessToken,
          'Accept' => "application/hal+json",
          'Content-Type' => "application/hal+json; charset=utf-8",
        ],
        'json' => $parameters
      ]);
      //return the decoded response
      return $response = json_decode($request->getBody());
      // return $response = $request;
    } catch (\GuzzleHttp\Exception\ClientException $e) {
      $response = $e->getResponse();
      $responseBodyAsString = $response->getBody()->getContents();
      \Log::info($responseBodyAsString);
      \Log::info($e);
      throw new \Exception($responseBodyAsString);
    } catch (\Exception $e) {
      \Log::info($e);
      throw new \Exception($e->getMessage());
    }
  }
  public function sendOrderPostRequest($endpoint, $parameters = null, $method = "POST")
  {
    $client = new Client();
    try {
      // print("\n");
      // print_r($this->accessToken);
      // exit();
      //send the request using guzzle
      $request = $client->request($method, env('WMS_URL').$endpoint, [
        'headers' => [
          'Authorization' => 'Bearer '.$this->accessToken,
          'Accept' => "application/hal+json",
          // 'Content-Type' => "application/hal+json; charset=utf-8",
        ],
        'json' => $parameters
      ]);
      //return the decoded response
      return $response = json_decode($request->getBody());
      // return $response = $request;
    } catch (\GuzzleHttp\Exception\ClientException $e) {
      $response = $e->getResponse();
      $responseBodyAsString = $response->getBody()->getContents();
      \Log::info($responseBodyAsString);
      \Log::info($e);
      throw new \Exception($responseBodyAsString);
    } catch (\Exception $e) {
      \Log::info($e);
      throw new \Exception($e->getMessage());
    }
  }
  public function sendPutRequest($endpoint, $parameters = null, $method = "PUT")
  {
    $client = new Client();
    try {
      //send the request using guzzle
      $request = $client->request($method, env('WMS_URL').$endpoint, [
        'headers' => [
          'Authorization' => 'Bearer '.$this->accessToken,
          'Accept' => "application/hal+json",
          'Content-Type' => "application/hal+json; charset=utf-8",
          'If-Match' => $this->getEtag($endpoint)
        ],
        'json' => $parameters
      ]);
      //return the decoded response
      return $response = json_decode($request->getBody());
      // return $response = $request;
    } catch (\Exception $e) {
      \Log::info($e);
      throw new \Exception($e->getMessage());
    }
  }
  private function getEtag($endpoint)
  {
    $client = new Client();
    try {
        $req = $client->request('GET',  env('WMS_URL').$endpoint, [
        'headers' => [
            'Authorization' => 'Bearer '.$this->accessToken,
            'Accept' => "application/hal+json"
        ],
        'json' => []
        ]);
        $headers = $req->getHeaders();
        $etag = $headers['ETag'][0];
    } catch (\GuzzleHttp\Exception\ClientException $e) {
        $response = $e->getResponse();
        $responseBodyAsString = $response->getBody()->getContents();
        $errs[] = $responseBodyAsString;
    }
    return $etag;
  }

  public function sendRequestProxy($endpoint, $parameters = null, $method = "GET", $returnHeaders = false, $additionalHeaders = null,$rql = null)
  {
    $client = new Client();
    try {
      //format GET query string parameters
      $get_parameters = '';
      if ($method == 'GET' && is_array($parameters)) {
        $get_parameters = '?'.http_build_query($parameters);
        if($rql){
          $get_parameters .= $rql;
        }
      } elseif ($method == 'POST' || $method == 'PUT') {
        $get_parameters = '';
      }
      $headers = [
        'Authorization' => 'Bearer '.$this->accessToken,
        'Accept' => "application/hal+json",
        'Content-Type' => "application/hal+json; charset=utf-8",
      ];
      //add additional headers if needed
      if (is_array($additionalHeaders)) {
        $headers = array_merge($headers, $additionalHeaders);
      }
      //send the request using guzzle
      $request = $client->request($method, env('WMS_URL').$endpoint.$get_parameters, [
        'headers' => $headers,
        'json' => ($method == 'POST' || $method == 'PUT') ? $parameters : []
      ]);
      //decode the response
      $body = json_decode($request->getBody());
      if ($returnHeaders) {
        //return decoded response with headers
        return ['body' => $body, 'headers' => $request->getHeaders()];
      } else {
        //return decoded response only
        return $body;
      }
    } catch (\Exception $e) {
      \Log::info("Error message 2");
      \Log::info($e);
      $response = $e->getResponse();
      //var_dump($response->getStatusCode()); // HTTP status code
      //var_dump($response->getReasonPhrase()); // Message
      //var_dump($response->getBody()); // Body
      //print_r($response);
      \Log::info((string) $response->getBody());
      throw new \Exception($response->getBody());
      //return response()->json($response->getBody(),500);
    }
  }
}
