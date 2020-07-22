@extends('adminlte::page')
@section('title', 'Third Party Orders')
@section('content_header')
<!-- CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">
<h1 class="header-text">
    @if(isset($search))
    <a href=" {{ route('thirdparty_orders') }} " >THIRD PARTY ORDERS</a>
        <i class="fa fa-chevron-right"></i>
        SEARCH <i class="fa fa-chevron-right"></i> {{$search}}
    @else
    THIRD PARTY ORDERS
    @endif

</h1>
@stop
@section('content')
@if(session('data')['status'] == 'saved')
<div class="alert alert-info alert-dismissible alert-saved">
   <button type="button" class="close alert-close-btn" data-dismiss="alert" aria-hidden="true">×</button>
   <h4><i class="icon fa fa-check"></i>Order #{{session('data')['orderId']}} Successfully Created</h4>
</div>
@endif
@if(session('status') == 'saved')
<div class="alert alert-info alert-dismissible alert-saved">
   <button type="button" class="close alert-close-btn" data-dismiss="alert" aria-hidden="true">×</button>
   <h4><i class="icon fa fa-check"></i>Saved!</h4>
</div>
@elseif(session('status') == 'error_saving')
<div class="alert alert-info alert-dismissible alert-error">
   <button type="button" class="close alert-close-btn" data-dismiss="alert" aria-hidden="true">×</button>
   <h4><i class="icon fa fa-warning"></i>Error Saving Data!</h4>
</div>
@endif
<div class="box inventory-box filter-box">
  <div id="filter-form">
      <div class="col-md-12"><h3 class="box-title">Search All Orders</h3></div>
      <div class="box-body" >
        <form method="post" action="{{route('thirdparty_orders_search')}}">
            @csrf
            <input type="text" name="search" class="third-party-order-num" placeholder="Search Order#, Ref #, Ship To" style="display:inline-block" />
            <button type="submit" class="btn btn-default so-btn" style="margin-top: -6px;display:inline-block;" /><i class="fa fa-search"></i> Search</button>
                <!--<input type="text" name="search-order-num" class="third-party-order-search-order-num" placeholder="Search Order#" />
                <input type="text" name="search-ref-num" class="third-party-order-search-ref-num" placeholder="Search Ref #" />
                <input type="text" name="search-ship-to" class="third-party-order-search-ship-to" placeholder="Search Ship To" />!-->
        </form>
      </div>
  </div>
        <form  autocomplete="off" class="form-inline" method="POST" action="{{route('thirdparty_orders_filter')}}">
     @csrf
    <div class="box-header">
        <h3 class="box-title">Advanced Filters</h3></br>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-default so-btn" id="add-filter">
                <i class="fa fa-plus"></i> Add filter
            </button>
            <button type="submit" class="btn btn-default so-btn-close" id="add-filter">
                <i class="fa fa-search"></i> Apply Filters
            </button>
            @if(isset($filter))
                @if($filter)
                    <a href=" {{route('thirdparty_orders')}}">
                        <button type="button" class="btn btn-default so-btn-close" id="add-filter">
                            <i class="fa fa-times"></i> Clear Filter
                        </button>
                    </a>
                @endif
            @endif
        </div>
    </div>
    <div class="box-body">
            <fieldset id="filter-field-set">
                @if(isset($filter['from_date']))
                <div id="filter_div0">
                    <div class="form-group col-md-3">
                        <select class="form-control tpo-filter-field filter-input" onchange="filterFieldForm(0)" id="filterField0" name="filterField[]" required="">
                            <option value="">Select value to filter</option>
                            <option value="date_created" selected>Date Created</option>
                            <option value="status">Status</option>
                            <option value="ref">Reference Number</option>
                            <option value="ship_to_name">Ship to Name</option>
                        </select>
                    </div>
                    <div class="form-group col-md-6" id="filterVal0">
                        <div class="form-group">
                            <input type="text" class="form-control tpo-dates datepicker" name="from_date" placeholder="From Date" required="" value="{{$filter['from_date']}}">
                            <span style="margin:20px;"> to </span>
                            <input type="text" class="form-control tpo-dates datepicker" name="to_date" placeholder="To Date" required="" value="{{$filter['to_date']}}">
                        </div>
                        <a href="#" onclick="removeFilterInput(0)" class="filter-div-delete"><i class="fa fa-trash filter-div-delete-icn"></i></a>
                    </div>
                    <br><br>
                </div>
                @endif

                @if(isset($filter['status']))
                <div id="filter_div1">
                    <div class="form-group col-md-3">
                        <select class="form-control tpo-filter-field filter-input" onchange="filterFieldForm(1)" id="filterField1" name="filterField[]" required="">
                            <option value="">Select value to filter</option>
                            <option value="date_created">Date Created</option>
                            <option value="status" selected>Status</option>
                            <option value="ref">Reference Number</option>
                            <option value="ship_to_name">Ship to Name</option>
                        </select>
                    </div>
                    <div class="form-group col-md-6" id="filterVal1">
                        <select class="form-control tpo-filter-field-m" name="tpl_status" required="">
                            <option value="1" @if($filter['status'] == 1) selected @endif>Open</option>
                            <option value="0" @if($filter['status'] == 0) selected @endif>Closed</option>
                        </select>
                        <a href="#" onclick="removeFilterInput(1)" class="filter-div-delete"><i class="fa fa-trash filter-div-delete-icn"></i></a>
                    </div>
                    <br><br>
                    </div>
                @endif

                @if(isset($filter['ref']))
                    <div id="filter_div2">
                        <div class="form-group col-md-3">
                            <select class="form-control tpo-filter-field filter-input" onchange="filterFieldForm(2)" id="filterField2" name="filterField[]" required="">
                                <option value="">Select value to filter</option>
                                <option value="date_created">Date Created</option>
                                <option value="status" >Status</option>
                                <option value="ref" selected>Reference Number</option>
                                <option value="ship_to_name">Ship to Name</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6" id="filterVal2">
                            <input type="text" class="form-control tpo-filter-field-m" name="ref" placeholder="Reference Number" value="{{$filter['ref']}}" required >
                            <a href="#" onclick="removeFilterInput(2)" class="filter-div-delete"><i class="fa fa-trash filter-div-delete-icn"></i></a>
                        </div>
                        <br><br>
                    </div>
                @endif

                @if(isset($filter['ship_to_name']))
                    <div id="filter_div3">
                        <div class="form-group col-md-3">
                            <select class="form-control tpo-filter-field filter-input" onchange="filterFieldForm(3)" id="filterField2" name="filterField[]" required="">
                                <option value="">Select value to filter</option>
                                <option value="date_created">Date Created</option>
                                <option value="status" >Status</option>
                                <option value="ref" >Reference Number</option>
                                <option value="ship_to_name" selected>Ship to Name</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6" id="filterVal3">
                            <input type="text" class="form-control tpo-filter-field-m" name="ship_to_name" placeholder="Ship To Name" value="{{$filter['ship_to_name']}}" required >
                            <a href="#" onclick="removeFilterInput(3)" class="filter-div-delete"><i class="fa fa-trash filter-div-delete-icn"></i></a>
                        </div>
                        <br><br>
                    </div>
                @endif
            </fieldset>
    </div>
    </form>
