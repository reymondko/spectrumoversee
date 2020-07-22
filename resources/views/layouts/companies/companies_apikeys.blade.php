@extends('adminlte::page')



@section('title', 'Companies')



@section('content_header')
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <h1 class="header-text">
        <a href=" {{ route('companies') }} " >COMPANIES</a>
        <i class="fa fa-chevron-right"></i>
        API KEYS
        <i class="fa fa-chevron-right"></i>
        @if(isset($data['companies']))
            {{ $data['companies']['companies_name'] }}
        @endif
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
@elseif(session('status') == 'email_error')
    <div class="alert alert-info alert-dismissible alert-error">
        <button type="button" class="close alert-close-btn" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-warning"></i>Email Already Exists!</h4>
    </div>
@endif
<div class="container">
<br></br></br>
<div class="box companies-box">
   <div class="box-header">
      <h3 class="box-title"></h3>
      <div class="box-tools pull-right">
      <a href="{{ route('generate_apikeys',['companies_id' => $data['companies']['companies_id']]) }}">
        <button type="button" class="btn btn-flat add-company-btn">
            <i class="fa fa-key"></i> Generate Key
        </button>
      </a>
      </div>
      <!-- /.box-tools -->
   </div>
   <!-- /.box-header -->
   <div class="box-body">
        <table id="companies_table" class="table table-striped table-bordered" style="width:100%">
         <thead class="table_head">
            <tr>
               <th class="col-md-10 table_head_th">Key</th>
               <th class="col-md-1 table_head_th">Status</th>
               <th class="col-md-1 table_head_th">Actions</th>
            </tr>
         </thead>
         <tbody>
            @foreach($data['api_keys'] as $k)
                <tr>
                    <td>{{$k->api_token}}</td>
                    <td>{{($k->enabled == 1 ? 'Enabled':'Disabled')}}</td>
                    <td>
                    <a href="{{ route('toggle_apikeys',['id' => $k->id]) }}">
                        @if($k->enabled == 1)
                            <button type="button" class="btn btn-flat so-btn-close">
                                <i class="fa fa-ban" aria-hidden="true"></i> Disable
                            </button>
                        @else
                            <button type="button" class="btn btn-flat so-btn">
                                <i class="fa fa-check" aria-hidden="true"></i> Enable
                            </button>
                        @endif
                    </td>
                    </a>
                </tr>
            @endforeach
         </tbody>
      </table>
   </div>
   <!-- /.box-body -->
</div>
</div>
<!-- /.box -->

@stop
@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
    <link rel="stylesheet" href="/css/spectrumoversee.tables.css">
    <link rel="stylesheet" href="/css/companies.css">
@stop
@section('js')
@stop
