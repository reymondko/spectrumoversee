<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\InventoryFields;
use App\Models\Inventory;
use App\Models\HiddenInventoryFields;
use App\Models\InventoryItemScans;
use App\Models\Locations;
use App\Models\Orders;
use App\Models\OrderItems;
use App\Models\NotificationSettings;
use App\Models\Notifications;
use App\Models\NotificationOrderSettings;
use App\Models\OrderNotes;
use App\Models\CustomOrders;
use App\Mail\NotificationEmail;
use App\Mail\NotificationOrderEmail;
use Illuminate\Support\Facades\Mail;
use App\User;
use Carbon\Carbon;

class OrdersController extends Controller
{
    public function index(){
        if (Gate::allows('can_see_orders', auth()->user()) || Gate::allows('company-only', auth()->user())) {
            

            $orders = Orders::with('orderItems')->where('companies_id',\Auth::user()->companies_id)->get();
            $inventorySkus = Inventory::select('sku')->where('companies_id',\Auth::user()->companies_id)->groupBy('sku')->get();
            $customOrderPages = CustomOrders::where('companies_id',\Auth::user()->companies_id)->get();

            $data = array(
                'orders' => $orders,
                'skus' => $inventorySkus,
                'custom_order_pages' => (count($customOrderPages) > 0 ? $customOrderPages : null),
            );

            return view('layouts/orders/orders')->with('data',$data);
        }else{
            return redirect()->route('dashboard');
        }
    }

    public function saveOrders(Request $request){
        if (Gate::allows('company-only', auth()->user()) || Gate::allows('can_see_add_orders', auth()->user())) {
           $lastOrder = Orders::select('order_number')
                              ->orderByDesc('id')
                              ->first();

            $orderNumber = ($lastOrder ? $lastOrder->order_number +1 : 1);

            $newOrder = new Orders;
            $newOrder->companies_id = \Auth::user()->companies_id;
            $newOrder->order_number = $orderNumber;
            $newOrder->order_by_name = $request->customer_name;
            $newOrder->address_1 = $request->address_1;
            $newOrder->address_2 = $request->address_2;
            $newOrder->city = $request->city;
            $newOrder->state = $request->state;
            $newOrder->zip = $request->zip;
            $newOrder->country = $request->country;
            
            if($newOrder->save()){
                $saveArray = array();
                foreach($request->item_name as $key => $value){
                    $tmpArray = array(
                        'orders_id' => $newOrder->id,
                        'title' => $value,
                        'quantity' => $request->item_quantity[$key],
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                        'companies_id' => \Auth::user()->companies_id,
                        'sku' => $value,
                    );
                    $saveArray[] = $tmpArray;
                }
                if(OrderItems::insert($saveArray)){
                    $this->processOrdersNotification($newOrder);
                    return redirect()->route('orders')->with('status','saved');
                }
            }
        }

        return redirect()->route('dashboard');
    }

    public function getFulfillDetails(Request $request){
        if (Gate::allows('user-only', auth()->user()) || Gate::allows('company-only', auth()->user())) {
            

            $orders = Orders::with('orderItems')
                            ->where('companies_id',\Auth::user()->companies_id)
                            ->where('id',$request->order_id)
                            ->first();

            $data = array(
                'orders' => $orders
            );

            $response = array('success'=>true,'data' => $data);
        }else{
            $response = array('success'=>false);
        }

        return response()->json($response, 201);
    }

    public function getFulfillItemDetails(Request $request){
        if (Gate::allows('user-only', auth()->user()) || Gate::allows('company-only', auth()->user())) {
            

            $orderItems = OrderItems::with('inventories:id,order_item_id')
                            ->where('companies_id',\Auth::user()->companies_id)
                            ->where('id',$request->order_item_id)
                            ->first();

            $data = array(
                'order_items' => $orderItems
            );

            $response = array('success'=>true,'data' => $data);
        }else{
            $response = array('success'=>false);
        }

        return response()->json($response, 201);
    }

