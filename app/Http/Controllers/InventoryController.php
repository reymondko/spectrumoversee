<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\InventoryFields;
use App\Models\Inventory;
use App\Models\HiddenInventoryFields;
use App\Models\InventoryItemScans;
use App\Models\InventoryNotes;
use App\Models\InventoryStatusLogs;
use App\Models\Locations;
use App\Models\Skus;
use App\Models\CaseLabelRequiredFields;
use App\Models\LogiwaDepositor;
use App\User;
use Carbon\Carbon;
use Yajra\Datatables\Datatables;
use Auth;
use Excel;
use Session;

class InventoryController extends Controller
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
        session()->forget('curentFilter');
        if (Gate::allows('company-only', auth()->user()) || Gate::allows('can_see_inventory', auth()->user()) ){

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

            $depositorIds = $this->getLogiwaDepositorIds(\Auth::user()->companies->id);

            if(!$depositorIds){
                $depositorIds = [];
            }


            $inventoryFields = InventoryFields::select('field_number','field_name')
                                            ->where('companies_id',\Auth::user()->companies_id)
                                            ->get();


            $inventoryLocations = Locations::select('name','tpl_customer_id')
                                            ->whereIn('tpl_customer_id',$depositorIds)
                                            ->get();

            $inventoryUsers = User::select('id','name','companies_id')
                                ->where('companies_id',\Auth::user()->companies_id)
                                ->get();


            $data = array(
                "inventory_fields" => $inventoryFields,
                "inventory_locations" => $inventoryLocations,
                "inventory_users" => $inventoryUsers,
                "hidden_inventory_fields" => $hiddenFields
            );

            return view('layouts/inventory/inventory')->with('data',$data);
        }

        return redirect()->route('dashboard');
    }

    public function inventoryGraph(Request $request)
    {
        session()->forget('curentFilter');
        if (Gate::allows('company-only', auth()->user()) || Gate::allows('can_see_inventory', auth()->user())){

            if(isset($request->location) && isset($request->type)){
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

                 $depositorIds = $this->getLogiwaDepositorIds(\Auth::user()->companies->id);

                if(!$depositorIds){
                    $depositorIds = [];
                }

                $inventoryFields = InventoryFields::select('field_number','field_name')
                                                ->where('companies_id',\Auth::user()->companies_id)
                                                ->get();


                $inventoryLocations = Locations::select('name','tpl_customer_id')
                                                ->whereIn('tpl_customer_id',$depositorIds)
                                                ->get();

                $inventoryUsers = User::select('id','name','companies_id')
                                    ->where('companies_id',\Auth::user()->companies_id)
                                    ->get();


                $data = array(
                    "inventory_fields" => $inventoryFields,
                    "inventory_locations" => $inventoryLocations,
                    "inventory_users" => $inventoryUsers,
                    "hidden_inventory_fields" => $hiddenFields
                );
            }

            return view('layouts/inventory/inventory_graph')->with('data',$data);
        }

        return redirect()->route('dashboard');
    }

    public function inventoryGraphData(Request $request){

        $depositorIds = $this->getLogiwaDepositorIds(\Auth::user()->companies->id);

        if(!$depositorIds){
            $depositorIds = [];
        }

        if($request->type == 'incoming'){
            return Datatables::of(Inventory::with('latestScan','latestScan.user:id,name')
                                ->whereIn('tpl_customer_id',$depositorIds)
                                ->where('last_scan_location',$request->location)
                                ->where('last_scan_type','incoming')
                                ->where('deleted',0))->make(true);

        }else{
            return Datatables::of(Inventory::with('latestScan','latestScan.user:id,name')
                                ->whereIn('tpl_customer_id',$depositorIds)
                                ->where('last_scan_location',$request->location)
                                ->where('last_scan_type','outgoing')
                                ->where('deleted',0))->make(true);
        }
    }

    public function paginateInventoryData(){
        $depositorIds = $this->getLogiwaDepositorIds(\Auth::user()->companies->id);

        if(!$depositorIds){
            $depositorIds = [];
        }
        return Datatables::of(Inventory::with('latestScan','latestScan.user:id,name')
                          ->whereIn('tpl_customer_id',$depositorIds)
                          ->where('deleted',0))->make(true);
    }

    public function paginateInventoryDataWithFilter(){
        if(session('curentFilter')){
            $inventoryValues = Inventory::with('latestScan','latestScan.user:id,name')
                                        ->whereIn('tpl_customer_id',$depositorIds)
                                        ->where('deleted',0);
            $filterFields = session('curentFilter');

            foreach($filterFields as $key => $value){
                if($value['filterField'] == 'created_at' || $value['filterField'] == 'last_scan_date'){
                    $date = Carbon::parse($value['filterValue']);
                    if($value['filterType'] == 'greater_than'){
                        $inventoryValues =  $inventoryValues->where($value['filterField'], '>=',$date->format('Y-m-d'));
                    }elseif($value['filterType'] == 'less_than'){
                        $inventoryValues =  $inventoryValues->where($value['filterField'], '<=',$date->format('Y-m-d'));
                    }
                }else{
                    if($value['filterType'] == 'is'){
                        $inventoryValues =  $inventoryValues->where($value['filterField'],$value['filterValue']);
                    }elseif($value['filterType'] == 'is_not'){
                      $inventoryValues =  $inventoryValues->where($value['filterField'],'<>',$value['filterValue']);
                    }elseif($value['filterType'] == 'has'){
                      $inventoryValues =  $inventoryValues->where($value['filterField'],'like','%'.$value['filterValue'].'%');
                    }
                }
            }

            return Datatables::of($inventoryValues)->make(true);

        }
    }

    public function inventoryFields(){
        return view('layouts/inventory/inventory_fields');
    }

    public function import(){
        if (Gate::allows('company-only', auth()->user()) ||
            Gate::allows('can_see_inventory_import', auth()->user())){
            return view('layouts/inventory/inventory_import');
        }else{
            return redirect()->route('dashboard');
        }
    }

    public function importMap(Request $request){
        if (Gate::allows('company-only', auth()->user()) ||
            Gate::allows('can_see_inventory_import', auth()->user())){

            $path = $request->file('import_file')->getRealPath();
            $importData = Excel::load($path)->get();
            $inventoryFields = InventoryFields::select('field_number','field_name')->where('companies_id',\Auth::user()->companies_id)->get();

            //store data on session temporarily
            $request->session()->put('import_data', $importData);

            $data = array(
                'inventory_fields' => $inventoryFields,
                'import_data_heading' => $importData->getHeading(),
            );

            return view('layouts/inventory/inventory_import_map')->with("data",$data);

        }else{
            return redirect()->route('dashboard');
        }
    }

    public function importCaseLabel(){
        if (Gate::allows('company-only', auth()->user()) ||
            Gate::allows('can_see_inventory_import', auth()->user())){
                $inventorySkus = Skus::select('sku')->where('companies_id',\Auth::user()->companies_id)->get();
                $inventoryFields = InventoryFields::select('field_number','field_name')
                                            ->where('companies_id',\Auth::user()->companies_id)
                                            ->get();
                $inventoryFieldsCount = $inventoryFields->count();

                $caseLabelsReq = CaseLabelRequiredFields::with('inventoryField')
                                                        ->where('companies_id',\Auth::user()->companies_id)
                                                        ->where('case_number_field','=','0')
                                                        ->get();

                $data = array(
                    'skus' => $inventorySkus,
                    'inventory_fields' => $inventoryFields,
                    'inventory_fields_count' => $inventoryFieldsCount,
                    'case_label_req' => $caseLabelsReq
                );

                return view('layouts/inventory/import_caselabel')->with('data',$data);
        }else{
            return redirect()->route('dashboard');
        }
    }

    public function importCaseLabelSave(Request $request){

        //get user location
        $userLocation = Locations::where('id',\Auth::user()->location_id)->first();
        if($userLocation){
            $scanLocation = $userLocation->name;
        }else{
            $scanLocation = 'N/A';
        }

        if (Gate::allows('company-only', auth()->user()) ||
            Gate::allows('can_see_inventory_import', auth()->user()))
        {

                $caseLabelsNumber = CaseLabelRequiredFields::with('inventoryField')
                                                            ->where('companies_id',\Auth::user()->companies_id)
                                                            ->where('case_number_field','=','1')
                                                            ->first();

                if($caseLabelsNumber){
                    $case_number = 'custom_field'.$caseLabelsNumber->inventoryField->field_number;
                }else{
                    $result = array('status' => 'no_case_number_field');
                    return redirect()->route('inventory_import_caselabel')->with('data',$result);
                }

                $path = $request->file('import_file')->getRealPath();
                $importData = Excel::load($path, function($reader) { $reader->noHeading = true; }, 'ISO-8859-1')->get();
                $row_to_skip = 0;
                $row_ctr = 0;
                $saveArray = array();
                $saveArrayCtr = 0;
                $sku = $request->sku;
                $batchId = \Auth::user()->companies_id."-".time()."-".uniqid();
                $currentTimestamp = Carbon::now();

                foreach($importData as $id){

                    if($row_ctr == 0){
                        $headersArray = $id->toArray();
                        if(!$headersArray){
                            $result = array('status' => 'incorrect_format');
                            return redirect()->route('inventory_import_caselabel')->with('data',$result);
                        }elseif($headersArray){
                            if(strtolower($headersArray[0]) != 'kit case'){
                                $result = array('status' => 'incorrect_format');
                                return redirect()->route('inventory_import_caselabel')->with('data',$result);
                            }
                        }

                    }

                    if($row_ctr > $row_to_skip){
                        foreach($id as $key=>$value){
                            if($key == 0){
                                $customField1 = $value;
                            }else{
                                if($value != NULL){
                                    $saveArray[$saveArrayCtr]['sku'] = $sku;
                                    $saveArray[$saveArrayCtr]['barcode_id'] = $value;
                                    $saveArray[$saveArrayCtr][$case_number] = $customField1;

                                    if($request->customfield){
                                        foreach($request->customfield as $key=>$value){
                                            $saveArray[$saveArrayCtr]['custom_field'.$value] = $request->customvalue[$key];
                                        }
                                    }

                                    $saveArray[$saveArrayCtr]['companies_id'] = \Auth::user()->companies_id;
                                    $saveArray[$saveArrayCtr]['created_at'] = $currentTimestamp;
                                    $saveArray[$saveArrayCtr]['updated_at'] = $currentTimestamp;
                                    $saveArray[$saveArrayCtr]['last_scan_by'] = \Auth::user()->id;
                                    $saveArray[$saveArrayCtr]['last_scan_location'] = $scanLocation;
                                    $saveArray[$saveArrayCtr]['last_scan_date'] = $currentTimestamp;
                                    $saveArray[$saveArrayCtr]['import_batch_id'] = $batchId;
                                    $saveArrayCtr++;
                                }
                            }
                        }
                    }
                $row_ctr++;
            }

            if($saveArray){

                //assign to array and redirect to @caseLabelSaveRecursive
                $collection = collect($saveArray);
                $chunks = $collection->chunk(1000);
                $chunks->toArray();

                $request->session()->put('caseLabelImport', $chunks->chunk(20));
                $request->session()->put('batchId', $batchId);
                $request->session()->put('scanLocation', $scanLocation);

                return redirect()->route('caselabel_save_recursive');

            }
        }else{
            return redirect()->route('dashboard');
        }
    }

    public function caseLabelSaveRecursive(Request $request){


        //get all imported data in the session and import to inventory
        //repeat if session array still has value
        if(count(session('caseLabelImport'))  > 0){
            foreach(session('caseLabelImport') as $key => $chunks){
                foreach($chunks as $chunk){
                    Inventory::insert($chunk->toArray());
                }
                unset(session('caseLabelImport')[$key]);
                break;
            }

            return redirect()->route('caselabel_save_recursive');
        }

        //if all inventory data has been imported
        //get all ids and insert to inventory scan
        //assign to array and redirect to @caseLabelSaveRecursiveScan
        $inventoryIds = Inventory::select('id','barcode_id')
                                    ->where('companies_id',\Auth::user()->companies_id)
                                    ->where('import_batch_id',session('batchId'))
                                    ->get();

        if($inventoryIds){

            $request->session()->put('caseLabelImportCount', count($inventoryIds));
            $currentTimestamp = Carbon::now();
            $saveScanArray = array();
            $scanArrayCtr = 0;

            foreach($inventoryIds as $i){
                $saveScanArray[$scanArrayCtr]['inventory_item_id'] = $i->id;
                $saveScanArray[$scanArrayCtr]['scanned_by_user_id'] = \Auth::user()->id;
                $saveScanArray[$scanArrayCtr]['barcode'] = $i->barcode_id;
                $saveScanArray[$scanArrayCtr]['scanned_location'] = session('scanLocation');
                $saveScanArray[$scanArrayCtr]['created_at'] = $currentTimestamp;
                $saveScanArray[$scanArrayCtr]['updated_at'] = $currentTimestamp;
                $saveScanArray[$scanArrayCtr]['companies_id'] = \Auth::user()->companies_id;
                $scanArrayCtr++;
            }

            $scanChunk = collect($saveScanArray)->chunk(1000);
            $scanChunk->toArray();
            $request->session()->put('caseLabelImportScan', $scanChunk->chunk(20));

            return redirect()->route('caselabel_save_recursive_scan');

        }


        //return to case label page if none
        $result = array(
            'total_records' => 0,
            'total_inserted' => 0,
            'status' => 'saved'
        );

        return redirect()->route('inventory_import_caselabel')->with('data',$result);

    }

    public function caseLabelSaveRecursiveScan(Request $request){

        //get all imported data in the session  and import to inventory scans
        //repeat if session array still has value
        if(count(session('caseLabelImportScan'))  > 0){
            foreach(session('caseLabelImportScan') as $key => $chunks){
                foreach($chunks as $chunk){
                    InventoryItemScans::insert($chunk->toArray());
                }
                unset(session('caseLabelImportScan')[$key]);
                break;
            }

            return redirect()->route('caselabel_save_recursive_scan');
        }


        $totalImported = session('caseLabelImportCount');

        //destroy all import sessions
        session()->forget('caseLabelImportScan');
        session()->forget('caseLabelImportCount');
        session()->forget('caseLabelImport');
        session()->forget('batchId');
        session()->forget('scanLocation');

        //return to case label page with count
        $result = array(
            'total_records' => $totalImported,
            'total_inserted' => $totalImported,
            'status' => 'saved'
        );

        return redirect()->route('inventory_import_caselabel')->with('data',$result);


    }

    public function importSave(Request $request){
        if (Gate::allows('company-only', auth()->user()) ||
            Gate::allows('can_see_inventory_import', auth()->user()))
        {

            $currentTimestamp = Carbon::now();

             //get user location
            $userLocation = Locations::where('id',\Auth::user()->location_id)->first();
            if($userLocation){
                $scanLocation = $userLocation->name;
            }else{
                $scanLocation = 'N/A';
            }
            $mappedFields = $request->inventory_field_map;
            $importData = $request->session()->get('import_data');
            $totalInserted = 0;
            $batchId = \Auth::user()->companies_id."-".time()."-".uniqid();
            $inventoryIds = array();
            $ctr = 0;

            foreach($importData as $d){
                $importDataArray = array_values($d->toArray());
                $inventory = new Inventory;
                $inventory->companies_id = \Auth::user()->companies_id;

                //assign mapped fields
                foreach($importDataArray as $key=>$value){
                    if($mappedFields[$key] != "0"){
                        $field = $mappedFields[$key];
                        $inventory->$field = $value;
                    }
                }

                $inventory->last_scan_by = \Auth::user()->id;;
                $inventory->last_scan_location = $scanLocation;
                $inventory->last_scan_date = $currentTimestamp;
                $inventory->import_batch_id = $batchId;

                if($inventory->save()){
                    $totalInserted++;
                }

                $saveScanArray[$ctr]['inventory_item_id'] = $inventory->id;
                $saveScanArray[$ctr]['scanned_by_user_id'] = \Auth::user()->id;
                $saveScanArray[$ctr]['barcode'] = $inventory->barcode_id;
                $saveScanArray[$ctr]['scanned_location'] = $scanLocation;
                $saveScanArray[$ctr]['created_at'] = $currentTimestamp;
                $saveScanArray[$ctr]['updated_at'] = $currentTimestamp;
                $saveScanArray[$ctr]['companies_id'] = \Auth::user()->companies_id;
                $ctr++;
            }

            InventoryItemScans::insert($saveScanArray);

            $result = array(
                'total_records' => $importData->count(),
                'total_inserted' => $totalInserted,
                'status' => 'saved'
            );

            return redirect()->route('inventory_import')->with('data',$result);
        }else{
            return redirect()->route('dashboard');
        }
    }

    public function exportInventory(){
        $data = array();

        $depositorIds = $this->getLogiwaDepositorIds(\Auth::user()->companies->id);

        if(!$depositorIds){
            $depositorIds = [];
        }

        $inventoryFields = InventoryFields::select('field_number','field_name')
                                          ->where('companies_id',\Auth::user()->companies_id)
                                          ->get();

        $inventoryValues = Inventory::whereIn('tpl_customer_id',$depositorIds)->where('deleted',0)->get();

        $dataToExport = array();
        $ctr = 0;
        $currentTime = Carbon::now();

        if($inventoryValues){
            foreach($inventoryValues as $iv){
                $dataToExport[$ctr]['sku'] = $iv->sku;
                $dataToExport[$ctr]['barcode_id'] = $iv->sku;
                foreach($inventoryFields as $if){
                    $field_name = 'custom_field'.$if->field_number;
                    $dataToExport[$ctr][$if->field_name] = $iv->$field_name;
                }
                $ctr++;
            }

            Excel::create('inventory_'.$currentTime, function($excel) use ($dataToExport) {
                $excel->sheet('inventory', function($sheet) use ($dataToExport)
                {
                    $sheet->fromArray($dataToExport);
                });
            })->download('xlsx');

            return redirect()->route('inventory');
        }
    }

    public function hideInventoryFields(Request $request){
        //InventoryHiddenFields
            $hiddenField = HiddenInventoryFields::where('users_id',\Auth::user()->id)->first();
            if($hiddenField){
                $hiddenField->hidden_inventory_fields = json_encode($request->inventoryFieldFilter);
            }else{
                $hiddenField = new HiddenInventoryFields;
                $hiddenField->users_id = \Auth::user()->id;
                $hiddenField->hidden_inventory_fields = json_encode($request->inventoryFieldFilter);
            }
            if($hiddenField->save()){
                return redirect()->route('inventory')->with('status','saved');
            }

        return redirect()->route('inventory');

    }

    public function inventoryFilter(Request $request){

        $filterFields = $request->filterField;
        $filterTypes = $request->filterType;
        $filterValues = $request->filterValue;

        $hiddenInventoryFields = HiddenInventoryFields::where('users_id',\Auth::user()->id)->first();

        if($hiddenInventoryFields){
            $hiddenFields = json_decode($hiddenInventoryFields->hidden_inventory_fields,true);
            if(!$hiddenFields){
                $hiddenFields = array('_EMPTY_VALUE_');
            }
        }else{
            $hiddenFields = array('_EMPTY_VALUE_');
        }

        $depositorIds = $this->getLogiwaDepositorIds(\Auth::user()->companies->id);

        if(!$depositorIds){
            $depositorIds = [];
        }

        $inventoryFields = InventoryFields::select('field_number','field_name')
                                      ->where('companies_id',\Auth::user()->companies_id)
                                      ->get();
        $inventoryLocations = Locations::select('name','tpl_customer_id')
                                        //->where('companies_id',\Auth::user()->companies_id)
                                      ->whereIn('tpl_customer_id',$depositorIds)
                                      ->get();

        $inventoryUsers = User::select('id','name','companies_id')
                             ->where('companies_id',\Auth::user()->companies_id)
                             ->get();

        if($filterValues){
            $currentFilterCtr = 0;
            foreach($request->filterField as $key => $value){
                $currentFilter[$currentFilterCtr]['filterField'] = $value;
                $currentFilter[$currentFilterCtr]['filterType'] = $filterTypes[$key];
                $currentFilter[$currentFilterCtr]['filterValue'] = $filterValues[$key];
                $currentFilterCtr++;
            }

            session()->put('curentFilter',$currentFilter);
            $data = array(
                "inventory_fields" => $inventoryFields,
                "inventory_locations" => $inventoryLocations,
                "inventory_users" => $inventoryUsers,
                "hidden_inventory_fields" => $hiddenFields,
                "current_filter" => $currentFilter
            );

            return view('layouts/inventory/inventory')->with('data',$data);
        }

        return redirect()->route('inventory');
    }

    public function scanInventory(Request $request){

        if(Gate::allows('company-only', auth()->user()) == false
        && Gate::allows('can_see_inventory_scan', auth()->user()) == false){
            $response = array('success'=>false,'message'=>"Unauthorized");
            return response()->json($response, 403);
        }

        if($request->barcode_id != '' || $request->barcode_id != null){

            $currentTimestamp = Carbon::now();

            //get user location
            $userLocation = Locations::where('id',\Auth::user()->location_id)->first();
            if($userLocation){
                $scanLocation = $userLocation->name;
            }else{
                $scanLocation = 'N/A';
            }

            //get all barcode custom fields
            $customBarcodeField = InventoryFields::where('companies_id',\Auth::user()->companies_id)
                                                 ->where('is_barcode',1)
                                                 ->get();

            $inventoryValues = Inventory::where('companies_id',\Auth::user()->companies_id)->where('barcode_id',$request->barcode_id)->where('deleted',0)->first();

            //if barcode field matches
            if($inventoryValues){
                if($inventoryValues->last_scan_location == $scanLocation){
                    $response = array('success'=>false,'message'=>"ITEM ALREADY SCANNED ON LOCATION");
                    return response()->json($response, 201);
                }

                $scan = new InventoryItemScans;
                $scan->inventory_item_id = $inventoryValues->id;
                $scan->scanned_by_user_id = \Auth::user()->id;
                $scan->barcode = $request->barcode_id;
                $scan->scanned_location = $scanLocation;
                $scan->companies_id = \Auth::user()->companies_id;
                $scan->created_at = $currentTimestamp;
                if($scan->save()){
                    $response = array('success'=>true,'total_items_scanned' =>1);
                    $inventoryValues->last_scan_location = $scanLocation;
                    $inventoryValues->last_scan_by = \Auth::user()->id;
                    $inventoryValues->last_scan_date = $currentTimestamp;
                    $inventoryValues->last_scan_type = 'scan_in';
                    $inventoryValues->save();
                    return response()->json($response, 201);
                }
            }

            //if barcode field does not match check other barcode fields
            if(!$inventoryValues && $customBarcodeField){
                foreach($customBarcodeField as $c){
                    $inventoryValues = Inventory::where('companies_id',\Auth::user()->companies_id);
                    $inventoryValues = $inventoryValues->where('custom_field'.$c->field_number,$request->barcode_id);
                    $inventoryValues = $inventoryValues->where('deleted','0');
                    $inventoryValues =  $inventoryValues->get();

                    //break loop if found
                    if($inventoryValues->count() > 0){
                        //filter by location
                        $inventoryValues = $inventoryValues->filter(function($item) use ($scanLocation){
                            return $item->last_scan_location != $scanLocation;
                        });

                        if($inventoryValues->count() == 0){
                            $response = array('success'=>false,'message'=>"ITEMS ALREADY SCANNED ON LOCATION",'total_items_scanned' =>0);
                            return response()->json($response, 201);
                        }

                        $inventoryScans = array();
                        $inventoryValueIds = array();
                        foreach($inventoryValues as $iv){
                            $inventoryScans[] = array(
                                'inventory_item_id' => $iv->id,
                                'scanned_by_user_id' => \Auth::user()->id,
                                'barcode' => $request->barcode_id,
                                'scanned_location' => $scanLocation,
                                'companies_id' => \Auth::user()->companies_id,
                                'created_at' => $currentTimestamp,
                                'updated_at' => $currentTimestamp
                            );
                            $inventoryValueIds[] = $iv->id;
                        }

                        if(InventoryItemScans::insert($inventoryScans)){
                            Inventory::where('companies_id',\Auth::user()->companies_id)
                                     ->whereIn('id',$inventoryValueIds)
                                     ->update(array(
                                                'last_scan_location' => $scanLocation,
                                                'last_scan_by'=> \Auth::user()->companies_id,
                                                'last_scan_date' => $currentTimestamp,
                                                'last_scan_type' => 'scan_in',
                                                'updated_at'=>$currentTimestamp
                                             ));
                        }

                        $response = array('success'=>true,'total_items_scanned' =>$inventoryValues->count());
                        return response()->json($response, 201);
                    }
                }
            }

            $response = array('success'=>false,'message'=>"BARCODE NOT FOUND");
        }else{
            $response = array('success'=>false,'message'=>"BARCODE NOT FOUND");
        }

        return response()->json($response, 201);
    }

    public function inventoryDetail(){
        if($_GET['id']){

            $depositorIds = $this->getLogiwaDepositorIds(\Auth::user()->companies->id);

            if(!$depositorIds){
                $depositorIds = [];
            }

            $inventoryValues = Inventory::where('id',$_GET['id'])->whereIn('tpl_customer_id',$depositorIds)->first();
            $inventoryFields = InventoryFields::select('field_number','field_name')
                                            ->where('companies_id',\Auth::user()->companies_id)
                                            ->get();

            $inventoryNotes = InventoryNotes::where('inventory_id',$_GET['id'])
                                            ->where('companies_id',\Auth::user()->companies_id)
                                            ->get();

            $inventoryStatus = InventoryStatusLogs::where('inventory_id',$_GET['id'])
                                                  ->where('companies_id',\Auth::user()->companies_id)
                                                  ->get();

            if($inventoryValues){

                $inventoryScans = InventoryItemScans::with('user:id,name')->where('inventory_item_id',$_GET['id'])->orderByDesc('created_at')->get();
                $data = array(
                    'barcode_id' => $inventoryValues->barcode_id,
                    'inventory_scans' =>$inventoryScans,
                    'inventory' => $inventoryValues,
                    'inventory_fields' => $inventoryFields,
                    'inventory_notes' => $inventoryNotes,
                    'inventory_status_logs' => $inventoryStatus
                );

                return view('layouts/inventory/inventory_detail')->with('data',$data);
            }
        }

        return redirect()->route('inventory');
    }

    public function deleteInventory(Request $request){
        if (Gate::allows('company-only', auth()->user()) ||
            Gate::allows('can_see_delete_inventory', auth()->user()))
        {
            if($request->inventory_ids){
                $inventory = Inventory::where('companies_id',\Auth::user()->companies_id)
                                        ->whereIn('id',$request->inventory_ids)
                                        ->update(
                                            array(
                                                'deleted' => 1,
                                                'deleted_by' => \Auth::user()->id,
                                                'deleted_at' => Carbon::now(),
                                            )
                                        );
                $response = array('success'=>true);
                return response()->json($response, 201);
            }

        }

        $response = array('success'=>false);
        return response()->json($response, 201);
    }

    public function addNote(Request $request){
        $inventory = Inventory::where('companies_id',\Auth::user()->companies_id)
                        ->where('id',$request->inventory_id)
                        ->first();
        if($inventory){
            InventoryNotes::insert([
                'companies_id' => \Auth::user()->companies_id,
                'users_id' => \Auth::user()->id,
                'inventory_id' => $request->inventory_id,
                'note' => $request->note,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            return redirect()->route('inventory_detail',['id' => $request->inventory_id])->with('status','saved');
        }

        return redirect()->route('dashboard');
    }

    public function updateStatus(Request $request){
        $inventory = Inventory::where('companies_id',\Auth::user()->companies_id)
                              ->where('id',$request->inventory_id)
                              ->first();

        if($inventory){
            $inventory->status = $request->status;
            if($inventory->save()){
                InventoryStatusLogs::insert([
                    'inventory_id' => $inventory->id,
                    'users_id' => \Auth::user()->id,
                    'companies_id' => \Auth::user()->companies_id,
                    'status' => $request->status,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
                $response = array('success'=>true);
            }
        }else{
            $response = array('success'=>false);
        }
        return response()->json($response, 201);
    }

    /**
     * Retrieves company depositor data
     *
     * @return App\Models\LogiwaDepositor
     */
    private function getLogiwaDepositorIds($companyId){
        $logiwaDepositors = LogiwaDepositor::where('companies_id',$companyId)->get()->pluck('logiwa_depositor_id')->toArray();
        return $logiwaDepositors;
    }
}
