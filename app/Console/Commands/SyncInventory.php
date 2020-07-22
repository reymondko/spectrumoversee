<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use App\Libraries\SecureWMS\SecureWMS;
use Illuminate\Support\Facades\Gate;
use App\Models\Companies;
use App\Models\Inventory;
use App\Models\Locations;
use App\Models\InventoryItemScans;
use DB;

class SyncInventory extends Command
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
    protected $signature = 'SyncInventory';

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

        $test = $this->SyncInventory();
        if($test){
            // var_dump(count($companies));
        }
    }

    private function SyncInventory(){
        $companies = [16,17,20,32,37,35,36,21,19,38,34,39];


        foreach ($companies as $company_id) {
          //$company_id = $this->argument('company_id');
          // WMS class
          $wms = new SecureWMS();

          $response = $wms->sendRequest("/inventory", ['pgsiz' => 100, 'rql' => 'customerIdentifier.id=='.$company_id]);
          if (!isset($response->{'_embedded'}))
            continue;

          $inventoryz = $response->{'_embedded'};
          $totalResults = $response->totalResults;
          echo "totalresults: ".$totalResults;

          if($totalResults > 0){

              //get company id
              $spectrum_company_id = Companies::whereRaw("fulfillment_ids like '%$company_id%'")->whereRaw('length(fulfillment_ids) < 20')->pluck('id')->first();
              if (!is_numeric($spectrum_company_id))
                continue;

              //get company spectrum location id
              $spectrum_location_id = Locations::where('tpl_customer_id', $company_id)->where('location_type', 1)->pluck('id')->first();

              $pageSize = 1000;
              $totalPages = ceil($totalResults/$pageSize);
              $initial = 1;
              $inventories = array();
              while($initial <= $totalPages){
                  $response = $wms->sendRequest("/inventory", ['pgsiz' => $pageSize,
                  'pgnum' => $initial, 'rql' => 'customerIdentifier.id=='.$company_id]);
                  $inventoryz = $response->{'_embedded'};
                  #print_r($inventoryz);
                  foreach ($inventoryz->item as $inv) {


                      $company_id = $inv->customerIdentifier->id;
                      $sku = $inv->itemIdentifier->sku;
                      $barcode_id= (isset($inv->serialNumber)) ? $inv->serialNumber : null;
                      $return_tracking_number = (isset($inv->lotNumber)) ? $inv->lotNumber : null;
                      $INV= Inventory::where('sku',$sku)
                                                  ->where('barcode_id',$barcode_id)->first();
                      #echo "  sku : ". $sku . " barcode_id: " .$barcode_id. " return_tracking_number: " .$return_tracking_number." <br>";
                      if(!$INV){
                          $INV = new Inventory;
                          $INV->companies_id = $spectrum_company_id;
                          $INV->tpl_customer_id = $company_id;
                          $INV->sku = $sku;
                          $INV->barcode_id = $barcode_id;
                          $INV->last_scan_type = 'incoming';
                          $INV->last_scan_date = date('Y-m-d', strtotime($inv->receivedDate));
                          $INV->last_scan_location_id = $spectrum_location_id;
                          $INV->last_scan_location = 'Spectrum';
                          $INV->last_scan_by = 'Carrier';
                          $INV->return_tracking_number = (strlen($return_tracking_number)) ? $return_tracking_number : null;
                          $INV->save();

                          //create inventory item scan record
                          InventoryItemScans::create([
                            'inventory_item_id' => $INV->id,
                            'location_id' => $spectrum_location_id,
                            'scan_type' => 'incoming',
                            'scanned_location' => 'Spectrum',
                            'companies_id' => $spectrum_company_id,
                            'barcode' => $barcode_id,
                            'scanned_by_user_id' => 999,
                            'created_at' => date('Y-m-d', strtotime($inv->receivedDate)),
                            'updated_at' => date('Y-m-d', strtotime($inv->receivedDate))
                          ]);

                      }
                      else{
                          Inventory::where('sku',$sku)
                              ->where('barcode_id',$barcode_id)
                              ->update(['return_tracking_number' => $return_tracking_number]);
                      }

                  }
                  $initial++;
              }
              echo "Sync Success";
          }
        }
    }
}
