<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Models\TplShipperAddress;
use Carbon\Carbon;
use Session;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

class ThirdPartyShipperAddressController extends Controller
{
    public function index(){
        if (Gate::allows('admin-only', auth()->user())) {
            $shipper = TplShipperAddress::get();
            $data = array();
            $data['shipper'] = $shipper;
            return view('layouts/thirdparty/shipperaddress')->with('data',$data);
        }else{
            return redirect()->route('dashboard');
        }
    }

    public function addShipperAddress(Request $request){
        if (Gate::allows('admin-only', auth()->user())) {

            $status = 'error_saving';
            try{
                $shipper = new TplShipperAddress;
                $shipper->tpl_customer_id = $request->tpl_customer_id;
                $shipper->first_name = $request->first_name;
                $shipper->last_name = $request->last_name;
                $shipper->address = $request->address;
                $shipper->city = $request->city;
                $shipper->state = $request->state;
                $shipper->country = $request->country;
                //$shipper->postal_code = null;
                $shipper->phone_number = $request->phone_number;
                $shipper->zip = $request->zip;
                $shipper->account_number = $request->account_number;
                $shipper->minimum_package_weight = $request->minimum_package_weight;
                if($shipper->save()){
                    $status = 'saved';
                }
            }catch(Exception $e){

            }

            return redirect()->back()->with('status',$status);
        }else{
            return redirect()->route('dashboard');
        }
    }

    public function editShipperAddress(Request $request){
        if (Gate::allows('admin-only', auth()->user())) {

            $status = 'error_saving';
            try{
                $shipper = TplShipperAddress::where('id',$request->edit_id)->first();
                $shipper->tpl_customer_id = $request->edit_tpl_customer_id;
                $shipper->first_name = $request->edit_first_name;
                $shipper->last_name = $request->edit_last_name;
                $shipper->address = $request->edit_address;
                $shipper->city = $request->edit_city;
                $shipper->state = $request->edit_state;
                $shipper->country = $request->edit_country;
                //$shipper->postal_code = null;
                $shipper->phone_number = $request->edit_phone_number;
                $shipper->zip = $request->edit_zip;
                $shipper->account_number = $request->edit_account_number;
                $shipper->minimum_package_weight = $request->edit_minimum_package_weight;
                if($shipper->save()){
                    $status = 'saved';
                }
            }catch(Exception $e){

            }

            return redirect()->back()->with('status',$status);
        }else{
            return redirect()->route('dashboard');
        }
    }

    public function deleteShipperAddress($id){
        if (Gate::allows('admin-only', auth()->user())) {

            $status = 'error_deleting';
            try{
                $shipper = TplShipperAddress::where('id',$id);
                if($shipper->delete()){
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
