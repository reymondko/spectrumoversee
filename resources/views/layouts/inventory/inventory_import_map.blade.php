@extends('adminlte::page')



@section('title', 'Inventory Import Map')



@section('content_header')
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <h1 class="header-text">
        <a href=" {{ route('inventory') }} " >INVENTORY</a>
        <i class="fa fa-chevron-right"></i>
        <a href=" {{ route('inventory_import') }} " >IMPORT</a>
        <i class="fa fa-chevron-right"></i>
        MAP
    </h1>
    <!--<button type="button" id="topbar_search" class="search-bar-btn btn btn-default so-btn">
        <i class="fa fa-search"></i> Search
    </button>
    <input type="text" class="search-bar col-md-2" id="global_search" aria-describedby="search" placeholder="Search">-->
@stop



@section('content')
@if(isset(session('data')['status']))
<div class="alert alert-info alert-dismissible alert-saved">
    <button type="button" class="close alert-close-btn" data-dismiss="alert" aria-hidden="true">×</button>
    <h4><i class="icon fa fa-check"></i>Saved!</h4>
    <h4>Total Records: {{session('data')['total_records']}}</h4>
    <h4>Total Records Inserted: {{ session('data')['total_inserted'] }}</h4>
</div>
@endif
<div class="box inventory-box">
   <div class="box-header">
      <h3 class="box-title"></h3>
      <div class="box-tools pull-right">
      </div>
      <!-- /.box-tools -->
   </div>
   <!-- /.box-header -->
   <div class="box-body">
        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                <div class="box box-solid box-primary import-box">
                    <div class="box-header import-box-header">
                        <h3 class="box-title">Map Fields</h3>
                    </div>
                    <div class="box-body">
                        <!-- <form method="POST" action="{{ route('inventory_import_save') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                            </div>
                        </form> -->
                        <div>
                        <form method="POST" action="{{ route('inventory_import_save') }}" enctype="multipart/form-data">
                            @csrf
                            <table id="map_fields_table" class="table table-striped table-bordered" style="width:100%">
                                <thead class="table_head">
                                    <tr>
                                        <th class="table_head_th">Column Header</th>
                                        <th class="table_head_th">Map To</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php ($ctr = 1)
                                    @foreach($data['import_data_heading'] as $i)
                                        <tr>
                                            <td class="table_td">{{ $i }}</td>
                                            <td>
                                                <select class="form-control inventory_field_map" name="inventory_field_map[]" id="select_map{{ $ctr++ }}">
                                                    <option value="0">None</option>
                                                    <option value="sku">SKU</option>
                                                    <option value="barcode_id">Barcode ID</option>
                                                    @foreach($data['inventory_fields'] as $if)
                                                        <option value="custom_field{{ $if['field_number'] }}">{{ $if['field_name'] }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <tfoot>
                            <div class="form-group import-btn-container">
                                    <button type="submit" class="btn btn-flat so-btn" data-dismiss="modal">submit</button>
                                </div>
                            </tfoot>
                            </form>
                        </div>
                    </div><!-- /.box-body -->
                </div><!-- /.box -->
            </div>
        </div>
   </div>
   <!-- /.box-body -->
</div>
<!-- /.box -->
@stop



@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
    <link rel="stylesheet" href="/css/inventory.css">
@stop

@section('js')

    <script>
    </script>
    <script src="{{ asset('js/jquery/inventory.jquery.js') }}?v={{preg_replace('/[^0-9]/', '', \Tremby\LaravelGitVersion\GitVersionHelper::getNameAndVersion())}}" defer></script>
    <script type="text/javascript" src="/js/jquery/topbarsearch.jquery.js?v={{preg_replace('/[^0-9]/', '', \Tremby\LaravelGitVersion\GitVersionHelper::getNameAndVersion())}}"></script>
@stop
