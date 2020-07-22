<?php

namespace App\Http\Controllers;

use App\Models\Skus;
use App\Models\Companies;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use App\User;
use Carbon\Carbon;
use Session;
use Auth;
use DB;

class KitSkuController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {
        if (Gate::allows('admin-only', auth()->user()) || Gate::allows('can_see_kit_sync_report', auth()->user())){
            $kitskus = Skus::where('active', 1)->join('companies', 'companies_id', '=', 'companies.id')->select(['companies.company_name', DB::raw('skus.*')])->get();

            $companies = Companies::get();

            $data = array(
                'kitskus' => $kitskus,
                'companies' =>$companies
            );

            return view('layouts/sku/skus')->with('data',$data);
        }

        return redirect()->route('dashboard');
    }

    public function addKitSku(request $r){
        if (Gate::allows('admin-only', auth()->user()) || Gate::allows('can_see_kit_sync_report', auth()->user())){
            $expiration_date=null;
            if($r->expiration_date){
                $expiration_date = date("Y-m-d", strtotime( $r->expiration_date) );
            }
            $sku = new Skus;
            $sku->companies_id = $r->company;
            $sku->sku = $r->sku;
            $sku->multi_barcode = $r->multi_barcode;
            $sku->multi_barcode_count = $r->multi_barcode_count;
            $sku->bulk_count = $r->bulk_count;
            
            #$sku->hs_code = $r->hs_code;
            $sku->box_limit = $r->box_limit;
            $sku->requires_expiration_date = $expiration_date;
            $sku->active = 1;
            $sku->save();

            return redirect('kit-sku')->with('status', 'saved');
        }

        return redirect()->route('dashboard');
    }

    public function editKitSku(request $r){ 
        $values=array('companies_id' => $r->company_edit,'sku' => $r->sku_edit,'multi_barcode' => $r->multi_barcode_edit,'multi_barcode_count' => $r->multi_barcode_count_edit,'requires_expiration_date' => $r->expiration_date_edit, 'box_limit' => $r->box_limit_edit,'bulk_count' => $r->bulk_count_edit);

        $batch=Skus::where('id', $r->id_edit)->update($values);

        return redirect('kit-sku')->with('status', 'saved');
    }

    public function deleteKitSku(Request $request){
        if (Gate::allows('admin-only', auth()->user()) || Gate::allows('can_see_kit_sync_report', auth()->user())){
           $sku = Skus::where('id',$request->sku_id)->first();
           if($sku){
               $sku->active = 0;
               $sku->save();

               return redirect('kit-sku')->with('status', 'deleted');
           }
        }

        return redirect()->route('dashboard');
    }

}
