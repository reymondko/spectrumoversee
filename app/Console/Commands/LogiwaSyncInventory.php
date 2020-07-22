<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use Excel;
use App\Models\LogiwaDepositor;
use App\Models\Inventory;
use App\Models\InventoryItemScans;
use App\Models\Locations;

class LogiwaSyncInventory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Logiwa:SyncInventory';

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
      $filesToProcess = [];

      $oClient = \Webklex\IMAP\Facades\Client::account('default');
      $oClient->connect();
      $aFolder = $oClient->getFolders();
      foreach($aFolder as $oFolder){
        if ($oFolder->name == 'INBOX') {
          $aMessage = $oFolder->messages()->all()->get();

          foreach($aMessage as $oMessage){
            if (preg_match("/Serial Follow Up Report/", $oMessage->getSubject())) {
              //save the attachment (xls)
              $aAttachment = $oMessage->getAttachments();
              foreach ($aAttachment as $oAttachment) {
                if (preg_match("/Serial Follow Up Report/", $oAttachment->getName())) {
                  $filesToProcess[] = (string)$oAttachment->getName();
                  print $oAttachment->getName()."\n";
                  $oAttachment->save("/var/www/serial-reports/");
                }
              }

              $oMessage->moveToFolder('Completed');
            }
          }
        }
      }

      //load list of depositors
      $depositors = [];
      $results = LogiwaDepositor::get();
      foreach ($results as $row) {
        $depositors[$row->logiwa_depositor_code] = $row->logiwa_depositor_id;
      }

      foreach ($filesToProcess as $file) {
        print "processing file: $file\n";
        $data = Excel::load('/var/www/serial-reports/'.$file)->get();
        foreach($data->toArray() as $key => $value) {

          if (isset($depositors[$value['client']])) {
            print "looping\n";

            //get company spectrum location id
            $spectrum_location_id = Locations::where('tpl_customer_id', $depositors[$value['client']])->where('location_type', 1)->pluck('id')->first();

            //if location doesn't exist, create it
            $result = Locations::where('tpl_customer_id', $depositors[$value['client']])->get();
            if (count($result) <= 0) {
              //create each of the locations for the tpl customer
              Locations::create([
                'name' => 'Spectrum',
                'tpl_customer_id' => $depositors[$value['client']],
                'location_type' => 1 //Spectrum
              ]);
              Locations::create([
                'name' => 'Customer',
                'tpl_customer_id' => $depositors[$value['client']],
                'location_type' => 2 //Customer
              ]);
              Locations::create([
                'name' => 'Lab',
                'tpl_customer_id' => $depositors[$value['client']],
                'location_type' => 3 //Lab
              ]);

              $spectrum_location_id = Locations::where('tpl_customer_id', $depositors[$value['client']])->where('location_type', 1)->pluck('id')->first();
            }

            //get companies id
            $companies_id = LogiwaDepositor::where('logiwa_depositor_id', $depositors[$value['client']])->max('companies_id');

            //see if inventory entry already exists
            $inventory = Inventory::where([
              'tpl_customer_id' => $depositors[$value['client']],
              'sku' => $value['itemcode'],
              'barcode_id' => $value['serialno']
            ])->first();

            if ($inventory) {
              //update existing inventory with proper return tracking number
              if (strlen($value['lotnumber']) > 3) {
                $inventory->return_tracking_number = $value['lotnumber'];
                $inventory->save();
                print "updated inventory entry\n";
              }
            } else {
              //create new inventory
              $inventory = new Inventory;
              $inventory->companies_id = $companies_id;
              $inventory->tpl_customer_id = $depositors[$value['client']];
              $inventory->sku = $value['itemcode'];
              $inventory->barcode_id = $value['serialno'];
              $inventory->last_scan_type = 'incoming';
              $inventory->last_scan_date = date('Y-m-d');
              $inventory->last_scan_location_id = $spectrum_location_id;
              $inventory->last_scan_location = 'Spectrum';
              $inventory->last_scan_by = 'Carrier';
              $inventory->return_tracking_number = $value['lotnumber'];
              $inventory->save();

              //create inventory item scan record
              InventoryItemScans::create([
                  'inventory_item_id' => $inventory->id,
                  'location_id' => $spectrum_location_id,
                  'scan_type' => 'incoming',
                  'scanned_location' => 'Spectrum',
                  'companies_id' => $companies_id,
                  'barcode' => $value['serialno'],
                  'scanned_by_user_id' => 999,
                  'created_at' => date('Y-m-d'),
                  'updated_at' => date('Y-m-d'),
              ]);

              print "created new inventory entry\n";
            }
          }
        }
      }
    }
}
