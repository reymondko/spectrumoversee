@extends('adminlte::page')
@section('title', 'Warehouse Inventory Summary')
@section('content_header')
<!-- CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">
<h1 class="header-text">
    WAREHOUSE INVENTORY SUMMARY
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
<div class="box inventory-box">
   <div class="box-header">
      <h3 class="box-title">Warehouse Inventory Summary</h3>
      <!-- <div class="box-tools pull-right">
            <input type="text" name="search" class="third-party-inventory-search" placeholder="Search by SKU" id="thirdparty_search"/>
            <button type="button"  class="btn btn-default so-btn" id="thirdparty_search_btn"/>Search</button>
        </div> -->
      </br>
      <!-- /.box-tools -->
   </div>
   <!-- /.box-header -->
   <div class="box-body">
        <a href="{{ route('thirdparty_summary_export') }}" class="btn btn-primary pull-right">Export to Excel</a>
        </br></br>
        <table id="third_party_inventory" class="table table-striped table-bordered" style="width:100%">
           <thead class="table_head">
              <tr>
                 <th class="table_head_th">Company</th>
                 <th class="table_head_th">SKU</th>
                 <th class="table_head_th">Description</th>
                 <th class="table_head_th">Current Stock</th>
                 <th class="table_head_th">Damaged</th>
                 <th class="table_head_th">Available Stock</th>
                 <th class="table_head_th">Total Stock</th>
              </tr>
           </thead>
           <tbody>
            @if(!empty($inventory_summary))
                @foreach($inventory_summary as $inventory)
                    <tr>
                        <td>{{$inventory->DepositorDescription}}</td>
                        <td>{{$inventory->InventoryItemDescription}}</td>
                        <td>{{$inventory->Description}}</td>
                        <td>{{$inventory->StockQty}}</td>
                        <td>{{$inventory->Damaged}}</td>
                        <td>{{$inventory->Undamaged}}</td>
                        <td>{{$inventory->PackQuantity}}</td>
                    </tr>
                @endforeach
            @endif
          </tbody>
        </table>
   </div>
   <!-- /.box-body -->
</div>
@stop
@section('css')
<link rel="stylesheet" href="/css/admin_custom.css">
<link rel="stylesheet" href="/css/spectrumoversee.tables.css">
<link rel="stylesheet" href="/css/jquery-ui.css">

@stop
@section('js')
<script src="{{ asset('js/jquery-ui.js') }}" defer></script>
<script src="{{ asset('js/moment.js') }}" defer></script>
<script>

$(document).ready(function() {

var inventory_fields_table =  $('#third_party_inventory').DataTable({
    "sDom": '<"row view-filter"<"col-sm-12"<"pull-left"l><"pull-right"f><"clearfix">>>t<"row view-pager"<"col-sm-12"<"text-center"ip>>>',
    "bLengthChange": false,
    "bFilter": false,
    "bInfo": false,
});

});
</script>
@stop
