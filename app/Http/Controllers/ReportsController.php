<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use App\Models\Companies;
use App\Models\ShipPackSubmissions;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Pagination\Paginator;
use Excel;
use DB;

class ReportsController extends Controller
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
        return view('layouts/reports/reports');
    }

    /**
     * Show Return Label Report
     * 
     * @return \Illuminate\Http\Response
     */
    public function returnLabelReport(){
        if (Gate::allows('admin-only', auth()->user())) {
            return view('layouts/reports/returnlabelreport');
        }else{
            return redirect()->route('dashboard');
        }
    }

    /**
     * Search for return labels based on date range
     * 
     * @param Illuminate\Http\Request
     *    $request
     * 
     * @return \Illuminate\Http\Response
     */
    public function returnLabelReportSearch(Request $request){

        $response = ['success'=>false];
        $return_label_summary = [];
        $start = strtotime($request->start);
        $end = strtotime($request->end);

        // Get Companies to be used to filter the Company Names
        $companies = Companies::all();

        $sql = "SELECT i.tpl_customer_id, Count(*)  as total_return_labels
                FROM  inventory_item_scans s 
                INNER JOIN inventory i 
                ON s.inventory_item_id = i.id 
                WHERE scan_type = 'outgoing' 
                AND scanned_location = 'customer' 
                AND s.created_at BETWEEN FROM_UNIXTIME(".$start.") AND FROM_UNIXTIME(".$end.")
                GROUP BY i.tpl_customer_id";

        $result = DB::select($sql);

        if($result){
            foreach($result as $res){
                $tmp = [];
                $tmp['return_labels_used'] = $res->total_return_labels;
                $tmp['tpl_customer'] = $res->tpl_customer_id;
                foreach($companies as $company){
                    if($company->fulfillment_ids == $res->tpl_customer_id){
                        $tmp['tpl_customer'] = $company->company_name;
                        break;
                    }
                }
                $return_label_summary[] = $tmp;
            }
            $response['success'] = true;
        }
        
        $response['data']['table_data'] = $return_label_summary;
        return response()->json($response,200);
    }

    /**
     * Search for return labels based on date range
     * 
     * @param Illuminate\Http\Request
     *    $request
     * 
     * @return Excel
     */
    public function returnLabelReportExport(Request $request){
        
        $response = ['success'=>false];
        $return_label_summary = [];
        $start = strtotime($request->start);
        $end = strtotime($request->end);


        // Get Companies to be used to filter the Company Names
        $companies = Companies::all();

        $sql = "SELECT i.tpl_customer_id, i.barcode_id,i.return_tracking_number,s.created_at
                FROM  inventory_item_scans s 
                INNER JOIN inventory i 
                ON s.inventory_item_id = i.id 
                WHERE scan_type = 'outgoing' 
                AND scanned_location = 'customer' 
                AND s.created_at BETWEEN FROM_UNIXTIME(".$start.") AND FROM_UNIXTIME(".$end.")
                ORDER BY i.tpl_customer_id";

        $result = DB::select($sql);
        if($result){
            foreach($result as $res){
                $table_data[] = [
                    'Customer ID'=>  $res->tpl_customer_id,
                    'Return Label' =>$res->return_tracking_number,
                    'Scanned Return Label'=> $res->barcode_id,
                    'Scan Date'=>$res->created_at
                ];
            }
        }
        // Export to excel
        $currentTime = Carbon::now();
        $excel = Excel::create('inventory_'.$currentTime, function($excel) use ($table_data) {
            $excel->sheet('inventory', function($sheet) use ($table_data)
            {
                $sheet->setFontFamily('Calibri');
                $sheet->setFontSize(9);
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
                // Set background color for a specific cell
                $sheet->getStyle('A1:D1')->applyFromArray(array(
                    'font' => array(
                        'name'      =>  'Calibri',
                        'size'      =>  12,
                        'bold'      =>  true
                    )
                ));
                $sheet->cell('A1:A'.(count($table_data) + 2), function($cell) {
                    // manipulate the cell
                    $cell->setAlignment('center');
                
                });
                $sheet->cell('B1:B'.(count($table_data) + 2), function($cell) {
                    // manipulate the cell
                    $cell->setAlignment('center');
                
                });
                $sheet->cell('C1:C'.(count($table_data) + 2), function($cell) {
                    // manipulate the cell
                    $cell->setAlignment('center');
                
                });
                $sheet->cell('D1:D'.(count($table_data) + 2), function($cell) {
                    // manipulate the cell
                    $cell->setAlignment('center');
                
                });
                $sheet->fromArray(array_merge($table_data));
            });
        })->download('xlsx');
        return $excel;
    }
    
    /**
     * Show Shipping Cost Report Page
     * 
     * @return \Illuminate\Http\Response
     */
    public function shippingCostReport(){
        if (Gate::allows('admin-only', auth()->user())) {
            $companies = Companies::orderBy('company_name','ASC')->get();
            return view('layouts/reports/shippingcostreport')->with('companies',$companies);
        }else{
            return redirect()->route('dashboard');
        }
    }

     /**
     * Search for shipping cost report
     * 
     * @param Illuminate\Http\Request
     *    $request
     * 
     * @return \Illuminate\Http\Response
     */
    public function shippingCostReportSearch(Request $request){

        $response = ['success'=>false];
        $return_label_summary = [];
        $start = Carbon::createFromTimestamp(strtotime($request->start));
        $end = Carbon::createFromTimestamp(strtotime($request->end));
        $selected_company = $request->company;

        $customer_ids = [];
        $companies_array = [];
        if($selected_company == 'all'){
            $companies = Companies::get();
            foreach($companies as $company){
                $companies_array[$company->id] = $company->company_name;
                if($company->fulfillment_ids != NULL){
                    $tmpFulfillmentArr = explode(',',$company->fulfillment_ids);
                    foreach($tmpFulfillmentArr as $arr){
                        $customer_ids[] = $arr;
                    }
                }
            }
        }else{
            $companies = Companies::where('id',$selected_company)->first();
            if($companies->fulfillment_ids != NULL){
                $tmpFulfillmentArr = explode(',',$companies->fulfillment_ids);
                foreach($tmpFulfillmentArr as $arr){
                    $customer_ids[] = $arr;
                }
            }
        }

        $shipping_cost_report = [];

        // Set pagination size
        $tableSize = null;
        $tableSizePerPage = 25;
        $page = 1;
        $lastPage = 0;

        // Make sure that you call the static method currentPageResolver()
        // before querying ShipPackSubmissions
        if($request->page){
            $currentPage = $request->page;
            Paginator::currentPageResolver(function () use ($currentPage) {
                return $currentPage;
            });
        }

       
        // Retrieve Ship Pack Submission for Customer
        $ship_pack_submissions = ShipPackSubmissions::select('users.companies_id','companies.company_name','user_id','tpl_customer_id','tpl_order_id','tracking_number','shipping_cost','shipping_cost_with_markup','weight','shipping_vendor_id')
                                                    ->join('users','users.id','=','user_id')
                                                    ->join('companies','companies.id','=','users.companies_id')
                                                    ->leftJoin('shipping_vendors','shipping_vendors.id','=','shipping_vendor_id')
                                                    ->where('ship_pack_submissions.created_at','>=',$start)
                                                    ->where('ship_pack_submissions.created_at','<=',$end)
                                                    ->whereIn('tpl_customer_id',$customer_ids);
        if($request->sort_by){
            switch($request->sort_by){
                case 'company_name':
                    $ship_pack_submissions->orderBy('companies.company_name',$request->sort_dir);
                    break;
                case 'tpl_customer_id':
                    $ship_pack_submissions->orderBy('ship_pack_submissions.tpl_customer_id',$request->sort_dir);
                    break;
                case 'tpl_order_id':
                    $ship_pack_submissions->orderBy('ship_pack_submissions.tpl_order_id',$request->sort_dir);
                    break;
                case 'tracking_number':
                    $ship_pack_submissions->orderBy('ship_pack_submissions.tracking_number',$request->sort_dir);
                    break;
                case 'shipping_cost':
                    $ship_pack_submissions->orderBy('ship_pack_submissions.shipping_cost',$request->sort_dir);
                    break;
                case 'shipping_cost_with_markup':
                    $ship_pack_submissions->orderBy('ship_pack_submissions.shipping_cost_with_markup',$request->sort_dir);
                    break;
                case 'weight':
                    $ship_pack_submissions->orderBy('ship_pack_submissions.weight',$request->sort_dir);
                    break;
                case 'shipping_vendor':
                    $ship_pack_submissions->orderBy('shipping_vendors.vendor_name',$request->sort_dir);
                    break;
            }
        }


        $ship_pack_submissions = $ship_pack_submissions->paginate($tableSizePerPage);
        if($ship_pack_submissions){
            foreach($ship_pack_submissions as $submission){
                $shipping_cost_report[] = [
                    'company_name' =>  ($selected_company == 'all' ? $companies_array[$submission->fulffiller->companies_id] : $companies->company_name), 
                    'tpl_customer_id' => $submission->tpl_customer_id ??  'N/A',
                    'tpl_order_id' => $submission->tpl_order_id ??  'N/A',
                    'tracking_number' => $submission->tracking_number ??  'N/A',
                    'shipping_cost' => '$' . $submission->shipping_cost ??  'N/A',
                    'shipping_cost_with_markup' => ($submission->shipping_cost_with_markup ? '$' . $submission->shipping_cost_with_markup : 'N/A'),
                    'weight' => $submission->weight ??  'N/A',
                    'shipping_vendor' => $submission->vendor->vendor_name ?? 'N/A',
                ];
            }

            if($ship_pack_submissions->total()){
                $response['table_size'] = $ship_pack_submissions->total();
            }

            if(count($shipping_cost_report) > 0){
                $response['success'] = true;
            }
        }

        $response['data']['table_data'] = $shipping_cost_report;
        return response()->json($response,200);
    }

    /**
     * Export for shipping cost report
     * 
     * @param Illuminate\Http\Request
     *    $request
     * 
     * @return Excel
     */
    public function shippingCostReportExport(Request $request){
        
        $response = ['success'=>false];
        $return_label_summary = [];
        $start = Carbon::createFromTimestamp(strtotime($request->start));
        $end = Carbon::createFromTimestamp(strtotime($request->end));
        $selected_company = $request->company;

        $customer_ids = [];
        $companies_array = [];
        if($selected_company == 'all'){
            $companies = Companies::get();
            foreach($companies as $company){
                $companies_array[$company->id] = $company->company_name;
                if($company->fulfillment_ids != NULL){
                    $tmpFulfillmentArr = explode(',',$company->fulfillment_ids);
                    foreach($tmpFulfillmentArr as $arr){
                        $customer_ids[] = $arr;
                    }
                }
            }
        }else{
            $companies = Companies::where('id',$selected_company)->first();
            if($companies->fulfillment_ids != NULL){
                $tmpFulfillmentArr = explode(',',$companies->fulfillment_ids);
                foreach($tmpFulfillmentArr as $arr){
                    $customer_ids[] = $arr;
                }
            }
        }


        $table_data = [];
        // Retrieve Ship Pack Submission for Customer
        $ship_pack_submissions = ShipPackSubmissions::with('fulffiller:id,companies_id','vendor:id,vendor_name')
                                                    ->select('companies_id','user_id','tpl_customer_id','tpl_order_id','tracking_number','shipping_cost','shipping_cost_with_markup','weight','shipping_vendor_id')
                                                    ->where('created_at','>=',$start)
                                                    ->where('created_at','<=',$end)
                                                    ->whereIn('tpl_customer_id',$customer_ids)
                                                    ->get();
        if($ship_pack_submissions){
            foreach($ship_pack_submissions as $submission){
                $table_data[] = [
                    'Company Name' => ($selected_company == 'all' ? $companies_array[$submission->fulffiller->companies_id] : $companies->company_name),
                    '3pl Customer ID' => $submission->tpl_customer_id ??  'N/A',
                    'Transaction ID' => $submission->tpl_order_id ??  'N/A',
                    'Tracking Number' => $submission->tracking_number ??  'N/A',
                    'Shipping Cost' => '$' . $submission->shipping_cost ??  'N/A',
                    'Shipping Cost with Markup' => ($submission->shipping_cost_with_markup ? '$' . $submission->shipping_cost_with_markup : 'N/A'),
                    'Weight' => $submission->weight ??  'N/A',
                    'Shipping Vendor' => $submission->vendor->vendor_name ?? 'N/A',
                ];
            }
        }

        // Export to excel
        $currentTime = Carbon::now();
        $excel = Excel::create('inventory_'.$currentTime, function($excel) use ($table_data) {
            $excel->sheet('inventory', function($sheet) use ($table_data)
            {
                $sheet->setFontFamily('Calibri');
                $sheet->setFontSize(9);
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
                // Set background color for a specific cell
                $sheet->getStyle('A1:H1')->applyFromArray(array(
                    'font' => array(
                        'name'      =>  'Calibri',
                        'size'      =>  12,
                        'bold'      =>  true
                    )
                ));
                $sheet->cell('A1:A'.(count($table_data) + 2), function($cell) {
                    // manipulate the cell
                    $cell->setAlignment('center');
                
                });
                $sheet->cell('B1:B'.(count($table_data) + 2), function($cell) {
                    // manipulate the cell
                    $cell->setAlignment('center');
                
                });
                $sheet->cell('C1:C'.(count($table_data) + 2), function($cell) {
                    // manipulate the cell
                    $cell->setAlignment('center');
                
                });
                $sheet->cell('D1:D'.(count($table_data) + 2), function($cell) {
                    // manipulate the cell
                    $cell->setAlignment('center');
                
                });
                $sheet->cell('E1:E'.(count($table_data) + 2), function($cell) {
                    // manipulate the cell
                    $cell->setAlignment('center');
                
                });
                $sheet->cell('F1:F'.(count($table_data) + 2), function($cell) {
                    // manipulate the cell
                    $cell->setAlignment('center');
                
                });
                $sheet->cell('G1:G'.(count($table_data) + 2), function($cell) {
                    // manipulate the cell
                    $cell->setAlignment('center');
                
                });
                $sheet->cell('H1:H'.(count($table_data) + 2), function($cell) {
                    // manipulate the cell
                    $cell->setAlignment('center');
                
                });
                $sheet->fromArray(array_merge($table_data));
            });
        })->download('xlsx');
        return $excel;
    }
}
