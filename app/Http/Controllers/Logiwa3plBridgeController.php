<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Libraries\Logiwa\LogiwaAPI;
use GuzzleHttp\Exception\GuzzleException;
use Guzzle\Http\Exception\ClientException;
use GuzzleHttp\Client;
use App\Models\ApiTokens;
use App\Models\LogiwaDepositor;
use Illuminate\Http\Response;
use Carbon\Carbon;

class Logiwa3plBridgeController extends Controller
{

    private const ERR_UNHANDLED_EXCEPTION = [
        'status'=>'error',
        'message'=>'Invalid Data'
    ];

    /**
     * Logiwa - 3pl Bridge function for creating orders
     *
     * @param Illuminate\Http\Request - $request
     *
     * @return Illuminate\Http\Response
     */
    public function createOrder(Request $request){

        $api_body = $request->json()->all();
        if(!empty($api_body)){
            try{
                $data = [];
                $order_items = [];
                $current_date = Carbon::now();
                $depositor = $this->getDepositorData($request);

                // Get order items
                foreach($api_body['OrderItems'] as $item){
                    $order_items[] = [
                        'InventoryItem'=>$item['itemIdentifier']['sku'],
                        'InventoryItemPackType'=>'EA',
                        'PlannedPackQuantity'=>$item['qty']
                    ];
                }

                $stateCode = $this->getStateCode($api_body['shipTo']['state']);

                // Build Request Data
                $data[] = [
                    'Code'=>$api_body['referenceNum'],
                    'CustomerOrderNo'=>$api_body['referenceNum'],
                    'Depositor'=>$depositor['depositor_code'],
                    'InventorySite'=>'Spectrum Solutions',
                    'Warehouse' => 'Spectrum Solutions',
                    'WarehouseOrderType' => 'Customer Order',
                    'WarehouseOrderStatus' => 'Entered',
                    'Customer' => $api_body['shipTo']['name'],
                    'CustomerAddress' => $api_body['shipTo']['address1'],
                    'AdressText' => $api_body['shipTo']['companyName'],
                    'OrderDate' => $current_date->format('m.d.Y H:i:s'),
                    'State' =>$stateCode,
                    'Country'=>$api_body['shipTo']['country'],
                    'City'=>$api_body['shipTo']['city'],
                    'PostalCode' => $api_body['shipTo']['zip'],
                    'Phone'=> $api_body['shipTo']['phoneNumber'],
                    'AddressDirections'=> $api_body['shipTo']['address2'] ?? NULL,
                    'Details' => $order_items
                ];

                $logiwa = new LogiwaAPI;
                $result = $logiwa->insertShipmentOrder($data);
                if(isset($result["success"])){
                    if($result["success"] == true){
                        if(is_object($result['data'])){
                            return $this->createOrderSuccessResponse($api_body['referenceNum'],$depositor['depositor_code'],$depositor['depositor_id']);
                        }else{
                            return $this->errorBadRequest($result['data'][0]);
                        }
                    }
                }

            }catch( \Exception $e ){
                return $this->errorBadRequest($e->getMessage());
            }
        }
        return $this->errorBadRequest(self::ERR_UNHANDLED_EXCEPTION);
    }

