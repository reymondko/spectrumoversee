<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CustomOrders;
use App\Models\Orders;
use App\Models\OrderItems;
use App\Models\NotificationOrderSettings;
use App\Models\Notifications;
use App\Mail\NotificationOrderEmail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Auth;

class GuestController extends Controller
{
    public function home(Request $request) {
      if(Auth::check()) {
          return redirect('/dashboard');
      } else {
          return view('auth.login');
      }
    }

    public function customOrder(Request $request){
        $orderKey = $request->segments();
        $orderKey = $orderKey[1];
        $customOrder = CustomOrders::where('url',$orderKey)->first();
        $previousOrder = Orders::with('orderItems','orderItems.inventories')->where('custom_order_id',$customOrder->id)->orderByDesc('id')->get();
        if($customOrder){
            $data['custom_order'] = $customOrder;
            $data['previous_orders'] = $previousOrder;
            $data['custom_order_data'] = json_decode($customOrder['order_data'],true);
            $data['url'] = $orderKey;
            return view('guest/customorder')->with('data',$data);
        }

        return abort(404);
    }

    public function customOrderSave(Request $request){
        $customOrder = CustomOrders::where('url',$request->url)->first();
        if($customOrder){
            $lastOrder = Orders::select('order_number')
                              ->orderByDesc('id')
                              ->first();

            $orderNumber = ($lastOrder ? $lastOrder->order_number +1 : 1);

            $newOrder = new Orders;
            $newOrder->companies_id = $customOrder->companies_id;
            $newOrder->order_number = $orderNumber;
            $newOrder->order_by_name = $customOrder->customer_name;
            $newOrder->address_1 = $customOrder->customer_address_1;
            if($customOrder->customer_address_2){
                $newOrder->address_2 = $customOrder->customer_address_2;
            }
            $newOrder->city = $customOrder->city;
            $newOrder->state = $customOrder->state;
            $newOrder->zip = $customOrder->zip;
            $newOrder->country = $customOrder->country;
            $newOrder->custom_order_id = $customOrder->id;
            if($newOrder->save()){

                $data['custom_order'] = $customOrder;
                $data['custom_order_data'] = json_decode($customOrder['order_data'],true);
                $data['url'] = $request->url;

                $saveArray = array();
                $orderData = json_decode($customOrder['order_data'],true);
                $ctr = 0;
                foreach($orderData as $key => $value){
                    if($request->customorder_val[$ctr] != null){
                        $tmpArray = array(
                                        'orders_id' => $newOrder->id,
                                        'title' => $key,
                                        'quantity' => $request->customorder_val[$ctr],
                                        'created_at' => Carbon::now(),
                                        'updated_at' => Carbon::now(),
                                        'companies_id' => $customOrder->companies_id,
                                        'sku' => $key,
                                    );
                        $saveArray[] = $tmpArray;
                        $ctr++;
                    }
                }

                if(OrderItems::insert($saveArray)){
                    $data['order_number'] = $orderNumber;
                    $data['url'] = $request->url;
                    $data['success'] = true;

                    $this->processOrdersNotification($newOrder);

                    return redirect()->route('guest_custom_order_saved')->with('data',$data);
                }
            }
        }
        die();
        return abort(404);
    }

    private function processOrdersNotification($orderData){
        $notificationOrderSettings = NotificationOrderSettings::where('companies_id' , $orderData->companies_id)->first();
        if($notificationOrderSettings){
            $notification = new Notifications;
            $notification->companies_id = \Auth::user()->companies_id;
            $notification->notification_settings_id = $notificationOrderSettings['id'];
            $notification->message = "<a href='/orders/details?id=".$orderData->id."'> Order #".$orderData->id."</a>";
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

    public function customOrderSaved(){
        return view('guest/customordersaved');
    }

    private function filterEmails($emailArray){
        $filteredEmails = array();
        foreach($emailArray as $e){
            $email = trim($e);
            if(filter_var($email,FILTER_VALIDATE_EMAIL)){
                $filteredEmails[] = $email;
            }
        }
        return $filteredEmails;
    }
}
