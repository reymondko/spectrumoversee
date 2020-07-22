<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Libraries\Carriers\USPS;
use App\Libraries\Carriers\FedEx;
use App\Libraries\Carriers\Ups;
use App\Models\Inventory;
use App\Models\Locations;
use App\Models\InventoryItemScans;
use DB;


class GetTrackingStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tracking:getStatuses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get Tracking Statuses of provided tracking numbers';

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
        //$tracking_number = '9400109205578515422188';
        //$tracking_number = '9205590193341905568198';
        //$tracking_number = '788395057656';
        //$tracking_number = '789692919107';
        //$tracking_number = '789642464682';
        //$tracking_number = '9400109205578012282940';
        //$tracking_number = '9205590193341905575738';
        //$tracking_number = '1ZA7R4410338535251';
        //$tracking_number = '1ZA7R4410336139120';
        //$tracking_number = '1ZA7R4410320231059';
        $tracking_number = '788867156060';

        //get customer locations
        $customer_locations = [];
        $locations = Locations::get();
        foreach ($locations as $location) {
          if (is_numeric($location->location_type)) {
            $customer_locations[$location->tpl_customer_id][$location->location_type] = $location->id;
          }
        }

        //update return tracking for orders that are empty and have been done via kit return sync
        $result = DB::select("select i.id, i.sku, i.barcode_id, bi.master_kit_id, bi.return_tracking from inventory i inner join batches_items bi on i.barcode_id=bi.master_kit_id inner join batches b on bi.batch_id=b.id inner join skus s on b.sku=s.id inner join companies c on s.companies_id=c.id and i.tpl_customer_id=c.fulfillment_ids where (i.return_tracking_number is null or i.return_tracking_number='N/A')");
        foreach ($result as $row) {
          Inventory::where('id', $row->id)
          ->update(['return_tracking_number' => $row->return_tracking]);
          print "updated $row->id\n";
        }


        //loop through inventory and update tracking information where necessary
        $inventories = Inventory::whereNotNull('return_tracking_number')->get();
        foreach ($inventories as $inventory) {
          try {

            if (empty($inventory->tracking_number)) continue; //don't do anything with this if the tracking number is null

            //clean up the tracking number if there are multiples
            if (preg_match("/\,/", $inventory->tracking_number)) {
              $split = explode(',', $inventory->tracking_number);
              $inventory->tracking_number = $split[0];
            }

            print "$inventory->return_tracking_number\n";
            print "$inventory->tracking_number\n";

            //check to see if outgoing already delivered
            $outgoing_delivered = false;
            if ($inventory->last_scan_location == 'Customer' || $inventory->last_scan_location == 'Lab') { //outgoing already delivered if last scanned location is customer or lab
              $outgoing_delivered = true;
            }

            //check outgoing tracking
            if (strlen($inventory->tracking_number) && $outgoing_delivered == false) {
              //get tracking status and only continue if it is delivered or in transit
              print "Tracking number: $inventory->tracking_number\n";
              $tracking_status = getTrackingStatus($inventory->tracking_number);
              print_r($tracking_status);
              if ($tracking_status['tracking_status'] == 'Delivered') {
                //*** DELIVERED
                //see if the outgoing scan exists
                $scan_result = InventoryItemScans::where('inventory_item_id', $inventory->id)
                              ->where('scan_type', 'outgoing')
                              ->where('location_id', $customer_locations[$inventory->tpl_customer_id]['1'])
                              ->first();
                if (!$scan_result) {
                  InventoryItemScans::create([
                    'inventory_item_id' => $inventory->id,
                    'location_id' => $customer_locations[$inventory->tpl_customer_id]['1'],
                    'scan_type' => 'outgoing',
                    'scanned_location' => 'Spectrum',
                    'companies_id' => $inventory->companies_id,
                    'barcode' => $inventory->tracking_number,
                    'scanned_by_user_id' => 999,
                    'created_at' => $tracking_status['ship_date'],
                    'updated_at' => $tracking_status['ship_date']
                  ]);
                }

                //create delivered scan if it doesn't exist
                $scan_result = InventoryItemScans::where('inventory_item_id', $inventory->id)
                              ->where('scan_type', 'incoming')
                              ->where('location_id', $customer_locations[$inventory->tpl_customer_id]['2'])
                              ->first();
                if (!$scan_result) {
                  InventoryItemScans::create([
                    'inventory_item_id' => $inventory->id,
                    'location_id' => $customer_locations[$inventory->tpl_customer_id]['2'],
                    'scan_type' => 'incoming',
                    'scanned_location' => 'Customer',
                    'companies_id' => $inventory->companies_id,
                    'barcode' => $inventory->tracking_number,
                    'scanned_by_user_id' => 999,
                    'created_at' => $tracking_status['status_date'],
                    'updated_at' => $tracking_status['status_date']
                  ]);

                  $inventory->last_scan_location = 'Customer';
                  $inventory->last_scan_type = 'incoming';
                  $inventory->last_scan_location_id = $customer_locations[$inventory->tpl_customer_id]['2'];
                  $inventory->last_scan_by = 'Carrier';
                  $inventory->last_scan_date = $tracking_status['status_date'];
                  $inventory->save();
                }
              } elseif ($tracking_status['tracking_status'] == 'In Transit') {
                //*** IN TRANSIT
                //see if the outgoing scan exists
                $scan_result = InventoryItemScans::where('inventory_item_id', $inventory->id)
                              ->where('scan_type', 'outgoing')
                              ->where('location_id', $customer_locations[$inventory->tpl_customer_id]['1'])
                              ->first();
                //if (!$scan_result) {
                  /*InventoryItemScans::create([
                    'inventory_item_id' => $inventory->id,
                    'location_id' => $customer_locations[$inventory->tpl_customer_id]['1'],
                    'scan_type' => 'outgoing',
                    'scanned_location' => 'Spectrum',
                    'companies_id' => $inventory->companies_id,
                    'barcode' => $inventory->tracking_number,
                    'scanned_by_user_id' => 999,
                    'created_at' => $tracking_status['ship_date'],
                    'updated_at' => $tracking_status['ship_date']
                  ]);*/

                  $inventory->last_scan_location = 'Spectrum';
                  $inventory->last_scan_type = 'outgoing';
                  $inventory->last_scan_location_id = $customer_locations[$inventory->tpl_customer_id]['2'];
                  $inventory->last_scan_by = 'Carrier';
                  $inventory->last_scan_date = $tracking_status['ship_date'];
                  $inventory->save();
                //}
              }
            }

            //check to see if return tracking already delivered
            $return_delivered = false;
            if ($inventory->last_scan_location == 'Lab' && $inventory->last_scan_type == 'incoming') {
              $lab_delivered = true;
            }

            //check return tracking
            if (strlen($inventory->return_tracking_number) && $return_delivered == false && $inventory->last_scan_location != 'Spectrum') {
              //get tracking status and only continue if it is delivered or in transit
              $tracking_status = getTrackingStatus($inventory->return_tracking_number);
              print_r($tracking_status);
              if ($tracking_status['tracking_status'] == 'Delivered') {
                //*** DELIVERED
                //see if the outgoing scan exists
                $scan_result = InventoryItemScans::where('inventory_item_id', $inventory->id)
                              ->where('scan_type', 'outgoing')
                              ->where('location_id', $customer_locations[$inventory->tpl_customer_id]['2'])
                              ->first();
                if (!$scan_result) {

                  InventoryItemScans::create([
                    'inventory_item_id' => $inventory->id,
                    'location_id' => $customer_locations[$inventory->tpl_customer_id]['2'],
                    'scan_type' => 'outgoing',
                    'scanned_location' => 'Customer',
                    'companies_id' => $inventory->companies_id,
                    'barcode' => $inventory->return_tracking_number,
                    'scanned_by_user_id' => 999,
                    'created_at' => $tracking_status['ship_date'],
                    'updated_at' => $tracking_status['ship_date']
                  ]);
                }

                //create delivered scan if it doesn't exist
                $scan_result = InventoryItemScans::where('inventory_item_id', $inventory->id)
                              ->where('scan_type', 'incoming')
                              ->where('location_id', $customer_locations[$inventory->tpl_customer_id]['3'])
                              ->first();
                if (!$scan_result) {
                  InventoryItemScans::create([
                    'inventory_item_id' => $inventory->id,
                    'location_id' => $customer_locations[$inventory->tpl_customer_id]['3'],
                    'scan_type' => 'incoming',
                    'scanned_location' => 'Lab',
                    'companies_id' => $inventory->companies_id,
                    'barcode' => $inventory->return_tracking_number,
                    'scanned_by_user_id' => 999,
                    'created_at' => $tracking_status['status_date'],
                    'updated_at' => $tracking_status['status_date']
                  ]);

                  $inventory->last_scan_location = 'Lab';
                  $inventory->last_scan_type = 'incoming';
                  $inventory->last_scan_location_id = $customer_locations[$inventory->tpl_customer_id]['3'];
                  $inventory->last_scan_by = 'Carrier';
                  $inventory->last_scan_date = $tracking_status['status_date'];
                  $inventory->save();
                }
              } elseif ($tracking_status['tracking_status'] == 'In Transit') {
                //*** IN TRANSIT
                //see if the outgoing scan exists
                $scan_result = InventoryItemScans::where('inventory_item_id', $inventory->id)
                              ->where('scan_type', 'outgoing')
                              ->where('location_id', $customer_locations[$inventory->tpl_customer_id]['2'])
                              ->first();
                if (!$scan_result) {
                  InventoryItemScans::create([
                    'inventory_item_id' => $inventory->id,
                    'location_id' => $customer_locations[$inventory->tpl_customer_id]['2'],
                    'scan_type' => 'outgoing',
                    'scanned_location' => 'Customer',
                    'companies_id' => $inventory->companies_id,
                    'barcode' => $inventory->return_tracking_number,
                    'scanned_by_user_id' => 999,
                    'created_at' => $tracking_status['ship_date'],
                    'updated_at' => $tracking_status['ship_date']
                  ]);

                  $inventory->last_scan_location = 'Customer';
                  $inventory->last_scan_type = 'outgoing';
                  $inventory->last_scan_location_id = $customer_locations[$inventory->tpl_customer_id]['2'];
                  $inventory->last_scan_by = 'Carrier';
                  $inventory->last_scan_date = $tracking_status['ship_date'];
                  $inventory->save();
                }
              }
            }
          } catch (\Exception $e) {
            \Log::info($e);
            print "ERROR: ".$e->getMessage()."\n";
          }
        }

    }

}
