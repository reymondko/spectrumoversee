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

class LogiwaApiProxyController extends Controller
{

    private const ERR_UNAUTHORIZED = [
        'status'=>'error',
        'message'=>'Invalid Depositor Code'
    ];

    private const ERR_UNHANDLED_EXCEPTION = [
        'status'=>'error',
        'message'=>'Invalid Data'
    ];

    /**
     * External API handler for inserting Inventory
     * Items to Logiwa
     *
     * @param Illuminate\Http\Request - $request
     *
     * @return Illuminate\Http\Response
     *
     */
    public function insertInventoryItem(Request $request){
        $body = $request->json()->all();

        $depositors = $this->getDepositorIds($request);
        foreach($body as $key=>$value){
            if(!isset($depositors[$value['DepositorID']])){
                return response()->json(self::ERR_UNAUTHORIZED,403);
            }else{
                if($depositors[$value['DepositorID']] != $value['DepositorCode']){
                    return response()->json(self::ERR_UNAUTHORIZED,403);
                }else{
                    $body[$key]['Client'] = $depositors[$value['DepositorID']];
                    unset($body[$key]['DepositorID']);
                    unset($body[$key]['DepositorCode']);
                }
            }
        }

        $logiwa = new LogiwaAPI;
        $result = $logiwa->insertInventoryItem($body);
        return response()->json($result,200);
    }

    /**
     * External API handler for inserting orders
     * Items to Logiwa
     *
     * @param Illuminate\Http\Request - $request
     *
     * @return Illuminate\Http\Response
     *
     */
    public function insertShipmentOrder(Request $request){
        $body = $request->json()->all();

        $depositors = $this->getDepositorIds($request);
        foreach($body as $key=>$value){
            if(!isset($depositors[$value['DepositorID']])){
                return response()->json(self::ERR_UNAUTHORIZED,403);
            }else{
                if($depositors[$value['DepositorID']] != $value['DepositorCode']){
                    return response()->json(self::ERR_UNAUTHORIZED,403);
                }else{
                    $body[$key]['Depositor'] = $depositors[$value['DepositorID']];
                    if(!isset($body[$key]['CustomerAddress'])){
                        $body[$key]['CustomerAddress'] = $body[$key]['Customer'];
                    }else{
                        if($body[$key]['CustomerAddress'] != $body[$key]['Customer']){
                            $body[$key]['CustomerAddress'] = $body[$key]['Customer'];
                        }
                    }
                    unset($body[$key]['DepositorID']);
                    unset($body[$key]['DepositorCode']);
                }
            }
        }

        $logiwa = new LogiwaAPI;
        $result = $logiwa->insertShipmentOrder($body);
        return response()->json($result,200);
    }

    /**
     * External API handler for inventory report
     *
     * @param Illuminate\Http\Request - $request
     *
     * @return Illuminate\Http\Response
     *
     */
    public function getListInventoryReport(Request $request){
        $body = $request->json()->all();

        $depositors = $this->getDepositorIds($request);
        if(!isset($depositors[$body['DepositorID']])){
            return response()->json(self::ERR_UNAUTHORIZED,403);
        }else{
            if($depositors[$body['DepositorID']] != $body['DepositorCode']){
                return response()->json(self::ERR_UNAUTHORIZED,403);
            }
        }

        $logiwa = new LogiwaAPI;
        $result = $logiwa->getListInventoryReport($body);
        $result = $this->unsetSensitiveCarrierData($result);
        return response()->json($result,200);
    }

    /**
     * External API handler for consolidated inventory report
     *
     * @param Illuminate\Http\Request - $request
     *
     * @return Illuminate\Http\Response
     *
     */
    public function getConsolidatedInventoryReport(Request $request){
        $body = $request->json()->all();

        $depositors = $this->getDepositorIds($request);
        if(!isset($depositors[$body['DepositorID']])){
            return response()->json(self::ERR_UNAUTHORIZED,403);
        }else{
            if($depositors[$body['DepositorID']] != $body['DepositorCode']){
                return response()->json(self::ERR_UNAUTHORIZED,403);
            }
        }

        $logiwa = new LogiwaAPI;
        $result = $logiwa->getConsolidatedInventoryReport($body);
        $result = $this->unsetSensitiveCarrierData($result);
        return response()->json($result,200);
    }

    /**
     * External API handler for shipment search
     *
     * @param Illuminate\Http\Request - $request
     *
     * @return Illuminate\Http\Response
     *
     */
    public function getShipmentInfoSearch(Request $request){
        $body = $request->json()->all();

        $depositors = $this->getDepositorIds($request);
        if(!isset($depositors[$body['DepositorID']])){
            return response()->json(self::ERR_UNAUTHORIZED,403);
        }else{
            if($depositors[$body['DepositorID']] != $body['DepositorCode']){
                return response()->json(self::ERR_UNAUTHORIZED,403);
            }
        }
        $body['WarehouseID'] = env('LOGIWA_WAREHOUSE_ID');

        $logiwa = new LogiwaAPI;
        $result = $logiwa->getShipmentInfoSearch($body);
        $result = $this->unsetSensitiveCarrierData($result);
        return response()->json($result,200);
    }

