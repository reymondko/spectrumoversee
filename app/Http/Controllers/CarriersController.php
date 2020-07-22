<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Models\ShippingCarriers;
use App\Models\ShippingCarrierMethods;
use App\Models\ShippingVendors;
use Carbon\Carbon;
use Session;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

class CarriersController extends Controller
{
    public function index(){
        if (Gate::allows('admin-only', auth()->user())) {
            $shippingVendors = ShippingVendors::get();
            $shippingCarriers = ShippingCarriers::with('methods')->get();

            $carriers = array();
            $vendors = array();

            if($shippingVendors){
                foreach($shippingVendors as $vendor){
                    $vendors[] = array('id' => $vendor->id,'vendor_name'=>$vendor->vendor_name);
                }
            }

            if($shippingCarriers){       
                foreach($shippingCarriers as $sc){
                    $carriers[] = array(
                                        'id'=>$sc->id,
                                        'name'=>$sc->name,
                                        'methods'=>$sc->methods            
                                      );     
                }
            }
            return view('layouts/carriers/carriers',compact(['carriers','vendors']));
        }else{
            return redirect()->route('dashboard');
        }
    }

    public function addCarrierMethod(Request $request){
        if (Gate::allows('admin-only', auth()->user())) {

            $status = 'error_saving';
            try{
                $carrierMethod = new ShippingCarrierMethods;
                $carrierMethod->shipping_carriers_id = $request->carrier_id;
                $carrierMethod->name = $request->method;
                $carrierMethod->value = $request->val;
                $carrierMethod->shipping_vendor_id = $request->vendor_id;
                $carrierMethod->account_number = $request->account_number;
                $carrierMethod->markup = number_format($request->markup,2);
                if($carrierMethod->save()){
                    $data = [
                        'status' => 'saved',
                        'previous' => $request->carrier_id
                    ];
                }
            }catch(Exception $e){
                
            }
            
            return redirect()->back()->with('data',$data);
        }else{
            return redirect()->route('dashboard');
        }
    }

    public function editCarrierMethod(Request $request){
        if (Gate::allows('admin-only', auth()->user())) {
            $status = 'error_saving';
            try{
                $carrierMethod = ShippingCarrierMethods::where('id',$request->id)->first();
                $carrierMethod->shipping_carriers_id = $request->carrier_id;
                $carrierMethod->name = $request->method;
                $carrierMethod->value = $request->val;
                $carrierMethod->shipping_vendor_id = $request->vendor_id;
                $carrierMethod->account_number = $request->account_number;
                $carrierMethod->markup = number_format($request->markup,2);
                if($carrierMethod->save()){
                    $data = [
                        'status' => 'saved',
                        'previous' => $request->carrier_id
                    ];
                }
            }catch(Exception $e){
                
            }
            
            return redirect()->back()->with('data',$data);
        }else{
            return redirect()->route('dashboard');
        }
    }

    public function deleteMethod($id){
        if (Gate::allows('admin-only', auth()->user())) {

            $status = 'error_deleting';
            try{
                $carrierMethod = ShippingCarrierMethods::where('id',$id);
                $data['previous'] = $carrierMethod->first()->shipping_carriers_id;
                if($carrierMethod->delete()){
                     $data['status'] = 'deleted';
                     
                }
            }catch(Exception $e){
                
            }
            
            return redirect()->back()->with('data',$data);
        }else{
            return redirect()->route('dashboard');
        }
    }

    public function addCarrier(Request $request){
        if (Gate::allows('admin-only', auth()->user())) {

            $status = 'error_saving';
            try{
                $carrier = new ShippingCarriers;
                $carrier->name = $request->carrier_name;
                if($carrier->save()){
                    $status = 'saved';
                }
                
                if(!empty($request->method)){
                    foreach($request->method as $key=>$value){
                        $carrierMethod = new ShippingCarrierMethods;
                        $carrierMethod->shipping_carriers_id = $carrier->id;
                        $carrierMethod->name = $value;
                        $carrierMethod->value = $request->val[$key];
                        $carrierMethod->save();
                    }
                }
            }catch(Exception $e){
                
            }
            
            return redirect()->back()->with('status',$status);
        }else{
            return redirect()->route('dashboard');
        }
    }

    public function editCarrier(Request $request){
        if (Gate::allows('admin-only', auth()->user())) {
            $status = 'error_saving';
            try{
                $carrier = ShippingCarriers::where('id',$request->id)->first();
                $carrier->name = $request->name;
                if($carrier->save()){
                    $status = 'saved';
                }
            }catch(Exception $e){
                
            }
            
            return redirect()->back()->with('status',$status);
        }else{
            return redirect()->route('dashboard');
        }
    }

    public function deleteCarrier($id){
        if (Gate::allows('admin-only', auth()->user())) {

            $status = 'error_deleting';
            try{
                $carrier = ShippingCarriers::where('id',$id);
                if($carrier->delete()){
                    $status = 'deleted';
                }
            }catch(Exception $e){
                
            }
            
            return redirect()->back()->with('status',$status);
        }else{
            return redirect()->route('dashboard');
        }
    }
}
