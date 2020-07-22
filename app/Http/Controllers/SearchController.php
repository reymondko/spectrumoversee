<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\InventoryFields;
use App\Models\Inventory;
use App\Models\HiddenInventoryFields;
use App\Models\InventoryItemScans;
use App\Models\Locations;
use App\User;

class SearchController extends Controller
{
    public function index(){

        $data = array();

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
        $inventoryLocations = Locations::select('name','tpl_customer_id')
                                        //->where('companies_id',\Auth::user()->companies_id)
                                        ->whereIn('tpl_customer_id',explode(',', \Auth::user()->companies->fulfillment_ids))
                                        ->get();
  
        $inventoryUsers = User::select('id','name','companies_id')
                                 ->where('companies_id',\Auth::user()->companies_id)
                                 ->get();

        $currentFilter = array();
        $currentFilterCtr = 0;

        $inventoryValues = Inventory::where('companies_id',\Auth::user()->companies_id)->where('deleted',0)->where('barcode_id',$_GET['s'])->get();
        //get all barcode custom fields
        $customBarcodeField = InventoryFields::where('companies_id',\Auth::user()->companies_id)
                                             ->where('is_barcode',1)
                                             ->get();
        
        if($inventoryValues->count() == 0){
            if($customBarcodeField){
                foreach($customBarcodeField as $c){
                    $inventoryValues = Inventory::where('companies_id',\Auth::user()->companies_id)->where('deleted',0);
                    $inventoryValues = $inventoryValues->where('custom_field'.$c->field_number,$_GET['s']);
                    $inventoryValues =  $inventoryValues->get();
                    if($inventoryValues->count() > 0){
                        break;
                    }
                }
            }
        }

        $data = array(
            "inventory_fields" => $inventoryFields,
            "inventory_values" => $inventoryValues,
            "inventory_locations" => $inventoryLocations,
            "inventory_users" => $inventoryUsers,
            "hidden_inventory_fields" => $hiddenFields,
            "current_filter" => $currentFilter
        );

        return view('layouts/search/search')->with('data',$data);
    }
}