    /**
     * Retrieves and Returns the Order
     * Details by ID in 3pl data structure format
     *
     * @param Illuminate\Http\Request
     *  - $request | Additional Request Data
     * @param Integer
     *  - $order_id | the id of the requested order
     *
     * @return Illuminate\Http\Response
     */
    public function getOrderById(Request $request, $order_id){

        // Retrieve depositor data
        $depositor = $this->getDepositorData($request);

        $depositor_id = $depositor['depositor_id'];
        $depositor_code = $depositor['depositor_code'];

        $body['WarehouseID'] = env('LOGIWA_WAREHOUSE_ID');
        $body['IsGetOrderDetails'] = true;
        $body['DepositorCode'] = $depositor_code;
        $body['DepositorID'] = $depositor_id;
        $body['ID'] = $order_id;
        $logiwa = new LogiwaAPI;
        $request = $logiwa->getWarehouseOrderSearch($body);

        try{
            if(isset($request["success"])){
                if($request["success"] == true){
                    $order = reset($request['data']->Data);
                    // Get customer address data
                    $customer_address = $this->getCustomerAddressData($order->CustomerAddressID);
                    // Map order items
                    $order_items = [];
                    foreach($order->DetailInfo as $item){
                        $order_items[] = [
                            "readOnly" => [
                                "orderItemId" => $item->ID,
                                "fullyAllocated" => $order->IsAllocated,
                                "unitIdentifier" => [
                                    "name" => $item->InventoryItemPackTypeDescription,
                                    "id" => $item->InventoryItemPackTypeID
                                ],
                                "originalOrderQty" => $item->PlannedPackQuantity,
                                "isOrderQtySecondary" => false,
                                "allocations" => [
                                    [
                                        "receiveItemId" => $item->InventoryItemID,
                                        "qty" => $item->PackQuantity,
                                        "properlyPickedPrimary" => 0,
                                        "properlyPickedSecondary" => 0,
                                        "loadedOut" => false
                                    ]
                                ]
                            ],
                            "itemIdentifier" => [
                                "sku" => $item->InventoryItemDescription,
                                "id" => $item->InventoryItemID
                            ],
                            "qualifier" => "",
                            "qty" => $item->PackQuantity,
                            "savedElements" => [],
                            "_links" => []
                        ];
                    }

                    $response_data = [
                        "readOnly" => [
                            "orderId" => $order->ID,
                            "asnCandidate" => 0,
                            "routeCandidate" => 0,
                            "fullyAllocated" => true,
                            "deferNotification" => false,
                            "isClosed" => false,
                            "loadedState" => 0,
                            "routeSent" => false,
                            "asnSent" => false,
                            "packages" => [],
                            "outboundSerialNumbers" => [],
                            "parcelLabelType" => 0,
                            "customerIdentifier" => [
                                "name" => $depositor_code,
                                "id" => $depositor_id
                            ],
                            "facilityIdentifier" => [
                                "name" => "Spectrum Solutions",
                                "id" => env('LOGIWA_WAREHOUSE_ID')
                            ],
                            "warehouseTransactionSourceType" => 7,
                            "transactionEntryType" => 4,
                            "creationDate" => $this->convertLogiwaDateTo3plDate($order->OrderDate),
                            "createdByIdentifier" => [
                                "name" => "Sys Admin",
                                "id" => 1
                            ],
                            "lastModifiedDate" => $this->convertLogiwaDateTo3plDate($order->OrderDate),
                                "lastModifiedByIdentifier" => [
                                "name" => "Sys Admin",
                                "id" => 1
                            ],
                            "status" => 0
                        ],
                        "referenceNum" => $order->Code,
                        "numUnits1" => count($order_items),
                        "totalWeight" => $order->UnitWeight ?? 0,
                        "totalVolume" => $order->UnitVolume ?? 0,
                        "addFreightToCod" => false,
                        "upsIsResidential" => false,
                        "fulfillInvInfo" => [],
                        "routingInfo" => [
                            "isCod" => false,
                            "isInsurance" => false,
                            "requiresDeliveryConf" => false,
                            "requiresReturnReceipt" => false,
                            "scacCode" => $order->CarrierDescription,
                            "carrier" => $order->CarrierDescription,
                            "mode" => "Standard"
                        ],
                        "billing" => [],
                        "shipTo" => [
                            "isQuickLookup" => false,
                            "contactId" => $order->CustomerAddressID,
                            "companyName" => $customer_address->CompanyName,
                            "name" => $order->CustomerDescription,
                            "address1" => $customer_address->CompanyName,
                            "city" => $customer_address->CityDescription,
                            "state" => $customer_address->StateDescription,
                            "zip" => $customer_address->PostalCode,
                            "country" => $this->getStateCode($customer_address->CountryDescription),
                            "phoneNumber" => $customer_address->Phone,
                            "emailAddress" => $customer_address->Email,
                            "addressStatus" => 0
                        ],
                        "savedElements" => [],
                        "_links" => [],
                        "_embedded" => [
                            "http://api.3plCentral.com/rels/orders/item" => $order_items
                        ]
                    ];

                    return response()->json($response_data,200);
                }
            }
        }catch( \Exception $e ){
            return response()->json(NULL,404);
        }


        return response()->json(NULL,404);
    }

