<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Models\ShippingPrinter;
use App\Models\Shipments;
use App\Models\DeliveryAddress;
use App\Models\ShipperAddress;
use App\Models\ShipRushAddress;
use GuzzleHttp\Client;

class ShipmentFinderController extends Controller
{

    public $ShipRushAPIKey = '88c9a9a7-de94-49f4-a6f6-0ff33bfe8f8b';
    public $ShipRushAPIEndpoint = 'https://api.my.shiprush.com';
    public $ShipRushShipperAddress;
    public $ShipRushDeliveryAddress;
    public $ShipRushPackageType;
    public $ShipRushWeight;
    public $ShipRushInsuranceAmount;
    public $ShipRushSelectedPrinter;
    public $ShipRushSelectedServiceType = null;

    public $shipRushAccounts = [
        [
          'account_id' => 'eed710f4-0137-4f48-924c-a98901739506',
          'account_name' => 'FedEx - 661830514',
          'carrier_id' => 1,
          'services' => [
            [
              'service_name' => 'FedEx Express Saver®',
              'service_type' => 'F20',
              'packaging_type' => '02'
            ],
            [
              'service_name' => 'FedEx 2Day®',
              'service_type' => 'F03',
              'packaging_type' => '02'
            ],
            [
              'service_name' => 'FedEx Standard Overnight®',
              'service_type' => 'F05',
              'packaging_type' => '02'
            ],
            [
              'service_name' => 'FedEx Priority Overnight®',
              'service_type' => 'F01',
              'packaging_type' => '02'
            ],
            [
              'service_name' => 'FedEx First Overnight®',
              'service_type' => 'F06',
              'packaging_type' => '02'
            ]
          ]
        ],
        [
          'account_id' => '8b39b549-0564-469c-a185-a9f900092a43',
          'account_name' => 'FedEx - 581576706',
          'carrier_id' => 1,
          'services' => [
            [
              'service_name' => 'FedEx Ground®',
              'service_type' => 'F92',
              'packaging_type' => '02'
            ],
            [
              'service_name' => 'FedEx Express Saver®',
              'service_type' => 'F20',
              'packaging_type' => '02'
            ],
            [
              'service_name' => 'FedEx Standard Overnight®',
              'service_type' => 'F05',
              'packaging_type' => '02'
            ]
          ]
        ],
        [
          'account_id' => 'faff58cf-9e33-4c7c-948a-a993017e90f1',
          'account_name' => 'FirstMile',
          'carrier_id' => 32,
          'services' => [
            [
              'service_name' => 'USPS Priority',
              'service_type' => 'U02',
              'packaging_type' => '02'
            ],
            [
              'service_name' => 'XParcel Expedited',
              'service_type' => 'FMXPE',
              'packaging_type' => '02'
            ],
            [
              'service_name' => 'USPS First Class',
              'service_type' => 'U01',
              'packaging_type' => '02'
            ]
          ]
        ],
        [
          'account_id' => '7260b9a7-353c-4c19-afc7-a97b01648ce4',
          'account_name' => 'UPS',
          'carrier_id' => 0,
          'services' => [
            [
              'service_name' => 'UPS Ground',
              'service_type' => '03',
              'packaging_type' => '02'
            ],
            [
              'service_name' => 'UPS SurePost® 1 lb or Greater',
              'service_type' => 'USPG',
              'packaging_type' => '02'
            ],
            [
              'service_name' => 'UPS 3 Day Select',
              'service_type' => '12',
              'packaging_type' => '02'
            ],
            [
              'service_name' => 'UPS 2nd Day Air',
              'service_type' => '02',
              'packaging_type' => '02'
            ],
            [
              'service_name' => 'UPS Next Day Air',
              'service_type' => '01',
              'packaging_type' => '02'
            ]
          ]
        ]
      ];

    public function index()
    {
        $printers = ShippingPrinter::all();

        return view('layouts.createshipment.index', compact('printers'));
    }