    public function getFulfillItemScan(Request $request){
        if (Gate::allows('can_see_fulfill_orders', auth()->user()) || 
            Gate::allows('company-only', auth()->user())) 
        {
            if($request->barcode_id != '' || $request->barcode_id != null){

                $currentTimestamp = Carbon::now();

                //get user location
                $userLocation = Locations::where('id',\Auth::user()->location_id)->first();
                if($userLocation){
                    $scanLocation = $userLocation->name;
                }else{
                    $scanLocation = 'N/A';
                }

                //get all barcode custom fields
                $customBarcodeField = InventoryFields::where('companies_id',\Auth::user()->companies_id)
                                                    ->where('is_barcode',1)
                                                    ->get();

                $inventoryValues = Inventory::where('companies_id',\Auth::user()->companies_id)
                                            ->where('barcode_id',$request->barcode_id)
                                            ->where('sku',$request->sku)
                                            ->where('last_scan_location',$scanLocation)
                                            ->where('deleted',0)
                                            ->whereNull('order_item_id')
                                            ->first();

                $order = Orders::with('orderItems','orderItems.inventories:id,order_item_id')
                                ->where('companies_id',\Auth::user()->companies_id)
                                ->where('id',$request->order_id)
                                ->first();

                //if barcode field does not match check other barcode fields
                $total_orders = 0;
                $total_fulfilled = 0;
                if(!$inventoryValues && $customBarcodeField){
                    foreach($customBarcodeField as $c){
                        $inventoryValues = Inventory::where('companies_id',\Auth::user()->companies_id)
                                                    ->where('deleted',0)
                                                    ->where('sku',$request->sku)
                                                    ->where('last_scan_location',$scanLocation)
                                                    ->whereNull('order_item_id');

                        $inventoryValues = $inventoryValues->where('custom_field'.$c->field_number,$request->barcode_id);
                        $inventoryValues =  $inventoryValues->get();
                        
                        $orderItems = OrderItems::with('inventories:id,order_item_id')
                                        ->where('companies_id',\Auth::user()->companies_id)
                                        ->where('id',$request->order_item_id)
                                        ->first();

                        foreach($order->orderItems as $o){
                            $total_orders += $o->quantity;
                            $total_fulfilled += count($o->inventories);
                        }

                        //break loop if found
                        if($inventoryValues->count() > 0){

                            $currentQuantity = $orderItems->inventories->count();
                            $requiredQuantity = $orderItems->quantity;
                            
                            foreach($inventoryValues as $iv){
                                $lastScanBeforeUpdate = $iv->last_scan_location;
                                $inventoryScans[] = array(
                                    'inventory_item_id' => $iv->id,
                                    'scanned_by_user_id' => \Auth::user()->id,
                                    'barcode' => $request->barcode_id,
                                    'scanned_location' => $scanLocation,
                                    'companies_id' => \Auth::user()->companies_id,
                                    'description' => "used to fulfill order #".$order->order_number,
                                    'created_at' => $currentTimestamp,
                                    'updated_at' => $currentTimestamp
                                );
                                
                                $iv->order_item_id = $request->order_item_id;
                                $iv->order_id = $request->order_id;
                                $iv->last_scan_location = $scanLocation;
                                $iv->last_scan_by = \Auth::user()->id;
                                $iv->last_scan_date = $currentTimestamp;
                                $iv->last_scan_type = 'scan_out';
                                if($request->tracking_no != '' || $request->tracking_no != null){
                                    $iv->tracking_number = $request->tracking_no;
                                }
                                $iv->save();
                                $currentQuantity++;
                                $total_fulfilled++;
                                $this->processNotification($lastScanBeforeUpdate,$request->sku);
                                if($currentQuantity >= $requiredQuantity){
                                    break;
                                }
                            }

                            if($inventoryScans){
                                InventoryItemScans::insert($inventoryScans);
                            }

                            if($total_orders == $total_fulfilled){
                                $order->status = 2;
                            }else{
                                $order->status = 1;
                            }
    
                            $order->save();

                            $orderItems = OrderItems::with('inventories:id,order_item_id')
                                                    ->where('companies_id',\Auth::user()->companies_id)
                                                    ->where('id',$request->order_item_id)
                                                    ->first();

                            $data = array('order_items' => $orderItems,'status'=>$order->status);
                            $response = array('success'=>true,'data' => $data);
                            return response()->json($response, 201);
                        }
                    }
                }
                
                if($inventoryValues->count() > 0){
                    $lastScanBeforeUpdate = $inventoryValues->last_scan_location;
                    $scan = new InventoryItemScans;
                    $scan->inventory_item_id = $inventoryValues->id;
                    $scan->scanned_by_user_id = \Auth::user()->id;
                    $scan->barcode = $request->barcode_id;
                    $scan->scanned_location = $scanLocation;
                    $scan->companies_id = \Auth::user()->companies_id;
                    $scan->description  = "used to fulfill order #".$order->order_number;
                    $scan->created_at = $currentTimestamp;
                    $scan->updated_at = $currentTimestamp;
                    if($scan->save()){
                    
                        $inventoryValues->order_item_id = $request->order_item_id;
                        $inventoryValues->order_id = $request->order_id;
                        $inventoryValues->last_scan_location = $scanLocation;
                        $inventoryValues->last_scan_by = \Auth::user()->id;
                        $inventoryValues->last_scan_date = $currentTimestamp;
                        $inventoryValues->last_scan_type = 'scan_out';
                        if($request->tracking_no != '' || $request->tracking_no != null){
                            $inventoryValues->tracking_number = $request->tracking_no;
                        }
                        $inventoryValues->save();
                        
                        
                        
                        $total_orders = 0;
                        $total_fulfilled = 0;
                        
                        foreach($order->orderItems as $o){
                            $total_orders += $o->quantity;
                            $total_fulfilled += count($o->inventories);
                        }

                        if($total_orders == ($total_fulfilled+1)){
                            $order->status = 2;
                        }else{
                            $order->status = 1;
                        }

                        $order->save();
                        

                        $orderItems = OrderItems::with('inventories:id,order_item_id')
                                                ->where('companies_id',\Auth::user()->companies_id)
                                                ->where('id',$request->order_item_id)
                                                ->first();

                        $data = array('order_items' => $orderItems,'status'=>$order->status);
                        $response = array('success'=>true,'data' => $data);
                        $this->processNotification($lastScanBeforeUpdate,$request->sku);
                        return response()->json($response, 201);
                    }
                }
            }
        }
        
        $response = array('success'=>false);
        return response()->json($response, 201);
    }

