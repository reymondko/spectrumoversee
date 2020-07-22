<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use App\Libraries\Logiwa\LogiwaAPI;
use Illuminate\Support\Facades\Gate;
use App\Models\Companies;
use DB;

class BorboletaPanoplySync extends Command
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
    protected $signature = 'BorboletaPanoplySync {instance}';

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
      $instance = $this->argument('instance');

      if ($instance == 'inventory') {
        $this->syncInventory();
      }
      if ($instance == 'orders') {
        $this->syncOrders();
      }
      if ($instance == 'inventory-history') {
        $this->syncInventoryHistory();
      }
    }

    private function syncInventoryHistory() {
      $apikey = env('PANOPLY_KEY');
      $apisecret = env('PANOPLY_SECRET');
      $decoded = explode( "/", base64_decode( $apisecret ) );
      $rand = $decoded[ 0 ];
      $awsaccount = $decoded[ 2 ];
      $region = $decoded[ 3 ];
      $account = explode( "/", $apikey )[ 0 ];
      $url = sprintf( "https://sqs.%s.amazonaws.com/%s/sdk-%s-%s", $region, $awsaccount, $account, $rand);


      $body = [];
      $body['DepositorID'] = '9228';
      $body['EntryDateTime_Start'] = date('m.d.Y H:i:00', time()-(86400*2));
      $body['EntryDateTime_End'] = date('m.d.Y H:i:00', time());
      $body['TransactionTypeID'] = 9;
      $logiwa = new LogiwaAPI;
      $request = $logiwa->getTransactionHistoryReportSearch($body);
      if($request['success'] == true){
          if (isset($request['data']->Data) && count($request['data']->Data)) {
            foreach ($request['data']->Data as $inv) {

              //see if already synced
              $result = DB::table('borboleta_orders_synced')->where('order_id', 'INV-'.$inv->ID)->first();
              if (is_object($result))
                continue;

              print "$inv->ID\n";

              $data = [
                "user" => $inv->User,
                "sku" => $inv->InventoryItemCode,
                "sku_description" => $inv->InventoryItemDescription,
                "subtype_description" => $inv->TransactionSubTypeDescription,
                "location" => $inv->LocationDescription,
                "Quantity" => $inv->Quantity,
                "WarehouseComment" => ''
              ];

              $data[ "__table" ] = 'inventory_details_sync';
              $body = json_encode( $data );
              $body = urlencode( $body )."\n";

              $body = array(
                  "Action=SendMessage",
                  "MessageBody=" . $body,
                  "MessageAttribute.1.Name=key",
                  "MessageAttribute.1.Value.DataType=String",
                  "MessageAttribute.1.Value.StringValue=" . $apikey,
                  "MessageAttribute.2.Name=secret",
                  "MessageAttribute.2.Value.DataType=String",
                  "MessageAttribute.2.Value.StringValue=" . $apisecret,
                  "MessageAttribute.3.Name=sdk",
                  "MessageAttribute.3.Value.DataType=String",
                  "MessageAttribute.3.Value.StringValue=panoply-python-sdk-1.0.0",
              );

              $body = join( "&", $body );

              $ch = curl_init( $url );
              curl_setopt( $ch, CURLOPT_POST, true );
              curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
              curl_setopt( $ch, CURLOPT_POSTFIELDS, $body );
              curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
                  "Content-Length" => strlen( $body ),
                  "Content-Type" => "application/x-www-form-urlencoded"
              ));
              $response = curl_exec( $ch );
              curl_close( $ch );

              DB::table('borboleta_orders_synced')->insert([
                'order_id' => 'INV-'.$inv->ID
              ]);
            }
          }
      }
    }

    private function syncOrders() {
      // Prepare order request API
      $body['WarehouseID'] = env('LOGIWA_WAREHOUSE_ID');
      //$body['IsGetOrderDetails'] = true;
      $body['DepositorID'] = '9228';
      $body['DepositorCode'] = 'Borboleta Beauty';
      $body['ActualShipDate_Start'] = date('m.d.Y H:i:00', time()-(3600*10));
      $body['ActualShipDate_End'] = date('m.d.Y H:i:00', time());
      $logiwa = new LogiwaAPI;
      $request = $logiwa->getWarehouseOrderSearch($body);

      $apikey = env('PANOPLY_KEY');
      $apisecret = env('PANOPLY_SECRET');
      $decoded = explode( "/", base64_decode( $apisecret ) );
      $rand = $decoded[ 0 ];
      $awsaccount = $decoded[ 2 ];
      $region = $decoded[ 3 ];
      $account = explode( "/", $apikey )[ 0 ];
      $url = sprintf( "https://sqs.%s.amazonaws.com/%s/sdk-%s-%s", $region, $awsaccount, $account, $rand);

      foreach ($request['data']->Data as $order) {
        $data = [
          'id' => $order->ID,
          'reference_number' => $order->CustomerOrderNo,
          'order_date' => $order->OrderDate,
          'shipping_method' => $order->ShipmentMethodDescription,
          'ship_date' => $order->ActualShipDate,
          'channel_order_code' => $order->ChannelOrderCode,
          'order_status' => $order->WarehouseOrderStatusCode,
          'tracking_number' => $order->CarrierTrackingNumber,
          'shipping_cost' => $order->TotalMarkupRate,
        ];

        //check if order already synced
        $result = DB::table('borboleta_orders_synced')->where('order_id', $order->ID)->first();
        if (is_object($result))
          continue;

        print "$order->ID\n";

        // Get Order Address Details
        $body = [];
        $body['ID'] = $order->CustomerAddressID;
        $address_request = $logiwa->getAddressDataByID($body);
        if($address_request['success'] == true){
            $data['shipping_address'] = $address_request['data']->AdressText;
            $data['shipping_city'] = $address_request['data']->CityDescription;
            $data['shipping_state'] = $address_request['data']->StateDescription;
            $data['shipping_name'] = $address_request['data']->Description;
            $data['shipping_zip'] = $address_request['data']->PostalCodeDescription;
        }

        $data[ "__table" ] = 'shipped_orders';
        $body = json_encode( $data );
        $body = urlencode( $body )."\n";

        $body = array(
            "Action=SendMessage",
            "MessageBody=" . $body,
            "MessageAttribute.1.Name=key",
            "MessageAttribute.1.Value.DataType=String",
            "MessageAttribute.1.Value.StringValue=" . $apikey,
            "MessageAttribute.2.Name=secret",
            "MessageAttribute.2.Value.DataType=String",
            "MessageAttribute.2.Value.StringValue=" . $apisecret,
            "MessageAttribute.3.Name=sdk",
            "MessageAttribute.3.Value.DataType=String",
            "MessageAttribute.3.Value.StringValue=panoply-python-sdk-1.0.0",
        );

        $body = join( "&", $body );

        $ch = curl_init( $url );
        curl_setopt( $ch, CURLOPT_POST, true );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $body );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
            "Content-Length" => strlen( $body ),
            "Content-Type" => "application/x-www-form-urlencoded"
        ));
        $response = curl_exec( $ch );
        curl_close( $ch );

        DB::table('borboleta_orders_synced')->insert([
          'order_id' => $order->ID
        ]);
      }
    }

    private function syncInventory() {
      $body = [];
      $body['DepositorID'] = '9228';
      $body['DepositorCode'] = 'Borboleta Beauty';
      $body['WarehouseID'] = env('LOGIWA_WAREHOUSE_ID');
      $logiwa = new LogiwaAPI;
      $request = $logiwa->getConsolidatedInventoryReport($body);
      if($request['success'] == true){
          if(isset($request['data']->Data)){
              foreach($request['data']->Data as $inventory){
                  $inventory_summary[] = $inventory;
              }
          }
      }

      $apikey = env('PANOPLY_KEY');
      $apisecret = env('PANOPLY_SECRET');
      $decoded = explode( "/", base64_decode( $apisecret ) );
      $rand = $decoded[ 0 ];
      $awsaccount = $decoded[ 2 ];
      $region = $decoded[ 3 ];
      $account = explode( "/", $apikey )[ 0 ];
      $url = sprintf( "https://sqs.%s.amazonaws.com/%s/sdk-%s-%s", $region, $awsaccount, $account, $rand);

      foreach ($inventory_summary as $inv) {
        print "Sync: $inv->InventoryItemDescription\n";
        $data = [
          'sku' => $inv->InventoryItemDescription,
          'description' => $inv->Description,
          'current_stock' => $inv->StockQty,
          'damaged' => $inv->Damaged,
          'available_stock' => $inv->Undamaged,
          'total_stock' => $inv->PackQuantity,
        ];

        $data[ "__table" ] = 'inventory_sync';
        $body = json_encode( $data );
        $body = urlencode( $body )."\n";

        $body = array(
            "Action=SendMessage",
            "MessageBody=" . $body,
            "MessageAttribute.1.Name=key",
            "MessageAttribute.1.Value.DataType=String",
            "MessageAttribute.1.Value.StringValue=" . $apikey,
            "MessageAttribute.2.Name=secret",
            "MessageAttribute.2.Value.DataType=String",
            "MessageAttribute.2.Value.StringValue=" . $apisecret,
            "MessageAttribute.3.Name=sdk",
            "MessageAttribute.3.Value.DataType=String",
            "MessageAttribute.3.Value.StringValue=panoply-python-sdk-1.0.0",
        );

        $body = join( "&", $body );

        $ch = curl_init( $url );
        curl_setopt( $ch, CURLOPT_POST, true );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $body );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
            "Content-Length" => strlen( $body ),
            "Content-Type" => "application/x-www-form-urlencoded"
        ));
        $response = curl_exec( $ch );
        curl_close( $ch );
      }
    }
}
