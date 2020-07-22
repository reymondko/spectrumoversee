@extends('adminlte::page')



@section('title', 'Inventory Import Case Labels')



@section('content_header')
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <h1 class="header-text">
        <a href=" {{ route('inventory') }} " >INVENTORY</a>
        <i class="fa fa-chevron-right"></i>
         IMPORT CASE LABEL
    </h1>
    <!--<button type="button" id="topbar_search" class="search-bar-btn btn btn-default so-btn">
        <i class="fa fa-search"></i> Search
    </button>
    <input type="text" class="search-bar col-md-2" id="global_search" aria-describedby="search" placeholder="Search">-->
@stop



@section('content')
@if(isset(session('data')['status']))
    @if(session('data')['status'] == 'saved')
    <div class="alert alert-info alert-dismissible alert-saved">
        <button type="button" class="close alert-close-btn" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-check"></i>Saved!</h4>
        <h4>Total Records: {{session('data')['total_records']}}</h4>
        <h4>Total Records Inserted: {{ session('data')['total_inserted'] }}</h4>
    </div>
    @elseif(session('data')['status'] == 'incorrect_format')
    <div class="alert alert-warning alert-dismissible alert-saved">
        <button type="button" class="close alert-close-btn" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-warning"></i>Incorrect Format</h4>
    </div>
    @elseif(session('data')['status'] == 'no_case_number_field')
    <div class="alert alert-warning alert-dismissible alert-saved">
        <button type="button" class="close alert-close-btn" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-warning">
        </i>You have not assigned a case number field please assign a case number field from one of your inventory field <a href="{{route('case_label_required')}}"> here</a>
    </h4>
    </div>
    @endif
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
                        <form method="POST" action="{{ route('inventory_import_caselabel_save') }}" enctype="multipart/form-data">
                            @csrf
                            <fieldset id="case_label_form">
                            <div class="form-group">
                                <div class="col-md-12">
                                    <input type="file" class="form-control import-caselabel-form-fields" id="import_file" name="import_file" placeholder="Name" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-12">
                                <select class="form-control select-md import-caselabel-form-fields"  name="sku" required>
                                    <option value="">
                                        SKU
                                    </option>
                                    @foreach($data['skus'] as $s)
                                        <option value="{{$s->sku}}">
                                            {{$s->sku}}
                                        </option>
                                    @endforeach
                                </select>

                                </div>
                            </div>
                            @foreach($data['case_label_req'] as $c)
                                <div class="form-group">
                                    <div class="col-md-4">
                                     <label class="caselabel-label">{{$c->inventoryField['field_name']}}</label>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="hidden" class="form-control" value="{{$c->inventoryField['field_number']}}" name="customfield[]" required="">
                                        <input type="text" class="form-control" placeholder="Value" name="customvalue[]" required>
                                        </br>
                                    </div>
                                </div>
                            @endforeach
                            </fieldset>
                            <div class="form-group import-btn-container">
                            </br>
                                <button type="submit" class="btn btn-flat so-btn case-label-submit-btn">submit</button>
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
    <script type="text/javascript" src="/js/jquery/topbarsearch.jquery.js?v={{preg_replace('/[^0-9]/', '', \Tremby\LaravelGitVersion\GitVersionHelper::getNameAndVersion())}}"></script>
    <script type="text/javascript" src="/js/jquery/inventory_caselabel.jquery.js?v={{preg_replace('/[^0-9]/', '', \Tremby\LaravelGitVersion\GitVersionHelper::getNameAndVersion())}}"></script>
    <script>
        function removeElem(elem_id){
           $(elem_id).remove();
        }
    </script>
@stop
