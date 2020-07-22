<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use App\Models\Companies;
use App\Models\BatchesItems;
use Illuminate\Support\Facades\Log;
use App\Libraries\Logiwa\LogiwaAPI;
use Carbon\Carbon;
use App\Models\LogiwaDepositor;

class LogiwaOrdersController extends Controller
{

    /**
     * Show Orders Page
     */
    public function orders(Request $request){

        $body = [];
        $search = [];
        $orders = [];

        // $page_num = $request->pagenum ?? 0;
        // $body['PageSize'] = 100;
        // $body['SelectedPageIndex'] = $page_num;

        // Get customer codes and ids
        $depositors = $this->getDepositors();

        if(!empty($depositors)){

            $depositor = $depositors[0];
            $selected_depositor = null;
            if(isset($request->selected_depositor)){
                foreach($depositors as $dp){
                    if($dp->logiwa_depositor_id == $request->selected_depositor){
                        $depositor = $dp;
                        $selected_depositor = $request->selected_depositor;
                        break;
                    }
                }
            }


            $body['WarehouseId'] = env('LOGIWA_WAREHOUSE_ID');
            $body['DepositorID'] = $depositor->logiwa_depositor_id;
            $body['DepositorCode'] = $depositor->logiwa_depositor_code;
            $logiwa = new LogiwaAPI;

            // Statuses to retrieve
            if(isset($request->cancelled)){
                if($request->cancelled == 1){
                    $order_statuses = [99]; // Cancelled order status
                }
            }else{
                $order_statuses = [
                    1, // Entered
                    2, // Approved
                    3, // Check Inventory
                    4, // Started
                    5, // Completed
                    7, // Delivered
                    9, // Freeze / On Hold
                ];
            }


            $body['WarehouseOrderStatusID'] = $order_statuses;
            $request = $logiwa->getWarehouseOrderSearch($body);
            if($request['success'] == true){
                if(isset($request['data']->Data)){
                    foreach($request['data']->Data as $data){

                            $orders[\DateTime::createFromFormat('m.d.Y H:i:s', $data->OrderDate)->getTimestamp()] = $data;

                    }
                }
            }

            ksort($orders);
            $orders = array_reverse($orders);

        }

        if(!empty($search)){
            return view('layouts/logiwa/orders', compact('orders','depositors','selected_depositor'))->with('filter',$search);
        }
        return view('layouts/logiwa/orders', compact('orders','depositors'));
    }

     /**
     * Search Orders Page
     */
    public function searchOrders(Request $r){

        $body = [];
        $search = [];
        $orders = [];

        $logiwa = new LogiwaAPI;

        // Get customer codes and ids
        $depositors = $this->getDepositors();
        if(isset($r->search)){
            if(!empty($depositors)){

                    $depositor = $depositors[0];
                    $search_depositor_id = null;
                    if(isset($r->search_depositor_id)){
                        foreach($depositors as $dp){
                            if($dp->logiwa_depositor_id == $r->search_depositor_id){
                                $depositor = $dp;
                                $search_depositor_id = $r->search_depositor_id;
                                break;
                            }
                        }
                    }

                    $body['WarehouseId'] = env('LOGIWA_WAREHOUSE_ID');

                    // Identifies if search is customer
                    // if false this will try to search for reference,id or order #
                    $is_ship_to = false;
                    // Retrieve customer id first
                    $cust_body = [];
                    $cust_body['ID'] = 1; // Default api value
                    $cust_body['Code'] = $r->search;
                    $cust_id_request = $logiwa->getCustomerIdByCode($cust_body);
                    $customer_id = null;
                    if($cust_id_request['success'] == true){
                        if(isset($cust_id_request['data']->Data)){
                            foreach($cust_id_request['data']->Data as $data){
                                $customer_id = $data->ID;
                            }
                        }
                    }

                    if($customer_id != null){
                        //Search By Ship to
                        $body = [];
                        $body['WarehouseId'] = env('LOGIWA_WAREHOUSE_ID');
                        $body['CustomerID'] = $customer_id;
                        $body['DepositorID'] = $depositor->logiwa_depositor_id;
                        $request = $logiwa->getWarehouseOrderSearch($body);
                        if($request['success'] == true){
                            if(isset($request['data']->Data)){
                                foreach($request['data']->Data as $data){
                                    if($data->DepositorID == $depositor->logiwa_depositor_id){
                                        $orders[] = $data;
                                        $is_ship_to = true;
                                    }
                                }
                            }
                        }
                    }

                    if($is_ship_to == false){

                        // Check if search is order id
                        $is_order_id = true;

                        //Search By Reference Number
                        $logiwa = new LogiwaAPI;
                        $body = [];
                        $body['WarehouseId'] = env('LOGIWA_WAREHOUSE_ID');
                        $body['Code'] = $r->search;
                        $request = $logiwa->getWarehouseOrderSearch($body);
                        if($request['success'] == true){
                            if(isset($request['data']->Data)){
                                foreach($request['data']->Data as $data){
                                    if($data->DepositorID == $depositor->logiwa_depositor_id){
                                        $orders[] = $data;
                                        $is_order_id = false;
                                    }
                                }
                            }
                        }

                        //Search By Order Number
                        $body = [];
                        $body['WarehouseId'] = env('LOGIWA_WAREHOUSE_ID');
                        $body['CustomerOrderNo'] = $r->search;
                        $request = $logiwa->getWarehouseOrderSearch($body);
                        if($request['success'] == true){
                            if(isset($request['data']->Data)){
                                foreach($request['data']->Data as $data){
                                    if($data->DepositorID == $depositor->logiwa_depositor_id){
                                        $orders[] = $data;
                                        $is_order_id = false;
                                    }
                                }
                            }
                        }

                        if($is_order_id){
                            // Search By ID
                            $body = [];
                            $body['WarehouseId'] = env('LOGIWA_WAREHOUSE_ID');
                            $body['ID'] = $r->search;
                            $request = $logiwa->getWarehouseOrderSearch($body);
                            if($request['success'] == true){
                                if(isset($request['data']->Data)){
                                    foreach($request['data']->Data as $data){
                                        if($data->DepositorID == $depositor->logiwa_depositor_id){
                                            $orders[] = $data;
                                        }
                                    }
                                }
                            }
                        }

                    }
            }

            //$global_search = $r->search;
            return view('layouts/logiwa/orders', compact('orders','depositors','search_depositor_id'));
        }

        return redirect()->route('thirdparty_orders');

    }