    public function store(Request $request)
    {

        $user = Auth::user();

        $to_address = request()->input('to_address');
        $from_address = request()->input('from_address');
        $package = request()->input('package');
        $carrier = request()->input('selected_service');

        // Delivery address
        $shipper_address = new ShipRushAddress();
        $shipper_address->FirstName = $to_address['first_name'];
        $shipper_address->LastName = $to_address['last_name'];
        $shipper_address->Address1 = $to_address['address1'];
        $shipper_address->City = $to_address['city'];
        $shipper_address->State = $to_address['state'];
        $shipper_address->Country = $to_address['country'];
        $shipper_address->PostalCode = $to_address['postalcode'];
        $shipper_address->PhoneNumber = preg_replace("/[^0-9]/", "", $to_address['phone'] );

        // Shipper address
        $delivery_address  = new ShipRushAddress();
        $delivery_address->FirstName = $from_address['first_name'];
        $delivery_address->LastName = $from_address['last_name'];
        $delivery_address->Address1 = $from_address['address1'];
        $delivery_address->City = $from_address['city'];
        $delivery_address->State = $from_address['state'];
        $delivery_address->Country = $from_address['country'];
        $delivery_address->PostalCode = $from_address['postalcode'];
        $delivery_address->PhoneNumber = preg_replace("/[^0-9]/", "",  $from_address['phone'] );


        
        $shipments = Shipments::create([
            'companies_id' => $user->companies_id,
            'delivery_address_id' => $delivery_address->id,
            'shipper_address_id' => $shipper_address->id,
            'weight' => $package['weight'],
            'package_type' => '',
            'insurance_amount' => '',
        ]);
        

         /** SHIP RUSH INTEGRATION */
         $shipRushRecord = $this->createShipRushRecord($shipper_address,$delivery_address,$shipments,$package,$carrier);
         if($shipRushRecord){
             $shipments->shiprush_shipment_id = $shipRushRecord['shipmentId'];
             $shipments->shiprush_tracking_no = $shipRushRecord['trackingNo'];
             $shipments->shiprush_base_64_zpl = $shipRushRecord['zpl'];
         }

         $shipments->save();

        return response()->json([
            'status' => 'saved',
            'redirect_to' => '/create-shipment',
            'shiprush_record' => $shipRushRecord
        ]);
    }