    public function getOrderDetails(){
        
        $hiddenInventoryFields = HiddenInventoryFields::where('users_id',\Auth::user()->id)->first();
        if($hiddenInventoryFields){
            $hiddenFields = json_decode($hiddenInventoryFields->hidden_inventory_fields,true);
            if(!$hiddenFields){
                $hiddenFields = array('_EMPTY_VALUE_');
            }
        }else{
            $hiddenFields = array('_EMPTY_VALUE_');
        }

        $inventoryFields = InventoryFields::select('field_number','field_name')
                                          ->where('companies_id',\Auth::user()->companies_id)
                                          ->get();

        $orderId = $_GET['id'];
        $order = Orders::with('orderItems','orderItems.inventories')
                      ->where('id',$orderId)
                      ->where('companies_id',\Auth::user()->companies_id)
                      ->first();

        $orderNotes = OrderNotes::with('user')
                                ->where('orders_id',$orderId)
                                ->where('companies_id',\Auth::user()->companies_id)
                                ->get();
                      
        if($order){
            $data = array(
                'order' => $order,
                'order_notes' => $orderNotes,
                'hidden_inventory_fields' => $hiddenFields,
                'inventory_fields' => $inventoryFields
            );
            return view('layouts/orders/ordersdetails')->with('data',$data);
        }else{
            return redirect()->route('orders');
        }
    }

    private function processNotification($location,$sku){
        $threshold = NotificationSettings::select('id','threshold','notification_emails')
                                                    ->where('companies_id',\Auth::user()->companies_id)
                                                    ->where('location',$location)
                                                    ->where('sku',$sku)
                                                    ->first();

        $inventorySkuLocationCount = Inventory::where('companies_id',\Auth::user()->companies_id)
                                           ->where('last_scan_location',$location)
                                           ->where('sku',$sku)
                                           ->where('order_id',null)
                                           ->count();

        

        $company_admin = User::select('email')->where('companies_id',\Auth::user()->companies_id)->where('role',2)->first();
        
        

        if(isset($threshold['threshold'])){
            if($inventorySkuLocationCount == ($threshold['threshold'] - 1)){

                $notification = new Notifications;
                $notification->companies_id = \Auth::user()->companies_id;
                $notification->notification_settings_id = $threshold['id'];
                $notification->message = "Total ".$sku." inventory items on ".$location." are now below ".$threshold['threshold'];
                $notification->type = "alert";
                $notification->save();

                $mail = new \stdClass();
                $mail->message = $notification->message;
                $mail->sender = 'Spectrum Oversee';
                
                if($threshold->notification_emails){
                    $notificationEmails = explode(',',$threshold->notification_emails);
                    $notificationEmails = $this->filterEmails($notificationEmails);
                    Mail::to($notificationEmails)->send(new NotificationEmail($mail));
                }
                
            }
        }
    }

