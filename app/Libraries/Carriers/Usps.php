<?php

/**
 *
 * USPS API Library
 * @documentation
 *  - https://www.usps.com/business/web-tools-apis/track-and-confirm-api.htm
 *  - https://www.usps.com/business/web-tools-apis/general-api-developer-guide.pdf
 *  - https://www.usps.com/business/web-tools-apis/technical-documentation.htm
 *
 * @test ENV vars
 * -USPS_USER_ID=460ARBDE2229
 * -USPS_API_URL=https://secure.shippingapis.com/ShippingAPI.dll
 */

 namespace App\Libraries\Carriers;
 use Guzzle\Http\Exception\ClientException;
 use GuzzleHttp\Client;
 use Illuminate\Support\Facades\Log;

 class USPS
 {

    // user id provided by USPS upon registration
    private $uspsUserId;

    // base url of the USPS API
    private $uspsApiUrl;

    public function __construct()
    {
        $this->uspsUserId = env('USPS_USER_ID');
        $this->uspsApiUrl = env('USPS_API_URL');
    }

    /**
    *
    * Track shipment from provided tracking numbers
    * @param array $trackingNumber | array of tracking numbers of the package
    * @return array $trackingStatuses | array of data associated with the provided tracking numbers
    */
    public function getShipmentsTrackingStatus($trackingNumber)
    {
        try {
          $endpoint = '?API=TrackV2&XML=';
          $xmlData = $this->createShipmentsTrackingStatusXML($trackingNumber);
          $trackingStatuses = $this->sendGetXMLRequest($xmlData,$endpoint);

          if (isset($trackingStatuses['TrackInfo']['TrackSummary'])) {
            //see if package is delivered
            if (preg_match("/Delivered/i", $trackingStatuses['TrackInfo']['TrackSummary']['Event'])) {
              return ['status' => 'success',
                      'tracking_status' => 'Delivered',
                      'status_date' => date('Y-m-d', strtotime($trackingStatuses['TrackInfo']['TrackSummary']['EventDate'])),
                      'ship_date' => date('Y-m-d', strtotime($trackingStatuses['TrackInfo']['TrackDetail'][count($trackingStatuses['TrackInfo']['TrackDetail'])-1]['EventDate']))];
            } else {
              //in transit
              if (isset($trackingStatuses['TrackInfo']['TrackDetail']) && is_array($trackingStatuses['TrackInfo']['TrackDetail']) && !isset($trackingStatuses['TrackInfo']['TrackDetail']['EventDate'])) {
                return ['status' => 'success',
                        'tracking_status' => 'In Transit',
                        'status_date' => date('Y-m-d', strtotime($trackingStatuses['TrackInfo']['TrackSummary']['EventDate'])),
                        'ship_date' => date('Y-m-d', strtotime($trackingStatuses['TrackInfo']['TrackDetail'][count($trackingStatuses['TrackInfo']['TrackDetail'])-1]['EventDate']))];
              } else {
                if (isset($trackingStatuses['TrackInfo']['TrackDetail'])) {
                  return ['status' => 'success',
                          'tracking_status' => 'In Transit',
                          'status_date' => date('Y-m-d', strtotime($trackingStatuses['TrackInfo']['TrackSummary']['EventDate'])),
                          'ship_date' => date('Y-m-d', strtotime($trackingStatuses['TrackInfo']['TrackDetail']['EventDate']))];
                } else {
                  return ['status' => 'success',
                          'tracking_status' => 'In Transit',
                          'status_date' => date('Y-m-d', strtotime($trackingStatuses['TrackInfo']['TrackSummary']['EventDate'])),
                          'ship_date' => date('Y-m-d', strtotime($trackingStatuses['TrackInfo']['TrackSummary']['EventDate']))];
                }
              }
            }
            return ['status' => 'success',
                    'tracking_status' => $trackingStatuses['TrackInfo']['TrackSummary']['Event'],
                    'status_date' => date('Y-m-d', strtotime($trackingStatuses['TrackInfo']['TrackSummary']['EventDate'])),
                    'ship_date' => date('Y-m-d', strtotime($trackingStatuses['TrackInfo']['TrackDetail'][count($trackingStatuses['TrackInfo']['TrackDetail'])-1]['EventDate']))];
          } else {
            //not shipped
            return ['status' => 'success', 'tracking_status' => 'Not Shipped', 'status_date' => date('Y-m-d')];
          }
        } catch (\Exception $e) {
          \Log::info($e);
          return ['status'=>'error','message'=>'Error: USPS '.$e->getMessage()];
        }

        return $trackingStatuses;
    }

    /**
     *
     * Constructs and Prepares XML request for
     * @param array $trackingNumbers | array of tracking numbers of the package
     * @return string $xmlString | XML formatted values of tracking numbers
     */
    public function createShipmentsTrackingStatusXML($trackingNumber)
    {
        $xml = new \DOMDocument();
        $xml->formatOutput = true;

        // Create Tracking Request element
        $trackingRequest = $xml->createElement('TrackFieldRequest');
        $trackingRequest->setAttribute('USERID',$this->uspsUserId);

        // Create Tracking Number element
        $trackId = $xml->createElement('TrackID');
        $trackId->setAttribute('ID',$trackingNumber);
        //Append to parent element $trackingRequest
        $trackingRequest->appendChild($trackId);

        $xml->appendChild($trackingRequest);
        $xmlString = $xml->saveXML();
        $xmlString = preg_replace( "/\r|\n/", "", $xmlString );

        return $xmlString;
    }

    /**
     *
     * Sends a Get XML request to USPS API endpoint
     * @param string $xmlData | xml formatted string to be used on the request
     * @return array $response
     */
    public function sendGetXMLRequest($xmlData,$endpoint)
    {
        $client = new Client();

        try {
            $request = $client->request('GET',env('USPS_API_URL') . $endpoint . $xmlData,[
                'headers' => [
                    'Content-Type' => 'application/xml'
                ]
            ]);
            $xmlResponse = simplexml_load_string($request->getBody(),'SimpleXMLElement',LIBXML_NOCDATA);
            $response = json_encode($xmlResponse);
            $response = json_decode($response,true);
            return $response;
        } catch (\GuzzleHttp\Exception\ClientException $e){
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            \Log::info('USPS_API_ERROR');
            \Log::info($responseBodyAsString);
            \Log::info($e);
        }
    }

 }

?>
