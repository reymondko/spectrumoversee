@extends('adminlte::page')


@section('title', 'Required Case Label Fields')



@section('content_header')
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <h1 class="header-text">
        <a href=" {{ route('settings') }} " >SETTINGS</a>
        <i class="fa fa-chevron-right"></i>
        IMPORT CASE REQUIRED FIELDS
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
      </div>
      <!-- /.box-tools -->
   </div>
   <!-- /.box-header -->
   <div class="box-body">
    <div class="col-md-5 col-md-offset-3">
        <form method="POST" action="{{route('case_label_required_save')}}">
            @csrf
        <table id="import_required_fields" class="table table-striped table-bordered" style="width:100%">
         <thead class="table_head">
            <tr>
               <th class="table_head_th col-md-1">Required</th>
               <th class="table_head_th col-md-2">Case # Field</th>
               <th class="table_head_th ">Field</th>
            </tr>
         </thead>
         <tbody>
           @foreach($data['inventory_fields'] as $i)
            <tr>
                    <td class="sku-toggle-td">
                        @if($i->id != $data['case_number_field'])
                        <input type="checkbox" class="sku-toggle" value="{{$i->id}}" name="required_fields[]" {{in_array($i->id,$data['req_case_labels']) ? 'checked':null}}>
                        @endif
                    </td>
                    <td class="sku-toggle-td">
                    <div class="form-check">
                        <input type="checkbox"  class="sku-toggle case-number-field" value="{{$i->id}}" name="case_number_field"  {{$i->id == $data['case_number_field'] ? 'checked':null}} >
                    </div>
                    </td>
                    <td>{{$i->field_name}}</td>
            </tr>
           @endforeach
         </tbody>
            <button type="submit" class="btn btn-flat so-btn required-field-save" >
                Save
            </button>
        </table>
        </form>
    </div>
   </div>
   <!-- /.box-body -->
</div>
<!-- /.box -->

@stop
@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
    <link rel="stylesheet" href="/css/settings.css">
    <link rel="stylesheet" href="/css/spectrumoversee.tables.css">
@stop

@section('js')
<script src="{{ asset('js/jquery/settings.importcaserequired.jquery.js') }}?v={{preg_replace('/[^0-9]/', '', \Tremby\LaravelGitVersion\GitVersionHelper::getNameAndVersion())}}" ></script>
<script type="text/javascript" src="/js/jquery/topbarsearch.jquery.js?v={{preg_replace('/[^0-9]/', '', \Tremby\LaravelGitVersion\GitVersionHelper::getNameAndVersion())}}"></script>
@stop