    /**
     * Retrieves Order Items on Logiwa by order id
     * in 3pl data structure format
     *
     * @param Illuminate\Http\Request
     *  - $request | Additional Request Data
     * @param Integer
     *  - $order_id | the id of the requested order
     *
     * @return Illuminate\Http\Response
     */
    public function getOrderItemsByOrderId(Request $request, $order_id){
        // Retrieve depositor data
        $depositor = $this->getDepositorData($request);

        $depositor_id = $depositor['depositor_id'];
        $depositor_code = $depositor['depositor_code'];

        $body['WarehouseID'] = env('LOGIWA_WAREHOUSE_ID');
        $body['IsGetOrderDetails'] = true;
        $body['DepositorCode'] = $depositor_code;
        $body['DepositorID'] = $depositor_id;
        $body['ID'] = $order_id;
        $logiwa = new LogiwaAPI;
        $request = $logiwa->getWarehouseOrderSearch($body);

        try{
            if(isset($request["success"])){
                if($request["success"] == true){
                    $order = reset($request['data']->Data);
                    // Get Order Serial Numbers
                    $body = [];
                    $body['WarehouseID'] = env('LOGIWA_WAREHOUSE_ID');
                    $body['WarehouseOrderID'] = $order_id;
                    $logiwa = new LogiwaAPI;
                    $serial_request = $logiwa->getShipmentInfoSerialSearch($body);

                    if(isset($serial_request["success"])){
                        if($serial_request["success"] == true){
                            $order_serials = $serial_request['data']->Data;
                        }
                    }
                    $order_items = [];
                    foreach($order->DetailInfo as $item){
                        $lot_number = null;
                        $serial_number = null;

                        foreach($order_serials as $serial){
                            if($serial->InventoryItemID == $item->InventoryItemID){
                                $lot_number = $serial->LotBatchNo;
                                $serial_number = $serial->Serial;
                                break;
                            }
                        }
                        $order_items[] = [
                            "readOnly" => [
                                    "orderItemId" => $item->ID,
                                    "fullyAllocated" => $order->IsAllocated,
                                    "unitIdentifier" => [
                                        "name" => $item->InventoryItemPackTypeDescription,
                                        "id" => $item->InventoryItemPackTypeID
                                    ],
                                    "originalOrderQty" => $item->PlannedPackQuantity,
                                    "isOrderQtySecondary" => false,
                                    "allocations" => [
                                        [
                                            "receiveItemId" => $item->InventoryItemID,
                                            "qty" => $item->PackQuantity,
                                            "properlyPickedPrimary" => 0,
                                            "properlyPickedSecondary" => 0,
                                            "loadedOut" => false,
                                            "detail" => [
                                                "itemTraits" => [
                                                    "itemIdentifier" => [
                                                        "sku" =>  $item->InventoryItemDescription,
                                                        "id" => $item->InventoryItemID
                                                    ],
                                                    "qualifier" => "",
                                                    "lotNumber" => $lot_number,
                                                    "serialNumber" => $serial_number,
                                                    "palletIdentifier" => []
                                                ],
                                                "savedElements" => []
                                            ]
                                        ]
                                    ]
                                ],
                                "itemIdentifier" => [],
                                "qualifier" => "",
                                "qty" =>  $item->PackQuantity,
                                "_links" => []
                        ];
                    }

                    $response_data = [
                        "_links" => [
                              "self" => [
                                 "href" => "/orders/".$order_id."/items?detail=AllocationsWithDetail"
                              ],
                              "edit" => [
                                    "href" => "/orders/".$order_id."/items"
                                 ],
                              "http://api.3plcentral.com/rels/orders/order" => [
                                       "href" => "/orders/".$order_id
                                    ],
                              "http://api.3plcentral.com/rels/orders/item" => [
                                          "href" => "/orders/{id}/items/{iid}{?detail}",
                                          "templated" => true
                                       ]
                           ],
                        "_embedded" => [
                            "http://api.3plCentral.com/rels/orders/item" => $order_items
                        ]
                    ];
                    return response()->json($response_data,200);
                }
            }
        }catch( \Exception $e ){
            return response()->json(NULL,404);
        }


        return response()->json(NULL,404);
    }

