@extends('adminlte::page')
@section('title', 'Warehouse Orders')
@section('content_header')
<!-- CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">
<h1 class="header-text">
    @if(isset($search))
    <a href=" {{ route('thirdparty_orders') }} " >WAREHOUSE ORDERS</a>
        <i class="fa fa-chevron-right"></i>
        SEARCH <i class="fa fa-chevron-right"></i> {{$search}}
    @else
    WAREHOUSE ORDERS
    @endif

</h1>
@stop
@section('content')
@if(session('data')['status'] == 'saved')
<div class="alert alert-info alert-dismissible alert-saved">
   <button type="button" class="close alert-close-btn" data-dismiss="alert" aria-hidden="true">×</button>
   <h4><i class="icon fa fa-check"></i>Order with reference #{{session('data')['orderId']}} Successfully Created</h4>
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
            <input type="text" @if(isset($global_search)) value="{{$global_search}}" @endif name="search" class="third-party-order-num" placeholder="Search Order#, Ref #, Ship To" style="display:inline-block;width: 250px;font-size: 16px;padding: 5px;"  />
                @if(count($depositors) > 1)
                <select name="search_depositor_id" id="search_depositor_id">
                    @foreach($depositors as $depositor)
                        <option value="{{$depositor->logiwa_depositor_id}}" @if(isset($search_depositor_id)) @if($search_depositor_id == $depositor->logiwa_depositor_id) selected @endif @endif>
                            {{$depositor->logiwa_depositor_code}}
                        </option>
                    @endforeach
                </select>
                @endif
            <button type="submit" class="btn btn-default so-btn" style="margin-top: -6px;display:inline-block;" /><i class="fa fa-search"></i> Search</button>
            @if(isset($global_search))
                @if($global_search != null)
                 <a href="/thirdparty/orders/">
                    <button type="button" class="btn btn-default so-btn-close" style="margin-top: -6px;display:inline-block;" /><i class="fa fa-times"></i> Clear Search</button>
                 </a>
                 @endif
            @endif
        </form>
      </div>
  </div>
<!--<form  autocomplete="off" class="form-inline" method="POST" action="{{route('thirdparty_orders_filter')}}">
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
                            <option value="Entered" @if($filter['status'] == 'Entered') selected @endif>Entered</option>
                            <option value="Approved" @if($filter['status'] == 'Approved') selected @endif>Approved</option>
                            <option value="CheckInventory" @if($filter['status'] == 'CheckInventory') selected @endif>CheckInventory</option>
                            <option value="Started" @if($filter['status'] == 'Started') selected @endif>Started</option>
                            <option value="Completed" @if($filter['status'] == 'Completed') selected @endif>Completed</option>
                            <option value="Shipped" @if($filter['status'] == 'Shipped') selected @endif>Shipped</option>
                            <option value="Delivered" @if($filter['status'] == 'Delivered') selected @endif>Delivered</option>
                            <option value="Freezed" @if($filter['status'] == 'Freezed') selected @endif>Freezed</option>
                            <option value="Cancelled" @if($filter['status'] == 'Cancelled') selected @endif>Cancelled</option>
                            <option value="Suspended" @if($filter['status'] == 'Suspended') selected @endif>Suspended</option>
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
  </form>-->