    /**
     * Show Orders Page
     */
    public function orderDetail($order_id){

        // Get customer codes and ids
        $depositors = $this->getDepositors();
        $depositor_codes = [];

        foreach($depositors as $depositor){
            $depositor_codes[] = $depositor->logiwa_depositor_code;
        }

        // Prepare order request API
        $body['WarehouseID'] = env('LOGIWA_WAREHOUSE_ID');
        $body['IsGetOrderDetails'] = true;
        $body['ID'] = $order_id;
        $logiwa = new LogiwaAPI;
        $request = $logiwa->getWarehouseOrderSearch($body);

        $order_line_items = [];

        if(!empty($request['data']->Data[0]->DetailInfo)){
            foreach($request['data']->Data[0]->DetailInfo as $line_item){
                $order_line_items[] = [
                    'id'=>$line_item->ID,
                    'sku'=>$line_item->InventoryItemInfo,
                    'desc'=>$line_item->InventoryItemDescription,
                    'serial'=>"",
                    'quantity'=>$line_item->PackQuantity
                ];
            }
        }

        // Get Order Serial Numbers
        $body = [];
        $body['WarehouseID'] = env('LOGIWA_WAREHOUSE_ID');
        $body['WarehouseOrderID'] = $order_id;
        $logiwa = new LogiwaAPI;
        $serial_request = $logiwa->getShipmentInfoSerialSearch($body);

        if(!empty($serial_request['data']->Data)){
            if(isset($serial_request['data']->Data[0]->InventoryItemID)){
                $order_line_items = [];
                foreach($serial_request['data']->Data as $line_item){
                    $order_line_items[] = [
                        'id'=>$line_item->ID,
                        'sku'=>$line_item->Barcode,
                        'desc'=>$line_item->InventoryItemDescription,
                        'serial'=>$line_item->Serial,
                        'quantity'=>1
                    ];
                }
            }
        }

        // Get Order Address Details
        $body = [];
        $body['ID'] = $request['data']->Data[0]->CustomerAddressID;
        $logiwa = new LogiwaAPI;
        $address_request = $logiwa->getAddressDataByID($body);
        if($address_request['success'] == true){
            $address_data = $address_request['data'];
        }

        if($request['success'] == true){
            $data = $request['data']->Data[0];
            // Verify if order belongs to user
            if(in_array($data->DepositorCode,$depositor_codes)){
                return view('layouts/logiwa/logiwaordersdetails', compact('data','order_line_items','address_data'));
            }
        }
        return redirect()->route('thirdparty_orders');
    }