    /**
     * Cancels orders on Logiwa by order id
     * in 3pl data structure format
     *
     * @param Illuminate\Http\Request
     *  - $request | Additional Request Data
     * @param Integer
     *  - $order_id | the id of the requested order
     *
     * @return Illuminate\Http\Response
     */
    public function cancelOrderByOrderId(Request $request, $order_id){
        // Retrieve depositor data
        $depositor = $this->getDepositorData($request);

        $depositor_id = $depositor['depositor_id'];
        $depositor_code = $depositor['depositor_code'];

        $body['WarehouseID'] = env('LOGIWA_WAREHOUSE_ID');
        $body['ID'] = $order_id;
        $logiwa = new LogiwaAPI;
        $request = $logiwa->getWarehouseOrderSearch($body);

        try{
            if($request['success'] == true){
                $order = reset($request['data']->Data);
                // Verify if order belongs to user
                if($order->DepositorCode == $depositor_code){
                    // Map order values as they are requiered by Logiwa
                    $cancel_request_data = [
                        'ID'=>$order_id,
                        'WarehouseID'=>env('LOGIWA_WAREHOUSE_ID'),
                        'WarehouseOrderTypeID'=>$order->WarehouseOrderTypeID,
                        'DepositorID' => $order->DepositorID,
                        'InventorySiteID'=> $order->InventorySiteID,
                        'CustomerID'=>$order->CustomerID,
                        'CustomerAddressID'=>$order->CustomerAddressID,
                        'OrderDate'=>$order->OrderDate,
                        'WarehouseOrderStatusID'=>99 // cancelled order status
                    ];
                    $logiwa = new LogiwaAPI;
                    $result = $logiwa->updateOrder($cancel_request_data);
                    if($result['success'] == true){
                        return response()->json(NULL,200);
                    }
                }
            }
        }catch( \Exception $e ){
            return response()->json(NULL,404);
        }
        return response()->json(NULL,404);
    }