    private function createShipRushRecord($shipper_address,$delivery_address,$shipment,$package,$carrier)
    {
        $return = array();

        //delivery address
        $this->ShipRushDeliveryAddress  = new ShipRushAddress();
        $this->ShipRushDeliveryAddress->FirstName = $shipper_address->FirstName;
        $this->ShipRushDeliveryAddress->LastName = $shipper_address->LastName;
        $this->ShipRushDeliveryAddress->Address1 = $shipper_address->Address1;
        $this->ShipRushDeliveryAddress->City = $shipper_address->City;
        $this->ShipRushDeliveryAddress->State = $shipper_address->State;
        $this->ShipRushDeliveryAddress->Country = $shipper_address->Country;
        $this->ShipRushDeliveryAddress->PostalCode = $shipper_address->PostalCode;
        $this->ShipRushDeliveryAddress->PhoneNumber = preg_replace("/[^0-9]/", "", $shipper_address->PhoneNumber);

        //shipper address
        $this->ShipRushShipperAddress  = new ShipRushAddress();
        $this->ShipRushShipperAddress->FirstName = $delivery_address->FirstName;
        $this->ShipRushShipperAddress->LastName = $delivery_address->LastName;
        $this->ShipRushShipperAddress->Address1 = $delivery_address->Address1;
        $this->ShipRushShipperAddress->City = $delivery_address->City;
        $this->ShipRushShipperAddress->State = $delivery_address->State;
        $this->ShipRushShipperAddress->Country = $delivery_address->Country;
        $this->ShipRushShipperAddress->PostalCode = $delivery_address->PostalCode;
        $this->ShipRushShipperAddress->PhoneNumber = preg_replace("/[^0-9]/", "", $delivery_address->PhoneNumber);

        $this->ShipRushWeight = $package['weight'];
        $this->ShipRushPackageType = '02';
        $this->ShipRushInsuranceAmount = 0.0;

        $response = $this->getRates();


        if ($response->IsSuccess == 'true') {
            $cheapest = 100000;
            $maxDays = 5;
            $this->ShipRushSelectedServiceType = null;
            foreach ($response->AvailableServices->AvailableService as $service) {
            if ($service->Total < $cheapest && $service->TimeInTransitDays <= $maxDays && !preg_match("/USPS/", $service->Name) && !preg_match("/DHL/", $service->Name) && !preg_match("/XParcel/", $service->Name) && !preg_match("/FirstMile/", $service->Name) && !preg_match("/PMOD/", $service->Name)) {
                $cheapest = $service->Total;
                $this->ShipRushSelectedServiceType = $service;
            }
            }
        } else {
            //error processing request
            print "ERROR: ".$request->getBody()."\n";
        }

        if ($this->ShipRushSelectedServiceType != null) {

            if (preg_match("/USPS/i", $this->ShipRushSelectedServiceType->Name)) {
            $carrierId = 3;
            } elseif (preg_match("/DHL/i", $this->ShipRushSelectedServiceType->Name)) {
            $carrierId = 29;
            } elseif (preg_match("/UPS/i", $this->ShipRushSelectedServiceType->Name)) {
            $carrierId = 0;
            } elseif (preg_match("/FEDEX/i", $this->ShipRushSelectedServiceType->Name)) {
            $carrierId = 1;
            }
        }


        //book shipment and create label
        $ShiprushXMLRequest = $this->createShiprushXMLRequest($carrier);

        try {
            $client = new Client();
            $request = $client->request('POST', 'https://api.my.shiprush.com/shipmentservice.svc/shipment/ship', [
            'headers' => [
                'X-SHIPRUSH-SHIPPING-TOKEN' => '88c9a9a7-de94-49f4-a6f6-0ff33bfe8f8b',
                'Content-Type' => 'application/xml'
            ],
            'body' => $ShiprushXMLRequest
            ]);

            $response = simplexml_load_string($request->getBody(),'SimpleXMLElement',LIBXML_NOCDATA);
            // print_r($response);
            if ($response->IsSuccess == 'true') {
            //   print "Shipment ID: ".$response->ShipTransaction->Shipment->ShipmentId."\n";

            $shipmentId = (string) $response->ShipTransaction->Shipment->ShipmentId->asXML();
            $shipmentId = str_replace('<ShipmentId>','',$shipmentId);
            $shipmentId = str_replace('</ShipmentId>','',$shipmentId);

            $trackingNo = (string) $response->ShipTransaction->Shipment->ShipmentNumber->asXML();
            $trackingNo = str_replace('<ShipmentNumber>','',$trackingNo);
            $trackingNo = str_replace('</ShipmentNumber>','',$trackingNo);

            $packageCost = (string) $response->ShipTransaction->Shipment->TotalCharges->asXML();
            $packageCost = str_replace('<TotalCharges>','',$packageCost);
            $packageCost = str_replace('</TotalCharges>','',$packageCost);

            $zpl = (string) $response->ShipTransaction->Shipment->Documents->PaperDocument->ContentMimeEncoded->asXML();
            $zpl = str_replace('<ContentMimeEncoded>','',$zpl);
            $zpl = str_replace('</ContentMimeEncoded>','',$zpl);


            $result['shipmentId'] =  $shipmentId;
            $result['trackingNo'] =  $trackingNo;
            $result['packageCost'] = $packageCost;
            $result['zpl'] = $zpl;

            return $result;
            }
        } catch (\GuzzleHttp\Exception\ServerException $e) {
        dd($e->getResponse()->getBody()->getContents());
        }
    }

