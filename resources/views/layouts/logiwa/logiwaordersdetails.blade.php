@extends('adminlte::page')



@section('title', 'Order Details - '.$data->ID)



@section('content_header')
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <h1 class="header-text">Order Details - {{ $data->ID }}</h1>

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
              @if(isset($data->CustomerDescription))
              {{$data->CustomerDescription}}<br />
              @endif
              @if(isset($data->CompanyName))
              @if($data->CompanyName != '')
              {{$data->CompanyName}}<br />
              @endif
              @endif
             @if(isset($address_data->AdressText))
              {{$address_data->AdressText}},&nbsp;{{$address_data->CityDescription}}&nbsp;</br>{{$address_data->StateDescription}} {{$address_data->PostalCodeDescription}} , {{$address_data->CountryDescription}}
              <br />
              {{$address_data->Phone}}
             @endif
           </td>
           <td style="width:50%;">
             @if (!isset($data->CarrierTrackingNumber))
              @if($company->can_manual_fulfill == 1)
               <a class="od-manual-ff-link btn btn-default so-btn" onclick="return confirm('Are you sure you want fulfill this order?')" href="/thirdparty/orders/manual-fulfill/{{$data->ID}}">Fulfill Order</a>
               @endif
             @endif
             <h4>Creation Date: {{$data->OrderDate }}</h4>
             <h4>Reference Number: {{$data->Code}}</h4>
             <h4>Customer Order Number: {{$data->CustomerOrderNo}}</h4>
             <h4>Status: {{$data->WarehouseOrderStatusCode}}</h4>
             @if($data->WarehouseOrderStatusCode != 'Cancelled')
                <a style="color:red" onclick="return confirm('Are you sure you want to cancel this order?')" href="/thirdparty/orders/cancel/{{$data->ID}}">Cancel Order</a>
             @endif
             @if($data->WarehouseOrderStatusCode == 'Shipped')
              @if (isset($data->CarrierDescription)) <h4>Carrier: {{$data->CarrierDescription }}</h4> @endif
              @if (isset($data->ShipmentMethod)) <h4>Carrier Method: {{$data->ShipmentMethod }}</h4> @endif
              @if (isset($data->CarrierMarkupRate)) <h4>Shipping Cost: {{number_format($data->CarrierMarkupRate,2) }}</h4> @endif
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
               <!-- <th class="table_head_th">Lot # (Return Tracking)</th> -->
               <!-- <th class="table_head_th">Exp. Date</th> -->
               <th class="table_head_th">Quantity</th>
            </tr>
         </thead>
         <tbody>
           @if (!empty($order_line_items))
             @foreach($order_line_items as $item)
                  <tr>
                    <td>{{$item['id']}}</td>
                    <td>{{$item['sku']}}</td>
                    <td>{{$item['desc']}}</td>
                    <td>{{$item['serial']}}</td>
                    <td>{{$item['quantity']}}</td>
                  </tr>
             @endforeach
           @endif
        </tbody>
      </table>
      <br /><br />
     </div>
  </div>
@stop
@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop
