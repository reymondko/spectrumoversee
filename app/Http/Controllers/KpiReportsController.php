<?php

namespace App\Http\Controllers;

use App\Models\Companies;
use App\Models\ShipPackSubmissions;
use App\Models\ShippingClient;
use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use DB;
use Excel;

class KpiReportsController extends Controller
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

    }

    /**
     * Display Kpi Report
     *
     * @return \Illuminate\Http\Response
     */
    public function kpiReport(){
        //DB::connection()->enableQueryLog();
        // Get 3pl client ids
        if (\Auth::user()->role != 1) {
        $companies = Companies::select('fulfillment_ids')
                              ->where('id',\Auth::user()->companies_id)
                              ->first();
            if($companies->fulfillment_ids){
            // Get 3pl client names
            $tplIdsArray = explode(',',$companies->fulfillment_ids);
            $shippingClients = ShippingClient::select('name','tpl_client_id')
                                             ->whereIn('tpl_client_id',$tplIdsArray)
                                             ->orderBy('name')
                                             ->get();
            }
        } else {
            //display all if admin or not user
            $shippingClients = ShippingClient::select('name','tpl_client_id')
                                             ->orderBy('name')
                                             ->get();
        }
            return view('layouts/kpi/report',['shippingClients'=>$shippingClients]);
        //}
        return redirect()->route('kpi-report');
    }

    /**
     * Generate Report Data
     *
     * @return \Illuminate\Http\Response
     */
    public function searchKpiReport(Request $request){

        $response = ['success'=>false];

        $validatedData = $request->validate([
            'start' => 'required',
            'end' => 'required',
        ]);

        // Get 3pl client ids
        /*$companies = Companies::select('fulfillment_ids')
                              ->where('id',\Auth::user()->companies_id)
                              ->first();*/

        //if($companies){
            //if($companies->fulfillment_ids != null){

                // Get Ship Pack Submissions
                $tplIdsArray = [];
                if($request->client != ''){
                    $tplIdsArray = [$request->client];
                }else{
                    //$tplIdsArray = explode(',',$companies->fulfillment_ids);
                    if (\Auth::user()->role != 1) {
                    $companies = Companies::select('fulfillment_ids')
                                          ->where('id',\Auth::user()->companies_id)
                                          ->first();
                        if($companies->fulfillment_ids){
                        // Get 3pl client names
                        $tplIdsArray = explode(',',$companies->fulfillment_ids);
                    }
                  }
                }

                $startDate = Carbon::parse($request->start)->startOfDay()->format('Y-m-d H:i:s');
                $endDate = Carbon::parse($request->end)->endOfDay()->format('Y-m-d H:i:s');

                if (count($tplIdsArray)) {
                  $shipPackSubmissions = ShipPackSubmissions::with('tpl_client','fulffiller')
                                                            ->whereIn('tpl_customer_id',$tplIdsArray)
                                                            ->whereBetween('created_at',[$startDate,$endDate])
                                                            ->orderBy('created_at','desc')
                                                            ->get();
                } else {
                  $shipPackSubmissions = ShipPackSubmissions::with('tpl_client','fulffiller')
                                                            ->whereBetween('created_at',[$startDate,$endDate])
                                                            ->orderBy('created_at','desc')
                                                            ->get();
                }

                if(count($shipPackSubmissions) > 0){

                    // Current Date
                    $now = Carbon::now();

                    // Table Data
                    $table_data = [];

                    // Array of average days before shipment is ordered and created
                    $averageDaysArray = [];

                    // Counter of reships
                    $reshipCounter = 0;

                    // Set response to success
                    $response['success'] = true;

                    foreach($shipPackSubmissions as $submission){
                        // Get difference in days betweend order_created_date - created_at
                        if($submission->order_created_date != NULL){
                            $tmpStart = Carbon::createFromFormat('Y-m-d H:i:s', $submission->order_created_date);
                            $tmpEnd = Carbon::createFromFormat('Y-m-d H:i:s', $submission->created_at);
                            $averageDaysArray[] = round((($tmpEnd->diffInMinutes($tmpStart)/60)/24),2);
                            // Get order age
                            $orderAge = round((($tmpStart->diffInMinutes($tmpEnd)/60)/24),2);
                        }

                        if(strpos(strtoupper($submission->reference_number),'REPL') !== FALSE){
                            $reshipCounter++;
                        }

                        $table_data[] = [
                            'client' => $submission->tpl_client->name ?? 'N/A',
                            'ship_date' => Carbon::parse($submission->created_at)->format('Y-m-d H:i A'),
                            'order_date' => Carbon::parse($submission->order_created_date)->format('Y-m-d H:i A'),
                            'order_number' => $submission->tpl_order_id,
                            'reference_number' => $submission->reference_number,
                            'carrier' => $submission->carrier,
                            'tracking_number' => $submission->tracking_number,
                            'fulfilled_by' => $submission->fulffiller->name ?? 'N/A',
                            'order_age' => $orderAge ?? 'N/A'
                        ];
                    }

                    // Get average of days between order and creation
                    $averageOrderDays = 0;
                    if(count($averageDaysArray) > 0){
                        $averageOrderDays = round((array_sum($averageDaysArray) / count($averageDaysArray)),2);
                    }

                    // Get error rate percentage
                    if(count($shipPackSubmissions)  > 0){
                        $errorCalc = ($reshipCounter / count($shipPackSubmissions)) * 100;
                        $errorRate = round($errorCalc,2);
                    }else{
                        $errorRate = 0;
                    }

                    $response['data']['average_days'] = $averageOrderDays;
                    $response['data']['error_rate'] = $errorRate;
                    $response['data']['reships'] = $reshipCounter;
                    $response['data']['table_data'] = $table_data;
                }
            //}
        //}

        return response()->json($response,200);
    }

    /**
     * Export Report Data
     *
     * @return Excel
     */
    public function exportKpiReport(Request $request){

        $response = ['success'=>false];

        $validatedData = $request->validate([
            'start' => 'required',
            'end' => 'required',
        ]);

        // Get 3pl client ids
        $companies = Companies::select('fulfillment_ids')
                              ->where('id',\Auth::user()->companies_id)
                              ->first();

        //if($companies){
        //    if($companies->fulfillment_ids != null){

                // Get Ship Pack Submissions
                $tplIdsArray = [];
                if($request->client != ''){
                    $tplIdsArray = [$request->client];
                }else{
                    //$tplIdsArray = explode(',',$companies->fulfillment_ids);
                    if (\Auth::user()->role != 1) {
                    $companies = Companies::select('fulfillment_ids')
                                          ->where('id',\Auth::user()->companies_id)
                                          ->first();
                        if($companies->fulfillment_ids){
                        // Get 3pl client names
                        $tplIdsArray = explode(',',$companies->fulfillment_ids);
                    }
                  }
                }

                $startDate = Carbon::parse($request->start)->startOfDay()->format('Y-m-d H:i:s');
                $endDate = Carbon::parse($request->end)->endOfDay()->format('Y-m-d H:i:s');

                if (count($tplIdsArray)) {
                  $shipPackSubmissions = ShipPackSubmissions::with('tpl_client','fulffiller')
                                                            ->whereIn('tpl_customer_id',$tplIdsArray)
                                                            ->whereBetween('created_at',[$startDate,$endDate])
                                                            ->orderBy('created_at','desc')
                                                            ->get();
                } else {
                  $shipPackSubmissions = ShipPackSubmissions::with('tpl_client','fulffiller')
                                                            ->whereBetween('created_at',[$startDate,$endDate])
                                                            ->orderBy('created_at','desc')
                                                            ->get();
                }

                if(count($shipPackSubmissions) > 0){

                    // Table Data
                    $table_data = [];
                    $table_data[] = [
                        'Client',
                        'Ship Date',
                        'Order Date',
                        'Tran #',
                        'Reference #',
                        'Carrier',
                        'Tracking Number',
                        'Fulfilled By',
                        'Order Age'
                    ];

                    // Set response to success
                    $response['success'] = true;

                    // Current Date
                    $now = Carbon::now();

                    // Table Data
                    $table_data = [];

                    // Array of average days before shipment is ordered and created
                    $averageDaysArray = [];

                    // Counter of reships
                    $reshipCounter = 0;

                    // Set response to success
                    $response['success'] = true;

                    foreach($shipPackSubmissions as $submission){
                        // Get difference in days betweend order_created_date - created_at
                        if($submission->order_created_date != NULL){
                            $tmpStart = Carbon::createFromFormat('Y-m-d H:i:s', $submission->order_created_date);
                            $tmpEnd = Carbon::createFromFormat('Y-m-d H:i:s', $submission->created_at);
                            $averageDaysArray[] = round((($tmpEnd->diffInMinutes($tmpStart)/60)/24),2);
                            // Get order age
                            $orderAge = round((($tmpStart->diffInMinutes($tmpEnd)/60)/24),2);
                        }

                        if(strpos(strtoupper($submission->reference_number),'REPL') !== FALSE){
                            $reshipCounter++;
                        }

                        $table_data[] = [
                          $submission->tpl_client->name ?? 'N/A',
                          Carbon::parse($submission->created_at)->format('Y-m-d H:i A'),
                          Carbon::parse($submission->order_created_date)->format('Y-m-d H:i A'),
                          $submission->tpl_order_id,
                          $submission->reference_number,
                          $submission->carrier,
                          $submission->tracking_number,
                          $submission->fulffiller->name ?? 'N/A',
                          'order_age' => $orderAge ?? 'N/A'
                        ];
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
                            $sheet->fromArray($table_data);
                        });
                    })->download('xlsx');

                    return $excel;
                }
            //}
        //}

        return response()->json($response,200);
    }

}