    private function getRates($isTest = false) {
        $xml = new \DOMDocument();
        $xml->formatOutput = true;

        $raterequest = $xml->createElement('RateShoppingRequest');
        $transaction = $xml->createElement('ShipTransaction');

        $shipment = $xml->createElement('Shipment');
        if ($isTest) {
          $shipment->appendChild($xml->createElement('IsTest', 1));
        } else {
          $shipment->appendChild($xml->createElement('IsTest', 0));
        }

        $package = $xml->createElement('Package');
        $package->appendChild($xml->createElement('PackageActualWeight', $this->ShipRushWeight));
        $package->appendChild($xml->createElement('PackageType', $this->ShipRushPackageType));
        $package->appendChild($xml->createElement('InsuranceAmount', $this->ShipRushInsuranceAmount));
        $shipment->appendChild($package);

        $deliveryaddress = $xml->createElement('DeliveryAddress');
        $deliveryaddress->appendChild($this->ShipRushDeliveryAddress->getFormattedAddress($xml));
        $shipment->appendChild($deliveryaddress);

        $shipperaddress = $xml->createElement('ShipperAddress');
        $shipperaddress->appendChild($this->ShipRushShipperAddress->getFormattedAddress($xml));
        $shipment->appendChild($shipperaddress);

        $transaction->appendChild($shipment);
        $raterequest->appendChild($transaction);
        $xml->appendChild($raterequest);

        $xmlString = $xml->saveXML();

        //shiprush
        try {
          $client = new Client();
          $request = $client->request('POST', $this->ShipRushAPIEndpoint.'/shipmentservice.svc/shipment/rateshopping', [
            'headers' => [
              'X-SHIPRUSH-SHIPPING-TOKEN' => $this->ShipRushAPIKey,
              'Content-Type' => 'application/xml'
            ],
            'body' => $xmlString
          ]);
          $response = simplexml_load_string($request->getBody(),'SimpleXMLElement',LIBXML_NOCDATA);
          return $response;

        } catch (\Exception $e) {
          $response = $e->getResponse();
          $responseBodyAsString = $response->getBody()->getContents();
          // print "Error: $responseBodyAsString\n";
          exit;
        }
    }

    public function createShiprushXMLRequest($carrier){

        $carrierId = $carrier['CarrierId'];
        $serviceType = $carrier['ServiceType'];

        $xml = new \DOMDocument();
        $xml->formatOutput = true;
        $ShipRequest = $xml->createElement('ShipRequest');

        $ShipTransaction = $xml->createElement('ShipTransaction');
        $Shipment = $xml->createElement('Shipment');
        $Shipment->appendChild($xml->createElement('IsTest',0));
        $Shipment->appendChild($xml->createElement('Carrier',$carrierId)); //test carrier on usps
        $Shipment->appendChild($xml->createElement('UPSServiceType',$serviceType));
        $Package = $xml->createElement('Package');
        $Package->appendChild($xml->createElement('PackageActualWeight', $this->ShipRushWeight));
        $Package->appendChild($xml->createElement('PackageType', $this->ShipRushPackageType));
        $Package->appendChild($xml->createElement('InsuranceAmount', $this->ShipRushInsuranceAmount));
        $Shipment->appendChild($Package);
        $Deliveryaddress = $xml->createElement('DeliveryAddress');
        $Deliveryaddress->appendChild($this->ShipRushDeliveryAddress->getFormattedAddress($xml));
        $Shipment->appendChild($Deliveryaddress);
        $Shipperaddress = $xml->createElement('ShipperAddress');
        $Shipperaddress->appendChild($this->ShipRushShipperAddress->getFormattedAddress($xml));
        $Shipment->appendChild($Shipperaddress);
        $ShipTransaction->appendChild($Shipment);

        $ShipSettings = $xml->createElement('ShipSettings');
        $PrinterShippingLabel = $xml->createElement('PrinterShippingLabel');
        $PrinterShippingLabel->appendChild($xml->createElement('AutoprintShippingLabel', 'true'));
        $PrinterShippingLabel->appendChild($xml->createElement('LabelType', 'ZPL'));
        $ShipSettings->appendChild($PrinterShippingLabel);

        $ShipRequest->appendChild($ShipTransaction);
        $ShipRequest->appendChild($ShipSettings);
        $ShipRequest->setAttribute('xmlns:xsi',"http://www.w3.org/2001/XMLSchema-instance");
        $ShipRequest->setAttribute('xmlns:xsd',"http://www.w3.org/2001/XMLSchema");
        $xml->appendChild($ShipRequest);
        $xmlString = $xml->saveXML();

        return $xmlString;

    }

