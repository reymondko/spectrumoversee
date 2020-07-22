<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use App\Models\Companies;
use App\Models\ShipPackage;
use App\Models\ShippingCarriers;
use App\Models\ShippingCarrierMethods;
use App\Models\ShipPackSubmissions;
use App\Models\ShipRushAddress;
use App\Models\TplShipperAddress;
use App\Models\ShippingAutomationRules;
use App\Models\ShippingPrinter;
use App\Models\ShippingClient;
use App\Libraries\SecureWMS\SecureWMS;
use App\Libraries\ShipCaddie\ShipCaddie;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;

class ShipPackController extends Controller
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
    protected $wms = null;

    public $shipRushAccounts = [
      /*[
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
          ],
          [
            'service_name' => 'FedEx Home Delivery®',
            'service_type' => 'F90',
            'packaging_type' => '02'
          ],
          [
            'service_name' => 'FedEx International Priority®',
            'service_type' => 'I01',
            'packaging_type' => '02'
          ]
        ]
      ],*/
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
            'service_name' => 'FedEx 2Day®',
            'service_type' => 'F03',
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
          ],
          [
            'service_name' => 'FedEx Home Delivery®',
            'service_type' => 'F90',
            'packaging_type' => '02'
          ],
          [
            'service_name' => 'FedEx International Priority®',
            'service_type' => 'I01',
            'packaging_type' => '02'
          ]
        ]
      ],
      [
        'account_id' => 'faff58cf-9e33-4c7c-948a-a993017e90f1',
        'account_name' => 'FirstMile',
        'carrier_id' => 32,
        'services' => [
          /*[
            'service_name' => 'USPS Priority',
            'service_type' => 'U02',
            'packaging_type' => '02'
          ],*/
          [
            'service_name' => 'XParcel Expedited',
            'service_type' => 'FMXPE',
            'packaging_type' => '02'
          ],
          [
            'service_name' => 'XParcel Ground',
            'service_type' => 'FMXPG',
            'packaging_type' => '02'
          ],
          [
            'service_name' => 'XParcel Priority',
            'service_type' => 'FMXPP',
            'packaging_type' => '02'
          ],
          /*[
            'service_name' => 'USPS First Class',
            'service_type' => 'U01',
            'packaging_type' => '02'
          ]*/
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
          ],
          [
            'service_name' => 'UPS Worldwide Expedited',
            'service_type' => '08',
            'packaging_type' => '02'
          ]
        ]
      ]
    ];

    public $allowedShipcadieAffiliates = [
        'USPS'
    ];

    public function index()
    {
        if (
            //Gate::allows('can_see_ship_pack_tpl', auth()->user()) ||
            Gate::allows('can_see_ship_pack', auth()->user())
           // ||Gate::allows('company-with-ship-pack', auth()->user())
        ) {

            // $carriers = array();
            // $shippingCarriers = ShippingCarriers::with('methods')->get();
            // if($shippingCarriers){
            //     foreach($shippingCarriers as $sc){
            //         $carriers[] = array(
            //             'id'=>$sc->id,
            //             'name'=>$sc->name,
            //             'methods'=>$sc->methods
            //         );
            //     }
            // }


            $package_sizes = array();
            $package_sizes_collection = ShipPackage::get();
            if($package_sizes_collection){
                foreach($package_sizes_collection as $key => $value){
                    $package_sizes[] = array(
                        'id' => $value->id,
                        'package_name' => $value->package_name,
                        'length' => $value->length,
                        'width' => $value->width,
                        'height' => $value->height,
                        'weight' => $value->weight,
                        'content_qty' => 1,
                        'selected' => false,
                    );
                }
            }

            // $printers = ShippingPrinter::all();
            // $printers = $this->fetchShiprushPrinters();

            return view('layouts/shippack/shippack',compact(['package_sizes']));
        }else{
            return redirect()->route('dashboard');
        }
    }

    private function fetchShiprushPrinters()
    {
        $client = new Client();
        $req = $client->request('POST', 'https://api.my.shiprush.com/accountservice.svc/print/getprinters', [
            'headers' => [
                'X-SHIPRUSH-SHIPPING-TOKEN' => '88c9a9a7-de94-49f4-a6f6-0ff33bfe8f8b',
                'Content-Type' => 'application/xml'
            ],
            'body' => '<?xml version="1.0" encoding="utf-8"?><GetPrintersRequest></GetPrintersRequest>'
        ]);

        $response = simplexml_load_string($req->getBody(),'SimpleXMLElement',LIBXML_NOCDATA);
        $initJson = json_encode($response->Printers);
        $initArray = json_decode($initJson,TRUE);
        $printers = array();
        //$printers = json_encode($initArray['CloudPrinter']);
        foreach($initArray['CloudPrinter'] as $printer)
        {
            $printerName = stripslashes($printer['PrinterName']);
            $printerName = str_replace('dc01','',$printerName);
            $printerName = stripslashes($printerName);

            if($printer['IsOnline'] == 'true'){
                $printers[] = array(
                    'ComputerId'=>$printer['ComputerId'],
                    'ComputerName'=>$printer['ComputerName'],
                    'PrinterId'=>$printer['PrinterId'],
                    'IsOnline'=>$printer['IsOnline'],
                    'WebShippingPrinterId'=>$printer['WebShippingPrinterId'],
                    //'PrinterName'=>$printer['PrinterName'],
                    'PrinterName'=>$printerName,
                );
            }
        }

        return $printers;
    }

    private function fetchPrintersByRule($company_id)
    {
        $rules = ShippingAutomationRules::where('companies_id', $company_id)->get();

        $arr_printers = array();
        foreach($rules as $rule) {
            $printers = json_decode($rule->shiprush_printers);
            foreach($printers as $p) {
                $printer = explode("-|-", $p);
                array_push($arr_printers, [
                    'name' => $printer[0],
                    'id' => $printer[1]
                ]);
            }
        }

        return $arr_printers;
        // return array_unique($arr_printers);
    }

    public function searchByTransactionId(Request $r)
    {
        $company_id = \Auth::user()->companies_id;

        $companies = Companies::select('fulfillment_ids')->where('id',$company_id)->first();

        $customer_id = null;

        /*//see if transaction id already fulfilled
        $result = ShipPackSubmissions::where('tpl_order_id', $r->transaction_id)->first();
        if ($result) {
          $response = array(
              "status" =>"error",
              'error_message' => 'TRANSACTION ALREADY SHIPPED'
          );
          return response()->json($response,200);
        }*/

        if($companies->fulfillment_ids){
            $client = new Client();
            /* get access token */
            $accessToken = null;
            $tmpLineItems = array();
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
            }
            catch (\Exception $e) {}
            //get recent orders
            try {
                $request = $client->request('GET', 'https://secure-wms.com/orders?detail=All&itemdetail=All&pgsiz=100&pgnum=1&rql=readonly.customeridentifier.id=in=('.$companies->fulfillment_ids.');readonly.OrderId=='.$r->transaction_id, [
                    'headers' => [
                        'Authorization' => 'Bearer '.$accessToken,
                        'Accept' => "application/hal+json"
                    ],
                    'json' => []
                ]);

                $response = json_decode($request->getBody());
                $orderz = $response->{'_embedded'}->{'http://api.3plCentral.com/rels/orders/order'};
                foreach ($orderz as $order) {
                    $returnOrder = $order;
                    $lineItems = $order->{'_embedded'}->{'http://api.3plCentral.com/rels/orders/item'};

                    //make sure viome can't be shipped within 24 hours
                    /*if ($order->readOnly->customerIdentifier->id == 34 && (strtotime($order->readOnly->creationDate)+(3600*6)+79200) > time()) {
                      $response = array(
                          "status" =>"error",
                          'error_message' => '24 HOUR HOLDING PERIOD ENFORCED'
                      );
                      return response()->json($response,200);
                    }*/

                    /*//check if order already has tracking information
                    if (isset($order->routingInfo) && isset($order->routingInfo->trackingNumber) && strlen($order->routingInfo->trackingNumber) > 4) {
                        $response = array(
                            "status" =>"error",
                            'error_message' => 'TRANSACTION ALREADY SHIPPED'
                        );
                        return response()->json($response,200);
                    }*/

                    foreach($lineItems as $item) {

                            if ($item->qty <= 0)
                              continue;
                            $request = $client->request('GET', 'https://secure-wms.com/customers/'.$order->readOnly->customerIdentifier->id.'/items?pgsiz=100&pgnum=1&rql=Upc==*'.$item->itemIdentifier->sku.'*,sku==*'.$item->itemIdentifier->sku.'*', [
                                'headers' => [
                                'Authorization' => 'Bearer '.$accessToken,
                                'Accept' => "application/hal+json"
                                ],
                                'json' => []
                            ]);
                            $item_desc = 'N/A';
                            $responseInv = json_decode($request->getBody());
                            $responseLineItem = $responseInv->{'_embedded'};
                            /*$request = $client->request('GET', 'https://secure-wms.com/inventory?pgsiz=1&pgnum=1&rql=ItemIdentifier.sku=='.$item->itemIdentifier->sku, [
                                    'headers' => [
                                    'Authorization' => 'Bearer '.$accessToken,
                                    'Accept' => "application/hal+json"
                                    ],
                                    'json' => []
                            ]);
                            $item_desc = 'N/A';
                            $responseInv = json_decode($request->getBody());
                            $responseLineItem = $responseInv->{'_embedded'};*/
                            foreach($responseLineItem as $LineItem) {
                                if (isset($LineItem[0]->upc)) {
                                  $upc = $LineItem[0]->upc;
                                } else {
                                  $upc = null;
                                }
                                if(!empty($LineItem)){
                                    foreach($LineItem as $li){
                                        if($li->sku == $item->itemIdentifier->sku){
                                            $item_desc = $li->description;
                                        }
                                    }
                                }
                            }
                            if(isset($responseLineItem->item[0]->itemDescription))
                              $item_desc = $responseLineItem->item[0]->itemDescription;

                                $request = $client->request('GET', 'https://secure-wms.com/orders/'.$order->readOnly->orderId.'/items/'.$item->readOnly->orderItemId.'?detail=AllocationsWithDetail', [
                                'headers' => [
                                'Authorization' => 'Bearer '.$accessToken,
                                'Accept' => "application/hal+json"
                                ],
                                'json' => []
                            ]);

                            $responseItems = json_decode($request->getBody());
                            if (count($responseItems->readOnly->allocations) == 0) {
                               $tmpLineItems[] = array(
                                   'item_id' => $item->readOnly->orderItemId,
                                   'sku' => $item->itemIdentifier->sku,
                                   'upc' => $upc,
                                   'description' => $item_desc,
                                   'serial_number' => null,
                                   'lot_number' => null,
                                   'expiration' => null,
                                   'qty' => $item->qty,
                                   'qty_packed' => 0,
                                   'qty_remaining' => $item->qty,
                                   'done' => ($item->qty > 0 ? false:true)
                               );
                            }
                            foreach($responseItems->readOnly->allocations as $allocation){

                              $details = $allocation->detail->itemTraits;

                              $serial_number = 'N/A';
                              $expiration = 'N/A';
                              $lot_number = 'N/A';
                              if(isset($details->serialNumber)){
                                  $serial_number =(($details->serialNumber != "") ? $details->serialNumber:'N/A');
                              }

                              if(isset($details->lotNumber)){
                                  $lot_number = (($details->lotNumber != "") ? $details->lotNumber:'N/A');
                              }

                              $tmpLineItems[] = array(
                                  'item_id' => $order->readOnly->orderId,
                                  'sku' => $details->itemIdentifier->sku,
                                  'upc' => $upc,
                                  'description' => $item_desc,
                                  'serial_number' => $serial_number,
                                  'lot_number' => $lot_number,
                                  'expiration' => $expiration,
                                  'qty' => $allocation->qty,
                                  'qty_packed' => 0,
                                  'qty_remaining' => $allocation->qty,
                                  'done' => ($allocation->qty > 0 ? false:true)
                              );

    //                                   if(isset($responseLineItem->item[0]->expirationDate)){
    //                                       $expiration = (isset($responseLineItem->item[0]->expirationDate) ? $responseLineItem->item[0]->expirationDate:'N/A');
    //                                   }
                            }
                            //\Log::info(json_decode(json_encode($responseItems), true));


                    }

                }
            }
            catch (\Exception $e) {
                $response = array(
                    "status" =>"other",
                    'error_message' => $e->getMessage()
                );
                return response()->json($response,200);
            }

            //$tmpLineItems = array();
            //$lineItems = $order->{'_embedded'}->{'http://api.3plCentral.com/rels/orders/item'};

    //             foreach($lineItems as $item) {
    //                 foreach($item->readOnly->allocations as $allocation){
    //                     $item_desc = 'N/A';
    //                     try {
    //                         $request = $client->request('GET', 'https://secure-wms.com/inventory?pgsiz=1&pgnum=1&rql=ItemIdentifier.sku=='.$item->itemIdentifier->sku, [
    //                             'headers' => [
    //                             'Authorization' => 'Bearer '.$accessToken,
    //                             'Accept' => "application/hal+json"
    //                             ],
    //                             'json' => []
    //                         ]);
    //                     $responseItem = json_decode($request->getBody());
    //                     $item_desc = 'N/A';
    //                     $serial_number = 'N/A';
    //                     $expiration = 'N/A';
    //                     $lot_number = 'N/A';

    //                     //\Log::info(json_decode(json_encode($allocation)));
    //                     if(isset($responseItem->{'_embedded'})){
    //                         $responseLineItem = $responseItem->{'_embedded'};
    //                         \Log::info(json_decode(json_encode($responseLineItem), true));
    //                         if(isset($responseLineItem->item[0]->itemDescription)){
    //                             $item_desc = $responseLineItem->item[0]->itemDescription;

    //                             if(isset($responseLineItem->item[0]->serialNumber)){
    //                                 $serial_number =(($responseLineItem->item[0]->serialNumber != "") ? $responseLineItem->item[0]->serialNumber:'N/A');
    //                             }

    //                             if(isset($responseLineItem->item[0]->lotNumber)){
    //                                 $lot_number = (($responseLineItem->item[0]->lotNumber != "") ? $responseLineItem->item[0]->lotNumber:'N/A');
    //                             }

    //                             if(isset($responseLineItem->item[0]->expirationDate)){
    //                                 $expiration = (isset($responseLineItem->item[0]->expirationDate) ? $responseLineItem->item[0]->expirationDate:'N/A');
    //                             }


    //                         }
    //                     }

    //                     } catch (\Exception $e) {
    //                         $response = array(
    //                             "status" =>"err",
    //                             'error_message' => $e->getMessage()
    //                         );
    //                         return response()->json($response,200);
    //                     }
    //                     $tmpLineItems[] = array(
    //                         'item_id' => $item->readOnly->orderItemId,
    //                         'sku' => $item->itemIdentifier->sku,
    //                         'description' => $item_desc,
    //                         'serial_number' => $serial_number,
    //                         'lot_number' => $lot_number,
    //                         'expiration' => $expiration,
    //                         'qty' => $item->qty,
    //                         'qty_packed' => 0,
    //                         'qty_remaining' => $item->qty,
    //                         'done' => ($item->qty > 0 ? false:true)
    //                     );
    //                 }
    //             }
        }

        $response = array(
            "status" =>"ok",
            "result" =>['order_details' => $returnOrder,'order_items' => $tmpLineItems]
        );
        return response()->json($response,200);
    }

    public function saveShipPack(Request $request)
    {


        $response = array(
            "status" =>"err",
        );

        if (
            //Gate::allows('can_see_ship_pack_tpl', auth()->user()) ||
            Gate::allows('can_see_ship_pack', auth()->user())
            //||Gate::allows('company-with-ship-pack', auth()->user())
        )
        {

          // WMS class
          $this->wms = new SecureWMS();

          //get the order details
          $parameters = [
              'detail' => 'All',
              'itemdetail' => 'All',
              'pgsiz' => 1,
              'pgnum' => 1,
              'rql' => 'readonly.OrderId=='.$request->transaction_id
          ];
          $response = $this->wms->sendRequest('/orders', $parameters, 'GET');
          $orderz = $response->{'_embedded'}->{'http://api.3plCentral.com/rels/orders/order'};
          foreach ($orderz as $order) {
              $returnOrder = $order;
          }

            //get the markup for carrier and service level
            $markup = 0;
            $shipping_vendor_id = 0;
            if(isset($request->carrier['ShippingAccountId'])){
                $ShippingMethod = ShippingCarrierMethods::where('account_number', $request->carrier['ShippingAccountId'])->where('value', $request->carrier['ServiceType'])->first();
                if (is_object($ShippingMethod)) {
                $markup = $ShippingMethod->markup;
                $shipping_vendor_id = $ShippingMethod->shipping_vendor_id;
                }
            }

            /** SHIP CADDIE */
            if(isset($request->carrier['Vendor']) && $request->carrier['Vendor'] == 'ShipCaddie'){
                $shipper = TplShipperAddress::where('tpl_customer_id',$request->customer_id)->first();
                if(!$shipper){
                    $response = [];
                    $response['message'] = 'No Address was setup for Customer ID: '.$request->customer_id;
                    return $response;
                }


                // Get Delivery Name
                $firstName = null;
                $lastName = null;
                if(isset($request->order_details['name'])){
                    if($request->order_details['name'] != 'null'){
                        $name = explode(' ',$request->order_details['name'],2);
                        $firstName = isset($name[0]) ? $name[0] : '';
                        $lastName = isset($name[1]) ? $name[1] : '';
                    }
                } elseif(isset($request->order_details['company'])) {
                    $name = explode(' ',$request->order_details['company'],2);
                    $firstName = isset($name[0]) ? $name[0] : '';
                    $lastName = isset($name[1]) ? $name[1] : '';
                }

                // Get Package Contents
                $scPackages = [];
                foreach($request->ship_package_data as $package){
                    $scPackages[] = [
                        'weight' => $package['weight'],
                        'length' => $package['length'],
                        'width' => $package['width'],
                        'height' => $package['height']
                    ];
                }

                $shipCaddie = new ShipCaddie;
                $shippingData = [
                    'from_company' => $shipper->first_name.' '.$shipper->last_name,
                    'from_name' => $shipper->first_name.' '.$shipper->last_name,
                    'from_address1' => $shipper->address,
                    'from_city' =>  $shipper->city,
                    'from_state' => $shipper->state,
                    'from_zip' => $shipper->zip,
                    'from_phone' => preg_replace("/[^0-9]/", "", $shipper->phone_number ),
                    'to_address1' => $request->order_details['address1'],
                    'to_address2' => (isset($request->order_details['address2'])) ? $request->order_details['address2'] : '',
                    'to_city' => $request->order_details['city'],
                    'to_state' => $request->order_details['state'],
                    'to_zip' => $request->order_details['zip'],
                    'company' => (isset($request->order_details['company'])) ? $request->order_details['company'] : '',
                    'to_phone' =>  preg_replace("/[^0-9]/", "", ($request->order_details['phoneNumber'] ?? '')),
                    'carrier_client_contract_id' => $request->carrier["CarrierContractId"],
                    'carrier_service_level_id' => $request->carrier["ServiceLevelID"],
                    'attention_of' => $firstName . ' '. $lastName,
                    'packages' => $scPackages,
                    'reference_number' => $returnOrder->referenceNum
                ];

                $shipment = $shipCaddie->addShipCaddieShipment($shippingData);
                if(isset($shipment->costDetails->shipmentId) && $shipment->costDetails->shipmentId > 0){
                    $shipmentId = $shipment->costDetails->shipmentId;
                    // Get Shipment Details
                    $shipmentDetails = $shipCaddie->getShipmentInformation($shipmentId);

                    // Retrofit shipcaddie functions to existing functions
                    // through $shipRushRecord variable
                    foreach($shipmentDetails->labels as $key=>$value){
                        if($key == 0){
                            $shipRushRecord['trackingNo'] = $value->trackingNumber;
                        }
                        $shipRushRecord['zpl'][] = $value->base64Label;
                    }
                    $shipRushRecord['shipmentId'] = $shipmentId;
                    $shipRushRecord['packageCost'] =  $shipment->costDetails->totalChargeAmount;

                }else{
                    $response = [];
                    $response['message'] = 'Error Shipping Through Shipcaddie '.$shipment->error->details[0];
                    return $response;
                }
            }else{
                /** SHIP RUSH INTEGRATION */
                $shipRushRecord = $this->createShipRushRecord(
                    $request->order_details, // Order Information (address,name etc.)
                    $request->ship_package_data, // Order Packages
                    $request->customer_id, // Customer ID
                    $request->carrier, // Selected Carrier,
                    $request->transaction_id, //transaction id,
                    $returnOrder->referenceNum,
                    $request->signature_required
                );
            }


            \Log::info("shippRushRecord");
            \Log::info(print_r($shipRushRecord, true));
            if($shipRushRecord == 'no_customer_address'){
              $response = [];
              $response['message'] = 'No Address was setup for Customer ID: '.$request->customer_id;
              return $response;
            } elseif (!is_array($shipRushRecord)) {
              $response = [];
              $response['message'] = $shipRushRecord;
              return $response;
            }

            \Log::info("ShipRush Data");
            \Log::info(print_r($shipRushRecord, true));

            $company = Companies::find(\Auth::user()->companies_id);
            $order_id = $request->transaction_id;
            $errs = array();
            $apires = array();

            // Get Carrier NAME
            /*
            if (preg_match("/USPS/i", $request->carrier['Name'])) {
                $carrierName = 'USPS';
            } elseif (preg_match("/DHL/i", $request->carrier['Name'])) {
                $carrierName = 'DHL';
            } elseif (preg_match("/UPS/i", $request->carrier['Name'])) {
                $carrierName = 'UPS';
            } elseif (preg_match("/FEDEX/i", $request->carrier['Name'])) {
                $carrierName = 'FEDEX';
            } elseif (preg_match("/^FM/i", $request->carrier['Name']) || $request->carrier['AccountName'] == 'FirstMile') {
                $carrierName = 'FirstMile';
            }*/

            $carrierName = $request->carrier['AccountName'];

            $totalWeight = 0;

            foreach($request->ship_package_data as $package){
                if(isset($package['weight'])){
                    $totalWeight = $totalWeight + $package['weight'];
                }
            }

            /*
            $accessToken = $this->getTPLAccessToken();
            if($accessToken != null){

                // Crude Route Request
                $routingApiUrl = 'https://secure-wms.com/orders/'.$request->transaction_id.'/routing';
                $routingArray = array(
                    'isCod' => false,
                    'isInsurance' => false,
                    'requiresDeliveryConf' => false,
                    'requiresReturnReceipt' => false,
                    'scacCode' => 'test',
                    'carrier' => 'test',
                    'mode' => 'test',
                    // 'account' => 'prepaid',
                    'shipPointZip' => $request->zip,
                    // 'capacityTypeIdentifier' =>
                    // array (
                    //   'name' => 'str',
                    //   'id' => 2,
                    // ),
                    // 'loadNumber' => 'str',
                    // 'billOfLading' => 'str',
                    'trackingNumber' => $shipRushRecord['trackingNo'],
                    // 'trailerNumber' => 'str',
                    // 'sealNumber' => 'str',
                    // 'doorNumber' => 'str',
                    // 'pickupDate' => '2016-12-25T23:00:00',
                    // 'notes' => 'str',
                    'numUnits1' => 1,
                    'numUnits1TypeId' => 0,
                    'numUnits2' => 0,
                    'numUnits2TypeId' => 0,
                    'totalWeight' => 21,
                    'totalVolume' => 0,
                );


                $etag = $this->getEtag($request->transaction_id,$accessToken);
                $shipmentRoute = $this->createShipRoute($request->transaction_id,$accessToken,$etag,$routingArray);

                if($shipmentRoute['response']){
                    $apires['routing_response'] = $shipmentRoute['response'];
                }

                if($shipmentRoute['err']){
                    $errs[] = $shipmentRoute['err'];
                }
            }
            */

            \Log::info("shipment 1");

            $tpl_company_id = 0;
            $thisOrder = null;

            // Confirm and Close the order
            try {
                $parameters = [
                    'detail' => 'All',
                    'itemdetail' => 'All',
                    'pgsiz' => 1,
                    'pgnum' => 1,
                    'rql' => 'readonly.OrderId=='.$order_id
                ];
                $response = $this->wms->sendRequest('/orders', $parameters, 'GET');

                $orders = $response->{'_embedded'}->{'http://api.3plCentral.com/rels/orders/order'};
                if (isset($orders[0])) {
                  $tpl_company_id = $orders[0]->readOnly->customerIdentifier->id;

                  //get the etag
                  $response = $this->wms->sendRequest("/orders/$order_id", null, 'GET', true);
                  $eTag = $response['headers']['ETag'][0];

                  //order confirmer
                  $confirmer = [
                      'OrderConfirmInfo' => [
                      ],
                      'trackingNumber' => $shipRushRecord['trackingNo'],
                      'recalcAutoCharges' => true,
                      'billing' => ['billingCharges' => [
                          [
                              'chargeType' => 3,
                              'details' => [],
                              'subtotal' => $shipRushRecord['packageCost'] //total shipping cost goes here plus the markup
                          ]
                        ]
                      ]
                  ];
                  //confirm order
                  $response = $this->wms->sendRequest("/orders/$order_id/confirmer", $confirmer, 'POST', false, ['If-Match' => $eTag]);
                  $apires['confirm_response'] = $response;

                  //add the billable shipment amount
                  $parameters = [
                      'detail' => 'All',
                      'itemdetail' => 'All',
                      'pgsiz' => 1,
                      'pgnum' => 1,
                      'rql' => 'readonly.OrderId=='.$order_id
                  ];
                  $response = $this->wms->sendRequest('/orders', $parameters, 'GET');

                  \Log::info("shipment 2");

                  $orders = $response->{'_embedded'}->{'http://api.3plCentral.com/rels/orders/order'};
                  if (isset($orders[0])) {
                    //get the etag
                    $response = $this->wms->sendRequest("/orders/$order_id", null, 'GET', true);
                    $thisOrder = $response;

                    $eTag = $response['headers']['ETag'][0];
                    //prepare the order with tracking information
                    $orders[0]->routingInfo->carrier = $carrierName;
                    $orders[0]->routingInfo->mode = $request->carrier['Name'];


                    // Get Shipper Address
                    $shipperAddress = TplShipperAddress::where('tpl_customer_id',$request->customer_id)->first();

                    if($shipperAddress){
                        if($shipperAddress->account_number != NULL){
                            $orders[0]->routingInfo->account = (string)$shipperAddress->account_number; //MAKE DYNAMIC
                            $orders[0]->routingInfo->shipPointZip = $shipperAddress->zip; //MAKE DYNAMIC
                            $orders[0]->billingCode = 'BillThirdParty';
                        } else {
                          $orders[0]->routingInfo->account = null;
                        }
                    } else {
                      $orders[0]->routingInfo->account = null;
                    }

                    $orders[0]->totalWeight = $totalWeight;
                    if (isset($orders[0]->billing->billingCharges) && is_array($orders[0]->billing->billingCharges)) {
                      $orders[0]->billing->billingCharges[] =
                          [
                            'chargeType' => 3,
                            'subtotal' => round(($shipRushRecord['packageCost'] + ($markup*$shipRushRecord['packageCost'])),2), //total shipping cost goes here plus the markup
                            'details' => []
                          ];
                    } else {
                      $orders[0]->billing = ['billingCharges' => [
                          [
                              'chargeType' => 3,
                              'subtotal' => round(($shipRushRecord['packageCost'] + ($markup*$shipRushRecord['packageCost'])),2), //total shipping cost goes here plus the markup
                              'details' => []
                          ]
                        ]
                      ];
                    }

                    \Log::info(print_r($orders[0], true));

                    //push tracking number updates to WMS
                    if ($orders[0]->readOnly->customerIdentifier->id != 34) { //temporarily do not make this API call for Viome
                      $response = $this->wms->sendRequest("/orders/$order_id", $orders[0], 'PUT', false, ['If-Match' => $eTag]);
                    }

                  }
                }
            } catch (\Exception $e) {
                $errs[] = "Error: ".$e->getMessage();
                //print "Error: ".$e->getMessage();
                \Log::info("Errors with Confirming Order - VOID VOID :: ".$request->transaction_id);
                \Log::info($e);
                $response = [];
                $response['message'] = "PLEASE VOID LABEL IN SHIPRUSH - Error while confirming order :: ".$e->getMessage();
                return $response;
            }

            if($response){
                $apires['confirm_response'] = $response;
            }



            // Save transaction for auditing
            if($company){
                $sp_submission = new ShipPackSubmissions;
                $sp_submission->user_id = \Auth::user()->id;
                $sp_submission->companies_id = $tpl_company_id;
                $sp_submission->ship_package_data = json_encode($request->ship_package_data);
                $sp_submission->carrier = $request->carrier['Name'];
                $sp_submission->carrier_service = $request->carrier_service;
                $sp_submission->carrier_service_id = $request->carrier_service_id;
                $sp_submission->tpl_order_id = $request->transaction_id;
                $sp_submission->tpl_customer_id = $tpl_company_id;
                $sp_submission->shipment_id = $shipRushRecord['shipmentId'];
                $sp_submission->shipto_name = isset($request->order_details['name']) ? $request->order_details['name'] : $request->order_details['company'];
                $sp_submission->shipto_address = $request->order_details['address1'];
                $sp_submission->shipto_zip = $request->order_details['zip'];
                $sp_submission->base_64_zpl = json_encode($shipRushRecord['zpl']);
                $sp_submission->reference_number = (is_array($thisOrder)) ? $thisOrder['body']->referenceNum : null;
                $sp_submission->order_created_date = (is_array($thisOrder)) ? date('Y-m-d H:i:s', strtotime($thisOrder['body']->readOnly->creationDate)) : null;
                $sp_submission->tracking_number = $shipRushRecord['trackingNo'];
                $sp_submission->shipping_cost = $shipRushRecord['packageCost'];
                $sp_submission->shipping_cost_with_markup = round(($shipRushRecord['packageCost'] + ($markup*$shipRushRecord['packageCost'])),2);
                $sp_submission->weight = $totalWeight;
                $sp_submission->shipping_markup = $markup;
                $sp_submission->shipping_vendor_id = $shipping_vendor_id;
            }

            if($sp_submission->save()){
                $response = array(
                    "status" =>"saved",
                    'fulfillment_id'=> $company->fulfillment_ids,
                    'order_id' => $request->transaction_id,
                    'errs' => $errs,
                    'api_res'=>$apires,
                    'shiprush_response'=>$shipRushRecord,
                    'shipperAddress' => $shipperAddress
                );
            }

        }
        $request->session()->put('spack_response', $response);
        return response()->json($response,200);
    }

    private function getTPLAccessToken()
    {
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
            //  print "Error1:";
         }

         return $accessToken;
    }

    private function getEtag($transactionId,$accessToken)
    {
        $client = new Client();
        try {
            $req = $client->request('GET', 'https://secure-wms.com/orders/'.$transactionId, [
            'headers' => [
                'Authorization' => 'Bearer '.$accessToken,
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

    private function createShipRoute($transactionId,$accessToken,$etag,$requestArray)
    {
        $apiRoute = 'https://secure-wms.com/orders/'.$transactionId.'/routing';
        $result = array(
            'err' => null,
            'response' => null
        );

        $client = new Client();
        try {
            $req = $client->request('PUT', $apiRoute, [
                'headers' => [
                    'Authorization' => 'Bearer '.$accessToken,
                    'Accept' => "application/hal+json",
                    'If-Match' => $etag
                ],
                'json' => $requestArray
            ]);

            $response = json_decode($req->getBody());
            if($response){
                $result['response'] = $response;
            }

        }catch (\Exception $e) {
            $result['err'] = $e->getMessage();
        }

        return $result;
    }

    private function confirmShipPack($transactionId,$accessToken,$etag,$requestArray)
    {
        $apiRoute = 'https://secure-wms.com/orders/'.$transactionId.'/confirmer';
        $result = array(
            'err' => null,
            'response' => null
        );
        $client = new Client();
        try {
            $req = $client->request('POST', $apiRoute, [
                'headers' => [
                    'Authorization' => 'Bearer '.$accessToken,
                    'Accept' => "application/hal+json",
                    'Content-Type' => 'application/hal+json; charset=utf-8',
                    'If-Match' => $etag
                ],
                'json' => $requestArray
            ]);

            $response = json_decode($req->getBody());
            if($response){
                $result['response'] = $response;
            }

        }catch (\GuzzleHttp\Exception\BadResponseException $e) {
            $result['err'] = json_decode($e->getResponse()->getBody()->getContents(), true);
        }

        return $result;
    }

    private function createShipStationRecord($orderDetails, $printer, $customer_id, $carrier_code, $carrier_service_code)
    {
        $return = array();

        if(!isset($orderDetails['name'])){
            $orderDetails['name'] = null;
        }

        // //book shipment and create label
        // $ShiprushXMLRequest = $this->createShiprushXMLRequest($carrierId);
        $body = $this->createShipStationBody($orderDetails, $carrier_code, $carrier_service_code);

        try {
            // REF: https://shipstation.docs.apiary.io/#reference/shipments/create-shipment-label/create-shipment-label

            $client = new Client();
            $response = $client->request('POST', 'https://ssapi.shipstation.com/shipments/createlabel', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => config('shipping.post_auth'),
                ],
                'body' => $body
            ]);
            return $response;
            // print "$ShiprushXMLRequest\n";
            // $response = simplexml_load_string($request->getBody(),'SimpleXMLElement',LIBXML_NOCDATA);
            // print_r($response);

            if ($response->IsSuccess == 'true') {
                // print "Shipment ID: ".$response->ShipTransaction->Shipment->ShipmentId."\n";

                $shipmentId = (string) $response->ShipTransaction->Shipment->ShipmentId->asXML();
                $shipmentId = str_replace('<ShipmentId>','',$shipmentId);
                $shipmentId = str_replace('</ShipmentId>','',$shipmentId);

                $trackingNo = (string) $response->ShipTransaction->Shipment->ShipmentNumber->asXML();
                $trackingNo = str_replace('<ShipmentNumber>','',$trackingNo);
                $trackingNo = str_replace('</ShipmentNumber>','',$trackingNo);

                $packageCost = (string) $response->ShipTransaction->Shipment->TotalCharges->asXML();
                $packageCost = str_replace('<TotalCharges>','',$packageCost);
                $packageCost = str_replace('</TotalCharges>','',$packageCost);


                $result['shipmentId'] =  $shipmentId;
                $result['trackingNo'] =  $trackingNo;
                $result['packageCost'] = $packageCost;

                return $result;
            }
        }
        catch (\GuzzleHttp\Exception\ServerException $e) {
            return $e->getResponse()->getBody()->getContents();
        }
    }


    private function createShipRushRecord($orderDetails, $packages, $customer_id, $carrier, $transactionId, $referenceNumber, $signature_required = false)
    {
        $return = array();

        $firstName = null;
        $lastName = null;

        if(isset($orderDetails['name'])){
            if($orderDetails['name'] != 'null'){
                $name = explode(' ',$orderDetails['name'],2);

                $firstName = isset($name[0]) ? $name[0] : '';
                $lastName = isset($name[1]) ? $name[1] : '';
            }
        } elseif(isset($orderDetails['company'])) {
          $name = explode(' ',$orderDetails['company'],2);

          $firstName = isset($name[0]) ? $name[0] : '';
          $lastName = isset($name[1]) ? $name[1] : '';
        }


        //printerLabels
        // $this->ShipRushSelectedPrinter = $printer;

        //delivery address
        $this->ShipRushDeliveryAddress = new ShipRushAddress();
        $this->ShipRushDeliveryAddress->FirstName = $firstName;
        $this->ShipRushDeliveryAddress->LastName = $lastName;
        if (strlen($orderDetails['company']) > 1 && strtolower($orderDetails['company']) != strtolower("$firstName $lastName")) {
          $this->ShipRushDeliveryAddress->Company = $orderDetails['company'];
        }
        $this->ShipRushDeliveryAddress->Address1 = $orderDetails['address1'];
        $this->ShipRushDeliveryAddress->Address2 = $orderDetails['address2'];
        $this->ShipRushDeliveryAddress->City = $orderDetails['city'];
        $this->ShipRushDeliveryAddress->State = $orderDetails['state'] ?? '';
        $this->ShipRushDeliveryAddress->Country = $orderDetails['country'];
        $this->ShipRushDeliveryAddress->PostalCode = $orderDetails['zip'];
        $this->ShipRushDeliveryAddress->PhoneNumber = preg_replace("/[^0-9]/", "", ($orderDetails['phoneNumber'] ?? '') );
        if (strlen($this->ShipRushDeliveryAddress->PhoneNumber) < 5) {
            $this->ShipRushDeliveryAddress->PhoneNumber = '8015690465';
        }
        //$this->ShipRushDeliveryAddress->PhoneNumber = "";

        $shipper = TplShipperAddress::where('tpl_customer_id',$customer_id)->first();

        if(!$shipper){
            return 'no_customer_address';
        }

        //shipper address
        $this->ShipRushShipperAddress  = new ShipRushAddress();
        $this->ShipRushShipperAddress->FirstName = "SHIPPING";
        $this->ShipRushShipperAddress->LastName = "DEPT";
        $this->ShipRushShipperAddress->Company = $shipper->first_name.' '.$shipper->last_name;
        $this->ShipRushShipperAddress->Address1 = $shipper->address;
        $this->ShipRushShipperAddress->City = $shipper->city;
        $this->ShipRushShipperAddress->State = $shipper->state;
        $this->ShipRushShipperAddress->Country = $shipper->country;
        $this->ShipRushShipperAddress->PostalCode = $shipper->zip;
        $this->ShipRushShipperAddress->PhoneNumber = preg_replace("/[^0-9]/", "", $shipper->phone_number );
        if (strlen($this->ShipRushDeliveryAddress->PhoneNumber) < 5) {
            $this->ShipRushShipperAddress->PhoneNumber = '8015690465';
        }

        // $this->ShipRushWeight = 0;

        // foreach($packages as $package){
        //     if(isset($package['weight'])){
        //         $this->ShipRushWeight = $this->ShipRushWeight + $package['weight'];
        //     }
        // }

        $this->ShipRushPackageType = '02';
        $this->ShipRushInsuranceAmount = 0.00;

        /*
        $response = $this->getRates();


        if ($response->IsSuccess == 'true') {
            $cheapest = 100000;
            $maxDays = 5;
            $this->ShipRushSelectedServiceType = null;
            foreach ($response->AvailableServices->AvailableService as $service) {
            // print "looping service: $service->Name ($service->Total)\n";
            if ($service->Total < $cheapest && $service->TimeInTransitDays <= $maxDays && !preg_match("/USPS/", $service->Name) && !preg_match("/DHL/", $service->Name) && !preg_match("/XParcel/", $service->Name) && !preg_match("/FirstMile/", $service->Name) && !preg_match("/PMOD/", $service->Name)) {
                $cheapest = $service->Total;
                $this->ShipRushSelectedServiceType = $service;
            }
            }
        } else {
            //error processing request
            // print "ERROR: ".$request->getBody()."\n";
        }

        if ($this->ShipRushSelectedServiceType != null) {
            // print "SELECTED SERVICE:\n";
            // print_r($this->ShipRushSelectedServiceType);

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
        **/


        //book shipment and create label
        if ($customer_id != NULL) {
            $ShiprushXMLRequest = $this->createShiprushXMLRequest($carrier, $packages, $transactionId, $referenceNumber, $orderDetails, $customer_id, $signature_required);
        } else {
            $ShiprushXMLRequest = $this->createShiprushXMLRequest($carrier, $packages, $transactionId, $referenceNumber, $orderDetails, null, $signature_required);
        }

        try {
          \Log::info($ShiprushXMLRequest);

          $client = new Client();
          $request = $client->request('POST', 'https://api.my.shiprush.com/shipmentservice.svc/shipment/ship', [
          'headers' => [
              'X-SHIPRUSH-SHIPPING-TOKEN' => '88c9a9a7-de94-49f4-a6f6-0ff33bfe8f8b',
              'Content-Type' => 'application/xml'
          ],
          'body' => $ShiprushXMLRequest
          ]);

          \Log::info("ShipRush response");
          \Log::info($request->getBody());

          $response = simplexml_load_string($request->getBody(),'SimpleXMLElement',LIBXML_NOCDATA);
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

            $zpl = array();

            foreach($response->ShipTransaction->Shipment->Documents->PaperDocument as $document){
                $tmp_zpl = null;
                $tmp_zpl = (string) $document->ContentMimeEncoded->asXML();
                $tmp_zpl = str_replace('<ContentMimeEncoded>','',$tmp_zpl);
                $tmp_zpl = str_replace('</ContentMimeEncoded>','',$tmp_zpl);
                $zpl[] = $tmp_zpl;
            }


            $result['shipmentId'] =  $shipmentId;
            $result['trackingNo'] =  $trackingNo;
            $result['packageCost'] = $packageCost;
            $result['zpl'] = $zpl;

            return $result;
          } else {
            $errors = [];
            $messages = (array)$response->Messages;
            \Log::info(print_r($messages['ShippingMessage'], true));
            if (is_array($messages['ShippingMessage'])) {
              foreach ($messages['ShippingMessage'] as $message) {
                $errors[] = (string)$message->Text;
              }
            } else {
              $errors[] = (string)$response->Messages->ShippingMessage->Text;
            }
            return implode("\n", $errors);
          }
        } catch (\GuzzleHttp\Exception\ServerException $e) {
          //dd($e->getResponse()->getBody()->getContents());
          return "Error processing the request: ".$e->getResponse()->getBody()->getContents();
        }
    }

    private function getRates($isTest = false)
    {
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
        $package->appendChild($xml->createElement('DCISType', 'DCS'));
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
            print_r($response->AvailableServices);
        }
        catch (\Exception $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            // print "Error: $responseBodyAsString\n";
            exit;
        }
    }

    public function createShiprushXMLRequest($carrier, $packages, $transactionId, $referenceNumber, $orderDetails, $thirdPartyBilling_customer_id = null, $signature_required=false)
    {
        // Get Carrier ID
        /*
        if (preg_match("/USPS/i", $carrier['Name'])) {
            $carrierId = 3;
        } elseif (preg_match("/DHL/i", $carrier['Name'])) {
            $carrierId = 29;
        } elseif (preg_match("/UPS/i", $carrier['Name'])) {
            $carrierId = 0;
        } elseif (preg_match("/FEDEX/i", $carrier['Name'])) {
            $carrierId = 1;
        } elseif (preg_match("/^FM/i", $carrier['Name']) || $carrier['AccountName'] == 'FirstMile') {
            $carrierId = 32;
        }*/
        $carrierId = $carrier['CarrierId'];

        $xml = new \DOMDocument();
        $xml->formatOutput = true;
        $ShipRequest = $xml->createElement('ShipRequest');

        $ShipTransaction = $xml->createElement('ShipTransaction');
        $Shipment = $xml->createElement('Shipment');
        $Shipment->appendChild($xml->createElement('IsTest',0));
        $Shipment->appendChild($xml->createElement('Carrier',$carrierId)); //test carrier on usps
        $Shipment->appendChild($xml->createElement('UPSServiceType',$carrier['ServiceType']));

        $Deliveryaddress = $xml->createElement('DeliveryAddress');
        $Deliveryaddress->appendChild($this->ShipRushDeliveryAddress->getFormattedAddress($xml));
        $Shipment->appendChild($Deliveryaddress);
        $Shipperaddress = $xml->createElement('ShipperAddress');
        $Shipperaddress->appendChild($this->ShipRushShipperAddress->getFormattedAddress($xml));
        $Shipment->appendChild($Shipperaddress);
        $ShipTransaction->appendChild($Shipment);

        $ShippingAccount = $xml->createElement('ShippingAccount');
        $ShippingAccount->appendChild($xml->createElement('ShippingAccountId', $carrier['ShippingAccountId']));
        $Shipment->appendChild($ShippingAccount);
        $shipperAddress = TplShipperAddress::where('tpl_customer_id',$thirdPartyBilling_customer_id)->first();

        if ($thirdPartyBilling_customer_id != null) { //ARBIE CHANGE THIS TO BE DYNAMIC
            // Get Shipper Address
            if($shipperAddress){
                if($shipperAddress->account_number != NULL){
                    $shipmentChargeType = $xml->createElement('ShipmentChgType','TPB');
                    $thirdPartyBilling = $xml->createElement('Shipper3PartyBillingAddress');
                    $thirdPartyBilling->appendChild($xml->createElement('UPSAccountNumber', $shipperAddress->account_number)); //DYNAMIC
                    $thirdPartyBillingAddress = $xml->createElement('Address');
                    $thirdPartyBillingAddress->appendChild($xml->createElement('StateOrEmpty'));
                    $thirdPartyBillingAddress->appendChild($xml->createElement('Country', 'US'));
                    $thirdPartyBillingAddress->appendChild($xml->createElement('StateAsString', 'UT'));
                    $thirdPartyBillingAddress->appendChild($xml->createElement('CountryAsString', 'US'));
                    $thirdPartyBillingAddress->appendChild($xml->createElement('PostalCode', '84043')); //DYNAMIC
                    $thirdPartyBillingAddress->appendChild($xml->createElement('City', $shipperAddress->city)); //DYNAMIC
                    $thirdPartyBilling->appendChild($thirdPartyBillingAddress);
                    $Shipment->appendChild($shipmentChargeType);
                    $Shipment->appendChild($thirdPartyBilling);
                }
            }
        }


        foreach($packages as $package){
            if(isset($package['weight'])){
                $weight = $package['weight'];
                $length = $package['length'];
                $width = $package['width'];
                $height = $package['height'];

                if($shipperAddress){
                    if($shipperAddress->minimum_package_weight != NULL){
                        if($package['weight'] < $shipperAddress->minimum_package_weight){
                            $weight =  $shipperAddress->minimum_package_weight;
                        }
                    }
                }
                $Package = $xml->createElement('Package');
                $Package->appendChild($xml->createElement('PackageActualWeight', $weight));
                $Package->appendChild($xml->createElement('PkgLength', $length));
                $Package->appendChild($xml->createElement('PkgWidth', $width));
                $Package->appendChild($xml->createElement('PkgHeight', $height));
                $Package->appendChild($xml->createElement('PackageType', $this->ShipRushPackageType));
                if($signature_required){
                    $Package->appendChild($xml->createElement('DCISType', 'DCS'));
                }
                $Package->appendChild($xml->createElement('InsuranceAmount', $this->ShipRushInsuranceAmount));
                $Package->appendChild($xml->createElement('PackageReference1', $referenceNumber));
                $Package->appendChild($xml->createElement('Reference', $referenceNumber));
                $Package->appendChild($xml->createElement('Reference1', $referenceNumber));
                $Shipment->appendChild($Package);
            }
        }

        //handle international shipments
        if (strtoupper($orderDetails['country']) != 'US' && strtoupper($orderDetails['country']) != 'USA' && strtoupper($orderDetails['country']) != 'UNITED STATES') {
          //210794
          $totalWeight = 0;
          $totalItems = 0;
          foreach ($packages as $package) {
            $totalWeight += $package['weight'];
            foreach ($package['items'] as $item) {
              $totalItems += 1;
            }
          }

        $international = $xml->createElement('International');
        $international->appendChild($xml->createElement('fDXB13AFilingOptionField', 0));
        $international->appendChild($xml->createElement('IntlFilingType', 'FTR'));
        $international->appendChild($xml->createElement('FDXB13AFilingOption', 0));
        $international->appendChild($xml->createElement('DescriptionOfGoods', "Consumer Goods"));
        $international->appendChild($xml->createElement('InvoiceLineTotals', 1));
        $international->appendChild($xml->createElement('TermsOfShipment', 'CFR'));
        $international->appendChild($xml->createElement('ReasonForExport', 'SOLD'));
        $international->appendChild($xml->createElement('FreightCharges', 0));
        $international->appendChild($xml->createElement('DutiesBillCode', 'CBS'));
        $international->appendChild($xml->createElement('FTSRExemptionNumber', 'Auto'));
        $international->appendChild($xml->createElement('USPSUseFormCP72', 0));
        $international->appendChild($xml->createElement('EELPFC', 'Auto'));

          foreach ($packages as $package) {
            foreach ($package['items'] as $item) {

              $commodity = $xml->createElement('Commodity');
              $commodity->appendChild($xml->createElement('NAFTAProducerDetermination', 0));
              $commodity->appendChild($xml->createElement('LineCurrencyCode', 'USD'));
              $commodity->appendChild($xml->createElement('LineExtendedAmt', round(1/$totalItems,1)));
              $commodity->appendChild($xml->createElement('LineUnitAmtPrice', round(1/$totalItems,1)));
              $commodity->appendChild($xml->createElement('LineQty', '1'));
              $commodity->appendChild($xml->createElement('LineQtyUOM', 'EA'));
              $commodity->appendChild($xml->createElement('LineMerchDesc1', $item['sku']));
              $commodity->appendChild($xml->createElement('LineMerchDesc3', 'US'));
              $commodity->appendChild($xml->createElement('CommodityWeight', round($totalWeight/$totalItems,2)));
              $commodity->appendChild($xml->createElement('NumberOfPackagesPerCommodity', 0));
              $commodity->appendChild($xml->createElement('NAFTAPreferenceCriterion', 'A'));
              $international->appendChild($commodity);
            }
          }

          $Shipment->appendChild($international);
          $Shipment->appendChild($xml->createElement('IsInternational', '1'));
        }

        $ShipSettings = $xml->createElement('ShipSettings');
        $PrinterShippingLabel = $xml->createElement('PrinterShippingLabel');
        $PrinterShippingLabel->appendChild($xml->createElement('AutoprintShippingLabel', 'false'));
        $PrinterShippingLabel->appendChild($xml->createElement('LabelType', 'ZPL'));
        $ShipSettings->appendChild($PrinterShippingLabel);

        $Order = $xml->createElement('Order');
        $Order->appendChild($xml->createElement('OrderNumber', $transactionId));
        $ShipTransaction->appendChild($Order);

        $ShipRequest->appendChild($ShipTransaction);
        $ShipRequest->appendChild($ShipSettings);

        $ShipRequest->setAttribute('xmlns:xsi',"http://www.w3.org/2001/XMLSchema-instance");
        $ShipRequest->setAttribute('xmlns:xsd',"http://www.w3.org/2001/XMLSchema");
        $xml->appendChild($ShipRequest);
        $xmlString = $xml->saveXML();
        \Log::info($xmlString);
        return $xmlString;

    }

    public function createShipStationBody($orderDetails, $carrier_code, $carrier_service_code)
    {
        return json_encode(array(
            "carrierCode" => $carrier_code,
            "serviceCode" => $carrier_service_code,
            "packageCode" => "package",
            "confirmation" => "none",
            "shipDate" => "2019-06-14",
            "weight" => array(
                "value" => 3,
                "units" => "ounces"
            ),
            "dimensions" => array(
                "units" => "inches",
                "length" => 7,
                "width" => 5,
                "height" => 6
            ),
            "shipFrom" => array(
                "name" => $orderDetails['name'],
                "company" => $orderDetails['company'],
                "street1" => $orderDetails['address1'],
                "street2" => "Ste 2353242",
                "street3" => null,
                "city" => $orderDetails['city'],
                "state" => $orderDetails['state'],
                "postalCode" => $orderDetails['zip'],
                "country" => $orderDetails['country'],
                "phone" => $orderDetails['phoneNumber'],
                "residential" => false
            ),
            "shipTo" => array(
                "name" => "The President",
                "company" => "US Govt",
                "street1" => "1600 Pennsylvania Ave",
                "street2" => "Oval Office",
                "street3" => null,
                "city" => "Washington",
                "state" => "DC",
                "postalCode" => "20500",
                "country" => "US",
                "phone" => null,
                "residential" => false
            ),
            "insuranceOptions" => null,
            "internationalOptions" => null,
            "advancedOptions" => null,
            "testLabel" => false
        ));
    }

    public function getCarriers()
    {
        try {
            $client = new Client();
            $request = $client->request('GET', 'https://ssapi.shipstation.com/carriers', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => config('shipping.post_auth'),
                ],
            ]);
            if( $request->getStatusCode() == 200) {
                return $request->getBody();
            }
            else {
                return [];
            }
        }
        catch (\GuzzleHttp\Exception\ServerException $e) {
           return $e->getResponse()->getBody()->getContents();
        }
    }

    public function getServices($code)
    {
        try {
            $client = new Client();
            $request = $client->request('GET', 'https://ssapi.shipstation.com/carriers/listservices?carrierCode='.$code, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => config('shipping.post_auth'),
                ],
            ]);
            if( $request->getStatusCode() == 200) {
                return $request->getBody();
            }
            else {
                return [];
            }
        }
        catch (\GuzzleHttp\Exception\ServerException $e) {
           return $e->getResponse()->getBody()->getContents();
        }
    }

    public function getShippingClient()
    {
        // $client = ShippingClient::IsCustomerRequiredScan( 77 )->first();
        //$client = ShippingClient::IsCustomerRequiredScan( request()->input('customer_id') )->first();
        $client = ShippingClient::where('tpl_client_id', request()->input('customer_id'))->first();
        if ($client) {
            return response()->json([
                'status' => 'success',
                'found' => true,
                'client' => $client
            ], 200);
        }

        return response()->json([
            'status' => 'success',
            'found' => false,
            'client' => null
        ], 200);
    }

    public function getShipRushRates($isTest = false)
    {
        $order = request()->input('order');
        $packages = request()->input('packages');
        $signature_required = request()->input('signature_required');

        //delivery address
        $ShipRushDeliveryAddress = new ShipRushAddress();
        if (isset($order['name'])) {
          $ShipRushDeliveryAddress->FirstName = explode(' ',$order['name'])[0];
          $ShipRushDeliveryAddress->LastName = explode(' ',$order['name'])[1] ?? ' ';
        } else {
          $ShipRushDeliveryAddress->FirstName = explode(' ',$order['company'])[0];
          $ShipRushDeliveryAddress->LastName = explode(' ',$order['company'])[1] ?? ' ';
        }
        $ShipRushDeliveryAddress->Address1 = $order['address1'];
        $ShipRushDeliveryAddress->City = $order['city'];
        $ShipRushDeliveryAddress->State = $order['state'] ?? '';
        $ShipRushDeliveryAddress->Country = $order['country'];
        $ShipRushDeliveryAddress->PostalCode = $order['zip'];
        $ShipRushDeliveryAddress->PhoneNumber = preg_replace("/[^0-9]/", "", ($order['phoneNumber'] ?? '') );
        if (strlen($ShipRushDeliveryAddress->PhoneNumber) < 5) {
          $ShipRushDeliveryAddress->PhoneNumber = '8015690465';
        }

        $shipper = TplShipperAddress::where('tpl_customer_id',request()->input('customer_id'))->first();
        if(!$shipper){
            return 'no_customer_address';
        }
        //shipper address
        $ShipRushShipperAddress  = new ShipRushAddress();
        $ShipRushShipperAddress->FirstName = $shipper->first_name;
        $ShipRushShipperAddress->LastName = $shipper->last_name;
        $ShipRushShipperAddress->Address1 = $shipper->address;
        $ShipRushShipperAddress->City = $shipper->city;
        $ShipRushShipperAddress->State = $shipper->state;
        $ShipRushShipperAddress->Country = $shipper->country;
        $ShipRushShipperAddress->PostalCode = $shipper->zip;
        $ShipRushShipperAddress->PhoneNumber = preg_replace("/[^0-9]/", "", $shipper->phone_number );
        if (strlen($ShipRushDeliveryAddress->PhoneNumber) < 5) {
          $ShipRushShipperAddress->PhoneNumber = '8015690465';
        }

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

        foreach($packages as $package){
            if(count($package['items']) > 0){
                $weight = $package['weight'];
                $length = $package['length'];
                $width = $package['width'];
                $height = $package['height'];

                if($shipper->minimum_package_weight != NULL){
                    if($package['weight'] < $shipper->minimum_package_weight){
                        $weight =  $shipper->minimum_package_weight;
                    }
                }

                $package = $xml->createElement('Package');
                if($signature_required){
                    $package->appendChild($xml->createElement('DCISType', 'DCS'));
                }
                $package->appendChild($xml->createElement('PackageActualWeight', $weight));
                $package->appendChild($xml->createElement('PkgLength', $length));
                $package->appendChild($xml->createElement('PkgWidth', $width));
                $package->appendChild($xml->createElement('PkgHeight', $height));
                $package->appendChild($xml->createElement('PackageType', $ShipRushPackageType));
                $package->appendChild($xml->createElement('InsuranceAmount', $ShipRushInsuranceAmount));
                $shipment->appendChild($package);
            }
        }

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

        //\Log::info($xmlString);

        // var_dump($xmlString);
        //shiprush

        $services = [];
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

            //\Log::info($request->getBody());

            foreach ($response->AvailableServices->AvailableService as $rate) {
              //see if service is allowed and should be returned
              $found = false;
              foreach ($this->shipRushAccounts as $account) {
                foreach ($account['services'] as $service) {
                  if ($account['account_id'] == (string)$rate->ShippingAccountId && $service['service_type'] == (string)$rate->ServiceType && $service['packaging_type'] == (string)$rate->PackagingType) {
                    $rate = (object)$rate;
                    $rate->AccountName = preg_replace('/[\W\d_]/i', '', $account['account_name']);
                    $rate->CarrierId = $account['carrier_id'];
                    $services[] = $rate;
                    break;
                  }
                }
              }
            }
        }
        catch (\Exception $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            // print "Error: $responseBodyAsString\n";
            // return response()->json($responseBodyAsString);
        }

        // Filter carriers by
        $client = ShippingClient::where('tpl_client_id', request()->input('customer_id'))->first();
        $allowed_carriers = [];
        $allowed_service = [];
        if($client){
            if($client->carriers != null && strlen($client->carriers) > 1){
                $allowed_carriers = explode(',',$client->carriers);
                foreach($services as $key=>$service){
                    $hasMatch = false;
                    foreach($allowed_carriers as $carrier){
                        if(strpos(strtoupper($service->AccountName),strtoupper($carrier)) !== FALSE){
                            $hasMatch = true;
                            $allowed_service[] = $service;
                        }
                    }

                }
            }else{
                $allowed_service = $services;
            }
        }else{
            $allowed_service = $services;
        }

        // Get ShipCaddie rates
        $shipCaddiePackages = [];
        foreach($packages as $package){
            if(count($package['items']) > 0){
                if($shipper->minimum_package_weight != NULL){
                    if($package['weight'] < $shipper->minimum_package_weight){
                        $weight =  $shipper->minimum_package_weight;
                    }
                }
                $shipCaddiePackages[] = [
                    'weight' => $weight,
                    'length' => $length,
                    'width' => $width,
                    'height' => $height
                ];
            }
        }

        $shipCaddieData = [
            'from_address1' => $shipper->address,
            'from_city' => $shipper->city,
            'from_state' => $shipper->state,
            'from_zip' => $shipper->zip,
            'from_phone' => preg_replace("/[^0-9]/", "", $shipper->phone_number ),
            'to_address1' => $order['address1'],
            'to_city' => $order['city'],
            'to_state' => $order['state'] ?? '',
            'to_zip' => $order['zip'],
            'to_phone' => preg_replace("/[^0-9]/", "", ($order['phoneNumber'] ?? '') ),
            'packages' => $shipCaddiePackages
        ];

        $shipCaddie = new ShipCaddie;
        $contractIds = $shipCaddie->getCarriersContractId($this->allowedShipcadieAffiliates);
        $shipCaddyRates = $shipCaddie->getRates($shipCaddieData,$contractIds);

        if($shipCaddyRates){
            $allowed_service = array_merge($allowed_service,$shipCaddyRates);
        }

        return response()->json(['IsSuccess' => 'true', 'AvailableServices' => ['AvailableService' => $allowed_service]], 200);
    }

    public function test()
    {
        // exit(config('shipping.post_auth'));

        $carrier_code = 'ups';
        $carrier_service_code = 'ups_2nd_day_air_am';
        $orderDetails = [
            'name' => null,
            'company' => 'test company one',
            'address1' => '5795 Ridge Creek Circle',
            'city' => 'Austin',
            'state' => 'TX',
            'zip' => '78703',
            'country' => 'US',
            'phoneNumber' => '12345678910',
        ];

        $body = $this->createShipStationBody($orderDetails, $carrier_code, $carrier_service_code);

        try {
            $client = new Client();
            $response = $client->request('POST', 'https://ssapi.shipstation.com/shipments/createlabel', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => config('shipping.post_auth'),
                ],
                'body' => $body
            ]);
            echo $response->getBody();
            exit;
            // return $response;
        }
        catch (\GuzzleHttp\Exception\ServerException $e) {
            dd($e->getResponse()->getBody()->getContents());
        }
    }

    public function recentShipments(Request $request) {
      $shipments = ShipPackSubmissions::select('ship_pack_submissions.carrier', 'ship_pack_submissions.created_at', 'ship_pack_submissions.shipto_name', 'ship_pack_submissions.shipto_address', 'ship_pack_submissions.shipto_zip', 'ship_pack_submissions.id', 'shipping_clients.name')
        ->whereNotNull('shipment_id')
        ->join('shipping_clients', 'ship_pack_submissions.companies_id', '=', 'shipping_clients.tpl_client_id')
        ->orderBy('ship_pack_submissions.id', 'desc')
        ->limit(50)
        ->get();

      return view('layouts.shippack.recent', compact('shipments'));
    }

    public function reprintShipment(Request $request, $id) {
      $shipment = ShipPackSubmissions::find($id);

      return view('layouts.shippack.reprint', compact('shipment'));
    }

}
