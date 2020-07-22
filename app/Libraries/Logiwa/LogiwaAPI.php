<?php

/**
 * Logiwa API Requests Class
 * @documentation - http://developer.logiwa.com/
 *
 * @Note pagination use these properties on the request body
 *  - PageSize - Total Number of Results to return
 *  - SelectedPageIndex - The current Page number to be retrieved (starts @ 0)
 */
namespace App\Libraries\Logiwa;

use App\Libraries\Logiwa\Logiwa;

class LogiwaAPI extends Logiwa{

    // Parent class variable
    private $logiwa;

    // Constructor
    public function __construct(){
        $this->logiwa = new Logiwa;
    }

    /**
     * Creates an inventory item to logiwa
     * @documentation - http://developer.logiwa.com/?id=5e01d99ce6466c16842cb763
     *
     * @param Array - $body
     *  - Request Body
     * @return Mixed
     */
    public function insertInventoryItem($data){

        $endpoint = 'en/api/IntegrationApi/InsertInventoryItem';
        $headers = ['Content-Type' => 'application/json'];
        $request_body = $data;


        $result = $this->logiwa->postRequest($request_body,$endpoint,$headers);

        return $result;
    }

    /**
     * create a single or multiple receipt orders with lines in one go.
     * @documentation - http://developerbeta.logiwa.com/?id=5df0dd05e6466c2eec992f69
     *
     * @param Array - $body
     *  - Request Body
     * @return Mixed
     */
    public function warehouseReceiptBulkInsert($data){

        $endpoint = 'en/api/IntegrationApi/WarehouseReceiptBulkInsert';
        $headers = ['Content-Type' => 'application/json'];
        $request_body = $data;


        $result = $this->logiwa->postRequest($request_body,$endpoint,$headers);

        return $result;
    }

    /**
     * Creates an shipment item to logiwa
     * @documentation - http://developer.logiwa.com/?id=5df0db19e6466c2eec992f4d
     *
     * @param Array - $body
     *  - Request Body
     * @return Mixed
     */
    public function insertShipmentOrder($data){

        $endpoint = 'en/api/IntegrationApi/InsertShipmentOrder';
        $headers = ['Content-Type' => 'application/json'];
        $request_body = $data;
        $result = $this->logiwa->postRequest($request_body,$endpoint,$headers);

        return $result;
    }

    /**
     * Inventory list report
     * @documentation - http://developer.logiwa.com/?id=5e20a095e6466c2b285d6dc6
     *
     * @param Array - $body
     *  - Request Body
     * @return Mixed
     */
    public function getListInventoryReport($data){

        $endpoint = 'en/api/IntegrationApi/ListingInventoryReport';
        $headers = ['Content-Type' => 'application/json'];
        $request_body = $data;
        $result = $this->logiwa->postRequest($request_body,$endpoint,$headers);

        return $result;
    }

    /**
     * Inventory Stock list report
     * @documentation - https://ecom.logiwa.com/Help/Api/POST-lang-api-IntegrationApi-StockSearch
     *
     * @param Array - $body
     *  - Request Body
     * @return Mixed
     */
    public function getListStockInventoryReport($data){

        $endpoint = 'en/api/IntegrationApi/StockSearch';
        $headers = ['Content-Type' => 'application/json'];
        $request_body = $data;
        $result = $this->logiwa->postRequest($request_body,$endpoint,$headers);

        return $result;
    }

    /**
     * Consolidated Inventory report
     * @documentation - http://developer.logiwa.com/?id=5e20a084e6466c2b285d6dc4
     *
     * @param Array - $body
     *  - Request Body
     * @return Mixed
     */
    public function getConsolidatedInventoryReport($data){

        $endpoint = 'en/api/IntegrationApi/StockDamagedUndamagedReportSearch';
        $headers = ['Content-Type' => 'application/json'];
        $request_body = $data;
        $result = $this->logiwa->postRequest($request_body,$endpoint,$headers);

        return $result;
    }

    /**
     * Shipment Info Search
     * @documentation - http://developer.logiwa.com/?id=5df0dbc1e6466c2eec992f57
     *
     * @param Array - $body
     *  - Request Body
     * @return Mixed
     */
    public function getShipmentInfoSearch($data){

        $endpoint = 'en/api/IntegrationApi/ShipmentInfoSearch';
        $headers = ['Content-Type' => 'application/json'];
        $request_body = $data;
        $result = $this->logiwa->postRequest($request_body,$endpoint,$headers);

        return $result;
    }

    /**
     * Receipt All Search
     * @documentation - http://developer.logiwa.com/?id=5df0dcafe6466c2eec992f67
     *
     * @param Array - $body
     *  - Request Body
     * @return Mixed
     */
    public function getReceiptAllSearch($data){

        $endpoint = 'en/api/IntegrationApi/ReceiptAllSearch';
        $headers = ['Content-Type' => 'application/json'];
        $request_body = $data;
        $result = $this->logiwa->postRequest($request_body,$endpoint,$headers);

        return $result;
    }

