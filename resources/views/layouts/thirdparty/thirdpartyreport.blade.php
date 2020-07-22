@extends('adminlte::page')
@section('title', 'Create Third Party Orders')
@section('content_header')
<!-- CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">
<h1 class="header-text">
    @if(isset($search))
    <a href=" {{ route('thirdparty_orders') }} " >CREATE THIRD PARTY ORDER</a>
        <i class="fa fa-chevron-right"></i>
        SEARCH <i class="fa fa-chevron-right"></i> {{$search}}
    @else
    THIRD PARTY REPORTS
    @endif
   
</h1>
@stop
@section('content')
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
<section style="padding: 20px;">
<div class="col-md-6 col-md-offset-2">
<form autocomplete="off" method="POST" action="{{route('thirdparty_report_generate_lir')}}" id="line_item_report_form">
    @csrf
<div class="box box-solid box-primary spectrumbox-primary">
  <div class="box-header with-border spectrumbox-primary-header">
    <h3 class="box-title">Generate Line Item Report</h3>
    <div class="box-tools pull-right">
    </div>
  </div>
  <div class="box-body">
  <div class="form-group col-md-6" >
    <div class="form-group"></br>
        <label for="total_weight">From Date</label>
        <input type="text" class="form-control tpo-dates datepicker" name="from_date" placeholder="From Date" required>
    </div>
   </div>
   <div class="form-group col-md-6" >
    <div class="form-group"></br>
        <label for="total_weight">To Date</label>
        <input type="text" class="form-control tpo-dates datepicker" name="to_date" placeholder="To Date" required>
    </div>
   </div>
  </div>
  <div class="box-footer" style="text-align:center">
    <button type="submit" name="request_type" value="generate" class="btn btn-default so-btn"/><i class="fa fa-file-text-o" ></i>&nbsp;Generate</button>
    <button type="submit" name="request_type" value="export" class="btn btn-default so-btn"/><i class="fa fa-file-excel-o"></i>&nbsp;Export To Excel</button>
  </div>
</div>
</div>
</form>


@if(isset($reportData))
<div class="col-md-12">
    <div class="box inventory-box" style="overflow-x: scroll;">
    <div class="box-header">
        <h3 class="box-title">Transaction By Line Item Report </br></br><b>{{$reportData[1][0]}}</b> - {{implode(' ',$reportData[2])}}</h3>
        </br>
        <!-- /.box-tools -->
    </div>
    <!-- /.box-header -->
    <div class="box-body">
            <table id="third_party_orders_report" class="table table-striped table-bordered" style="width:100%">
            <thead class="table_head">
                <tr>
                    <th class="table_head_th">Name</th>
                    <th class="table_head_th">captured Kit ID#</th>
                    <th class="table_head_th">Kit ID#</th>
                    <th class="table_head_th">Return Tracking</th>
                    <th class="table_head_th">Trans #</th>
                    <th class="table_head_th">ShipDate</th>
                    <th class="table_head_th">Ref #</th>
                    <th class="table_head_th">Name</th>
                    <th class="table_head_th">Address</th>
                    <th class="table_head_th">City</th>
                    <th class="table_head_th">State</th>
                    <th class="table_head_th">Zip</th>
                    <th class="table_head_th">Country</th>
                    <th class="table_head_th">SKU</th>
                    <th class="table_head_th">Qty</th>
                    <th class="table_head_th">Outbound Tracking #</th>
                    <th class="table_head_th">Carrier</th>
                    <th class="table_head_th">Retailer Id</th>
                    <th class="table_head_th">Department Id</th>
                    <th class="table_head_th">PO Number</th>
                </tr>
            </thead>
            <tbody>
                @foreach(array_slice($reportData, 5) as $key => $value)
                    <tr> 
                        @foreach($value as $v)
                            <td>{{$v}}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
            </table>
    </div>
    <!-- /.box-body -->
    </div>
</div>
@endif
</section>
@stop
@section('css')
<link rel="stylesheet" href="/css/admin_custom.css">
<link rel="stylesheet" href="/css/jquery-ui.css">
<link rel="stylesheet" href="/css/spectrumoversee.tables.css">
@stop
@section('js')
<script src="{{ asset('js/jquery-ui.js') }}" defer></script>
<script src="{{ asset('js/moment.js') }}" defer></script>
<script>
    $(function() {
        $( ".datepicker" ).datepicker();
    });

   
</script>
@stop