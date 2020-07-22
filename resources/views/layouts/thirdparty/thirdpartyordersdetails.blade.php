@extends('adminlte::page')



@section('title', 'Order Details - '.$order->readOnly->orderId)



@section('content_header')
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <h1 class="header-text">Order Details - {{ $order->readOnly->orderId }}</h1>

@stop
@section('content')

  @if(session('status') == 'saved')
    <div class="alert alert-info alert-dismissible alert-saved">
        <button type="button" class="close alert-close-btn" data-dismiss="alert" aria-hidden="true">×</button>

        <h4><i class="icon fa fa-check"></i>Order Successfully Fulfilled!</h4>
    </div>
  @elseif(session('status') == 'error_saving')
    <div class="alert alert-info alert-dismissible alert-error">
        <button type="button" class="close alert-close-btn" data-dismiss="alert" aria-hidden="true">×</button>

        <h4><i class="icon fa fa-warning"></i>Error Fulfilling Order!</h4>
    </div>
  @endif
  <div class="box inventory-box">
     <div class="box-body">
       <table style="width:100%">
         <tr>
           <td style="width:50%;">
             <h4>Ship-To</h4>

              @if(isset($order->shipTo->name))
              {{$order->shipTo->name}}<br />
              @endif

              @if(isset($order->shipTo->companyName))
              {{$order->shipTo->companyName}}<br />
              @endif

             @if(isset($order->shipTo->address1))
              {{$order->shipTo->address1}}<br />
             @endif

             @if(isset($order->shipTo->address2))
              @if (strlen($order->shipTo->address2))
                {{$order->shipTo->address2}}<br />
              @endif
             @endif

             {{$order->shipTo->city ?? null}} @if(isset($order->shipTo->state)){{$order->shipTo->state}}@endif {{$order->shipTo->zip ?? null}}<br />
             {{$order->shipTo->phoneNumber ?? null}}<br />
             {{$order->shipTo->emailAddress ?? null}}<br />
             
             @if(isset($order->shipTo->dept))
              {{$order->shipTo->dept}}<br />
              @endif
           </td>
           <td style="width:50%;">
             @if (!isset($order->routingInfo->trackingNumber) || (isset($order->routingInfo->trackingNumber) && strlen($order->routingInfo->trackingNumber) < 5))
              @if($company->can_manual_fulfill == 1)
               <a class="od-manual-ff-link btn btn-default so-btn" onclick="return confirm('Are you sure you want fulfill this order?')" href="/thirdparty/orders/manual-fulfill/{{$order->readOnly->orderId}}">Fulfill Order</a>
               @endif
             @endif
             <h4>Creation Date: {{ date('m/d/Y H:i:s', strtotime($order->readOnly->creationDate)) }}</h4>
             <h4>Reference Number: {{$order->referenceNum}}</h4>
             @if (isset($order->routingInfo->trackingNumber))
               <h4><b>Outbound Tracking #: {{$order->routingInfo->trackingNumber}}</b></h4>
             @endif

            <h4>Ship Method: {{$order->routingInfo->mode ?? 'N/A'}}</h4>


             @if (!isset($order->routingInfo->trackingNumber) || (isset($order->routingInfo->trackingNumber) && strlen($order->routingInfo->trackingNumber) < 5))
               <a style="color:red" onclick="return confirm('Are you sure you want to cancel this order?')" href="/thirdparty/orders/cancel/{{$order->readOnly->orderId}}">Cancel Order</a>
             @endif
           </td>
         </tr>
       </table>
       <br /><br />
       <h4>Line Items</h4>
       <table class="table table-striped table-bordered" style="width:100%">
         <thead class="table_head">
            <tr>
               <th class="table_head_th">Line Item #</th>
               <th class="table_head_th">SKU</th>
               <th class="table_head_th">Description</th>
               <th class="table_head_th">Serial # (KIT ID)</th>
               <th class="table_head_th">Lot # (Return Tracking)</th>
               <th class="table_head_th">Exp. Date</th>
               <th class="table_head_th">Quantity</th>
            </tr>
         </thead>
         <tbody>
           @if (isset($line_items))
             @foreach($line_items as $item)
                  <tr>
                    <td>{{$item['item_id']}}</td>
                    <td>{{$item['sku']}}</td>
                    <td>{{$item['description']}}</td>
                    <td>{{$item['serial_number']}}</td>
                    <td>{{$item['lot_number']}}</td>
                    <td>{{$item['expiration']}}</td>
                    <td>{{$item['qty']}}</td>
                  </tr>
                  @foreach($item['subkits'] as $subkit)
                    <tr>
                      <td colspan="6" style="background-color:#f2f2f2;color:#a2a2a2;font-size:13px;padding:3px;">
                         &nbsp; &nbsp; &nbsp;
                        <strong>Subkit #:</strong> {{$subkit['subkit_id']}} &nbsp; &nbsp; &nbsp; |  &nbsp; &nbsp; &nbsp;
                        <strong>Return Tracking #:</strong> {{$subkit['return_tracking']}}
                      </td>
                    </tr>
                  @endforeach
             @endforeach
           @endif
        </tbody>
      </table>
      <br /><br />
      <!--
      <h4>Shipments</h4>
      <table class="table table-striped table-bordered" style="width:100%">
        <thead class="table_head">
           <tr>
              <th class="table_head_th">Line Item #</th>
              <th class="table_head_th">Quantity</th>
              <th class="table_head_th">Kit #/Serial #</th>
              <th class="table_head_th">Return Tracking #/Lot #</th>
              <th class="table_head_th">Exp. Date</th>
           </tr>
        </thead>
        <tbody>
          @if (count($order->readOnly->packages))
            @foreach($order->readOnly->packages as $package)
                @if(count($package->packageContents) > 0)
                  @foreach($package->packageContents as $key=>$value)
                    <tr>
                      <td>{{$value->orderItemId ?? 'N/A'}}</td>
                      <td>{{$value->qty ?? 'N/A'}}</td>
                      <td>{{$value->serialNumber ?? 'N/A'}}</td>
                      <td>{{$value->lotNumber ?? 'N/A'}}</td>
                      <td>{{isset($value->expirationDate) ? $value->expirationDate : 'N/A'}}</td>
                    </tr>
                  @endforeach
                @endif
            @endforeach
          @endif



       </tbody>
     </table>
     -->
     </div>
  </div>
@stop



@section('css')

    <link rel="stylesheet" href="/css/admin_custom.css">

@stop
