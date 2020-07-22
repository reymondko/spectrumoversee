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

class KitReturnSyncController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {
        return view('layouts/kit/kitreturnsync');
    }

    public function getSkus(){
        $skus = Skus::where('active', 1)
        ->where('multi_barcode', '!=' , 2)
        ->orderBy('sku', 'asc')->get();
        return response()->json($skus);
    }

    public function getOpenBatches(){
        $batches = Batches::where('batch_status', 'open')
        ->leftjoin('skus','skus.id','batches.sku')
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

        return response()->json(array('success' => true, 'last_insert_id' => $batch->id, 'box_limit' => $sku->box_limit, 'multi_barcode_count' => $multi_barcode_count), 200);
    }

    public function addMasterKit(Request $r){
        $created_by_id = Auth::user()->id;
        /*
        $batch =  Batches::leftjoin('skus','skus.id','batches.sku')->where('batches.id',$r->batch_id)->get();
        $batchitemlast=BatchesItems::where('batch_id',$r->batch_id)->orderby('created_at','desc')->first();

        $batchname = $batch[0]->batch_number;
        $box_limit = $batch[0]->box_limit;

        $box_id_last=$batchitemlast->box_id;
        if($batchitemlast){
          $batchcountz = BatchesItems::where('batch_id',$r->batch_id)->where('box_id',$box_id_last)->get()->count();
        }
        else{
          $batchcountz = 0;
        }
        */
        //DB::enableQueryLog(); // Enable query log
        $x=0;
        foreach($r->subkit_numbers as $subkit_numbers){
            if($subkit_numbers!=null){
                // $batchcount++;
                //$boxnum = ceil($batchcount/intval($box_limit));
                //if($boxnum <= 0) {$boxnum = 1;}
                $batchitem = new BatchesItems;
                $batchitem->batch_id = $r->batch_id;
                $batchitem->master_kit_id = $r->master_kit;
                $batchitem->box_id = $r->box_number;
                $batchitem->subkit_id = $subkit_numbers;
                $batchitem->return_tracking = $r->return_tracking_numbers[$x];
                $batchitem->created_by_id = $created_by_id;
                $batchitem->save();
            }
            $x++;
        }


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

    public function checkExpirtaion(Request $r){
        $skus = Skus::where('id', $r->sku_id)
        ->orderBy('sku', 'asc')->get();
        return response()->json($skus);
    }


    public function validateSubKit(Request $r){
        $validate=BatchesItems::join('batches', 'batch_id', '=', 'batches.id')
        ->where('subkit_id', $r->subkit_id)
        ->where('sku', $r->sku_id)->first();

        if ($validate === null) {
            return "valid";
        }
        else{
            return "invalid";
        }
    }

    public function validaterReturnTracking(Request $r){
      $validate=BatchesItems::join('batches', 'batch_id', '=', 'batches.id')
      ->where('return_tracking', $r->return_tracking)
      ->where('batch_id', $r->batch_id)->first();

      if ($validate === null) {
          return "valid";
      }
      else{
          return "invalid";
      }
  }

    public function validatBoxnum(Request $r){


      $batch =  Batches::leftjoin('skus','skus.id','batches.sku')->where('batches.id',$r->batch_id)->get();
      $batchname = $batch[0]->batch_number;
      $box_limit = $batch[0]->box_limit;
      $multi_barcode_count = $batch[0]->multi_barcode_count;
      if(!isset($box_limit)){
        $box_limit=0;
      }
      $batchitemlast=BatchesItems::where('batch_id',$r->batch_id)->where('box_id',$r->edit_box_number)->first();
      if($batchitemlast){
        $box_id_last=$batchitemlast->box_id;
        $batchcountz = BatchesItems::where('batch_id',$r->batch_id)->where('box_id',$box_id_last)->get()->count();
      }
      else{
        $batchcountz = 0;
      }
      //DB::enableQueryLog();
      if($batchcountz < $box_limit && $batchcountz !=0){
        $box_id=$box_id_last;
      }
      if($batchcountz >= $box_limit && $batchcountz !=0){

        $box_id="full";
      }
      else{
        $box_id="new";
      }

      return response()->json(array('success' => true, 'box_id' => $box_id, 'box_count' => $batchcountz,'box_limit' => $box_limit,'multi_barcode_count' => $multi_barcode_count), 200);
      //return back();
    }


    public function getBoxnum(Request $r){
      $batch =  Batches::leftjoin('skus','skus.id','batches.sku')->where('batches.id',$r->batch_id)->get();
      $batchname = $batch[0]->batch_number;
      $box_limit = $batch[0]->box_limit;
      $multi_barcode = $batch[0]->multi_barcode;
      $multi_barcode_count = $batch[0]->multi_barcode_count;
      if(!isset($box_limit)){
        $box_limit=0;
      }
      $batchitemlast=BatchesItems::where('batch_id',$r->batch_id)->orderby('created_at','desc')->first();


      if($batchitemlast){
        $box_id_last=$batchitemlast->box_id;
        if($multi_barcode==1)
        {
          $batchcountz = BatchesItems::where('batch_id',$r->batch_id)->where('box_id',$box_id_last)->groupBy('master_kit_id')->get()->count();
        }
        else{
          $batchcountz = BatchesItems::where('batch_id',$r->batch_id)->where('box_id',$box_id_last)->get()->count();
        }

      }
      else{
        $batchcountz = 0;
      }
      //DB::enableQueryLog();
      if($batchcountz < $box_limit && $batchcountz !=0){
        $box_id=$box_id_last;
      }
      else{
        $box_id="new";
      }

      return response()->json(array('success' => true, 'box_id' => $box_id, 'box_count' => $batchcountz,'box_limit' => $box_limit,'multi_barcode_count' => $multi_barcode_count), 200);
      //return back();
    }



    public function closeBatch(Request $r){
        $batchclose=Batches::where('id', $r->batch_id)
        ->update(['batch_status' => "closed" ]);
        //return response()->json($batchclose);
        return back();
    }

    public function summary(Request $r) {
      $data = DB::select("select
                            b.id,
                            b.batch_number,
                            b.created_at,
                            s.sku,
                            c.company_name,
                            b.batch_status,
                            b.expiration_date,
                            b.receiver_id,
                            (select count(distinct master_kit_id) from batches_items where batch_id=b.id) as master_kits,
                            (select count(distinct box_id) from batches_items where batch_id=b.id and box_id is not null) as boxes,
                            (select count(*) from batches_items where batch_id=b.id) as kits
                          from
                            batches b inner join skus s on b.sku=s.id inner join companies c on c.id=s.companies_id
                          order by b.created_at desc");
      return view('layouts/kit/summary')->with('data', $data);
    }

    public function details(Request $r, $id) {
      $batch = Batches::find($id);
      /*$data = DB::select("select
                            b.id,
                            b.batch_number,
                            b.created_at,
                            s.sku,
                            c.company_name,
                            bi.master_kit_id,
                            bi.subkit_id,
                            bi.box_id,
                            bi.created_at as subkit_created_at
                          from
                            batches b inner join skus s on b.sku=s.id inner join companies c on c.id=s.companies_id inner join batches_items bi on b.id=bi.batch_id
                          where b.id=$id");*/
          $data=DB::table('batches')
                ->leftjoin('skus', 'batches.sku', '=', 'skus.id')
                ->leftjoin('companies', 'companies.id', '=', 'skus.companies_id')
                ->leftjoin('batches_items', 'batches.id', '=', 'batches_items.batch_id')
                ->where('batches.id',$id)
                ->orderBy('batches.created_at','desc')
                ->select('batches.id',
                'batches.batch_number',
                'batches.created_at',
                'skus.sku',
                'companies.company_name',
                'batches_items.id as bi_id',
                'batches_items.master_kit_id',
                'batches_items.subkit_id',
                'batches_items.box_id',
                'batches_items.return_tracking',
                'batches_items.created_at as subkit_created_at')
                ->paginate(200);
      return view('layouts/kit/details')->with('data', ['data' => $data, 'batch' => $batch, 'id' => $id]);
    }

    public function export(Request $r, $id) {
      $data = DB::select("select
                            b.id,
                            b.batch_number,
                            b.created_at,
                            s.sku,
                            c.company_name,
                            bi.master_kit_id,
                            bi.subkit_id,
                            bi.return_tracking,
                            bi.box_id,
                            bi.created_at as subkit_created_at
                          from
                            batches b inner join skus s on b.sku=s.id inner join companies c on c.id=s.companies_id inner join batches_items bi on b.id=bi.batch_id
                          where b.id=$id");

      $table_data = [];
      $table_data[] = [
        'Batch Id',
        'Batch Date',
        'SKU',
        'Company',
        'Master Kit #',
        'Subkit #',
        'Return Tracking #',
        'Box #',
        'Subkit Date'
      ];

      foreach ($data as $row) {
        $table_data[] = [
          $row->batch_number,
          $row->created_at,
          $row->sku,
          $row->company_name,
          $row->master_kit_id,
          $row->subkit_id,
          $row->return_tracking,
          $row->box_id,
          $row->subkit_created_at
        ];
      }

      // Export to excel
      $excel = Excel::create('Kit Codes Batch - '.$id, function($excel) use ($table_data) {
          $excel->sheet('kit codes', function($sheet) use ($table_data)
          {
              $sheet->setFontFamily('Calibri');
              $sheet->setFontSize(11);
              $sheet->setStyle([
                  'borders' => [
                      'allborders' => [
                          'style' => 'thin',
                          'color' => [
                              'rgb' => 'FFFFFF'
                          ]
                      ]
                  ]
              ]);
              $sheet->fromArray($table_data);
          });
      })->download('xlsx');

      return $excel;
    }

    public function createReceiver(Request $r, $id) {

      //set default timezone to MST
      date_default_timezone_set('America/Los_Angeles');

      $batchcomplete=Batches::where('id', $id)->get();
      $serialnum=$batchcomplete[0]->batch_number;
      $sku=$batchcomplete[0]->sku;

      //get the 3pl company id
      $company= Companies::join('skus', 'companies_id', '=', 'companies.id')
      ->where('skus.id',  $sku)->get();
      $company_3pl = $company[0]->fulfillment_ids;
      if (strpos($company_3pl, ',') !== false) {
        $company_3plarr=explode(",",$company_3pl);
        $company_3pl=$company_3plarr[0];
      }

      //get the sku
      $sku_name = $company[0]->sku;

      //build array of items for the receiver
      /*$items = [];
      $batchItems = BatchesItems::where('batch_id', $id)->get();
      foreach ($batchItems as $batchItem) {
        $items[] = [
          'ReadOnly' => [
              'UnitIdentifier' => [
                'Name' => 'Each',
                'Id' => 1,
              ],
            ],
            'ItemIdentifier' => [ 'Sku' => $sku_name ],
            'Qualifier' => null,
            'Qty' => 1.0,
            'LotNumber' => $batchItem->return_tracking,
            'SerialNumber' => $batchItem->master_kit_id,
            'OnHold' => false,
            'locationInfo' => ['locationId' => 3691],
            'SavedElements' => [],
            '_links' => [],
            'palletInfo' => [
              'label' => $batchItem->box_id,
              'palletTypeIdentifier' => [
                'id' => 2
              ]
            ]
        ];
      }*/

      $batchItems = BatchesItems::where('batch_id', $id)
                ->select('master_kit_id', 'box_id', 'expiration_date', DB::raw('count(*) as kits'), DB::raw('count(distinct return_tracking) as tracking'), DB::raw('max(return_tracking) as return_tracking'))
                ->join('batches', 'batches_items.batch_id', '=', 'batches.id')
                ->groupBy('master_kit_id', 'box_id', 'expiration_date')
                ->get();
      foreach ($batchItems as $batchItem) {
        if ($batchItem->kits > 1) { //if master kit id has multiple subkits, then only send the master kit id

          $return_tracking = null;
          if ($batchItem->tracking == 1) {
            //get the return tracking
            $returnTracking = BatchesItems::where('master_kit_id', $batchItem->master_kit_id)->where('batch_id', $id)->whereRaw("length(return_tracking)>1")->get();
            if (count($returnTracking)) {
              foreach ($returnTracking as $track) { //get the first result
                $return_tracking = $track->return_tracking;
              }
            }
          }

          $items[] = [
            'ReadOnly' => [
                'UnitIdentifier' => [
                  'Name' => 'Each',
                  'Id' => 1,
                ],
              ],
              'ItemIdentifier' => [ 'Sku' => $sku_name ],
              'Qualifier' => null,
              'Qty' => 1.0,
              'LotNumber' => $return_tracking,
              'SerialNumber' => $batchItem->master_kit_id,
              'ExpirationDate' => (strlen($batchItem->expiration_date)) ? date('Y-m-d', strtotime($batchItem->expiration_date)).'T00:00:00' : null,
              'OnHold' => false,
              'locationInfo' => ['LocationID' => 4305],
              'SavedElements' => [],
              '_links' => [],
              'palletInfo' => [
                'label' => $batchItem->box_id,
                'palletTypeIdentifier' => [
                  'id' => 2
                ]
              ]
          ];
        } else { //else there is only a single subkit for the master
          //see if the subkit_id is the same as the master_kit_id
          $result = BatchesItems::where(['batch_id' => $id, 'master_kit_id' => $batchItem->master_kit_id])->whereRaw('master_kit_id<>subkit_id')->first();
          if (is_object($result)) { //master kit id is different than the subkit id

            $return_tracking = null;
            if ($batchItem->tracking == 1) {
              //get the return tracking
              $returnTracking = BatchesItems::where('master_kit_id', $batchItem->master_kit_id)->where('batch_id', $id)->whereRaw("length(return_tracking)>1")->get();
              if (count($returnTracking)) {
                foreach ($returnTracking as $track) { //get the first result
                  $return_tracking = $track->return_tracking;
                }
              }
            }

            $items[] = [
              'ReadOnly' => [
                  'UnitIdentifier' => [
                    'Name' => 'Each',
                    'Id' => 1,
                  ],
                ],
                'ItemIdentifier' => [ 'Sku' => $sku_name ],
                'Qualifier' => null,
                'Qty' => 1.0,
                'LotNumber' => $return_tracking,
                'SerialNumber' => $batchItem->master_kit_id,
                'ExpirationDate' => (strlen($batchItem->expiration_date)) ? date('Y-m-d', strtotime($batchItem->expiration_date)).'T00:00:00' : null,
                'OnHold' => false,
                'locationInfo' => ['LocationID' => 4305],
                'SavedElements' => [],
                '_links' => [],
                'palletInfo' => [
                  'label' => $batchItem->box_id,
                  'palletTypeIdentifier' => [
                    'id' => 2
                  ]
                ]
            ];
          } else { //master kit id is the same as the subkit id so we can include the tracking number
            $items[] = [
              'ReadOnly' => [
                  'UnitIdentifier' => [
                    'Name' => 'Each',
                    'Id' => 1,
                  ],
                ],
                'ItemIdentifier' => [ 'Sku' => $sku_name ],
                'Qualifier' => null,
                'Qty' => 1.0,
                'LotNumber' => $batchItem->return_tracking,
                'SerialNumber' => $batchItem->master_kit_id,
                'ExpirationDate' => (strlen($batchItem->expiration_date)) ? date('Y-m-d', strtotime($batchItem->expiration_date)).'T00:00:00' : null,
                'OnHold' => false,
                'locationInfo' => ['LocationID' => 4305],
                'SavedElements' => [],
                '_links' => [],
                'palletInfo' => [
                  'label' => $batchItem->box_id,
                  'palletTypeIdentifier' => [
                    'id' => 2
                  ]
                ]
            ];
          }
        }
      }

      \Log::info($items);

      $client = new Client();

      /* get access token */
      $accessToken = null;
      $link = null;
      try {
          $request = $client->request('POST', 'https://secure-wms.com/AuthServer/api/Token', [
              'headers' => ['Authorization' => 'Basic Yzc5YWVjNjktNmE4ZC00ZDIyLTg2NmUtZGI3NjQ2ZTFiYTYxOnU1WEMrRTBWQVlZMGtDZnVYZFNtbTFheFhtcW5ZUnA4'],
              'json' => [
                  'grant_type' => 'client_credentials',
                  'tpl' => '{e55a580d-29d1-43d0-9b7b-448c5602a223}',
                  'user_login_id' => '1'
              ]]);
          $response = json_decode($request->getBody());
          $accessToken = $response->access_token;
      } catch (\Exception $e) {
          //do something
          \Log::info('Error while getting access token');
          return response()->json(['success' => 'false', 'error' => 'Error while getting access token.']);
      }

      //build array to create receiver
      $data=array (
          'CustomerIdentifier' =>
          array (
            'Id' => $company_3pl,
          ),
          'FacilityIdentifier' =>
          array (
            'Name' => 'Spectrum Solutions',
            'Id' => 1,
          ),
          'ReferenceNum' => 'OVERSEE-BATCH-'.$company_3pl.'-'.$id.'-'.time(),
          'PoNum' => NULL,
          'ReceiptAdviceNumber' => NULL,
          'ArrivalDate' => date('Y-m-d').'T'.date('H:i:s'),
          'Notes' => NULL,
          'ScacCode' => NULL,
          'Carrier' => NULL,
          'BillOfLading' => NULL,
          'DoorNumber' => NULL,
          'TrackingNumber' => NULL,
          'TrailerNumber' => NULL,
          'SealNumber' => NULL,
          'numUnits1' => 1.0,
          'ReceiveItems' => $items
      );

      // api post to reciever
      $receiverId = null;
      try {
          \Log::info(json_encode($data));

          $request2 = $client->request('POST', 'https://secure-wms.com/inventory/receivers', [
              'headers' => [
                'Authorization' => 'Bearer '.$accessToken,
                  'Content-Type' => 'application/json',
              ],
              'body' => json_encode($data)
            ]);
          $headers = $request2->getHeaders();
          $etag = $headers['ETag'][0];
          $response2 = json_decode($request2->getBody());

          $receiverId = $response2->ReadOnly->ReceiverId;
      }catch (\GuzzleHttp\Exception\BadResponseException $e) {
          //echo $e->getResponse()->getBody()->getContents();
          \Log::info("Error with creating receiver");
          \Log::info($e->getResponse()->getBody()->getContents());
          \Log::info(json_encode($data));

          return response()->json(['success' => 'false', 'error' => 'Error while creating receiver.']);
      }

      //confirm receiver
      $data2=array (
          'arrivalDate' => date('Y-m-d').'T'.date('H:i:s'),
          'trackingNumber' => NULL,
          'trailerNumber' => NULL,
          'sealNumber' => NULL,
          'billOfLading' => NULL,
          'loadNumber' => NULL,
          'billing' =>
          array (
            'billingCharges' => NULL,
          ),
          'recalcAutoCharges' => false,
          'invoiceCreationInfo' => NULL,
      );
      try {
          $request = $client->request('POST', "https://secure-wms.com/inventory/receivers/".$receiverId."/confirmer", [
              'headers' => [
                'Authorization' => 'Bearer '.$accessToken,
                  'Content-Type' => 'application/json',
                  'If-Match' => $etag
              ],
              'body' => json_encode($data2)
            ]);

          $response = json_decode($request->getBody());
      }catch (\GuzzleHttp\Exception\BadResponseException $e) {
          //echo $e->getResponse()->getBody()->getContents();
          \Log::info("Error with confirming receiver");
          \Log::info($e->getResponse()->getBody()->getContents());
          \Log::info(json_encode($data2));

          return response()->json(['success' => 'false', 'error' => 'Error while confirming receiver.']);
      }

      //everything worked, go ahead and update the batch and mark as confirmed
      $batchcompletez=Batches::where('id', $id)
      ->update(['boxing_status' => "1", 'receiver_id' => $receiverId]);

      //redirect
      header("Location: /kit-return-sync/summary/$id?status=created");
      exit;
    }

    // Delete batch items
    public function deleteBatchItem(Request $r, $id) {
      //dd($r->deleteid);
      //$batchid=$r->batchid;
      //foreach($r->deleteid as $id){
        BatchesItems::where('id', $id)->first()->delete();
      //}
      //return $batchid;
      return back();
    }
    // Delete batch number
    public function deleteBatch(Request $r){
          //dd($r->batchid);
          $batchid = $r->batchid;
          $batchitems=BatchesItems::where('batch_id', $batchid)->delete();

          Batches::where('id', $batchid)->first()->delete();
          return $batchid;
    }


    public function exportKitReturnSyncLogiwa(Request $request, $batchid){

        $data = DB::select("select
                            b.id,
                            b.batch_number,
                            b.created_at,
                            b.expiration_date,
                            s.sku,
                            c.company_name,
                            bi.master_kit_id,
                            bi.subkit_id,
                            bi.return_tracking,
                            bi.box_id,
                            bi.created_at as subkit_created_at
                        from
                            batches b inner join skus s on b.sku=s.id inner join companies c on c.id=s.companies_id inner join batches_items bi on b.id=bi.batch_id
                        where b.id=$batchid");


        $serial_data = [];
        $order_data = [];

        $masterData = array_chunk($data,19996);
        $ctr = 1;
        foreach($masterData as $mdata){
            $serial_data = [];
            $order_data = [];
            foreach($mdata as $d){
                $serial_data[] = [
                    $d->batch_number,
                    $d->company_name,
                    'Spectrum Solutions',
                    $d->sku,
                    $d->subkit_id ?? 'N/A',
                    $d->return_tracking ?? 'N/A',
                    $d->expiration_date ?? 'N/A',
                ];

                $order_data[] = [
                    ' ',
                    $d->batch_number,
                    'Spectrum - Purchase Order',
                    'Spectrum Solutions',
                    $d->company_name,
                    'Spectrum DNA',
                    'Spectrum DNA',
                    'US',
                    'UT',
                    'Draper',
                    '84020',
                    '12248 Lone Peak Pkwy',
                    '8015690465',
                    date('m-d-Y'),
                    $d->sku,
                    'UNIT',
                    1,
                    'RECV',
                    'case',
                    $d->box_id,
                    $d->return_tracking ?? 'N/A',
                    $d->expiration_date ?? 'N/A',
                ];
            }

            // Serials
            $filename = 'Receipt Serials Import-'.date('m.d.y').'_part-'.$ctr;
            $this->storeExcelFile($serial_data,$filename,'serials');
            // Orders
            $filename = 'Receipt Order Import-'.date('m.d.y').'_part-'.$ctr;
            $this->storeExcelFile($order_data,$filename,'orders');
            $ctr++;
        }

        // Create a zip file for the spreadsheets
        $zip_file = 'LogiwaReturnSync-Batch-'.$batchid.'-'.date('m.d.y').'.zip';
        #$zip_file = 'LogiwaReturnSync-Batch.zip';
        $zip = new ZipArchive();
        $zip->open('/tmp/'.$zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $path = storage_path('app/public/excel');
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
        foreach ($files as $name => $file)
        {
            if (!$file->isDir()) {
                $filePath     = $file->getRealPath();
                $relativePath = substr($filePath, strlen($path) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }
        $zip->close();
        // Delete folder after compression
        //File::deleteDirectory(storage_path('app/public/excel'));
        return response()->download('/tmp/'.$zip_file)->deleteFileAfterSend(true);;
    }


    /**
     * Creates and Stores excel files to public storage
     *
     * @param array
     *  - $export_data = data to be saved
     * @param string
     *  - $filename = file name
     * @param string
     *  - $type = type of excel template to use
     *
     * @return bool
     */
    public function storeExcelFile($export_data,$filename,$type){
        if($type == 'serials'){
            Excel::load('/public/assets/templates/serials_template.xlsx', function($reader) use ($export_data)
            {
                $reader->sheet('Sheet1',function($sheet) use ($export_data) {
                    foreach($export_data as $data){
                        $sheet->appendRow($data);
                    }
                });
            })->setFilename($filename)->store('xlsx', storage_path('app/public/excel'),true);
        }else{
            Excel::load('/public/assets/templates/orders_template.xlsx', function($reader) use ($export_data)
            {
                $reader->sheet('Sheet1',function($sheet) use ($export_data) {
                    foreach($export_data as $data){
                        $sheet->appendRow($data);
                    }
                });
            })->setFilename($filename)->store('xlsx', storage_path('app/public/excel'));
        }

    }
}
