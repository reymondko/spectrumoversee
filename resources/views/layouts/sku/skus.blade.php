@extends('adminlte::page')

@section('title', 'Kit Skus')


@section('content_header')
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <h1 class="header-text">Kit Skus</h1>
    <!--<button type="button" id="topbar_search" class="search-bar-btn btn btn-default so-btn">
        <i class="fa fa-search"></i> Search
    </button>
    <input type="text" class="search-bar col-md-2" id="global_search" aria-describedby="search" placeholder="Search">!-->
@stop


@section('content')

@if(session('status') == 'saved')
    <div class="alert alert-info alert-dismissible alert-saved">
        <button type="button" class="close alert-close-btn" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-check"></i>Saved!</h4>
    </div>
@elseif(session('status') == 'deleted')
<div class="alert alert-info alert-dismissible alert-saved">
    <button type="button" class="close alert-close-btn" data-dismiss="alert" aria-hidden="true">×</button>
    <h4><i class="icon fa fa-check"></i>Deleted Successfully!</h4>
</div>
@endif
<div class="box KitSku-box">
    <div class="box-header">
        <h3 class="box-title"></h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-flat add-user-btn" data-toggle="modal" data-target="#addKitSkuModal">
                <i class="fa fa-plus"></i> Add Kit SKU
            </button>
        </div>
        <!-- /.box-tools -->
    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <table id="KitSkus_table" class="table table-striped table-bordered" style="width:100%">
            <thead class="table_head">
                <tr>
                    <th class="table_head_th">ID</th>
                    <th class="table_head_th">Company ID</th>
                    <th class="table_head_th">Sku</th>
                    <th class="table_head_th">Box Limit</th>
                    <th class="table_head_th">Action</th>
                </tr>
            </thead>
            <tbody>
                    @foreach($data['kitskus'] as $sku)
                    <tr>
                        <td>{{$sku->id}}</td>
                        <td>{{$sku->company_name}}</td>
                        <td>{{$sku->sku}}</td>
                        <td>{{$sku->box_limit}}</td>
                        <td>
                            <div class="btn-group">
                                <button type="button" class="btn btn-flat action-button">Action</button>
                                <button type="button" class="btn btn-flat dropdown-toggle action-button" data-toggle="dropdown" aria-expanded="false">
                                    <span class="caret"></span>
                                    <span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <ul class="dropdown-menu" role="menu">
                                    <li>
                                        <a class="action-list-a"
                                            href="#"
                                            data-toggle="modal"
                                            data-target="#editKitSkuModal"
                                    onClick="editKitSku('{{ $sku->id }}','{{ $sku->companies_id }}','{{ $sku->sku }}','{{ $sku->multi_barcode }}','{{ $sku->hs_code }}','{{ $sku->requires_expiration_date }}','{{ $sku->box_limit }}',{{ $sku->multi_barcode_count}}),{{ $sku->bulk_count}}">
                                            Edit Kit Sku
                                        </a>
                                    </li>
                                    @if(Gate::allows('admin-only', auth()->user()))
                                    <li><a class="action-list-a" href="#" onClick="deleteKitSku({{$sku->id}})">Delete</a></li>
                                    @endif
                                </ul>
                            </div>
                        </td>

                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>


<!--Add kitsku Modal -->
<div class="modal fade" id="addKitSkuModal" tabindex="-1" role="dialog" aria-labelledby="addkitskuModallLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                 <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                 <h4 class="modal-title" id="addkitskuModalLabel">Add KIT SKU</h4>
            </div>
            <form id="add_kitsku_form" class="form-horizontal" method="POST" action="{{ route('add_kitsku') }}" >
                @csrf
                <div class="modal-body">
                <fieldset>
                    <!-- Form Name -->
                    <!-- Text input-->
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="company_name">Company</label>
                        <div class="col-md-4">
                        <select class="form-control select-md" data-show-subtext="true" data-live-search="true" id="company" name="company">
                                <option value="0">---</option>
                                @foreach($data['companies'] as $c)
                                    <option value="{{ $c->id }}">{{$c->company_name}}</option>
                                @endforeach
                        </select>
                        </div>
                    </div>
                    <!-- Text input-->
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="sku"></label>
                        <div class="col-md-4">
                            <input id="hs_code" name="sku" type="text" placeholder="SKU" class="form-control input-md" required="">
                        </div>
                    </div>
                    <!--<div class="form-group">
                        <label class="col-md-4 control-label" for="sku"></label>
                        <div class="col-md-4">
                            <input id="sku" name="hs_code" type="text" placeholder="HS Code" class="form-control input-md">
                        </div>
                    </div>!-->
                    
                    <!-- radio Buttons-->
                    <div class="col-md-8 col-md-offset-4 form-check">
                        <input id="multi_barcode" name="multi_barcode" class="form-check-input" type="radio" value="0">
                        <label class="form-check-label" for="multi_barcode">
                            Single Barcode
                        </label>
                    </div>
                    <div class="col-md-8 col-md-offset-4 form-check">
                        <input id="multi_barcode2"  name="multi_barcode" class="form-check-input" type="radio" checked="checked"  value="1" style="display:inline-block">
                        <label class="form-check-label" for="multi_barcode2" style="display:inline-block">
                            Multiple Barcode
                        </label>
                        <input id="multi_barcode_count"  name="multi_barcode_count" class="form-check-input" type="text" style="width:40px;display:inline-block"  value="1">
                        
                    </div>
                    <div class="col-md-8 col-md-offset-4 form-check">
                        <input id="multi_barcode3"  name="multi_barcode" class="form-check-input" type="radio" value="2">
                        <label class="form-check-label" for="multi_barcode3" style="display:inline-block">
                            Bulk
                        </label>
                        <select id="bulk_count"  name="bulk_count" class="form-check-input" type="text" style="width:40px;display:inline-block"  >
                            <option>50</option>
                            <option>200</option>
                        </select>
                    </div>
                    <!-- Button -->
                        <input id="expiration_date"   name="expiration_date" class="form-check-input" type="hidden" value="1">
                    
                    <!--<div class="col-md-8 col-md-offset-4 form-check">
                        <input id="expiration_date" checked  name="expiration_date" class="form-check-input" type="checkbox" value="1">
                        <label class="form-check-label" for="expiration_date">
                            Requires Expiration Date
                        </label>
                    </div>!-->
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="box_limit"></label>
                        <div class="col-md-4">
                            <input id="box_limit" name="box_limit" type="text" placeholder="Box Limit" class="form-control input-md">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="submit"></label>
                    </div>
                </fieldset>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-flat close-btn" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-flat add-user-btn">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!--Edit kitsku Modal -->
