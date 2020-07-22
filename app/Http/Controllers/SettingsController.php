<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Gate;
use App\Models\InventoryFields;
use App\Models\Locations;
use App\Models\CustomOrders;
use App\Models\Inventory;
use App\Models\NotificationSettings;
use App\Models\NotificationOrderSettings;
use App\Models\Skus;
use App\Models\CaseLabelRequiredFields;
use Carbon\Carbon;

class SettingsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {   

        $user = array(
            'name' => \Auth::user()->name,
            'email' => \Auth::user()->email,
        );

        $inventoryFields = InventoryFields::select('field_number','field_name','is_barcode')
                                          ->where('companies_id',\Auth::user()->companies_id)
                                          ->get();


        $data = array(
            'user' => $user,
            "inventory_fields" => $inventoryFields,
        );

        return view('layouts/settings/settings')->with('data',$data);
    }

    public function inventoryFields()
    {   

        $inventoryFields = InventoryFields::select('field_number','field_name','is_barcode')
                                          ->where('companies_id',\Auth::user()->companies_id)
                                          ->get();


        $data = array(
            "inventory_fields" => $inventoryFields,
        );

        return view('layouts/settings/inventoryfields')->with('data',$data);
    }

    public function updateUser(){
        if($_POST){
            \Auth::user()->name = $_POST['name'];
            \Auth::user()->email = $_POST['email'];

            if($_POST['password'] != null){
                \Auth::user()->password = Hash::make($_POST['password']);
                
            }

            if(\Auth::user()->save()){
                return redirect('settings')->with('status', 'saved');
            }else{
                return redirect('settings')->with('status', 'error_saving');
            }
            

        }
    }

    public function saveInventoryFields(){
        if (Gate::allows('company-only', auth()->user())){
            
            $customFields = array();
            $fieldNumberArray = array();
            $ctr = 0; 

            //map customfields submitted
            for($customFieldLimit = 20; $customFieldLimit > 0; $customFieldLimit--){
                if(isset($_POST['customfield_'.$customFieldLimit])){
                    $fieldNumberArray[] = $customFieldLimit;
                    $customFields[$ctr]['field_number'] = $customFieldLimit;
                    $customFields[$ctr]['field_name'] = $_POST['customfield_'.$customFieldLimit];
                    $ctr++;
                }
            }
            
            //remove deleted fields
            InventoryFields::whereNotIn('field_number',$fieldNumberArray)
                           ->where('companies_id',\Auth::user()->companies_id)
                           ->delete();

            
            if(!empty($customFields)){
                $customFields = array_reverse($customFields);
                foreach($customFields as $c){  
                    //check if record exists
                    $inventoryField = InventoryFields::where('companies_id',\Auth::user()->companies_id)
                                                     ->where('field_number',$c['field_number'])
                                                     ->first();
                    if($inventoryField){
                        $inventoryField->field_name = $c['field_name'];
                    }else{
                        $inventoryField = new inventoryFields;
                        $inventoryField->companies_id = \Auth::user()->companies_id;
                        $inventoryField->field_number = $c['field_number'];
                        $inventoryField->field_name = $c['field_name'];
                    }
                    if(isset($_POST['customfield_checkbox_'.$c['field_number']])){
                        $inventoryField->is_barcode = 1;
                    }else{
                        $inventoryField->is_barcode = 0;
                    }

                    $inventoryField->save();
                }
            }
            return redirect('settings/inventoryfields')->with('status', 'saved');
        }else{
            return redirect()->route('dashboard');
        }
    }

    public function locations(){

        if (Gate::allows('company-only', auth()->user())){
            $locations = Locations::where('companies_id',\Auth::user()->companies_id)->get();
            $data = array(
                'locations' => $locations
            );
            return view('layouts/settings/locations')->with('data',$data);
        }
        return redirect()->route('dashboard');
    }

    public function saveLocations(Request $request){
        if (Gate::allows('company-only', auth()->user())){
            if($request->locations){
                foreach($request->locations as $loc){
                    $location = new Locations;
                    $location->name = $loc;
                    $location->companies_id = \Auth::user()->companies_id;
                    $location->save();
                }
            }

            return redirect()->route('locations')->with('status','saved');
        }
        return redirect()->route('dashboard');
    }

    public function updateLocations(Request $request){
        if (Gate::allows('company-only', auth()->user())){
            if($request->edit_location_id){
                $location = locations::where('id',$request->edit_location_id)
                                     ->where('companies_id',\Auth::user()->companies_id)
                                     ->first();
                if($location){
                    $location->name = $request->edit_location_name;
                }

                $location->save();
            }

            return redirect()->route('locations')->with('status','saved');
        }
        return redirect()->route('dashboard');
    }

    public function deleteLocations(Request $request){
        if (Gate::allows('company-only', auth()->user())){
            if($request->delete_location_id){
                $location = locations::where('id',$request->delete_location_id)
                                     ->where('companies_id',\Auth::user()->companies_id)
                                     ->first();
        
                $location->delete();
            }

            return redirect()->route('locations')->with('status','deleted');
        }
        return redirect()->route('dashboard');
    }

    public function customOrders(){
        if (Gate::allows('company-only', auth()->user())){
            
            $customOrders = CustomOrders::where('companies_id',\Auth::user()->companies_id)->get();
            $inventorySkus = Skus::select('sku')->where('companies_id',\Auth::user()->companies_id)->get();
            $data = array(
                'custom_orders'=>$customOrders,
                'skus' => $inventorySkus
            );

            return view('layouts/settings/customorders')->with('data',$data);
        }
        return redirect()->route('dashboard');
    }

    public function SaveCustomOrders(Request $request){
        if (Gate::allows('company-only', auth()->user())){
            $customOrder = new CustomOrders;
            $customOrder->companies_id = \Auth::user()->companies_id;
            $customOrder->custom_order_name = $request->title;
            $customOrder->company_name = $request->company_name; 
            $customOrder->customer_name = $request->customer_name;
            $customOrder->customer_address_1 = $request->address_1;
            $customOrder->customer_address_2 = $request->address_2;
            $customOrder->city = $request->city;
            $customOrder->state = $request->state;
            $customOrder->zip = $request->zip;
            $customOrder->country = $request->country;

            $url = strtr(base64_encode(time() . \Auth::user()->email), '+/=', '._-');
            $orderData = array();
            foreach($request->item_name as $key => $value){
                $orderData[$value] = array(
                                            "quantities"=>$request->quantities_of[$key],
                                            "min" =>$request->min[$key],
                                            "max" =>$request->max[$key]
                                          );
            }

            $customOrder->url = $url;
            $customOrder->order_data = json_encode($orderData);
            if($customOrder->save()){
                return redirect()->route('custom_orders_settings')->with('status','saved');
            }
        }
        return redirect()->route('dashboard');
    }

    public function UpdateCustomOrders(Request $request){
        if (Gate::allows('company-only', auth()->user())){
            $customOrder = customOrders::where('id',$request->edit_id)->where('companies_id',\Auth::user()->companies_id)->first();
            if($customOrder){
                $customOrder->custom_order_name = $request->edit_title;
                $customOrder->company_name = $request->edit_company_name; 
                $customOrder->customer_name = $request->edit_customer_name;
                $customOrder->customer_address_1 = $request->edit_address_1;
                $customOrder->customer_address_2 = $request->edit_address_2;
                $customOrder->city = $request->edit_city;
                $customOrder->state = $request->edit_state;
                $customOrder->zip = $request->edit_zip;
                $customOrder->country = $request->edit_country;

                foreach($request->edit_item_name as $key => $value){
                    $orderData[$value] = array(
                                                "quantities"=>$request->edit_quantities_of[$key],
                                                "min" =>$request->edit_min[$key],
                                                "max" =>$request->edit_max[$key]
                                            );
                }

                $customOrder->order_data = json_encode($orderData);
                if($customOrder->save()){
                    return redirect()->route('custom_orders_settings')->with('status','saved');
                }
            }
        }
        return redirect()->route('dashboard');
    }

    public function deleteCustomOrders(Request $request){
        if(Gate::allows('company-only', auth()->user())){
            $customOrder = CustomOrders::where('companies_id',\Auth::user()->companies_id)->where('id',$request->id)->first();
            if($customOrder){
                $customOrder->delete();
            }
            return redirect()->route('custom_orders_settings')->with('status','deleted');
        }
    }

    public function notificationSettings(){
        if(Gate::allows('company-only', auth()->user())){
            $locations = Locations::where('companies_id',\Auth::user()->companies_id)->get();
            $inventorySkus = Inventory::select('sku')->where('companies_id',\Auth::user()->companies_id)->groupBy('sku')->get();
            $notificationSettings = NotificationSettings::where('companies_id',\Auth::user()->companies_id)->get();
            $notificationOrderSettings = NotificationOrderSettings::where('companies_id' , \Auth::user()->companies_id)->first();


            $data = array(
                'locations' => $locations,
                'skus' => $inventorySkus,
                'notification_settings' => $notificationSettings,
                'notification_order_settings' => $notificationOrderSettings
            );

            return view('layouts/settings/notificationsettings')->with('data',$data);
        }
        return redirect()->route('dashboard');
    }

    public function saveNotificationSettings(Request $request){
        if(Gate::allows('company-only', auth()->user())){
            NotificationSettings::where('companies_id',\Auth::user()->companies_id)->delete();
            if($request->sku){
                foreach($request->sku as $key => $value){
                    $notificationSetting = new NotificationSettings;
                    $notificationSetting->companies_id = \Auth::user()->companies_id;
                    $notificationSetting->sku = $value;
                    $notificationSetting->location = $request->location[$key];
                    $notificationSetting->threshold = $request->threshold[$key];
                    $notificationSetting->notification_emails = $request->notification_emails[$key];
                    $notificationSetting->save();
                }
            }
                                                            
            return redirect()->route('notification_settings')->with('status','saved');
        }
        return redirect()->route('dashboard');
    }

    public function saveOrderNotificationSettings(Request $request){
        if(Gate::allows('company-only', auth()->user())){
            $orderNotification = NotificationOrderSettings::where('companies_id' , \Auth::user()->companies_id)->first();
            if($orderNotification){
                $orderNotification->notification_emails = $request->notification_order_emails;
                $orderNotification->enabled = ($request->notification_order_enabled ? 1:0);
            }else{
                $orderNotification = new NotificationOrderSettings;
                $orderNotification->companies_id = \Auth::user()->companies_id;
                $orderNotification->notification_emails = $request->notification_order_emails;
                $orderNotification->enabled = ($request->notification_order_enabled ? 1:0);
            }

            $orderNotification->save();
                                                            
            return redirect()->route('notification_settings')->with('status','saved');
        }
        return redirect()->route('dashboard');
    }

    public function getCustomOrderDetails(Request $request){
        if(Gate::allows('company-only', auth()->user())){

            $response = array(
                "success" =>false,
            );

            $customOrder = CustomOrders::where('companies_id',\Auth::user()->companies_id)->where('id',$request->id)->first();
            $tmpCustomOrder = array();
            
            if($customOrder){

                $orderData = json_decode($customOrder->order_data,true);
                $orderDataCtr = 0;
                $orderDataTmp = array();
                foreach($orderData as $key => $value){
                    $orderDataTmp[$orderDataCtr]['sku'] = $key;
                    $orderDataTmp[$orderDataCtr]['quantities'] = $value['quantities'];
                    $orderDataTmp[$orderDataCtr]['min'] = $value['min'];
                    $orderDataTmp[$orderDataCtr]['max'] = $value['max'];
                    $orderDataCtr++;
                }

                $customOrder->order_data = $orderDataTmp;

                $response = array(
                    "success" =>true,
                    "result" =>array("custom_order"=>$customOrder)
                );
            }
            return response()->json($response,200);
        }

        return response()->json($response,403);
    }

    public function skus(){
        if(Gate::allows('company-only', auth()->user())){
            $skus = Skus::where('companies_id',\Auth::user()->companies_id)->get();
            $data = array(
                'skus' => $skus,
            );

            return view('layouts/settings/skus')->with('data',$data);
        }
        return redirect()->route('dashboard');

    }

    public function saveSkus(Request $request){
        if(Gate::allows('company-only', auth()->user())){

           $skusArray = array();
           foreach($request->sku as $s){
               $skusArray[] = array(
                   'companies_id' => \Auth::user()->companies_id,
                   'sku' => $s,
                   'active' => 1,
                   'created_at' => Carbon::now(),
                   'updated_at' => Carbon::now()
               );
           }

           if($skusArray){
               Skus::insert($skusArray);
               return redirect()->route('skus')->with('status','saved');
           }
        }
        return redirect()->route('dashboard');
    }

    public function toggleSku(Request $request){
        if(Gate::allows('company-only', auth()->user())){
            if($request->id){
                $sku = Skus::where('id',$request->id)->where('companies_id',\Auth::user()->companies_id)->first();
                if($sku){
                    $sku->active = ($sku->active == 1 ? 0:1);
                    $sku->save();
                    $response = array(
                        "success" =>true,
                    );
                    return response()->json($response,200);
                }
            }
        }
        return response()->json(array('success' => false),403);
    }

    public function updateSku(Request $request){
        if(Gate::allows('company-only', auth()->user())){
            if($request->edit_sku_id){
                $sku = Skus::where('id',$request->edit_sku_id)->where('companies_id',\Auth::user()->companies_id)->first();
                if($sku){
                    $sku->sku = $request->edit_sku_name;
                    $sku->save();
                    return redirect()->route('skus')->with('status','saved');
                }
            }
        }
        return redirect()->route('dashboard');
    }

    public function caseLabelRequiredFields(){
        if(Gate::allows('company-only', auth()->user())){

            $inventoryFields = InventoryFields::select('id','field_number','field_name','is_barcode')
                                            ->where('companies_id',\Auth::user()->companies_id)
                                            ->get();

            $caseLabelsReq = CaseLabelRequiredFields::where('companies_id',\Auth::user()->companies_id)->get();
            $caseLabelsReqArr = array();
            $caseLabelCaseNum = null;
            foreach($caseLabelsReq as $r){
                $caseLabelsReqArr[] = $r->inventory_fields_id;
                if($r->case_number_field == 1){
                    $caseLabelCaseNum = $r->inventory_fields_id;
                }
            }
            
            $data = array(
                'inventory_fields' => $inventoryFields,
                'req_case_labels' => $caseLabelsReqArr,
                'case_number_field' => $caseLabelCaseNum,
            );

            return view('layouts/settings/required_caselabel_fields')->with('data',$data);
        }

        return redirect()->route('dashboard');
    }

    public function saveCaseLabelRequiredFields(Request $request){
        if(Gate::allows('company-only', auth()->user())){
            CaseLabelRequiredFields::where('companies_id',\Auth::user()->companies_id)->delete();

            if($request->required_fields || $request->case_number_field){
                $saveArray = array();
                $match = 0;

                if($request->required_fields){
                    foreach($request->required_fields as $r){
                        if($request->case_number_field == $r){
                            $saveArray[] = array(
                                'companies_id' => \Auth::user()->companies_id,
                                'inventory_fields_id' => $r,
                                'case_number_field' => 1
                            );
                            $match = 1;
                        }else{
                            $saveArray[] = array(
                                'companies_id' => \Auth::user()->companies_id,
                                'inventory_fields_id' => $r,
                                'case_number_field' => 0
                            );
                        }
                        
                    }
                }
                

                if($match == 0 && $request->case_number_field != null){
                    $saveArray[] = array(
                        'companies_id' => \Auth::user()->companies_id,
                        'inventory_fields_id' => $request->case_number_field,
                        'case_number_field' => 1
                    );
                }

                if($saveArray){
                    CaseLabelRequiredFields::insert($saveArray);
                }
            }
            return redirect()->route('case_label_required')->with('status','saved');
        }

        return redirect()->route('dashboard');
    }
}
