<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use App\Libraries\SecureWMS\SecureWMS;
use Illuminate\Support\Facades\Gate;
use App\Models\ShipPackSubmissions;
use DB;

class CheckConfirmedOrders extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:checkconfirmedorders';

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
      $wms = new SecureWMS();

      $orders = ShipPackSubmissions::select('id', 'tpl_order_id', 'carrier', 'tracking_number', 'shipping_cost', 'weight')->where('created_at', '>', date('Y-m-d H:i:s', time()-86400))->get();
      foreach ($orders as $order) {
        print "$order->tpl_order_id\n";
        $response = $wms->sendRequest("/orders/$order->tpl_order_id", ['detail'=>'All']);
        if (isset($response) && isset($response->readOnly)) {
          if (strlen($response->routingInfo->trackingNumber) <= 4) {
            print "Missing tracking info: $order->tpl_order_id\n";
          }
        }
      }
    }
}