    /*
    * Creating logiwa orders page
    */
    public function createOrder(Request $request){

        $customerItemData = [];
        $customerItemDataCid = [];
        $customerItemDataSku = [];
        $customers = [];

        // Get customer codes and ids
        $depositors = $this->getDepositors();
        $selected_depositor = null;
        $selected_depositor_code = null;
        if(!empty($depositors)){
            // Retrieve User Inventories
            $ctr = 0;
            if(count($depositors) > 1){
                foreach($depositors as $depositor){
                    if($depositor->logiwa_depositor_id == $request->c){
                        $selected_depositor = $request->c;
                        $selected_depositor_code = $depositor->logiwa_depositor_code;
                        break;
                    }
                }
            }else{
                $selected_depositor = $depositors[0]->logiwa_depositor_id;
                $selected_depositor_code = $depositors[0]->logiwa_depositor_code;
            }

            if($selected_depositor != null){
                $body['DepositorID'] = $selected_depositor;
                $logiwa = new LogiwaAPI;
                $request = $logiwa->getConsolidatedInventoryReport($body);
                if($request['success'] == true){
                    foreach($request['data']->Data as $data){
                        // $customerItemData[$ctr][] = $data;
                        $customerItemData[$ctr]['id'] = $data->ID;
                        $customerItemData[$ctr]['sku'] = $data->InventoryItemDescription;
                        $customerItemData[$ctr]['description'] = $data->Description;
                        $ctr++;
                        $customerItemDataSku[$data->InventoryItemDescription] = $data->Description;
                        $customerItemDataCid[$data->InventoryItemDescription]['customer_id'] = $data->DepositorID;
                        $customerItemDataCid[$data->InventoryItemDescription]['customer_name'] = $selected_depositor_code;
                        $customers[$selected_depositor] = $selected_depositor_code;
                    }
                }
            }

        }

        return view('layouts/logiwa/createorders',compact('depositors','customerItemData','customerItemDataCid','customerItemDataSku','customers','selected_depositor'));
    }

    /**
     * Creating orders to logiwa
     * @param Illuminate\Http\Request;
     *
     * @return mixed
     */
    public function createOrderSave(Request $request){
        // Build order creation request
        $data = [];
        $current_date = Carbon::now();
        if(count($request->line_item_id) > 0){
            // Build order items
            $order_items = [];
            foreach($request->line_item_sku as $key=>$value){
                $order_items[] = [
                    'InventoryItem'=>$value,
                    'InventoryItemPackType'=>'EA',
                    'PlannedPackQuantity'=>$request->line_item_qty[$key]
                ];
            }

            $stateCode = $this->getStateCode($request->street);

            $data[] = [
                'Code'=>$request->ref_number,
                'CustomerOrderNo'=>$request->ref_number,
                'Depositor'=>$request->customer_name,
                'InventorySite'=>'Spectrum Solutions',
                'Warehouse' => 'Spectrum Solutions',
                'WarehouseOrderType' => 'Customer Order',
                'WarehouseOrderStatus' => 'Entered',
                'Customer' => ($request->name) ? $request->name: $request->company,
                'CustomerAddress' => $request->company,
                'AdressText' => $request->address1,
                'OrderDate' => $current_date->format('m.d.Y H:i:s'),
                'State' =>$stateCode,
                'Country'=>$request->country,
                'City'=>$request->city,
                'PostalCode' => $request->zip,
                'Phone'=>$request->phone,
                'AddressDirections'=>$request->address2,
                // 'carrier'=>$request->carrier,
                'Details' => $order_items
            ];

            $logiwa = new LogiwaAPI;
            $result = $logiwa->insertShipmentOrder($data);
            if(isset($result["success"])){
                if($result["success"] == true){
                    if(isset($result['data']->Success)){
                        $return = array(
                            'status' => 'saved',
                            'orderId' => $request->ref_number
                        );
                        return redirect()->route('thirdparty_orders')->with('data',$return);
                    }
                }
            }
            return redirect()->route('thirdparty_orders_create')->with('status','error_saving');
        }
        return redirect()->route('dashboard');
    }

    public function cancelOrder($order_id){

        // Get customer codes and ids
        $depositors = $this->getDepositors();
        $depositor_codes = [];

        foreach($depositors as $depositor){
            $depositor_codes[] = $depositor->logiwa_depositor_code;
        }

        // Retrieve order
        $body['WarehouseID'] = env('LOGIWA_WAREHOUSE_ID');
        $body['ID'] = $order_id;
        $logiwa = new LogiwaAPI;
        $request = $logiwa->getWarehouseOrderSearch($body);
        if($request['success'] == true){
            $data = $request['data']->Data[0];
            // Verify if order belongs to user
            if(in_array($data->DepositorCode,$depositor_codes)){
                // Map order values as they are requiered by Logiwa
                $cancel_request_data = [
                    'ID'=>$order_id,
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
                if($request['success'] == true){
                    return redirect('thirdparty/orders/details/'.$order_id);
                }
            }
        }
        return redirect()->route('dashboard');
    }

    /**
     * Retrieve user customer code for logiwa
     */
    public function getDepositors(){
        $depositor_array = [];
        $depositors = LogiwaDepositor::where('companies_id',\Auth::user()->companies_id)->get();
        foreach($depositors as $depositor){
            $depositor_array[] = $depositor;
        }
        return $depositor_array;
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

}
