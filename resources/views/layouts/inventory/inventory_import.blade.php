@extends('adminlte::page')



@section('title', 'Inventory Import')



@section('content_header')
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <h1 class="header-text">
        <a href=" {{ route('inventory') }} " >INVENTORY</a>
        <i class="fa fa-chevron-right"></i>
         IMPORT
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
            <div class="col-md-4 col-md-offset-4">
                <div class="box box-solid box-primary import-box">
                    <div class="box-header import-box-header">
                        <h3 class="box-title">Import Files</h3>
                    </div>
                    <div class="box-body">
                        <form method="POST" action="{{ route('inventory_import_map') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <div>
                                    <input type="file" class="form-control" id="import_file" name="import_file" placeholder="Name" required>
                                </div>
                                </br>
                                <div class="form-group import-btn-container">
                                    <button type="submit" class="btn btn-flat so-btn" data-dismiss="modal">submit</button>
                                </div>
                            </div>
                        </form>
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
    <script type="text/javascript" src="/js/jquery/topbarsearch.jquery.js?v={{preg_replace('/[^0-9]/', '', \Tremby\LaravelGitVersion\GitVersionHelper::getNameAndVersion())}}"></script>

@stop