    /**
     * Returns API Data in the format of 3pl
     * data structure
     *
     * @param String
     * - $order_reference | reference number of the order
     * @param String
     * - $depositor_code
     * @param Integer
     * - $depositor_id
     *
     * @return Illuminate\Http\Response
     */
    private function createOrderSuccessResponse($order_reference,$depositor_code,$depositor_id){

        $body['WarehouseID'] = env('LOGIWA_WAREHOUSE_ID');
        $body['IsGetOrderDetails'] = true;
        $body['DepositorCode'] = $depositor_code;
        $body['DepositorID'] = $depositor_id;
        $body['Code'] = $order_reference;
        $logiwa = new LogiwaAPI;
        $request = $logiwa->getWarehouseOrderSearch($body);

        if(isset($request["success"])){
            if($request["success"] == true){
                $order = reset($request['data']->Data);

                // Get customer address data
                $customer_address = $this->getCustomerAddressData($order->CustomerAddressID);

                // Map order items
                $order_items = [];
                foreach($order->DetailInfo as $item){
                    $order_items[] = [
                        "readOnly" => [
                            "orderItemId" => $item->ID,
                            "fullyAllocated" => true,
                            "unitIdentifier" => [
                                "name" => $item->InventoryItemPackTypeDescription,
                                "id" => $item->InventoryItemPackTypeID
                            ],
                            "originalOrderQty" => $item->PlannedPackQuantity,
                            "isOrderQtySecondary" => false,
                            "allocations" => [
                                [
                                    "receiveItemId" => $item->InventoryItemID,
                                    "qty" => $item->PackQuantity,
                                    "properlyPickedPrimary" => 0,
                                    "properlyPickedSecondary" => 0,
                                    "loadedOut" => false
                                ]
                            ]
                        ],
                        "itemIdentifier" => [
                            "sku" => $item->InventoryItemDescription,
                            "id" => $item->InventoryItemID
                        ],
                        "qualifier" => "",
                        "qty" => $item->PackQuantity,
                        "savedElements" => [],
                        "_links" => []
                    ];
                }

                $response_data = [
                    "readOnly" => [
                        "orderId" => $order->ID,
                        "asnCandidate" => 0,
                        "routeCandidate" => 0,
                        "fullyAllocated" => true,
                        "deferNotification" => false,
                        "isClosed" => false,
                        "loadedState" => 0,
                        "routeSent" => false,
                        "asnSent" => false,
                        "packages" => [],
                        "outboundSerialNumbers" => [],
                        "parcelLabelType" => 0,
                        "customerIdentifier" => [
                            "name" => $depositor_code,
                            "id" => $depositor_id
                        ],
                        "facilityIdentifier" => [
                            "name" => "Spectrum Solutions",
                            "id" => env('LOGIWA_WAREHOUSE_ID')
                        ],
                        "warehouseTransactionSourceType" => 7,
                        "transactionEntryType" => 4,
                        "creationDate" => $this->convertLogiwaDateTo3plDate($order->OrderDate),
                        "createdByIdentifier" => [
                            "name" => "Sys Admin",
                            "id" => 1
                        ],
                        "lastModifiedDate" => $this->convertLogiwaDateTo3plDate($order->OrderDate),
                            "lastModifiedByIdentifier" => [
                            "name" => "Sys Admin",
                            "id" => 1
                        ],
                        "status" => 0
                    ],
                    "referenceNum" => $order->Code,
                    "numUnits1" => count($order_items),
                    "totalWeight" => $order->UnitWeight ?? 0,
                    "totalVolume" => $order->UnitVolume ?? 0,
                    "addFreightToCod" => false,
                    "upsIsResidential" => false,
                    "fulfillInvInfo" => [],
                    "routingInfo" => [
                        "isCod" => false,
                        "isInsurance" => false,
                        "requiresDeliveryConf" => false,
                        "requiresReturnReceipt" => false,
                        "scacCode" => $order->CarrierDescription,
                        "carrier" => $order->CarrierDescription,
                        "mode" => "Standard"
                    ],
                    "billing" => [],
                    "shipTo" => [
                        "isQuickLookup" => false,
                        "contactId" => $order->CustomerAddressID,
                        "companyName" => $customer_address->CompanyName,
                        "name" => $order->CustomerDescription,
                        "address1" => $customer_address->CompanyName,
                        "city" => $customer_address->CityDescription,
                        "state" => $customer_address->StateDescription,
                        "zip" => $customer_address->PostalCode,
                        "country" => $this->getStateCode($customer_address->CountryDescription),
                        "phoneNumber" => $customer_address->Phone,
                        "emailAddress" => $customer_address->Email,
                        "addressStatus" => 0
                    ],
                    "savedElements" => [],
                    "_links" => [],
                    "_embedded" => [
                        "http://api.3plCentral.com/rels/orders/item" => $order_items
                    ]
                ];

                return response()->json($response_data,201);
            }
        }

        return $this->errorBadRequest(self::ERR_UNHANDLED_EXCEPTION);
    }

