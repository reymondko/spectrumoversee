@extends('adminlte::page')

@section('title', 'Third Party Inventory')

@section('content_header')
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <h1 class="header-text">
        THIRD PARTY INVENTORY DETAIL
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


    <div class="box inventory-box filter-box">
        <form  autocomplete="off" class="form-inline" method="POST" action="#">
            @csrf
            <div class="box-header">
                <h3 class="box-title">Filter Results</h3></br>
                <div class="box-tools pull-right"></div>
            </div>
            <div class="box-body">     
            </div>
        </form>
    </div>


    <div class="box inventory-box">
        <div class="box-header">
            <h3 class="box-title">Third Party Inventory List</h3>
            <!-- <div class="box-tools pull-right">
                <form method="post" action="#">
                    @csrf
                    <input type="text" name="search" class="third-party-order-search" placeholder="Search Order#, Ref #" />
                    <button type="submit"  class="btn btn-default so-btn"/>Search</button>
                </form>
                </div> -->
            </br>
            <!-- /.box-tools -->
        </div>
        <!-- /.box-header -->

        <div class="box-body">
            <a href="{{ route('thirdparty_inventory_export') }}" class="btn btn-primary pull-right">Export to Excel</a>

            <table id="third_party_inventory" class="table table-striped table-bordered" style="width:100%">
                <thead class="table_head">
                    <tr>
                        <th class="table_head_th">SKU</th>
                        <th class="table_head_th">Description</th>
                        <th class="table_head_th">Qualifier</th>
                        <th class="table_head_th">Location</th>
                        <th class="table_head_th">Serial # (KIT ID)</th>
                        <th class="table_head_th">Lot # (Return Tracking)</th>
                        <th class="table_head_th">Exp. Date</th>
                        <th class="table_head_th">On Hand</th>
                        <th class="table_head_th">Available</th>
                        <!-- <th class="table_head_th">Over</th> -->
                    </tr>
                </thead>

                <tbody></tbody>
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
                processing: true,
                serverSide: true,
                // ajax: '/inventory/data/paginate',
                ajax:'{{route("thirdparty_inventory_detail_list")}}',
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
                "pageLength": 75,
                "columnDefs": [{
                    "targets": [4,5],
                    "orderable": false
                }]
            
            });
        });
    </script>
@stop