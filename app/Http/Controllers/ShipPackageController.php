<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Models\ShipPackage;
use Carbon\Carbon;
use Session;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

class ShipPackageController extends Controller
{
    public function index(){
        if (Gate::allows('admin-only', auth()->user())) {
            $shipPackage = ShipPackage::get();
            $data = array();
            $data['ship_package'] = $shipPackage;
            return view('layouts/shippackage/shippackage')->with('data',$data);
        }else{
            return redirect()->route('dashboard');
        }
    }

    public function addPackageSize(Request $request){
        if (Gate::allows('admin-only', auth()->user())) {

            $status = 'error_saving';
            try{
                $shipPackage = new ShipPackage;
                $shipPackage->package_name = $request->package_name;
                $shipPackage->length = $request->length;
                $shipPackage->width = $request->width;
                $shipPackage->height = $request->height;
                $shipPackage->weight = $request->weight;
                if($shipPackage->save()){
                    $status = 'saved';
                }
            }catch(Exception $e){
                
            }
            
            return redirect()->back()->with('status',$status);
        }else{
            return redirect()->route('dashboard');
        }
    }

    public function editPackageSize(Request $request){
        if (Gate::allows('admin-only', auth()->user())) {

            $status = 'error_saving';
            try{
                $shipPackage = ShipPackage::where('id',$request->edit_id)->first();
                $shipPackage->package_name = $request->edit_package_name;
                $shipPackage->length = $request->edit_length;
                $shipPackage->width = $request->edit_width;
                $shipPackage->height = $request->edit_height;
                $shipPackage->weight = $request->edit_weight;
                if($shipPackage->save()){
                    $status = 'saved';
                }
            }catch(Exception $e){
                
            }
            
            return redirect()->back()->with('status',$status);
        }else{
            return redirect()->route('dashboard');
        }
    }

    public function deletePackageSize($id){
        if (Gate::allows('admin-only', auth()->user())) {

            $status = 'error_deleting';
            try{
                $shipPackage = ShipPackage::where('id',$id);
                if($shipPackage->delete()){
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