</div>
<div class="box inventory-box">
   <div class="box-header">
      <h3 class="box-title col-md-12 orders-head-title">
        Warehouse @if(isset($_GET['canceled']))@if($_GET['canceled'] == 1) Canceled @endif @endif Orders List

        @if(isset($_GET['cancelled']))
            @if($_GET['cancelled'] == 1)
            &nbsp;(
                @if(isset($_GET['selected_depositor']))
                    <a style="position:relative;z-index:999;font-size: 14px;" href="/thirdparty/orders&selected_depositor={{$_GET['selected_depositor']}}">Back</a>
                @else
                    <a style="position:relative;z-index:999;font-size: 14px;" href="/thirdparty/orders">Back</a>
                @endif
                )
            @endif
        @else
            &nbsp;(
                @if(isset($_GET['selected_depositor']))
                    <a style="position:relative;z-index:999;font-size: 14px;" href="/thirdparty/orders?cancelled=1&selected_depositor={{$_GET['selected_depositor']}}">View Canceled Orders</a>
                @else
                    <a style="position:relative;z-index:999;font-size: 14px;" href="/thirdparty/orders?cancelled=1">View Canceled Orders</a>
                @endif
                )
        @endif
           <div class="pull-right" style="margin: -6px;">
                  <a href="{{route('thirdparty_orders_create')}}"><button type="button"  class="btn btn-default so-btn"/><i class="fa fa-plus"></i> Create Order</button></a>
          </div>
      </h3>

      </br>
      <!-- /.box-tools -->
   </div>
   <!-- /.box-header -->
   <div class="box-body" style="margin-top:-10px;">
        @if(!isset($global_search))
        <div class="depositor_select">
            @if(count($depositors) > 1)
                <label>Customer</label>
                <select id="logiwa_customer_selection">
                    @foreach($depositors as $depositor)
                        <option value="{{$depositor->logiwa_depositor_id}}" @if(isset($_GET['selected_depositor'])) @if($_GET['selected_depositor'] == $depositor->logiwa_depositor_id) selected @endif @endif>
                            {{$depositor->logiwa_depositor_code}}
                        </option>
                    @endforeach
                </select>
            @endif
        </div>
        <table id="third_party_orders" class="table table-striped table-bordered" style="width:100%">
           <thead class="table_head">
              <tr>
                 <th class="table_head_th">Transc. ID</th>
                 <th class="table_head_th">Reference #</th>
                 <th class="table_head_th">Customer Order #</th>
                 <th class="table_head_th">Ship To</th>
                 <!-- <th class="table_head_th" style="display:none">Order Date</th> -->
                 <th class="table_head_th">Order Date</th>
                 <th class="table_head_th">Order Status</th>

                 <th class="table_head_th">Tracking #</th>
                 <th class="table_head_th">Customer</th>
              </tr>
           </thead>
           <tbody style="max-height:none;overflow:none;">
                @foreach($orders as $order)
                        @if(isset($_GET['cancelled']))
                            @if($_GET['cancelled'] == 1)
                                @if($order->WarehouseOrderStatusCode != 'Cancelled')
                                    @php
                                        continue;
                                    @endphp
                                @endif
                            @endif
                        @else
                            @if($order->WarehouseOrderStatusCode == 'Cancelled')
                                @php
                                    continue;
                                @endphp
                            @endif
                        @endif

                    <tr>
                      @if(isset($search))
                     <td><a href="/thirdparty/orders/details/{{ $order->ID }}">{{ $order->ID  }}</a></td>
                     @else
                     <td><a href="/thirdparty/orders/details/{{  $order->ID  }}">{{  $order->ID  }}</a></td>
                     @endif
                     <td>{{ $order->Code  }}</td>
                     <td>
                         {{ $order->CustomerOrderNo }}
                     </td>
                     <td>
                         {{ $order->CustomerCode }}
                     </td>
                     <td>{{ \DateTime::createFromFormat('m.d.Y H:i:s',$order->OrderDate)->format('m/d/Y') }}</td>
                     <!-- <td style="display:none">{{ $order->OrderDate }}</td> -->
                     <td>{{ $order->WarehouseOrderStatusCode }}</td>
                     <td>{{$order->CarrierTrackingNumber ?? 'N/A'}}</td>
                     <td>{{ $order->CustomerCode }}</td>
                   </tr>
                @endforeach
          </tbody>
        </table>
        <br />
        <!--
        <div class="box-footer-actions">
                @if(isset($_GET['pagenum']))
                    @if(isset($_GET['selected_depositor']))
                        <a @if ($_GET['pagenum'] <= 0) disabled @endif class="btn btn-default so-btn" href="/thirdparty/orders?pagenum={{$_GET['pagenum'] - 1}}&selected_depositor={{$_GET['selected_depositor']}}">Previous</a>
                    @else
                        <a @if ($_GET['pagenum'] <= 0) disabled @endif class="btn btn-default so-btn" href="/thirdparty/orders?pagenum={{$_GET['pagenum'] - 1}}">Previous</a>
                    @endif

                    <span class="pagenum">{{$_GET['pagenum'] + 1}}</span>
                    @if(count($orders) > 0)
                        @if(isset($_GET['selected_depositor']))
                            <a class="btn btn-default so-btn" href="/thirdparty/orders?pagenum={{$_GET['pagenum'] + 1}}&selected_depositor={{$_GET['selected_depositor']}}">Next</a>
                        @else
                            <a class="btn btn-default so-btn" href="/thirdparty/orders?pagenum={{$_GET['pagenum'] + 1}}">Next</a>
                        @endif
                    @else
                        <a class="btn btn-default so-btn" href="#" disabled>Next</a>
                    @endif

                @else
                    <a class="btn btn-default so-btn" href="#" disabled>Previous</a>
                    <span class="pagenum">1</span>
                    <a class="btn btn-default so-btn" href="/thirdparty/orders?pagenum=1">Next</a>
                @endif
            @endif
        </div>
        -->
   </div>
   <!-- /.box-body -->
</div>
@stop
@section('css')
<link rel="stylesheet" href="/css/admin_custom.css">
<link rel="stylesheet" href="/css/orders.css">
<link rel="stylesheet" href="/css/spectrumoversee.tables.css">
<link rel="stylesheet" href="/css/jquery-ui.css">
<link rel="stylesheet" href="/css/fixed-table.css">
@stop
@section('js')
<script src="{{ asset('js/jquery/logiwa_orders.jquery.js') }}?v={{preg_replace('/[^0-9]/', '', \Tremby\LaravelGitVersion\GitVersionHelper::getNameAndVersion())}}" defer></script>
<script src="{{ asset('js/jquery-ui.js') }}" defer></script>
<script src="{{ asset('js/moment.js') }}" defer></script>
<script>
$(document).ready(function() {

    // var table=$('#third_party_orders').DataTable({
    //     "sDom": '<"row view-filter"<"col-sm-12"<"pull-left"l><"pull-right"f><"clearfix">>>t<"row view-pager"<"col-sm-12"<"text-center"ip>>>',
    //     "bLengthChange": false,
    //     "bFilter": true,
    //     "orderCellsTop": true,
    //     "bInfo": false,
    //     "language": {
    //       "sSearch": "Filter Results:",
    //         "paginate": {
    //             "previous": '<i class="fa fa-chevron-left paginate-button"></i>',
    //             "next": '<i class="fa fa-chevron-right paginate-button"></i>',
    //         }
    //     },
    //     "order": [[ 3, "desc" ]],
    //     "pageLength": 75
    // });
    $("#aSearch_btn").click(function(){
        $( "#filter-form" ).slideToggle( "slow" );
    });
    @if(isset($filter['from_date']))
        $( ".datepicker" ).datepicker();
    @endif

    $('#logiwa_customer_selection').change(function(){
        window.location = '/thirdparty/orders?pagenum=0&selected_depositor='+$(this).val();
    })
} );
</script>
@stop
