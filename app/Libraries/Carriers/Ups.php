<?php

/**
 *
 * UPS API Library
 * @documentation
 *  -https://www.ups.com/upsdeveloperkit?loc=en_US#
 *
 */

 namespace App\Libraries\Carriers;
 use Guzzle\Http\Exception\ClientException;
 use GuzzleHttp\Client;
 use Illuminate\Support\Facades\Log;
 use Ups\Tracking;

 class UPS
 {

    public function __construct()
    {

    }

    /**
    *
    * Track shipment from provided tracking numbers
    * @param array $trackingNumber | array of tracking numbers of the package
    * @return array $trackingStatuses | array of data associated with the provided tracking numbers
    */
    public function getShipmentsTrackingStatus($trackingNumber)
    {

      $tracking = new Tracking('9D679E700E673912', 'money2k', 'F@k3riM0j0');
      $results = [];
      //foreach($trackingNumbers as $trackingNumber){
        $activities = [];
        try {
          $shipment = $tracking->track($trackingNumber);

          if (is_array($shipment->Package->Activity)) {
            //check if package delivered
            if (preg_match("/delivered/i", $shipment->Package->Activity[0]->Status->StatusType->Description)) {
                return ['status' => 'success',
                        'tracking_status' => 'Delivered',
                        'status_date' => date('Y-m-d', strtotime($shipment->Package->Activity[0]->Date)),
                        'ship_date' => date('Y-m-d', strtotime($shipment->PickupDate))];
            } else {
              return ['status' => 'success',
                      'tracking_status' => 'In Transit',
                      'status_date' => date('Y-m-d', strtotime($shipment->Package->Activity[0]->Date)),
                      'ship_date' => date('Y-m-d', strtotime($shipment->PickupDate))];
            }

          } else {
            return ['status' => 'success', 'tracking_status' => 'Not Shipped', 'status_date' => date('Y-m-d')];
          }
        } catch (\Exception $e) {
          return ['status'=>'error','message'=>'Error: '.$e->getMessage()];
        }

        $results[] = $activities;
      //}
      return $results;
    }



 }

?>
