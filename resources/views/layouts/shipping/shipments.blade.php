@extends('adminlte::page')

@section('title', 'Shipments')

@section('content_header')
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <h1 class="header-text">SHIPMENTS</h1>
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

@elseif(session('status') == 'deleted')
    <div class="alert alert-info alert-dismissible alert-saved">
        <button type="button" class="close alert-close-btn" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-check"></i>Deleted!</h4>
    </div>
@endif
<div class="container-fluid account-settings-container">
   <div class="row">
      <div class="box inventory-box">
         <div class="box-header">
            <!-- <div class="box-tools pull-right">
               <button type="button" class="btn btn-default so-btn" data-toggle="modal" data-target="#addRuleModal" >
               <i class="fa fa-plus"></i> Add Rule
               </button>
            </div> -->
            <!-- /.box-tools -->
         </div>
         <!-- /.box-header -->
         <div class="box-body">
            </br>
            </br>
            <table id="shipments_table" class="table table-striped table-bordered" style="width:100%">
               <thead class="table_head">
                  @if(Gate::allows('admin-only', auth()->user()))
                  <th class="table_head_th">Company</th>
                  @endif
                  <th class="table_head_th">Shipment ID</th>
                  <th class="table_head_th">Shipper</th>
                  <th class="table_head_th">Shipper Address</th>
                  <th class="table_head_th">Receiver</th>
                  <th class="table_head_th">Receiver Address</th>
                  <th class="table_head_th">Package Type</th>
                  <th class="table_head_th">Insurance</th>
                  <th class="table_head_th">Date Created</th>
               </thead>
               <tbody>
                   @foreach($data['shipments'] as $s)
                    <tr>
                        @if(Gate::allows('admin-only', auth()->user()))
                        <td class="table_head_th">{{$s->company->company_name}}</td>
                        @endif
                        <td>{{$s->id}}</td>
                        <td>{{$s->shipperAddress->FirstName.' '.$s->shipperAddress->LastName}}</td>
                        <td>{{$s->shipperAddress->Address1.', '.$s->shipperAddress->City.', '.$s->shipperAddress->State.', '.$s->shipperAddress->Country}}</td>
                        <td>{{$s->deliveryAddress->FirstName.' '.$s->deliveryAddress->LastName}}</td>
                        <td>{{$s->deliveryAddress->Address1.', '.$s->deliveryAddress->City.', '.$s->deliveryAddress->State.', '.$s->deliveryAddress->Country}}</td>
                        <td>{{$s->package_type}}</td>
                        <td>{{$s->insurance_amount}}</td>
                        <td>{{date('M d, Y - H:i', strtotime($s->created_at))}}</td>
                    </tr>
                   @endforeach
               </tbody>
            </table>
         </div>
         <!-- /.box-body -->
      </div>
   </div>
   <!-- /.box-body -->
</div>
</div>
</div>

@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
    <link rel="stylesheet" href="/css/spectrumoversee.tables.css">
    <link rel="stylesheet" href="/css/shipping.css">
@stop



@section('js')
    <script type="text/javascript" src="/js/jquery/topbarsearch.jquery.js?v={{preg_replace('/[^0-9]/', '', \Tremby\LaravelGitVersion\GitVersionHelper::getNameAndVersion())}}"></script>
    <script type="text/javascript" src="/js/jquery/shipping.jquery.js?v={{preg_replace('/[^0-9]/', '', \Tremby\LaravelGitVersion\GitVersionHelper::getNameAndVersion())}}"></script>
@stop
