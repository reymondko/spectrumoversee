<?php

namespace App\Libraries\ShipCaddie;
use Guzzle\Http\Exception\ClientException;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class ShipCaddie{

    protected $accessToken; // Shipcaddie Access Token

    public function __construct(){
        // Retrieve access token
        $client = new Client();
        try {
            $request = $client->request('GET', env('SHIPCADDIE_URL').'GetToken?UserName=' . env('SHIPCADDIE_USERNAME') . '&Password=' . env('SHIPCADDIE_PASSWORD'));
            $response = json_decode($request->getBody());
            $this->accessToken = $response->accessToken;
        } catch (\Exception $e) {
            \Log::info("SHIPCADDIE_ERROR: ERROR GETTING ACCESS TOKEN - ".$e->getMessage());
        }
    }

    public function getShippingRates($data){

    }

    /**
     *
     * Obtains a list of carrier contract Ids
     *  - https://api.shipcaddie.com/index.html#operation/GetCarrierServiceInformation
     *
     * @param $affiliateRestrictions
     *  - an array of restrictions based on affillateName from shipcaddie
     *
     * @return array
     *
     */
    public function getCarriersContractId($affiliateRestrictions = []){
        $carrierContractIds = [];
        $client = new Client();
        try {
            $request = $client->request('GET', env('SHIPCADDIE_URL').'GetCarrierServiceInformation?AccessToken=' . $this->accessToken);
            $response = json_decode($request->getBody());
            if(isset($response->carrierServiceList)){
                foreach($response->carrierServiceList as $carrier){
                    if(in_array($carrier->affillateName,$affiliateRestrictions)){
                        $carrierContractIds[] = $carrier->carrierClientContractId;
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::info("SHIPCADDIE_ERROR: ERROR RETRIEVING CARRIERS - ".$e->getMessage());
        }

        return $carrierContractIds;
    }

    /**
     *
     * Request a break down of shipping charges for specified carriers
     *  - https://api.shipcaddie.com/index.html#operation/GetRates
     *
     * @param $shippingData
     *  - shipment information
     * @param $carrierContractIds
     *  - contract ids of the carriers that we need to get the rates from
     *
     * @return array
     *
     */
    public function getRates($shippingData,$carrierContractIds = null){

        $rates = [];
        $client = new Client();

        /*if ($_SERVER['REMOTE_ADDR'] != '166.70.73.231')
          return $rates;*/

        $now = new \DateTime(null, new \DateTimeZone('America/Phoenix'));
        $currentTime = $now->format("Y-m-d\TH:i:s.000\Z");

        // Packages
        $packages = [];
        foreach($shippingData['packages'] as $package){
            $packages[] = [
                "weightInPounds" => $package['weight'],
                "lengthInInches" => $package['length'],
                "widthInInches" =>  $package['width'],
                "heightInInches" => $package['height'],
                "options" => [
                    "return" => "NOT_APPLICABLE",
                    "insuranceAmount" => 0,
                    "signature" => "NOT_APPLICABLE",
                    "cod" => [
                        "codType" => "NOT_APPLICABLE",
                        "codAmount" => 0
                    ]
                ]
            ];
        }

        // Get Shipping Rates
        foreach($carrierContractIds as $carrierContractId){
            $data = [
                "carrierClientContractId" => $carrierContractId,
                "dateShipped" => $currentTime,
                "options" => [
                    "isAPO_FPO_DPO_USTerritory" => false,
                    "isInternationalShipment" => false,
                    "billing" => [
                        "shippingPaidBy" => "NOT_APPLICABLE",
                        "accountNumber" => "",
                        "postalCode" => "",
                        "country_Alpha2Code" => "",
                        "dutiesPaidBy" => "NOT_APPLICABLE"
                    ]
                ],
                "addressFrom" => [
                    "isResidential" => false,
                    "address1" => $shippingData['from_address1'],
                    "address2" => '',
                    "city" => $shippingData['from_city'],
                    "countryCode" => "US",
                    "systemCountryId" => 840,
                    "stateOrProvince" => $shippingData['from_state'],
                    "zipCode" => $shippingData['from_zip'],
                    "phoneNumber" => $shippingData['from_phone']
                ],
                "addressTo" => [
                    "isResidential" => true,
                    "address1" => $shippingData['to_address1'],
                    "address2" => '',
                    "city" => $shippingData['to_city'],
                    "countryCode" => "US",
                    "systemCountryId" => 840,
                    "stateOrProvince" => $shippingData['to_state'],
                    "zipCode" => $shippingData['to_zip'],
                    "phoneNumber" => $shippingData['to_phone']
                ],
                "parcels" => $packages
            ];
            try {
                $request = $client->request('POST', env('SHIPCADDIE_URL').'GetRates?AccessToken=' . $this->accessToken, [
                    'headers' => [],
                    'json' => $data]);
                $response = json_decode($request->getBody());
                foreach($response->data as $responseData){
                    if ($responseData->carrierInfo->serviceLevelName == 'Media Mail' || $responseData->carrierInfo->serviceLevelName == 'Library Mail' || $responseData->carrierInfo->carrierName == 'USPS via iDrive')
                      continue;

                    $rates[] = [
                        'ServiceLevelID' => $responseData->carrierInfo->serviceLevelID,
                        'Name' => $responseData->carrierInfo->serviceLevelName,
                        'ServiceType' => $responseData->carrierInfo->serviceLevelID,
                        'Total' => $responseData->costs->costDetails->totalChargeAmount,
                        'AccountName' => $responseData->carrierInfo->carrierName,
                        'ParcelId' => $responseData->costs->costDetails->parcelChargeDetails[0]->parcelID,
                        'CarrierContractId' => $carrierContractId,
                        'Vendor' => 'ShipCaddie',
                        'ShippingAccountId' => 'ShipCaddie',
                    ];
                }
            } catch (\Exception $e) {
                \Log::info("SHIPCADDIE_ERROR: ERROR GETTING RATES - ".$e->getMessage());
            }
        }
        return $rates;
    }


    /**
     *
     * Adds a new shipment to shipcaddie
     *  - https://api.shipcaddie.com/index.html#operation/AddNewShipment
     *
     * @param $shippingData
     *  - shipment information
     *
     * @return int
     *  - returns the shipment id
     *
     */
    public function addShipCaddieShipment($shippingData){
        $shipment_id = null;
        $client = new Client();

        $now = new \DateTime(null, new \DateTimeZone('America/Phoenix'));
        $currentTime = $now->format("Y-m-d\TH:i:s.000\Z");

        // Packages
        $packages = [];
        foreach($shippingData['packages'] as $package){
            $packages[] = [
                "weightInPounds" => $package['weight'],
                "lengthInInches" => $package['length'],
                "widthInInches" =>  $package['width'],
                "heightInInches" => $package['height'],
                "options" => [
                    "return" => "NOT_APPLICABLE",
                    "insuranceAmount" => 0,
                    "signature" => "NOT_APPLICABLE",
                    "cod" => [
                        "codType" => "NOT_APPLICABLE",
                        "codAmount" => 0
                    ]
                ]
            ];
        }

        $data = [
            "shipmentID" => -1,
            "orderReferenceNumber" => $shippingData['reference_number'],
            "carrierClientContractId" => $shippingData['carrier_client_contract_id'],
            "carrierServiceLevelId" => $shippingData['carrier_service_level_id'],
            "dateShipped" => $currentTime,
            "options" => [
                "isAPO_FPO_DPO_USTerritory" => false,
                "isInternationalShipment" => false,
                "billing" => [
                    "shippingPaidBy" => "NOT_APPLICABLE",
                    "accountNumber" => "",
                    "postalCode" => "",
                    "country_Alpha2Code" => "",
                    "dutiesPaidBy" => "NOT_APPLICABLE"
                ]
            ],
            "addressFrom" => [
                "isResidential" => false,
                "address1" => $shippingData['from_address1'],
                "city" => $shippingData['from_city'],
                "countryCode" => "US",
                "systemCountryId" => 840,
                "stateOrProvince" => $shippingData['from_state'],
                "zipCode" => $shippingData['from_zip'],
                "phoneNumber" => $shippingData['from_phone'],
                "attentionOf" => $shippingData['from_name'],
                "companyName" => $shippingData['from_company'],
            ],
            "addressTo" => [
                "isResidential" => true,
                "address1" => $shippingData['to_address1'],
                "address2" => $shippingData['to_address2'],
                "city" => $shippingData['to_city'],
                "countryCode" => "US",
                "systemCountryId" => 840,
                "stateOrProvince" => $shippingData['to_state'],
                "zipCode" => $shippingData['to_zip'],
                "phoneNumber" => $shippingData['to_phone'],
                "attentionOf" => $shippingData['attention_of'],
                "companyName" => $shippingData['company']
            ],
            "parcels" => $packages
        ];
        try {
            $request = $client->request('POST', env('SHIPCADDIE_URL').'AddNewShipment?AccessToken=' . $this->accessToken, [
                'headers' => [],
                'json' => $data]);
            $response = json_decode($request->getBody());
            \Log::info("SHIPCADDIE_DEBUG1: ".print_r($data, true));
            \Log::info("SHIPCADDIE_DEBUG2: ".print_r($response, true));
            return $response;
        } catch (\Exception $e) {;
            \Log::info("SHIPCADDIE_ERROR: ERROR ADDING SHIPMENT - ".$e->getMessage());
        }

        return $shipment_id;
    }

    /**
     *
     * Retrieves shipment information including shipping label
     * - https://api.shipcaddie.com/index.html#operation/GetShippingLabelsByShippingID
     *
     * @param $shipment_id
     *  - the id of the shipment from shipcaddie
     * @return array
     */
    public function getShipmentInformation($shipment_id){
        $shipping_information = [];
        $client = new Client();
        try {
            $request = $client->request('GET', env('SHIPCADDIE_URL').'GetShippingLabelsByShippingID?AccessToken=' . $this->accessToken . '&printFormat=ZPL_4x6&ShipmentID=' . $shipment_id);
            $response = json_decode($request->getBody());
            return $response;
        } catch (\Exception $e) {
            \Log::info("SHIPCADDIE_ERROR: ERROR RETRIEVING SHIPMENT - ".$e->getMessage());
        }
        return $shipping_information;
    }


}
