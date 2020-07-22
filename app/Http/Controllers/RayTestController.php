<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Auth;
use App\Models\Batches;
use App\Models\BatchesItems;
use App\Libraries\SecureWMS\SecureWMS;
use App\Libraries\Logiwa\LogiwaAPI;
use Hash;

class RayTestController extends Controller
{
    public function test1()
    {
      // WMS class
      $this->wms = new SecureWMS();

      $order = array(
				'customerIdentifier' => array(
					'id' => 25
				),
				'facilityIdentifier' => array(
					'id' => 1
				),
				'referenceNum' => 'TESTORDER-MARK',
				'warehouseTransactionSourceEnum' => 8,
				'transactionEntryTypeEnum' => 4,
				'routingInfo' => array(
					'isCod' => null,
					'isInsurance' => null,
					'carrier' => null,
					'mode' => null,
					'account' => null,
					'shipPointZip' => null,
				),
				'shipTo' => array (
					'retailerId' => null,
					'isQuickLookup' => true,
					'companyName' => 'Mark Sly',
					'name' => 'Mark Sly',
					'address1' => '5795 Ridge Creek Circle',
					'address2' => null,
					'city' => 'Murray',
					'state' => 'UT',
					'zip' => '84107',
					'country' => 'US',
					'phoneNumber' => '8016740506',
					'emailAddress' => 'mark@digiance.com',
					'dept' => null,
				),
				'OrderItems' => array(array (
					'readOnly' => array (
						'fullyAllocated' => true,
					),
					'itemIdentifier' => array (
						'sku' => 'SHOPIFY1',
					),
					'qty' => 1,
				)),
				'billing' => array(
					'billingCharges' => [array(
						'chargeType' => null,
					)]
				)
			);

      $response = $this->wms->sendOrderPostRequest('/orders', $order, 'POST');

      print_r($response);
      exit;

    }

    public function syncOrderToPanoply() {
      // Prepare order request API
      $body['WarehouseID'] = env('LOGIWA_WAREHOUSE_ID');
      $body['IsGetOrderDetails'] = true;
      $body['ID'] = '2201454';
      $logiwa = new LogiwaAPI;
      $request = $logiwa->getWarehouseOrderSearch($body);

      $data = [
        'id' => $request['data']->Data[0]->ID,
        'reference_number' => $request['data']->Data[0]->CustomerOrderNo,
        'order_date' => $request['data']->Data[0]->OrderDate,
        'shipping_method' => $request['data']->Data[0]->ShipmentMethodDescription,
        'ship_date' => $request['data']->Data[0]->ActualShipDate,
        'channel_order_code' => $request['data']->Data[0]->ChannelOrderCode,
        'order_status' => $request['data']->Data[0]->WarehouseOrderStatusCode,
        'tracking_number' => $request['data']->Data[0]->CarrierTrackingNumber,
        'shipping_cost' => $request['data']->Data[0]->TotalMarkupRate,
      ];
      print_r($data);

      $apikey = 'panoply/u013187nw';
      $apisecret = 'ZDN6N2xjOTNzY28vYWNiNGRhNWYtZTQyNC00MDU5LWI0NDEtMjRkNmYxODcyNGY1LzAzNzMzNTk5OTU2Mi91cy1lYXN0LTE=';
      $decoded = explode( "/", base64_decode( $apisecret ) );
      $rand = $decoded[ 0 ];
      $awsaccount = $decoded[ 2 ];
      $region = $decoded[ 3 ];
      $account = explode( "/", $apikey )[ 0 ];
      $url = sprintf( "https://sqs.%s.amazonaws.com/%s/sdk-%s-%s", $region, $awsaccount, $account, $rand);

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
      print $response;
      print "\n\n".$body;
      exit;
    }

    public function syncInventoryToPanoply() {
      //get inventory report from logiwa
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

      $apikey = 'panoply/u013187nw';
      $apisecret = 'ZDN6N2xjOTNzY28vYWNiNGRhNWYtZTQyNC00MDU5LWI0NDEtMjRkNmYxODcyNGY1LzAzNzMzNTk5OTU2Mi91cy1lYXN0LTE=';
      $decoded = explode( "/", base64_decode( $apisecret ) );
      $rand = $decoded[ 0 ];
      $awsaccount = $decoded[ 2 ];
      $region = $decoded[ 3 ];
      $account = explode( "/", $apikey )[ 0 ];
      $url = sprintf( "https://sqs.%s.amazonaws.com/%s/sdk-%s-%s", $region, $awsaccount, $account, $rand);

      foreach ($inventory_summary as $inv) {
        $data = [
          'sku' => $inv->InventoryItemDescription,
          'description' => $inv->Description,
          'current_stock' => $inv->StockQty,
          'damaged' => $inv->Damaged,
          'available_stock' => $inv->Undamaged,
          'total_stock' => $inv->PackQuantity,
        ];
        print_r($data);

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
        print $body;
        print "response";
        print_r($response);
        exit;
      }
      exit;
    }

    public function importSerials() {

      //$this->syncOrderToPanoply();
      $this->syncInventoryToPanoply();

      print Hash::make("borb@20m");
      exit;

      $contents = file_get_contents('/var/www/GI-INT-20200407.csv');
      $lines = explode("\r", $contents);

      if (count($lines)) {
        //create the batch
        $batch_id = 1;

        $i = 0;
        foreach ($lines as $line) {
          $i++;
          if ($i==1) continue; //skip first line
          $cols = str_getcsv($line);


          BatchesItems::create([
            'batch_id' => $batch_id,
            'master_kit_id' => $this->clean($cols[0]),
            'subkit_id' => $this->clean($cols[1]),
            'return_tracking' => $this->clean('N/A'),
            'box_id' => $this->clean("TEST-IMPORT"),
            'created_by_id' => 1
          ]);

          BatchesItems::create([
            'batch_id' => $batch_id,
            'master_kit_id' => $this->clean($cols[0]),
            'subkit_id' => $this->clean($cols[2]),
            'return_tracking' => $this->clean('N/A'),
            'box_id' => $this->clean("TEST-IMPORT"),
            'created_by_id' => 1
          ]);

          /*BatchesItems::create([
            'batch_id' => $batch_id,
            'master_kit_id' => $cols[1],
            'subkit_id' => $cols[7],
            'return_tracking' => $cols[9],
            'box_id' => date('Ymd'),
            'created_by_id' => 1
          ]);

          BatchesItems::create([
            'batch_id' => $batch_id,
            'master_kit_id' => $cols[1],
            'subkit_id' => $cols[8],
            'return_tracking' => $cols[9],
            'box_id' => date('Ymd'),
            'created_by_id' => 1
          ]);*/
        }
      }
      exit;
    }


    public function clean($string) {
      return trim(str_replace("\n", "", str_replace("\r", "", $string)));
    }
}
