@extends('adminlte::page')


@section('title', 'Skus')



@section('content_header')
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <h1 class="header-text">
        <a href=" {{ route('settings') }} " >SETTINGS</a>
        <i class="fa fa-chevron-right"></i>
        SKUS
    </h1>
    <!--<button type="button" id="topbar_search" class="search-bar-btn btn btn-default so-btn">
        <i class="fa fa-search"></i> Search
    </button>
    <input type="text" class="search-bar col-md-2" id="global_search" aria-describedby="search" placeholder="Search">-->
@stop


@section('content')
@if(session('status') == 'saved')
    <div class="alert alert-info alert-dismissible alert-saved">
        <button type="button" class="close alert-close-btn" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-check"></i>Saved!</h4>
    </div>
@elseif(session('status') == 'deleted')
    <div class="alert alert-info alert-dismissible alert-error">
        <button type="button" class="close alert-close-btn" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-warning"></i>Deleted Successfully!</h4>
    </div>
@endif
<div class="box user-box">
   <div class="box-header">
      <h3 class="box-title"></h3>
      <div class="box-tools pull-right">
        <button type="button" class="btn btn-flat so-btn" data-toggle="modal" data-target="#addSkuModal">
            <i class="fa fa-plus"></i> Add SKU
        </button>
      </div>
      <!-- /.box-tools -->
   </div>
   <!-- /.box-header -->
   <div class="box-body">
    <div class="col-md-5 col-md-offset-3">
        <table id="skus" class="table table-striped table-bordered" style="width:100%">
         <thead class="table_head">
            <tr>
               <th class="table_head_th col-md-1">Active</th>
               <th class="table_head_th ">SKU</th>
               <th class="table_head_th col-md-3">Actions</th>
            </tr>
         </thead>
         <tbody>
            @foreach($data['skus'] as $s)
                <tr>
                    <td class="sku-toggle-td"><input type="checkbox" class="sku-toggle" value="{{$s->id}}" {{$s->active == 1 ? 'checked':null}}></td>
                    <td id="sku_{{$s->id}}">{{$s->sku}}</td>
                    <td>
                        <div class="btn-group">
                            <button type="button" class="btn btn-flat action-button">Action</button>
                            <button type="button" class="btn btn-flat dropdown-toggle action-button" data-toggle="dropdown" aria-expanded="false">
                                <span class="caret"></span>
                                <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu" role="menu">
                                <li><a class="action-list-a" href="#"  data-toggle="modal" data-target="#editSkuModal" onClick="editSku('{{$s->id}}')">Edit</a></li>
                            </ul>
                        </div>
                    </td>
                </tr>
            @endforeach
         </tbody>
        </table>
    </div>
   </div>
   <!-- /.box-body -->
</div>
<!-- /.box -->

<!--Add SKU Modal -->
<div class="modal fade" id="addSkuModal" tabindex="-1" role="dialog" aria-labelledby="addSkuModallLabel">
   <div class="modal-dialog" role="document">
      <div class="modal-content">
      <form method="POST" action="{{ route('skus_save') }}">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="addSkuModallLabel">Add SKUS</h4>
         </div>
         <div class="modal-body">
            @csrf
            <fieldset id="add-location-fieldset">
                <div class="form-group" >
                    <div class="col-md-8 col-md-offset-2 inventory-field-input-div">
                        <input name="sku[]" type="text" placeholder="SKU" class="form-control input-md add-location-field-input" required>

                    </div>
                </div>
            </fieldset>
         </div>
         <div class="modal-footer">
            <span class="col-md-4 inventory-field-btn-left">
                <button id="add_more_sku" type="button" class="btn btn-flat so-btn"><i class="fa fa-plus"></i>&nbsp;&nbsp;Add More</button>
            </span>
            <span class="col-md-8 inventory-field-btn-right">
                <button type="submit" class="btn btn-flat so-btn">Save</button>
            </span>
         </div>
         </form>
      </div>
   </div>
</div>

<!--Edit SKU Modal -->
<div class="modal fade" id="editSkuModal" tabindex="-1" role="dialog" aria-labelledby="editSkuModalLabel" data-backdrop="static" data-keyboard="false">
   <div class="modal-dialog" role="document">
      <div class="modal-content">
      <form method="POST" action="{{ route('skus_update') }}">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="editSkuModalLabel">Edit Sku</h4>
         </div>
         <div class="modal-body">
            @csrf
            <fieldset>
                <div class="form-group" >
                    <div class="col-md-8 col-md-offset-2 inventory-field-input-div">
                        <input type="hidden" name="edit_sku_id" id="edit_sku_id"/>
                        <input name="edit_sku_name" id="edit_sku_name" type="text" placeholder="Sku" class="form-control input-md add-location-field-input" required>
                    </div>
                </div>
            </fieldset>
         </div>
         <div class="modal-footer">
            <span class="col-md-12 inventory-field-btn-right">
                <button type="button" class="btn btn-flat so-btn-close" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-flat so-btn">Save</button>
            </span>
         </div>
         </form>
      </div>
   </div>
</div>

@stop
@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
    <link rel="stylesheet" href="/css/settings.css">
    <link rel="stylesheet" href="/css/spectrumoversee.tables.css">
@stop

@section('js')
<script src="{{ asset('js/jquery/settings.sku.jquery.js') }}?v={{preg_replace('/[^0-9]/', '', \Tremby\LaravelGitVersion\GitVersionHelper::getNameAndVersion())}}" ></script>
<script type="text/javascript" src="/js/jquery/topbarsearch.jquery.js?v={{preg_replace('/[^0-9]/', '', \Tremby\LaravelGitVersion\GitVersionHelper::getNameAndVersion())}}"></script>
@stop