    /**
     * Retrieves and Returns the Orders
     * Details by ID in 3pl data structure format
     *
     * @param Illuminate\Http\Request
     *  - $request | Additional Request Data
     *
     * @return Illuminate\Http\Response
     */
    public function getOrders(Request $request){

        // Retrieve depositor data
        $depositor = $this->getDepositorData($request);

        $depositor_id = $depositor['depositor_id'];
        $depositor_code = $depositor['depositor_code'];

        $body['WarehouseID'] = env('LOGIWA_WAREHOUSE_ID');

        if(isset($request->detail)){
            if($request->detail == 'All'){
                $body['IsGetOrderDetails'] = true;
            }
        }

        $body['SelectedPageIndex'] = 0;
        if(isset($request->pgnum)){
            $body['SelectedPageIndex'] = $request->pgnum == 0 ? 0 : $request->pgnum - 1;
        }

        $body['PageSize'] = 30;
        if(isset($request->pgsiz)){
            $body['PageSize'] = $request->pgsiz > 30 ? 30:$request->pgsiz;
        }


        if(isset($request->startDate)){
            $body['OrderDate_Start'] = date('m.d.Y',strtotime($request->startDate)). ' 00:00:00';
        }

        if(isset($request->endDate)){
            $body['OrderDate_End'] = date('m.d.Y',strtotime($request->endDate)).' 23:59:59';
        }



        $body['DepositorCode'] = $depositor_code;
        $body['DepositorID'] = $depositor_id;
        $logiwa = new LogiwaAPI;
        $request = $logiwa->getWarehouseOrderSearch($body);

        try{
            if(isset($request["success"])){
                if($request["success"] == true){
                    $total_records = null;
                    foreach($request['data']->Data as $order){

                        if($total_records == null){
                            $total_records = $order->RecordCount;
                        }

                        // Get customer address data
                        $customer_address = $this->getCustomerAddressData($order->CustomerAddressID);
                        // Map order items
                        $order_items = [];
                        if(isset($order->DetailInfo)){
                            foreach($order->DetailInfo as $item){
                                $order_items[] = [
                                    "readOnly" => [
                                        "orderItemId" => $item->ID,
                                        "fullyAllocated" => $order->IsAllocated,
                                        "unitIdentifier" => [
                                            "name" => $item->InventoryItemPackTypeDescription,
                                            "id" => $item->InventoryItemPackTypeID
                                        ],
                                        "originalOrderQty" => $item->PlannedPackQuantity,
                                        "isOrderQtySecondary" => false,
                                        "allocations" => [
                                            [
                                                "receiveItemId" => $item->InventoryItemID,
                                                "qty" => $item->PackQuantity,
                                                "properlyPickedPrimary" => 0,
                                                "properlyPickedSecondary" => 0,
                                                "loadedOut" => false
                                            ]
                                        ]
                                    ],
                                    "itemIdentifier" => [
                                        "sku" => $item->InventoryItemDescription,
                                        "id" => $item->InventoryItemID
                                    ],
                                    "qualifier" => "",
                                    "qty" => $item->PackQuantity,
                                    "savedElements" => [],
                                    "_links" => []
                                ];
                            }
                        }

                        $orders[] = [
                            "readOnly" => [
                                "orderId" => $order->ID,
                                "asnCandidate" => 0,
                                "routeCandidate" => 0,
                                "fullyAllocated" => true,
                                "deferNotification" => false,
                                "isClosed" => false,
                                "loadedState" => 0,
                                "routeSent" => false,
                                "asnSent" => false,
                                "packages" => [],
                                "outboundSerialNumbers" => [],
                                "parcelLabelType" => 0,
                                "customerIdentifier" => [
                                    "name" => $depositor_code,
                                    "id" => $depositor_id
                                ],
                                "facilityIdentifier" => [
                                    "name" => "Spectrum Solutions",
                                    "id" => env('LOGIWA_WAREHOUSE_ID')
                                ],
                                "warehouseTransactionSourceType" => 7,
                                "transactionEntryType" => 4,
                                "creationDate" => $this->convertLogiwaDateTo3plDate($order->OrderDate),
                                "createdByIdentifier" => [
                                    "name" => "Sys Admin",
                                    "id" => 1
                                ],
                                "lastModifiedDate" => $this->convertLogiwaDateTo3plDate($order->OrderDate),
                                    "lastModifiedByIdentifier" => [
                                    "name" => "Sys Admin",
                                    "id" => 1
                                ],
                                "status" => 0
                            ],
                            "referenceNum" => $order->Code,
                            "numUnits1" => count($order_items),
                            "totalWeight" => $order->UnitWeight ?? 0,
                            "totalVolume" => $order->UnitVolume ?? 0,
                            "addFreightToCod" => false,
                            "upsIsResidential" => false,
                            "fulfillInvInfo" => [],
                            "routingInfo" => [
                                "isCod" => false,
                                "isInsurance" => false,
                                "requiresDeliveryConf" => false,
                                "requiresReturnReceipt" => false,
                                "scacCode" => $order->CarrierDescription,
                                "carrier" => $order->CarrierDescription,
                                "mode" => "Standard"
                            ],
                            "billing" => [],
                            "shipTo" => [
                                "isQuickLookup" => false,
                                "contactId" => $order->CustomerAddressID,
                                "companyName" => $customer_address->CompanyName,
                                "name" => $order->CustomerDescription,
                                "address1" => $customer_address->CompanyName,
                                "city" => $customer_address->CityDescription,
                                "state" => $customer_address->StateDescription,
                                "zip" => $customer_address->PostalCode,
                                "country" => $this->getStateCode($customer_address->CountryDescription),
                                "phoneNumber" => $customer_address->Phone,
                                "emailAddress" => $customer_address->Email,
                                "addressStatus" => 0
                            ],
                            "savedElements" => [],
                            "_links" => [],
                            "_embedded" => [
                                "http://api.3plCentral.com/rels/orders/item" => $order_items
                            ]
                        ];
                    }

                    $totalPages = 0;
                    if($total_records > 0){
                        $totalPages =  ceil(($total_records/$body['PageSize']));
                    }

                    $response_data = [
                        'pageSize'=> $body['PageSize'],
                        'pageIndex'=>$body['SelectedPageIndex'],
                        'totalPages'=>$totalPages,
                        'totalResults' => $total_records,
                        'links' => [],
                        '_embedded'=>[
                            'http://api.3plcentral.com/rels/orders/order'=> $orders
                        ]
                    ];

                    return response()->json($response_data,200);
                }
            }
        }catch( \Exception $e ){
            return response()->json(NULL,404);
        }


        return response()->json(NULL,404);
    }