    public function updateTrackingNumber(Request $request){
        if($request->tracking_number){
            $inventoryValues = Inventory::where('companies_id',\Auth::user()->companies_id)->where('id',$request->id)->first();
            if($inventoryValues){
                $inventoryValues->tracking_number = $request->tracking_number;
                if($inventoryValues->save()){
                    $response = array('success'=>true);
                    return response()->json($response, 201);
                }
            }
        }
        $response = array('success'=>false);
        return response()->json($response, 201);
    }

    public function orderData(Request $request){
        if($request->id){
            $order = Orders::with('orderItems')->where('companies_id',\Auth::user()->companies_id)->where('id',$request->id)->first();
            $data = array(
                'order' => $order,
            );
            $response = array('success'=>true,'data'=>$data);
            return response()->json($response, 201);
        }
        $response = array('success'=>false);
        return response()->json($response, 201);
    }

    public function updateOrders(Request $request){
        if (Gate::allows('company-only', auth()->user()) || Gate::allows('can_see_add_orders', auth()->user())) {
            $order = Orders::where('companies_id',\Auth::user()->companies_id)->where('id',$request->edit_id)->first();
 
             $order->order_by_name = $request->edit_customer_name;
             $order->address_1 = $request->edit_address_1;
             $order->address_2 = $request->edit_address_2;
             $order->city = $request->edit_city;
             $order->state = $request->edit_state;
             $order->zip = $request->edit_zip;
             $order->country = $request->edit_country;
             if($order->save()){
                $saveArray = array();
                $deleteArray = array();
                foreach($request->edit_item_name as $key => $value){
                    $orderItem = OrderItems::where('orders_id',$request->edit_id)
                                           ->where('sku',$value)
                                           ->where('companies_id',\Auth::user()->companies_id)
                                           ->first();
                    if($orderItem){
                        $orderItem->quantity = $request->edit_item_quantity[$key];
                        $orderItem->updated_at = Carbon::now();
                        $orderItem->save();
                        $deleteArray[] = $orderItem->id;
                    }else{
                        $tmpArray = array(
                            'orders_id' => $request->edit_id,
                            'title' => $value,
                            'quantity' => $request->edit_item_quantity[$key],
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                            'companies_id' => \Auth::user()->companies_id,
                            'sku' => $value,
                        );
                        $saveArray[] = $tmpArray;
                    }
                }

                //delete removed order items
                OrderItems::where('companies_id',\Auth::user()->companies_id)
                          ->where('orders_id',$request->edit_id)
                          ->whereNotIn('id',  $deleteArray)
                          ->delete();

                if(OrderItems::insert($saveArray)){
                    return redirect()->route('orders')->with('status','saved');
                }
             }
             return redirect()->route('orders')->with('status','saved');

           
         }
 
         return redirect()->route('dashboard');
    }

    public function addNote(Request $request){
        $order = Orders::where('companies_id',\Auth::user()->companies_id)
                        ->where('id',$request->order_id)
                        ->first();
        if($order){
            OrderNotes::insert([
                'companies_id' => \Auth::user()->companies_id,
                'users_id' => \Auth::user()->id,
                'orders_id' => $request->order_id,
                'note' => $request->note,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            return redirect()->route('order_details',['id' => $request->order_id])->with('status','saved');
        }

        return redirect()->route('dashboard');
    }

    private function processOrdersNotification($orderData){
        $notificationOrderSettings = NotificationOrderSettings::where('companies_id' , \Auth::user()->companies_id)->first();
        if($notificationOrderSettings){
            $notification = new Notifications;
            $notification->companies_id = \Auth::user()->companies_id;
            $notification->notification_settings_id = $notificationOrderSettings['id'];
            $notification->message = "<a href='/orders/details?id=".$orderData->id."'> Order #".$orderData->order_number."</a>";
            $notification->type = "new_order";
            $notification->save();

            $mail = new \stdClass();
            $mail->message = "A new order has been placed.";
            $mail->order_number = $orderData->order_number;
            $mail->order_id = $orderData->id;
            $mail->sender = 'Spectrum Oversee';
            
            if($notificationOrderSettings->notification_emails){
                $notificationEmails = explode(',',$notificationOrderSettings->notification_emails);
                $notificationEmails = $this->filterEmails($notificationEmails);
                if($notificationEmails){
                    Mail::to($notificationEmails)->send(new NotificationOrderEmail($mail));
                }
            }
        }

        return false;
    }

    private function filterEmails($emailArray){
        $filteredEmails = array();
        foreach($emailArray as $e){
            $email = str_replace(' ','',$e);
            if(filter_var($email,FILTER_VALIDATE_EMAIL)){
                $filteredEmails[] = $email;
            }
        }
        return $filteredEmails;
    }

   
    
}