</div>
<div class="box inventory-box">
   <div class="box-header">
      <h3 class="box-title col-md-12">
        Third Party @if(isset($_GET['canceled']))@if($_GET['canceled'] == 1) Canceled @endif @endif Orders List
          <div class="pull-right" style="margin: -6px;">
                  <a href="{{route('thirdparty_orders_create')}}"><button type="button"  class="btn btn-default so-btn"/><i class="fa fa-plus"></i> Create Order</button></a>
          </div>
      </h3>
      <br /><br />
      @if(isset($_GET['canceled']))
        @if($_GET['canceled'] == 1)
         <a style="position:relative;z-index:999" href="/thirdparty/orders">Back</a>
        @endif
      @else
        <a style="position:relative;z-index:999" href="/thirdparty/orders?canceled=1">View Canceled Orders</a>
      @endif
      <!--<div class="box-tools pull-right" style="display: flex;">
          <form method="post" action="{{route('thirdparty_orders_search')}}">
            @csrf
            <input type="text" name="search" class="third-party-order-search" placeholder="Search Order#, Ref #" />
            <button type="submit"  class="btn btn-default so-btn"/><i class="fa fa-search"></i> Search</button>
          </form>
          <a href="{{route('thirdparty_orders_create')}}"><button type="button"  class="btn btn-default so-btn"/><i class="fa fa-plus"></i> Create Order</button></a>
        </div>!-->
      </br>
      <!-- /.box-tools -->
   </div>
   <!-- /.box-header -->
   <div class="box-body" style="margin-top:-35px;">
        <table id="third_party_orders" class="table table-striped table-bordered" style="width:100%">
           <thead class="table_head">
              <tr>
                 <th class="table_head_th">Transc. ID</th>
                 <th class="table_head_th">Reference #</th>

                 <th class="table_head_th">Ship To</th>
                 <th class="table_head_th" style="display:none">Order Date</th>
                 <th class="table_head_th">Order Date</th>
                 <th class="table_head_th">Order Status</th>

                 <th class="table_head_th">Tracking #</th>
                 <th class="table_head_th">Customer</th>
              </tr>
              <!--<tr id="filters">
                    <th class="table_head_th">Transc. ID</th>
                     <th class="table_head_th">Reference #</th>

                     <th class="table_head_th">Ship To</th>
                     <th class="table_head_th" style="display:none">Order Date</th>
                     <th class="table_head_th">Order Date</th>
                     <th class="table_head_th">Order Status</th>

                     <th class="table_head_th">Tracking #</th>
                     <th class="table_head_th">Customer</th>
                </tr>!-->
           </thead>
           <tbody>

                 @foreach($orders as $order)
                     @if(isset($_GET['canceled']))
                     @if($_GET['canceled'] == 1 && (strpos($order->referenceNum,'CANCELED') !== FALSE))
                     <tr>
                      @if(isset($search))
                     <td><a href="/thirdparty/orders/details/{{ $order->readOnly->orderId }}">{{ $order->readOnly->orderId }}</a></td>
                     @else
                     <td><a href="/thirdparty/orders/details/{{ $order->readOnly->orderId }}">{{ $order->readOnly->orderId }}</a></td>
                     @endif
                     <td>{{ $order->referenceNum }}</td>
                     <td>
                            @if(isset($order->shipTo->companyName)){{$order->shipTo->companyName}}@endif
                    </td>
                     <td>{{ date('m/d/Y', strtotime($order->readOnly->creationDate)) }}</td>
                     <td style="display:none">{{strtotime($order->readOnly->creationDate)}}</td>
                     <td>@if($order->readOnly->isClosed == true)Closed @else Open @endif</td>

                     <td>{{isset($order->routingInfo->trackingNumber) ? $order->routingInfo->trackingNumber: 'N/A'}}</td>
                     <td>{{ $order->readOnly->customerIdentifier->name }}</td>
                   </tr>
                     @endif
                  @else
                   @if(strpos($order->referenceNum,'CANCELED') === FALSE)
                   <tr>
                      @if(isset($search))
                     <td><a href="/thirdparty/orders/details/{{ $order->readOnly->orderId }}">{{ $order->readOnly->orderId }}</a></td>
                     @else
                     <td><a href="/thirdparty/orders/details/{{ $order->readOnly->orderId }}">{{ $order->readOnly->orderId }}</a></td>
                     @endif
                     <td>{{ $order->referenceNum }}</td>
                     <td>
                         @if(isset($order->shipTo->companyName)){{$order->shipTo->companyName}}@endif
                     </td>
                     <td>{{ date('m/d/Y', strtotime($order->readOnly->creationDate)) }}</td>
                     <td style="display:none">{{strtotime($order->readOnly->creationDate)}}</td>
                     <td>@if($order->readOnly->isClosed == true)Closed @else Open @endif</td>

                     <td>{{isset($order->routingInfo->trackingNumber) ? $order->routingInfo->trackingNumber: 'N/A'}}</td>
                     <td>{{ $order->readOnly->customerIdentifier->name }}</td>
                   </tr>
                   @endif
                  @endif
                 @endforeach
          </tbody>
        </table>
   </div>
   <!-- /.box-body -->
