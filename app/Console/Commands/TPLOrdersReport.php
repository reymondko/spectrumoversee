<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

use App\Models\Companies;
use App\Models\TPLOrderMonthlyReport;


class TPLOrdersReport extends Command
{

    private $currentMonth = null;
    private $currentYear  = null;
    private $startDate = null; //get current month start date
    private $endDate = null; //get current date

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tpl:ordersReport';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $this->startDate = $this->startDate->format('Y-m-d');
        $this->endDate = Carbon::now()->format('Y-m-d'); 

        
        $companies = $this->getTPLIdsByCompany();
        if($companies){
            // var_dump(count($companies));
        }
    }

    /**
     * Get all companies with 3pl/fulfillment ids
     * 
     * @return mixed
     */

     private function getTPLIdsByCompany(){
         $companies = Companies::whereNotNull('fulfillment_ids')->get();
         if($companies){
             foreach($companies as $company){
                 $this->getMonthlyData($company->id,$company->fulfillment_ids);
             }
         }else{
             return false;
         }
     }

     /**
      * Get all orders for the current month of the specific company
      * 
      * @param $companyId|int,$fulfillmentIds|string
      * @return bool
      */

      private function getMonthlyData($companyId,$fulfillmentIds){



        $startDateQ = 'readonly.CreationDate=gt='.$this->startDate.'T00:00:00;';
        $endDateQ = 'readonly.CreationDate=lt='.$this->endDate.'T23:59:59;';

        $orders  = [];
        $client = new Client();
        /* get access token */
        $accessToken = null;
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
        }


        //get order count for pagination
        try {
            $request = $client->request('GET', 'https://secure-wms.com/orders?detail=All&pgsiz=1&pgnum=1&rql='.$startDateQ.$endDateQ.'readonly.customeridentifier.id=in=('.$fulfillmentIds.')&sort=-readOnly.CreationDate', [
                'headers' => [
                  'Authorization' => 'Bearer '.$accessToken,
                  'Accept' => "application/hal+json"
                ],
                'json' => []
              ]);

            $response = json_decode($request->getBody());
            $totalResults = $response->totalResults;


            if($totalResults > 0){

                $pageSize = 1000;
                $totalPages = ceil($totalResults/$pageSize);
                $initial = 1;

                while($initial <= $totalPages){
                    try {
                        $request = $client->request('GET', 'https://secure-wms.com/orders?detail=All&pgsiz='.$pageSize.'&pgnum='.$initial.'&rql='.$startDateQ.$endDateQ.'readonly.customeridentifier.id=in=('.$fulfillmentIds.')&sort=-readOnly.CreationDate', [
                            'headers' => [
                            'Authorization' => 'Bearer '.$accessToken,
                            'Accept' => "application/hal+json"
                            ],
                            'json' => []
                        ]);

                        $response = json_decode($request->getBody());
                        $orderz = $response->{'_embedded'}->{'http://api.3plCentral.com/rels/orders/order'};
                        foreach ($orderz as $order) {
                            $o = $order->readOnly;
                            $orders[] = $o;
                        }
                    
                    } catch (\Exception $e) {
                        //do something
                    }
                    $initial++;
                }

                if(count($orders)  > 0){
                    $compMonthlyReport = TPLOrderMonthlyReport::where('companies_id',$companyId)
                                                              ->where('year',$this->currentYear)
                                                              ->where('month',$this->currentMonth)
                                                              ->first();

                    if(!$compMonthlyReport){
                        $compMonthlyReport = new TPLOrderMonthlyReport;
                        $compMonthlyReport->companies_id = $companyId;
                        $compMonthlyReport->year = $this->currentYear;
                        $compMonthlyReport->month = $this->currentMonth;
                    }

                    $compMonthlyReport->data = json_encode($orders);
                    $compMonthlyReport->save();

                }
            }

        }catch (\Exception $e) {
             //do something
        }

    }
}
