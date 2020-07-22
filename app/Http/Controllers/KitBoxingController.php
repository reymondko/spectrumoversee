<?php

namespace App\Http\Controllers;

use App\Models\Batches;
use App\Models\BatchesItems;
use App\Models\Companies;
use App\Models\Skus;
use Illuminate\Http\Request;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use App\User;
use Carbon\Carbon;
use Session;
use Auth;
use DB;

class KitBoxingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {
        return view('layouts/kit/kitboxing');
    }

    public function getBatches(){
        $batches = Batches::where('batch_status', 'closed')
        ->where('boxing_status',0)
        ->orderBy('batch_number', 'asc')->get();
        return response()->json($batches);
    }
    public function getCompleteBatches(){
        $batches = Batches::where('batch_status', 'closed')
        ->where('boxing_status',1)
        ->orderBy('batch_number', 'asc')->get();
        return response()->json($batches);
    }



    public function getBatchNumber(Request $r){
        $batch_id=$r->batch_id;
       // DB::enableQueryLog(); // Enable query log
        $batches = Batches::select('batches.batch_number','box_id','skus.box_limit','skus.multi_barcode_count','skus.bulk_count','skus.sku as sku_name','batches.sku','batches.id')
        ->leftJoin(
            DB::raw("(SELECT box_id,batch_id from batches_items where batch_id = $batch_id order by created_at desc limit 1) as z"), 'z.batch_id', '=', 'batches.id'
            )
            ->leftjoin('skus','skus.id','=','batches.sku')
            ->where('batches.id', $r->batch_id)->get();
        //dd(DB::getQueryLog()); // Show results of log
        return response()->json($batches);
    }

    public function updateBoxID(Request $r){
        $batchid = $r->batchid;
        $master_kit = $r->master_kit;
        $box_num_input = $r->box_num_input;
        #where('batch_id', $batchid)          ->

        $batch=BatchesItems::where('master_kit_id', $master_kit)
          ->update(['box_id' => $box_num_input]);
        return "success";
    }

    public function validateMkit(Request $r){
        //DB::enableQueryLog();
        $validate=BatchesItems::where('master_kit_id', $r->master_kit_id)
        ->where('batch_id', $r->batch_id)->first();

        #->where('box_id', '=', '')

        if ($validate === null) {
            return "notvalid";
        }
        else{
            $validate2=BatchesItems::where('master_kit_id', $r->master_kit_id)->where('batch_id', $r->batch_id)->where('box_id', '=', null)->first();
            if ($validate2 === null) {
                return "invalid";
            }
            else{
                return "valid";
            }
        }
    }

    public function completeBatch(Request $r){
        $batchcompletez=Batches::where('id', $r->batch_id)
        ->update(['boxing_status' => "1"]);

        return response()->json(['success' => true, 'batch' => $batchcomplete]);
    }
}