</div>
@stop
@section('css')
<link rel="stylesheet" href="/css/admin_custom.css">
<link rel="stylesheet" href="/css/orders.css">
<link rel="stylesheet" href="/css/spectrumoversee.tables.css">
<link rel="stylesheet" href="/css/jquery-ui.css">

@stop
@section('js')
<script src="{{ asset('js/jquery/thirdpartyorders.jquery.js') }}?v={{preg_replace('/[^0-9]/', '', \Tremby\LaravelGitVersion\GitVersionHelper::getNameAndVersion())}}" defer></script>
<script src="{{ asset('js/jquery-ui.js') }}" defer></script>
<script src="{{ asset('js/moment.js') }}" defer></script>
<script>
$(document).ready(function() {

    var table=$('#third_party_orders').DataTable({
        "sDom": '<"row view-filter"<"col-sm-12"<"pull-left"l><"pull-right"f><"clearfix">>>t<"row view-pager"<"col-sm-12"<"text-center"ip>>>',
        "bLengthChange": false,
        "bFilter": true,
        "orderCellsTop": true,
        "bInfo": false,
        "language": {
          "sSearch": "Filter Results:",
            "paginate": {
                "previous": '<i class="fa fa-chevron-left paginate-button"></i>',
                "next": '<i class="fa fa-chevron-right paginate-button"></i>',
            }
        },
        "order": [[ 3, "desc" ]],
        "pageLength": 75
    });

    /*$('#third_party_orders tr#filters th').each( function (i) {
        var title = $(this).text();
        $(this).html( '<input type="text" placeholder="Search '+title+'" />' );
        $( 'input', this ).on( 'keyup change', function () {
            if ( table.column(i).search() !== this.value ) {
                table
                    .column(i)
                    .search( this.value )
                    .draw();
            }
        } );
    } );*/
    $("#aSearch_btn").click(function(){
        $( "#filter-form" ).slideToggle( "slow" );
    });
    @if(isset($filter['from_date']))
        $( ".datepicker" ).datepicker();
    @endif
} );
</script>
@stop