    /**
     * Returns a bad request error with error message
     * from Logiwa but in the structure of 3pl
     *
     * @param String
     *  - $error_message | error message from logiwa
     * @return Illuminate\Http\Response
     */
    private function errorBadRequest($error_message){
        $errorBody = [
            '$type'=>NULL,
            'ModelType'=>NULL,
            'Properties'=>[],
            'ErrorCode'=>NULL,
            'Hint'=>$error_message,
            'ClassName'=>NULL
        ];
        return response()->json($errorBody,400);
    }

    /**
     * Retrieve Single Logiwa Depositor
     *
     * @param Illuminate\Http\Request - $request
     *
     * @return Array
     */
    private function getDepositorData($request){
        $depositorArray = [];
        $key = $request->header('key');
        $apiToken = ApiTokens::where('api_token',$key)->first();
        $depositors = LogiwaDepositor::where('companies_id',$apiToken->companies_id)->first();

        if($depositors){
            $depositorArray = [
                'depositor_id' => $depositors->logiwa_depositor_id,
                'depositor_code' => $depositors->logiwa_depositor_code
            ];
        }

        return $depositorArray;
    }

    /**
     * Returns state codes based on state name
     * if none is found return the string
     *
     * @param String
     *  $stateName - name of state
     * @return String
     *  $stateCode - code of state
     */
    private function getStateCode($stateName){
        $states = array(
            'ALABAMA'=>'AL',
            'ALASKA'=>'AK',
            'ARIZONA'=>'AZ',
            'ARKANSAS'=>'AR',
            'CALIFORNIA'=>'CA',
            'COLORADO'=>'CO',
            'CONNECTICUT'=>'CT',
            'DELAWARE'=>'DE',
            'FLORIDA'=>'FL',
            'GEORGIA'=>'GA',
            'HAWAII'=>'HI',
            'IDAHO'=>'ID',
            'ILLINOIS'=>'IL',
            'INDIANA'=>'IN',
            'IOWA'=>'IA',
            'KANSAS'=>'KS',
            'KENTUCKY'=>'KY',
            'LOUISIANA'=>'LA',
            'MAINE'=>'ME',
            'MARYLAND'=>'MD',
            'MASSACHUSETTS'=>'MA',
            'MICHIGAN'=>'MI',
            'MINNESOTA'=>'MN',
            'MISSISSIPPI'=>'MS',
            'MISSOURI'=>'MO',
            'MONTANA'=>'MT',
            'NEBRASKA'=>'NE',
            'NEVADA'=>'NV',
            'NEW HAMPSHIRE'=>'NH',
            'NEW JERSEY'=>'NJ',
            'NEW MEXICO'=>'NM',
            'NEW YORK'=>'NY',
            'NORTH CAROLINA'=>'NC',
            'NORTH DAKOTA'=>'ND',
            'OHIO'=>'OH',
            'OKLAHOMA'=>'OK',
            'OREGON'=>'OR',
            'PENNSYLVANIA'=>'PA',
            'RHODE ISLAND'=>'RI',
            'SOUTH CAROLINA'=>'SC',
            'SOUTH DAKOTA'=>'SD',
            'TENNESSEE'=>'TN',
            'TEXAS'=>'TX',
            'UTAH'=>'UT',
            'VERMONT'=>'VT',
            'VIRGINIA'=>'VA',
            'WASHINGTON'=>'WA',
            'WEST VIRGINIA'=>'WV',
            'WISCONSIN'=>'WI',
            'WYOMING'=>'WY'
        );

        $stateName = strtoupper($stateName);
        if(isset($states[$stateName])){
            $stateCode = $states[$stateName];
        }else{
            $stateCode = $stateName;
        }

        return $stateCode;
    }

    /**
     * Converts date from Logiwa standard format
     * to 3pl output format
     *
     * @param String
     *  - $date | the date to convert
     * @return String
     *  - the converted date
     */
    private function convertLogiwaDateTo3plDate($date){
        $date = \DateTime::createFromFormat("m.d.Y H:i:s", $date)->format('Y-m-d\TH:i:s\Z');
        return $date;
    }

    /**
     * Retrieves Customer address information
     *
     * @param Integer
     * - $customer_id
     * @return stdClass
     * - Customer address Object
     */
    private function getCustomerAddressData($customer_address_id){
        // Get Customer Address
        $body = [];
        $body['ID'] = $customer_address_id;
        $logiwa = new LogiwaAPI;
        $address_request = $logiwa->getAddressDataByID($body);
        if($address_request['success'] == true){
            $address_data = $address_request['data'];
            return $address_data;
        }

        return new \stdClass();
    }
}