<div class="modal fade" id="editKitSkuModal" tabindex="-1" role="dialog" aria-labelledby="editkitskuModallLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="addkitskuModalLabel">Edit KIT SKU</h4>
            </div>
            <form id="edit_kitsku_form" class="form-horizontal" method="POST" action="{{ route('edit_kitsku') }}" >
                @csrf
                <input type="hidden" name="id_edit" id="id_edit" />
                <div class="modal-body">
                <fieldset>
                    <!-- Form Name -->
                    <!-- Text input-->
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="company_name">Company</label>
                        <div class="col-md-4">
                        <select class="form-control select-md" data-show-subtext="true" data-live-search="true" id="company_edit" name="company_edit">
                                <option value="0">---</option>
                                @foreach($data['companies'] as $c)
                                    <option value="{{ $c->id }}">{{$c->company_name}}</option>
                                @endforeach
                        </select>
                        </div>
                    </div>
                    <!-- Text input-->
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="sku"></label>
                        <div class="col-md-4">
                            <input id="sku_edit" name="sku_edit" type="text" placeholder="SKU" class="form-control input-md" required="">
                        </div>
                    </div>
                    <!--<div class="form-group">
                        <label class="col-md-4 control-label" for="sku"></label>
                        <div class="col-md-4">
                            <input id="hs_code_edit" name="hs_code" type="text" placeholder="HS Code" class="form-control input-md">
                        </div>
                    </div>!-->
                    <div class="col-md-8 col-md-offset-4 form-check">
                        <input id="multi_barcode_edit" name="multi_barcode_edit" class="form-check-input" type="radio" value="0">
                        <label class="form-check-label" for="multi_barcode_edit">
                            Single Barcode
                        </label>
                    </div>
                    <div class="col-md-8 col-md-offset-4 form-check">
                        <input id="multi_barcode_edit2"  name="multi_barcode_edit" class="form-check-input" type="radio" value="1">
                        <label class="form-check-label" for="multi_barcode_edit2" style="display:inline-block">
                            Multiple Barcode
                        </label>
                        <input id="multi_barcode_count_edit"  name="multi_barcode_count_edit" class="form-check-input" type="text" style="width:40px;display:inline-block"  value="1">
                    </div>
                    <div class="col-md-8 col-md-offset-4 form-check">
                        <input id="multi_barcode_edit3"  name="multi_barcode_edit" class="form-check-input" type="radio" value="2">
                        <label class="form-check-label" for="multi_barcode_edit3" style="display:inline-block">
                            Bulk
                        </label>
                        <select id="bulk_count_edit"  name="bulk_count_edit" class="form-check-input" type="text" style="width:40px;display:inline-block" >
                            <option>50</option>
                            <option>200</option>
                        </select>
                    </div>
                    <!-- Button -->
                    <input id="expiration_date_edit"  name="expiration_date_edit" class="form-check-input" type="hidden" value="1">
                    <!--<div class="col-md-8 col-md-offset-4 form-check">
                        <input id="expiration_date_edit"  name="expiration_date_edit" class="form-check-input" type="checkbox" value="1">
                        <label class="form-check-label" for="expiration_date_edit">
                            Requires Expiration Date
                        </label>
                    </div>!-->
                    
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="box_limit"></label>
                        <div class="col-md-4">
                            <input id="box_limit_edit" name="box_limit_edit" type="text" placeholder="Box Limit" class="form-control input-md">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="submit"></label>
                    </div>
                </fieldset>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-flat close-btn" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-flat add-user-btn">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>


@stop
@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
    <link rel="stylesheet" href="/css/users.css">
    <link rel="stylesheet" href="/css/spectrumoversee.tables.css">
@stop

@section('js')
<script src="{{ asset('js/jquery/kitsku.jquery.js') }}?{{preg_replace('/[^0-9]/', '', \Tremby\LaravelGitVersion\GitVersionHelper::getNameAndVersion())}}" defer></script>
<script type="text/javascript" src="/js/jquery/topbarsearch.jquery.js?{{preg_replace('/[^0-9]/', '', \Tremby\LaravelGitVersion\GitVersionHelper::getNameAndVersion())}}"></script>
@stop