    /**
     * External API handler for receipt search
     *
     * @param Illuminate\Http\Request - $request
     *
     * @return Illuminate\Http\Response
     *
     */
    public function getReceiptAllSearch(Request $request){
        $body = $request->json()->all();

        $depositors = $this->getDepositorIds($request);
        if(!isset($depositors[$body['DepositorID']])){
            return response()->json(self::ERR_UNAUTHORIZED,403);
        }else{
            if($depositors[$body['DepositorID']] != $body['DepositorCode']){
                return response()->json(self::ERR_UNAUTHORIZED,403);
            }
        }
        $body['WarehouseID'] = env('LOGIWA_WAREHOUSE_ID');

        $logiwa = new LogiwaAPI;
        $result = $logiwa->getReceiptAllSearch($body);
        $result = $this->unsetSensitiveCarrierData($result);
        return response()->json($result,200);
    }

    /**
     * Purchase Order Search
     *
     * @param Illuminate\Http\Request - $request
     *
     * @return Illuminate\Http\Response
     *
     */
    public function getPurchaseOrderSearch(Request $request){
        $body = $request->json()->all();

        $depositors = $this->getDepositorIds($request);
        if(!isset($depositors[$body['DepositorID']])){
            return response()->json(self::ERR_UNAUTHORIZED,403);
        }else{
            if($depositors[$body['DepositorID']] != $body['DepositorCode']){
                return response()->json(self::ERR_UNAUTHORIZED,403);
            }
        }
        $body['WarehouseID'] = env('LOGIWA_WAREHOUSE_ID');

        $logiwa = new LogiwaAPI;
        $result = $logiwa->getPurchaseOrderSearch($body);
        $result = $this->unsetSensitiveCarrierData($result);
        return response()->json($result,200);
    }

    /**
     * Get warehouse orders
     *
     * @param Illuminate\Http\Request - $request
     *
     * @return Illuminate\Http\Response
     *
     */
    public function getWarehouseOrderSearch(Request $request){
        $body = $request->json()->all();

        $depositors = $this->getDepositorIds($request);
        if(!isset($depositors[$body['DepositorID']])){
            return response()->json(self::ERR_UNAUTHORIZED,403);
        }else{
            if($depositors[$body['DepositorID']] != $body['DepositorCode']){
                return response()->json(self::ERR_UNAUTHORIZED,403);
            }
        }
        $body['WarehouseID'] = env('LOGIWA_WAREHOUSE_ID');

        $logiwa = new LogiwaAPI;
        $result = $logiwa->getWarehouseOrderSearch($body);
        $result = $this->unsetSensitiveCarrierData($result);
        return response()->json($result,200);
    }

    /**
     * Get warehouse orders
     *
     * @param Illuminate\Http\Request - $request
     *
     * @return Illuminate\Http\Response
     *
     */
    public function getShipmentInfoSerialSearch(Request $request){
        $body = $request->json()->all();

        $depositors = $this->getDepositorIds($request);
        if(isset($body['DepositorID']) && isset($body['DepositorCode'])){
            if(!isset($depositors[$body['DepositorID']])){
                return response()->json(self::ERR_UNAUTHORIZED,403);
            }else{
                if($depositors[$body['DepositorID']] != $body['DepositorCode']){
                    return response()->json(self::ERR_UNAUTHORIZED,403);
                }
            }
        }

        //unset($body['DepositorCode']);
        //unset($body['DepositorID']);
        $body['WarehouseID'] = env('LOGIWA_WAREHOUSE_ID');

        //convert CustomerOrderNo into WarehouseOrderID
        $logiwa = new LogiwaAPI;

        if (isset($body['CustomerOrderNo'])) {
          $result = $logiwa->getWarehouseOrderSearch($body);

          if (isset($result['data']->Data[0]->ID)) {
            $warehouse_order_id = $result['data']->Data[0]->ID;
            unset($body['CustomerOrderNo']);
            $body['WarehouseOrderID'] = $warehouse_order_id;

            //send request to get serial number and lot number
            $result = $logiwa->getShipmentInfoSerialSearch($body);
            $result = $this->unsetSensitiveCarrierData($result);
            return response()->json($result,200);
          }
        } else {
          //send request to get serial number and lot number
          $result = $logiwa->getShipmentInfoSerialSearch($body);
          $result = $this->unsetSensitiveCarrierData($result);
          return response()->json($result,200);
        }

        return response()->json(['success' => false, 'error' => 'Unable to find requested order.'], 200);
    }

