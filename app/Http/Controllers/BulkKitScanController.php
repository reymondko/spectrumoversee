<?php

namespace App\Http\Controllers;

use App\Models\Batches;
use App\Models\BatchesItems;
use App\Models\Skus;
use App\Models\Companies;
use Illuminate\Http\Request;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use App\User;
use Carbon\Carbon;
use File;
use Session;
use Auth;
use DB;
use Excel;
use ZipArchive;

class BulkKitScanController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {
        return view('layouts/kit/bulkkitscan');
    }
    
    public function getSkus(){
        $skus = Skus::where('active', 1)
        ->where('multi_barcode' , 2)
        ->orderBy('sku', 'asc')->get();
        return response()->json($skus);
    }

    public function getOpenBatches(){
        $batches = Batches::select(DB::raw('batches.*'))->where('batch_status', 'open')
        ->leftjoin('skus','skus.id','batches.sku')
        ->where('multi_barcode' , 2)
        ->orderBy('batch_number', 'asc')->get();
        return response()->json($batches);
    }
    public function addBatchNum(Request $r){
      if($r->expirationdate){
        $expdate=date("Y-m-d",strtotime($r->expirationdate));
      }
      else{
        $expdate ="";
      }
        $batch = new Batches;
        $batch->batch_number = $r->batch_number;
        $batch->sku = $r->sku;
        $batch->batch_status ="open";
        $batch->created_by_id = Auth::user()->id;
        $batch->expiration_date = $expdate;
        $batch->save();

        $sku = Skus::where('id',$r->sku)->first();
        $box_limit = $sku->box_limit;
        $multi_barcode_count = $sku->multi_barcode_count;
        $box_id=0;

        return response()->json(array('success' => true, 'last_insert_id' => $batch->id, 'bulk_count' => $sku->bulk_count,'sku_name' => $sku->sku), 200);
    }

    public function addMasterKit(Request $r){
        $created_by_id = Auth::user()->id;
               
        $batchitem = new BatchesItems;
        $batchitem->batch_id = $r->batch_id;
        $batchitem->master_kit_id = $r->master_kit;
        $batchitem->box_id = $r->box_number;
        $batchitem->subkit_id = $r->subkit_numbers;
        $batchitem->created_by_id = $created_by_id;
        $batchitem->save();
        
        return response()->json(array('success' => true, 'last_insert_id' => $batchitem->id,'box_id' => $r->box_number), 200);
    }

    public function editMasterKit(request $r){

      $batchitem = BatchesItems::where('id',$r->edit_batch_item_id)->first();
      $batchitem->master_kit_id =$r->edit_master_kit_input;
      $batchitem->subkit_id =$r->edit_sub_kit_number;
      $batchitem->return_tracking =$r->edit_return_tracking_number;
      $batchitem->save();
      return back();

    }

    public function updateReturnTracking(request $r){
        $rtn_count=count($r->return_tracking_numbers);

        $minz=intval($r->bulk_count) / intval($rtn_count);
        $limit=ceil($minz);
        $batch_id=$r->batch_id;
        foreach($r->return_tracking_numbers as $rtn)
        {
            BatchesItems::where('batch_id', '=', $batch_id)
            ->whereNull('return_tracking')
            ->limit($limit)
            ->update(['return_tracking' => $rtn]);
            
        }
        return "waa";
      }

    
    
    public function validateMkit(Request $r){
        $validate=BatchesItems::join('batches', 'batch_id', '=', 'batches.id')
        ->where('master_kit_id', $r->master_kit_id)
        ->where('sku', $r->sku_id)->first();

        if ($validate === null) {
            return "valid";
        }
        else{
            return "invalid";
        }
    }

    public function checkBarcode(Request $r){
        $skus = Skus::where('id', $r->sku_id)
        ->orderBy('sku', 'asc')->get();
        return response()->json($skus);
    }
}
