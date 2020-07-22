<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Models\Companies;
use Illuminate\Support\Facades\Log;
use App\Libraries\Logiwa\LogiwaAPI;
use Carbon\Carbon;
use App\Models\LogiwaDepositor;
use App\Models\LogiwaOrderMonthlyReport;

class LogiwaOrdersReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logiwa:ordersReport';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retrieves current monthly data from logiwa';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        //initialize search dates
        $this->currentMonth = Carbon::now()->format('M');
        $this->currentYear  = Carbon::now()->year;
        $this->startDate = new Carbon('first day of this month');
        $this->startDate = $this->startDate->format('m.d.Y H:i:s');
        $this->endDate = Carbon::now()->format('m.d.Y H:i:s');

        $logiwaDepositors = $this->retrieveDepositorIds();
        foreach($logiwaDepositors as $depositorData){
            $this->getMonthlyData($depositorData);
        }

    }

    /**
     * Retrieves Depositor Ids
     *
     * @return App\Models\LogiwaDepositor
     */
    private function retrieveDepositorIds(){
        $logiwaDepositors = LogiwaDepositor::get();
        return $logiwaDepositors;
    }

    /**
    * Get all orders for the current month of the specific company
    *
    * @param App\Models\LogiwaDepositor
    *   - $depositorData
    *
    * @return bool
    */
    private function getMonthlyData($depositorData){
        $body = [];
        $orders = [];
        $body['WarehouseId'] = env('LOGIWA_WAREHOUSE_ID');
        $body['DepositorID'] = $depositorData->logiwa_depositor_id;
        $body['DepositorCode'] = $depositorData->logiwa_depositor_code;
        $body['OrderDateStart'] = $this->startDate;
        $body['OrderDateEnd'] = $this->endDate;
        $logiwa = new LogiwaAPI;
        $request = $logiwa->getWarehouseOrderSearch($body);
        if($request['success'] == true){
            if(isset($request['data']->Data)){
                foreach($request['data']->Data as $data){
                    $filtered = true;
                    if(\DateTime::createFromFormat('m.d.Y H:i:s', $data->OrderDate)->getTimestamp() <= strtotime($body['OrderDateStart'])
                    || \DateTime::createFromFormat('m.d.Y H:i:s', $data->OrderDate)->getTimestamp() >= strtotime($body['OrderDateEnd'])){
                        $filtered = false;
                    }
                    if($filtered){
                        $orders[] = $data;
                    }
                }


                $compMonthlyReport = LogiwaOrderMonthlyReport::where('companies_id',$depositorData->companies_id)
                                                            ->where('year',$this->currentYear)
                                                            ->where('month',$this->currentMonth)
                                                            ->where('depositor_id',$depositorData->logiwa_depositor_id)
                                                            ->where('depositor_code',$depositorData->logiwa_depositor_code)
                                                            ->first();
                if(!$compMonthlyReport){
                    $compMonthlyReport = new LogiwaOrderMonthlyReport;
                    $compMonthlyReport->companies_id = $depositorData->companies_id;
                    $compMonthlyReport->depositor_id = $depositorData->logiwa_depositor_id;
                    $compMonthlyReport->depositor_code = $depositorData->logiwa_depositor_code;
                    $compMonthlyReport->year = $this->currentYear;
                    $compMonthlyReport->month = $this->currentMonth;
                }

                $compMonthlyReport->data = json_encode($orders);
                $compMonthlyReport->save();
            }
        }
    }


}
