<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use App\Libraries\Logiwa\LogiwaAPI;
use Carbon\Carbon;
use App\Models\Companies;
use App\Models\Inventory;
use App\Models\InventoryItemScans;
use App\Models\Locations;
use App\Models\LogiwaDepositor;

class LogiwaSyncShippedOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Logiwa:SyncShippedOrders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command used to sync inventory from logiwa';

    /**
     * Max size of items returned per request
     *
     * @var integer
     */
    protected $maxPageSize = 100;


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
        $processed_depositors = [];

        // Get list of companies
        $company_list = Companies::where('end_to_end_tracking',1)->get()->pluck('id')->toArray();

        $depositors = LogiwaDepositor::whereIn('companies_id', $company_list)->select('logiwa_depositor_id','logiwa_depositor_code')->distinct()->get();
        foreach ($depositors as $depositorData) {
            //get company spectrum location id
            $spectrum_location_id = Locations::where('tpl_customer_id', $depositorData->logiwa_depositor_id)->where('location_type', 1)->pluck('id')->first();

            //if location doesn't exist, create it
            $result = Locations::where('tpl_customer_id', $depositorData->logiwa_depositor_id)->get();
            if (count($result) <= 0) {
              //create each of the locations for the tpl customer
              Locations::create([
                'name' => 'Spectrum',
                'tpl_customer_id' => $depositorData->logiwa_depositor_id,
                'location_type' => 1 //Spectrum
              ]);
              Locations::create([
                'name' => 'Customer',
                'tpl_customer_id' => $depositorData->logiwa_depositor_id,
                'location_type' => 2 //Customer
              ]);
              Locations::create([
                'name' => 'Lab',
                'tpl_customer_id' => $depositorData->logiwa_depositor_id,
                'location_type' => 3 //Lab
              ]);

              $spectrum_location_id = Locations::where('tpl_customer_id', $depositorData->logiwa_depositor_id)->where('location_type', 1)->pluck('id')->first();
            }

            //get companies id
            $companies_id = LogiwaDepositor::where('logiwa_depositor_id', $depositorData->logiwa_depositor_id)->max('companies_id');

            // Loop through pages
            $total_inserted = 0;
            $total_updated = 0;
            if($depositorData){
                $paginationData = $this->retrievePagingInfo($depositorData->logiwa_depositor_id);

                for($idx = 0;$paginationData['page_count_index'] >= $idx;$idx++){
                    sleep(2);
                    $body = [];
                    $body['DepositorID'] = $depositorData->logiwa_depositor_id;
                    $body['PageSize'] = $this->maxPageSize;
                    $body['SelectedPageIndex'] = $idx;
                    $logiwa = new LogiwaAPI;
                    $logiwaInventory = $logiwa->getShipmentInfoSerialSearch($body);
                    if($logiwaInventory['success'] == true){
                        if(isset($logiwaInventory['data']->Data)){
                            foreach($logiwaInventory['data']->Data as $data){

                                $inventory= Inventory::where('sku',$data->InventoryItemDescription)
                                                     ->where('barcode_id',$data->Serial)
                                                     ->first();

                                $orderSearch = [];
                                $orderSearch['ID'] = $data->WarehouseOrderID;
                                $orderSearch['WarehouseID'] = env('LOGIWA_WAREHOUSE_ID');
                                $order = $logiwa->getWarehouseOrderSearch($orderSearch);

                                if(!$inventory){
                                    $inventory = new Inventory;
                                    $inventory->companies_id = $companies_id;
                                    $inventory->tpl_customer_id = $depositorData->logiwa_depositor_id;
                                    $inventory->sku = $data->InventoryItemDescription;
                                    $inventory->barcode_id = $data->Serial;
                                    $inventory->last_scan_type = 'incoming';
                                    $inventory->last_scan_date = date('Y-m-d', \DateTime::createFromFormat('m.d.Y H:i:s', $data->EntryDateTime_End)->getTimestamp());
                                    $inventory->last_scan_location_id = $spectrum_location_id;
                                    $inventory->last_scan_location = 'Spectrum';
                                    $inventory->last_scan_by = 'Carrier';
                                    $inventory->tracking_number = $data->CarrierTrackingNumber;
                                    $inventory->return_tracking_number = $data->LotBatchNo;
                                    $inventory->transaction_id = $data->WarehouseOrderID;
                                    $inventory->reference_number = (isset($order['data']->Data[0])) ? $order['data']->Data[0]->CustomerOrderNo : null;
                                    $inventory->save();

                                    //create inventory item scan record
                                    InventoryItemScans::create([
                                        'inventory_item_id' => $inventory->id,
                                        'location_id' => $spectrum_location_id,
                                        'scan_type' => 'incoming',
                                        'scanned_location' => 'Spectrum',
                                        'companies_id' => $companies_id,
                                        'barcode' => $data->Serial,
                                        'scanned_by_user_id' => 999,
                                        'created_at' => date('Y-m-d', \DateTime::createFromFormat('m.d.Y H:i:s', $data->EntryDateTime_End)->getTimestamp()),
                                        'updated_at' => date('Y-m-d', \DateTime::createFromFormat('m.d.Y H:i:s', $data->EntryDateTime_End)->getTimestamp()),
                                    ]);

                                    $total_inserted++;

                                }else{
                                    $inventory->return_tracking_number = $data->LotBatchNo;
                                    $inventory->tracking_number = $data->CarrierTrackingNumber;
                                    $inventory->transaction_id = $data->WarehouseOrderID;
                                    $inventory->reference_number = (isset($order['data']->Data[0])) ? $order['data']->Data[0]->CustomerOrderNo : null;
                                    $inventory->save();
                                    $total_updated++;
                                }
                            }
                        }
                    }
                }
            }
            \Log::info('LogiwaInventorySync Company:'. $depositorData->logiwa_depositor_id); // log company
            \Log::info('LogiwaInventorySync Total Inserted:'. $total_inserted); // log total inserts
            \Log::info('LogiwaInventorySync Total Updated:'. $total_updated); // log total updates

            print 'LogiwaInventorySync Company:'. $depositorData->logiwa_depositor_id."\n";
            print 'LogiwaInventorySync Total Inserted:'. $total_inserted."\n";
            print 'LogiwaInventorySync Total Updated:'. $total_updated."\n";
        }
    }

    /**
     * Retrieves company depositor data
     *
     * @return App\Models\LogiwaDepositor
     */
    private function getLogiwaDepositorData($companyId){
        $logiwaDepositors = LogiwaDepositor::where('companies_id',$companyId)->first();
        return $logiwaDepositors;
    }

    /**
     * Retrieve pagination information
     * to limit total number of items per request
     *
     * @param $depositorId
     *  - Id of the depositor
     * @return array
     */
    private function retrievePagingInfo($depositorId){

        $paginationInfo = [
            'record_count' => 0,
            'page_count' => 0,
            'page_count_index' => 0,
        ];

        $body['DepositorID'] = $depositorId;
        $body['PageSize'] = $this->maxPageSize;
        $body['SelectedPageIndex'] = 0;
        $logiwa = new LogiwaAPI;
        $logiwaInventory = $logiwa->getShipmentInfoSerialSearch($body);
        if($logiwaInventory){
            if($logiwaInventory['success'] == true){
                if(isset($logiwaInventory['data']->Data[0])){
                    $paginationInfo['page_count'] = $logiwaInventory['data']->Data[0]->PageCount;
                    $paginationInfo['page_count_index'] = ($paginationInfo['page_count'] - 1);
                    $paginationInfo['record_count'] = $logiwaInventory['data']->Data[0]->RecordCount;
                }
            }
        }



        return $paginationInfo;
    }
}
