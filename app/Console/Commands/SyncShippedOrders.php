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

class SyncShippedOrders extends Command
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
    //protected $signature = 'SyncShippedOrders {company_id : company ID} {processDate : format yyyy-mm-dd example 2019-09-01}';
    protected $signature = 'SyncShippedOrders';

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

        $test = $this->SyncShippedOrders();
        if($test){
            // var_dump(count($companies));
        }
    }

    private function SyncShippedOrders() {
        $companies = [16,17,20,32,37,35,36,21,19,38,34,39];


        foreach ($companies as $company_id) {
          //$company_id = $this->argument('company_id');
          $processDate = date('Y-m-d', time()-(86400*14));
          // WMS class
          $wms = new SecureWMS();

          //get company id
          $spectrum_company_id = Companies::whereRaw("fulfillment_ids like '%$company_id%'")->whereRaw('length(fulfillment_ids) < 20')->pluck('id')->first();
          if (!is_numeric($spectrum_company_id))
            continue;

          //get company spectrum location id
          $spectrum_location_id = Locations::where('tpl_customer_id', $company_id)->where('location_type', 1)->pluck('id')->first();

          $response = $wms->sendRequest("/orders", ['detail'=>'All','pgsiz' => 100, 'rql' => 'readOnly.customerIdentifier.id=='.$company_id.';readonly.processDate=ge='.$processDate.'T00:00:00']);

          if (!isset($response->{'_embedded'}))
            continue;

          $orderz = $response->{'_embedded'}->{'http://api.3plCentral.com/rels/orders/order'};

          foreach ($orderz as $order) {
              $returnOrder = $order;
              if (!isset($order->{'_embedded'}))
                continue;
              $lineItems = $order->{'_embedded'}->{'http://api.3plCentral.com/rels/orders/item'};
              $foundItems = false;

              foreach($lineItems as $item) {
                  if ($item->qty <= 0)
                      continue;

                  $responseItems = $wms->sendRequest('/orders/'.$order->readOnly->orderId.'/items/'.$item->readOnly->orderItemId, ['detail'=>'AllocationsWithDetail']);

                  foreach($responseItems->readOnly->allocations as $allocation){

                      if (!isset($allocation->detail->itemTraits->serialNumber))
                        continue;

                      $sku = $allocation->detail->itemTraits->itemIdentifier->sku;
                      $barcode_id = $allocation->detail->itemTraits->serialNumber;
                      $transId = $order->readOnly->orderId;
                      $refNum = $order->referenceNum;
                      $trackingNo = (isset($order->routingInfo->trackingNumber) ? $order->routingInfo->trackingNumber : 'N/A');
                      $return_tracking_number = (isset($allocation->detail->itemTraits->lotNumber)) ? $allocation->detail->itemTraits->lotNumber : 'N/A';
                      echo "===========================";
                      echo "sku = ".$sku;
                      echo "barcode_id = ".$barcode_id;
                      echo "transId = ".$transId;
                      echo "refNum = ".$refNum;
                      echo "trackingNo = ".$trackingNo;
                      echo "===========================";
                      $INV= Inventory::where('sku',$sku)
                                                  ->where('barcode_id',$barcode_id)->first();

                      if(!$INV){

                          $INV = new Inventory;
                          $INV->companies_id = $spectrum_company_id;
                          $INV->tpl_customer_id = $company_id;
                          $INV->sku = $sku;
                          $INV->barcode_id = $barcode_id;
                          $INV->tracking_number = (strlen($trackingNo)) ? $trackingNo : NULL;
                          $INV->return_tracking_number = (strlen($return_tracking_number)) ? $return_tracking_number : NULL;
                          $INV->reference_number = $refNum;
                          $INV->transaction_id = $transId;
                          $INV->last_scan_type = 'incoming';
                          $INV->last_scan_date = date('Y-m-d', strtotime($order->readOnly->creationDate));
                          $INV->last_scan_location_id = $spectrum_location_id;
                          $INV->last_scan_location = 'Spectrum';
                          $INV->last_scan_by = 'Carrier';
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
                            'created_at' => date('Y-m-d', strtotime($order->readOnly->creationDate)),
                            'updated_at' => date('Y-m-d', strtotime($order->readOnly->creationDate))
                          ]);
                      }
                      else{
                          Inventory::where('sku',$sku)
                              ->where('barcode_id',$barcode_id)
                              ->update(['return_tracking_number' => $return_tracking_number,
                                  'reference_number' => $refNum,
                                  'transaction_id' => $transId,
                                  'tracking_number' => $trackingNo]);
                      }

                  }

              }
          }
        }
    }
}
