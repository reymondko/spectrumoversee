@extends('adminlte::page')



@section('title', 'Inventory Fields')



@section('content_header')
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <h1 class="header-text">
        <a href=" {{ route('settings') }} " >SETTINGS</a>
        <i class="fa fa-chevron-right"></i>
        INVENTORY FIELDS
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
@elseif(session('status') == 'error_saving')
    <div class="alert alert-info alert-dismissible alert-error">
        <button type="button" class="close alert-close-btn" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-warning"></i>Error Saving Data!</h4>
    </div>
@endif

<div class="container-fluid account-settings-container">
  <div class="row">
  <div class="col-md-6 col-md-offset-2">
        <form method="POST" action="{{ route('inventory_fields_save') }}">
            @csrf
            <div class="box box-solid box-primary account-settings-box">
                <div class="box-header account-settings-box-header">
                    <h3 class="box-title">Inventory Fields</h3>
                </div><!-- /.box-header -->
                <div class="box-body">

                     <div class="modal-body">
                <table class="col-md-12 table table-striped table-bordered" id="custom_field_table">

                <thead class="table_head">
                    <th class="table_head_th col-md-8">Custom Field</th>
                    <th class="table_head_th col-md-1">Is Barcode?</th>
                    <th class="table_head_th col-md-1">Action</th>
                </thead>
                <tbody>
                    @foreach($data['inventory_fields'] as $i)
                        <tr id="customfield_div{{ $i['field_number'] }}">
                            <td>
                                <div class="form-group" >
                                    <div class="col-md-12 inventory-field-input-div">
                                        <input id="customfield_{{  $i['field_number'] }}"
                                               name="customfield_{{ $i['field_number'] }}"
                                               type="text"
                                               placeholder="Custom Field"
                                               class="form-control input-md custom-field-input"
                                               value="{{ $i['field_name'] }}"
                                               required=""
                                        />
                                    </div>
                                </div>
                            </td>
                            <td class="center-element">
                                <input type="checkbox"
                                       class="form-check-input barcode-box"
                                       name="customfield_checkbox_{{ $i['field_number'] }}"
                                       {{ $i['is_barcode'] == 1 ? 'checked':'' }}
                                />
                            </td>
                            <td>
                                <a href="#" onClick="removeInput({{ $i['field_number'] }})" class="inventory-field-delete"><i class="fa fa-trash"></i></a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>

               </table>
            </div>
            <div class="modal-footer">
                <span class="col-md-4 inventory-field-btn-left">
                    <button id="add-inventory-field" type="button" class="btn btn-flat so-btn"><i class="fa fa-plus"></i>&nbsp;&nbsp;Add Custom Field</button>
                </span>
                <span class="col-md-8 inventory-field-btn-right">
                    <button type="submit" class="btn btn-flat so-btn">Save</button>
                </span>
            </div>
                </div><!-- /.box-body -->
            </div><!-- /.box -->
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

    <script>
    </script>
    <script src="{{ asset('js/jquery/settings.jquery.js') }}?v={{preg_replace('/[^0-9]/', '', \Tremby\LaravelGitVersion\GitVersionHelper::getNameAndVersion())}}" defer></script>
    <script type="text/javascript" src="/js/jquery/topbarsearch.jquery.js?v={{preg_replace('/[^0-9]/', '', \Tremby\LaravelGitVersion\GitVersionHelper::getNameAndVersion())}}"></script>
@stop