    /**
     * Purchase Order Search
     * @documentation - http://developer.logiwa.com/?id=5df0dcafe6466c2eec992f67
     *
     * @param Array - $body
     *  - Request Body
     * @return Mixed
     */
    public function getPurchaseOrderSearch($data){

        $endpoint = 'en/api/IntegrationApi/PurchaseOrderSearch';
        $headers = ['Content-Type' => 'application/json'];
        $request_body = $data;
        $result = $this->logiwa->postRequest($request_body,$endpoint,$headers);

        return $result;
    }

    /**
     * Warehouse Order Search
     * @documentation - http://developer.logiwa.com/?id=5df0dbf9e6466c2eec992f5d
     *
     * @param Array - $body
     *  - Request Body
     * @return Mixed
     */
    public function getWarehouseOrderSearch($data){

        $endpoint = 'en/api/IntegrationApi/WarehouseOrderSearch';
        $headers = ['Content-Type' => 'application/json'];
        $request_body = $data;
        $result = $this->logiwa->postRequest($request_body,$endpoint,$headers);

        return $result;
    }

    /**
    * Updates Order
    * @documentation http://developer.logiwa.com/?id=5e18596ce6466c20381289c3
    *
    * @param Array - $body
    *  - Request Body
    * @return Mixed
    */
    public function updateOrder($data){

        $request_body = $data;
        $endpoint = 'en/api/IntegrationApi/WarehouseOrderUpdate';
        $headers = ['Content-Type' => 'application/json'];
        $result = $this->logiwa->postRequest($request_body,$endpoint,$headers);

        return $result;
    }

    /**
     * Order Shipment Serial Search
     * @documentation - to be updated
     *
     * @param Array - $body
     *  - Request Body
     * @return Mixed
     */
    public function getShipmentInfoSerialSearch($data){

        $endpoint = 'en/api/IntegrationApi/ShipmentReportSerialSearch';
        $headers = ['Content-Type' => 'application/json'];
        $request_body = $data;
        $result = $this->logiwa->postRequest($request_body,$endpoint,$headers);

        return $result;
    }

    /**
     * Order Shipment Serial Search
     * @documentation - to be updated
     *
     * @param Array - $body
     *  - Request Body
     * @return Mixed
     */
    public function getShipmentReportAllSearch($data){

        $endpoint = 'en/api/IntegrationApi/ShipmentReportAllSearch';
        $headers = ['Content-Type' => 'application/json'];
        $request_body = $data;
        $result = $this->logiwa->postRequest($request_body,$endpoint,$headers);

        return $result;
    }

    /**
     * Address search by ID
     * @documentation - https://ecom.logiwa.com/Help/Api/POST-lang-api-IntegrationApi-PartyAddressSearch
     *
     * @param Array - $body
     *  - Request Body
     * @return Mixed
     */
    public function getAddressDataByID($data){

        $endpoint = 'en/api/IntegrationApi/PartyAddressGet';
        $headers = ['Content-Type' => 'application/json'];
        $request_body = $data;
        $result = $this->logiwa->postRequest($request_body,$endpoint,$headers);

        return $result;
    }

    /**
     * Get order ID by Code
     * @documentation - http://developer.logiwa.com/?id=5e1858ece6466c20381289bd
     *
     * @param Array - $body
     *  - Request Body
     * @return Mixed
     */
    public function getOrderIdByCode($data){

        $endpoint = 'en/api/IntegrationApi/WarehouseOrderGetID';
        $headers = ['Content-Type' => 'application/json'];
        $request_body = $data;
        $result = $this->logiwa->postRequest($request_body,$endpoint,$headers);

        return $result;
    }

    /**
     * Get order ID by Code
     * @documentation - https://ecom.logiwa.com/Help/Api/POST-lang-api-IntegrationApi-PartyCustomerGetID
     *
     * @param Array - $body
     *  - Request Body
     * @return Mixed
     */
    public function getCustomerIdByCode($data){

        $endpoint = 'en/api/IntegrationApi/PartyCustomerGetID';
        $headers = ['Content-Type' => 'application/json'];
        $request_body = $data;
        $result = $this->logiwa->postRequest($request_body,$endpoint,$headers);

        return $result;
    }

    /**
     * Get inventory transaction data
     * @documentation - http://developer.logiwa.com/?id=5df0dd5ce6466c2eec992f6f
     *
     * @param Array - $body
     *  - Request Body
     * @return Mixed
     */
    public function getTransactionHistoryReportSearch($data){

        $endpoint = 'en/api/IntegrationApi/TransactionHistoryReportSearch';
        $headers = ['Content-Type' => 'application/json'];
        $request_body = $data;
        $result = $this->logiwa->postRequest($request_body,$endpoint,$headers);

        return $result;
    }
}