    public function getShipRushRates($isTest = false)
    {
        $to_address = request()->input('to_address');
        $from_address = request()->input('from_address');
        $package = request()->input('package');

        //delivery address
        $ShipRushDeliveryAddress = new ShipRushAddress();
        $ShipRushDeliveryAddress->FirstName = $to_address['first_name'];
        $ShipRushDeliveryAddress->LastName = $to_address['last_name'];
        $ShipRushDeliveryAddress->Address1 = $to_address['address1'];
        $ShipRushDeliveryAddress->City = $to_address['city'];
        $ShipRushDeliveryAddress->State = $to_address['state'];
        $ShipRushDeliveryAddress->Country = $to_address['country'];
        $ShipRushDeliveryAddress->PostalCode = $to_address['postalcode'];
        $ShipRushDeliveryAddress->PhoneNumber = preg_replace("/[^0-9]/", "", $to_address['phone'] );

        //shipper address
        $ShipRushShipperAddress  = new ShipRushAddress();
        $ShipRushShipperAddress->FirstName = $from_address['first_name'];
        $ShipRushShipperAddress->LastName = $from_address['last_name'];
        $ShipRushShipperAddress->Address1 = $from_address['address1'];
        $ShipRushShipperAddress->City = $from_address['city'];
        $ShipRushShipperAddress->State = $from_address['state'];
        $ShipRushShipperAddress->Country = $from_address['country'];
        $ShipRushShipperAddress->PostalCode = $from_address['postalcode'];
        $ShipRushShipperAddress->PhoneNumber = preg_replace("/[^0-9]/", "",  $from_address['phone'] );

        // $ShipRushWeight = 3.5;
        $ShipRushPackageType = '02';
        $ShipRushInsuranceAmount = 0.0;

        $xml = new \DOMDocument();
        $xml->formatOutput = true;

        $raterequest = $xml->createElement('RateShoppingRequest');
        $transaction = $xml->createElement('ShipTransaction');

        $shipment = $xml->createElement('Shipment');
        if ($isTest) {
            $shipment->appendChild($xml->createElement('IsTest', 1));
        } else {
            $shipment->appendChild($xml->createElement('IsTest', 0));
        }

        // Package
        $weight = $package['weight'];
        $package = $xml->createElement('Package');
        $package->appendChild($xml->createElement('PackageActualWeight', $weight));
        $package->appendChild($xml->createElement('PackageType', $ShipRushPackageType));
        $package->appendChild($xml->createElement('InsuranceAmount', $ShipRushInsuranceAmount));
        $shipment->appendChild($package);


        $deliveryaddress = $xml->createElement('DeliveryAddress');
        $deliveryaddress->appendChild($ShipRushDeliveryAddress->getFormattedAddress($xml));
        $shipment->appendChild($deliveryaddress);

        $shipperaddress = $xml->createElement('ShipperAddress');
        $shipperaddress->appendChild($ShipRushShipperAddress->getFormattedAddress($xml));
        $shipment->appendChild($shipperaddress);

        $transaction->appendChild($shipment);
        $raterequest->appendChild($transaction);
        $xml->appendChild($raterequest);

        $xmlString = $xml->saveXML();
        // var_dump($xmlString);
        // die();
        //shiprush
        try {
            $client = new Client();
            $request = $client->request('POST', $this->ShipRushAPIEndpoint.'/shipmentservice.svc/shipment/rateshopping', [
                'headers' => [
                    'X-SHIPRUSH-SHIPPING-TOKEN' => $this->ShipRushAPIKey,
                    'Content-Type' => 'application/xml'
                ],
                'body' => $xmlString
            ]);
            $response = simplexml_load_string($request->getBody(),'SimpleXMLElement',LIBXML_NOCDATA);

            $services = [];
            foreach ($response->AvailableServices->AvailableService as $rate) {
              //see if service is allowed and should be returned
              $found = false;
              foreach ($this->shipRushAccounts as $account) {
                foreach ($account['services'] as $service) {
                  if ($account['account_id'] == (string)$rate->ShippingAccountId && $service['service_type'] == (string)$rate->ServiceType && $service['packaging_type'] == (string)$rate->PackagingType) {
                    $rate = (object)$rate;
                    $rate->AccountName = $account['account_name'];
                    $rate->CarrierId = $account['carrier_id'];
                    $services[] = $rate;
                    break;
                  }
                }
              }
            }
            return response()->json(['IsSuccess' => 'true', 'AvailableServices' => ['AvailableService' => $services]], 200);
        }
        catch (\ClientErrorResponseException  $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            // print "Error: $responseBodyAsString\n";
            return response()->json($responseBodyAsString);
        }
    }
}