    /**
     * Get warehouse orders
     *
     * @param Illuminate\Http\Request - $request
     *
     * @return Illuminate\Http\Response
     *
     */
    public function getShipmentReportAllSearch(Request $request){
        $body = $request->json()->all();

        $depositors = $this->getDepositorIds($request);
        if(!isset($depositors[$body['DepositorID']])){
            return response()->json(self::ERR_UNAUTHORIZED,403);
        }else{
            if($depositors[$body['DepositorID']] != $body['DepositorCode']){
                return response()->json(self::ERR_UNAUTHORIZED,403);
            }
        }

        unset($body['DepositorCode']);
        unset($body['DepositorID']);

        $body['WarehouseID'] = env('LOGIWA_WAREHOUSE_ID');
        /*
        //convert CustomerOrderNo into WarehouseOrderID
        $logiwa = new LogiwaAPI;
        $result = $logiwa->getWarehouseOrderSearch($body);

        if (isset($result['data']->Data[0]->ID)) {
          $warehouse_order_id = $result['data']->Data[0]->ID;
          unset($body['CustomerOrderNo']);
          $body['WarehouseOrderID'] = $warehouse_order_id;

          //send request to get serial number and lot number
          $result = $logiwa->getShipmentInfoSerialSearch($body);
          return response()->json($result,200);
        }

        return response()->json(['success' => false, 'error' => 'Unable to find requested order.'], 200);
        */

        $result = $logiwa->getShipmentReportAllSearch($body);
        $result = $this->unsetSensitiveCarrierData($result);
        return response()->json($result,200);
    }

    /**
     * Updates order status of an order in logiwa
     * to cancelled = 99\
     *
     * @param Illuminate\Http\Request - $request
     *
     * @return Illuminate\Http\Response
     */
    public function cancelOrder(Request $request){
        $body = $request->json()->all();
        $depositors = $this->getDepositorIds($request);
        if(!isset($depositors[$body['DepositorID']])){
            return response()->json(self::ERR_UNAUTHORIZED,403);
        }else{
            if($depositors[$body['DepositorID']] != $body['DepositorCode']){
                return response()->json(self::ERR_UNAUTHORIZED,403);
            }
        }
        // Get order
        $requestBody['WarehouseID'] = env('LOGIWA_WAREHOUSE_ID');
        $requestBody['ID'] = $body['OrderID'];
        $logiwa = new LogiwaAPI;
        $request = $logiwa->getWarehouseOrderSearch($requestBody);

        if($request['success'] == true){
            if(isset($request['data']->Data[0])){
                $data = $request['data']->Data[0];
                // Verify if order belongs to user
                if($data->DepositorID == $body['DepositorID']){
                    // Map order values as they are requiered by Logiwa
                    $cancel_request_data = [
                        'ID'=>$body['OrderID'],
                        'WarehouseID'=>env('LOGIWA_WAREHOUSE_ID'),
                        'WarehouseOrderTypeID'=>$data->WarehouseOrderTypeID,
                        'DepositorID' => $data->DepositorID,
                        'InventorySiteID'=> $data->InventorySiteID,
                        'CustomerID'=>$data->CustomerID,
                        'CustomerAddressID'=>$data->CustomerAddressID,
                        'OrderDate'=>$data->OrderDate,
                        'WarehouseOrderStatusID'=>99 // cancelled order status
                    ];
                    $logiwa = new LogiwaAPI;
                    $result = $logiwa->updateOrder($cancel_request_data);
                    $result = $this->unsetSensitiveCarrierData($result);
                    return response()->json($result,200);
                }
            }
        }

        return response()->json(self::ERR_UNHANDLED_EXCEPTION,400);
    }

    /**
     * Unsets Sensitive Carrier Information
     * From logiwa API response
     *
     * @param array - $data
     *
     * @return array
     */
    private function unsetSensitiveCarrierData($data){
        if($data['success'] == true){
            if(isset($data['data']->Data)){
                foreach($data['data']->Data as $key=>$value){
                    if(isset($value->CarrierRate)){
                        $data['data']->Data[$key]->CarrierRate = null;
                    }
                    if(isset($value->CarrierMarkupRate)){
                        $data['data']->Data[$key]->CarrierMarkupRate = null;
                    }
                }
            }
        }

        return $data;
    }

    /**
     * Retrieve Depositors
     *
     * @param Illuminate\Http\Request - $request
     *
     * @return Array
     */
    private function getDepositorIds($request){
        $depositorArray = [];
        $key = $request->header('key');
        $apiToken = ApiTokens::where('api_token',$key)->first();
        $depositors = LogiwaDepositor::where('companies_id',$apiToken->companies_id)->get();
        foreach($depositors as $depositor){
            $depositorArray[$depositor->logiwa_depositor_id] = $depositor->logiwa_depositor_code;
        }

        return $depositorArray;
    }
}
