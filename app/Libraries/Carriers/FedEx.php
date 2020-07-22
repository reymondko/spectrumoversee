<?php

/**
 *
 * FedEx API Library
 * @documentation
 *
 */

 namespace App\Libraries\Carriers;
 use Guzzle\Http\Exception\ClientException;
 use GuzzleHttp\Client;
 use Illuminate\Support\Facades\Log;

 class FedEx
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
        //PROD INFORMATION
        $tracking = new \FedEx\TrackService\Track( 'gKOOHjEWtTmT2T8s', 'b23SdavXAeaoSskD7Fk07j01v', '621673793', '250393898','TrackService_v16.wsdl',true);
        //TEST INFORMATION
        //$tracking = new \FedEx\TrackService\Track('https://wsbeta.fedex.com:443/web-services', 'hU19hiIi95Y0VGn3', 'YlZVpyMaYXcPAF7dkh5Mb6VHn', '510087100', '113990959');
        $results = [];
        //foreach($trackingNumbers as $trackingNumber){
          try {
            $shipment = $tracking->getByTrackingId($trackingNumber);
            if($shipment->HighestSeverity == 'SUCCESS'){
              $results[] = $shipment->CompletedTrackDetails;

              if (is_object($shipment->CompletedTrackDetails->TrackDetails) && isset($shipment->CompletedTrackDetails->TrackDetails->StatusDetail->Description)) {
                //check if delivered
                if (preg_match("/delivered/i", $shipment->CompletedTrackDetails->TrackDetails->StatusDetail->Description)) {
                  //delivered
                  return ['status' => 'success',
                          'tracking_status' => 'Delivered',
                          'status_date' => date('Y-m-d', strtotime($shipment->CompletedTrackDetails->TrackDetails->StatusDetail->CreationTime)),
                          'ship_date' => date('Y-m-d', strtotime($shipment->CompletedTrackDetails->TrackDetails->DatesOrTimes[count($shipment->CompletedTrackDetails->TrackDetails->DatesOrTimes)-1]->DateOrTimestamp))];
                } else {
                  //in transit
                  if (is_array($shipment->CompletedTrackDetails->TrackDetails->DatesOrTimes)) {
                    return ['status' => 'success',
                            'tracking_status' => 'In Transit',
                            'status_date' => date('Y-m-d', strtotime($shipment->CompletedTrackDetails->TrackDetails->StatusDetail->CreationTime)),
                            'ship_date' => date('Y-m-d', strtotime($shipment->CompletedTrackDetails->TrackDetails->DatesOrTimes[count($shipment->CompletedTrackDetails->TrackDetails->DatesOrTimes)-1]->DateOrTimestamp))];
                  } else {
                    return ['status' => 'success',
                            'tracking_status' => 'In Transit',
                            'status_date' => date('Y-m-d', strtotime($shipment->CompletedTrackDetails->TrackDetails->StatusDetail->CreationTime)),
                            'ship_date' => date('Y-m-d', strtotime($shipment->CompletedTrackDetails->TrackDetails->DatesOrTimes->DateOrTimestamp))];
                  }
                }

              } else {
                //not shipped
                return ['status' => 'success', 'tracking_status' => 'Not Shipped', 'status_date' => date('Y-m-d')];
              }
            }else{
              return ['status'=>'error','message'=>$shipment->Notifications->Message];
            }
          } catch (Exception $e) {
            return ['status'=>'error','message'=>'Error: '.$e->getMessage()];
          }
        //}
        return $results;
    }



 }

?>
