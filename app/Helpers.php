<?php

/* get tracking status helper. used in console comman and in API */
function getTrackingStatus($tracking_number) {
  //determine the type of tracking number
  if (preg_match("/^([0-9]{20,40})$/", $tracking_number)) {
    /*USPS TRACKING INFORMATION*/
    $uspsRequest = new \App\Libraries\Carriers\USPS;
    $tracking_status = $uspsRequest->getShipmentsTrackingStatus($tracking_number);
    unset($uspsRequest);
  } elseif (preg_match("/^([0-9]{12,15})$/", $tracking_number)) {
    $fedExRequest = new \App\Libraries\Carriers\FedEx;
    $tracking_status = $fedExRequest->getShipmentsTrackingStatus($tracking_number);
    unset($fedExRequest);
  } elseif (strlen($tracking_number) == 18) {
    $ups = new \App\Libraries\Carriers\Ups;
    $tracking_status = $ups->getShipmentsTrackingStatus($tracking_number);
    unset($ups);
  } else {
    return false;
  }

  return $tracking_status;
}
