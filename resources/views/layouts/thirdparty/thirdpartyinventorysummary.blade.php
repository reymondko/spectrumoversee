@extends('adminlte::page')
@section('title', 'Third Party Inventory Summary')
@section('content_header')
<!-- CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">
<h1 class="header-text">
    THIRD PARTY INVENTORY SUMMARY
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
      <h3 class="box-title">Third Party Inventory Summary</h3>
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

        <table id="third_party_inventory" class="table table-striped table-bordered" style="width:100%">
           <thead class="table_head">
              <tr>
                 <th class="table_head_th">SKU</th>
                 <th class="table_head_th">Description</th>
                 <!-- <th class="table_head_th">On Hold</th> -->
                 <th class="table_head_th">On Hand</th>
                 <th class="table_head_th">Allocated</th>
                 <th class="table_head_th">Available</th>
                 <!-- <th class="table_head_th">Back Ordered</th> -->
              </tr>
           </thead>
           <tbody>
                
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

    $.fn.dataTable.ext.errMode = 'none';
    var inventory_fields_table =  $('#third_party_inventory').DataTable({
        processing: true,
        ajax:'{{route("thirdparty_inventory_summary_list")}}',
        "sDom": '<"row view-filter"<"col-sm-12"<"pull-left"l><"pull-right"f><"clearfix">>>t<"row view-pager"<"col-sm-12"<"text-center"ip>>>',
        "bLengthChange": false,
        "bFilter": false,
        "bInfo": false,
        "language": {
            "paginate": {
                "previous": '<i class="fa fa-chevron-left paginate-button"></i>',
                "next": '<i class="fa fa-chevron-right paginate-button"></i>',
            }
        },
        "pageLength": 500,
       
    });
    
    $('#third_party_inventory')
    .on( 'error.dt', function ( e, settings, techNote, message ) {
        $('#third_party_inventory').DataTable().clear();
        $('#third_party_inventory').DataTable().destroy();

         var inventory_fields_table =  $('#third_party_inventory').DataTable({
            processing: true,
            ajax:'{{route("thirdparty_inventory_summary_list")}}',
            "sDom": '<"row view-filter"<"col-sm-12"<"pull-left"l><"pull-right"f><"clearfix">>>t<"row view-pager"<"col-sm-12"<"text-center"ip>>>',
            "bLengthChange": false,
            "bFilter": false,
            "bInfo": false,
            "language": {
                "paginate": {
                    "previous": '<i class="fa fa-chevron-left paginate-button"></i>',
                    "next": '<i class="fa fa-chevron-right paginate-button"></i>',
                }
            },
            "pageLength": 500,
        
        });
    } )
    .DataTable();
});
</script>
@stop