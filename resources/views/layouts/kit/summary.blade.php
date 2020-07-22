@extends('adminlte::page')


@section('title', 'Kit Return Sync Report')



@section('content_header')
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <h1 class="header-text">Kit Return Sync Report</h1>
    <!--<button type="button" id="topbar_search" class="search-bar-btn btn btn-default so-btn">
        <i class="fa fa-search"></i> Search
    </button>
    <input type="text" class="search-bar col-md-2" id="global_search" aria-describedby="search" placeholder="Search">-->
@stop


@section('content')
<div class="box user-box">
   <div class="box-header">
      <h3 class="box-title"></h3>
      <div class="box-tools pull-right">

      </div>
      <!-- /.box-tools -->
   </div>
   <!-- /.box-header -->
   <div class="box-body">
      <table id="kit_summary_table" class="table table-striped table-bordered" style="width:100%">
         <thead class="table_head">
            <tr>
               <th class="table_head_th">Batch ID</th>
               <th class="table_head_th">Exp. Date</th>
               <th class="table_head_th">Batch Date</th>
               <th class="table_head_th">SKU</th>
               <th class="table_head_th">Company</th>
               <th class="table_head_th"># Master Kits</th>
               <th class="table_head_th"># Subkits</th>
               <th class="table_head_th"># Boxes</th>
               <th class="table_head_th">Status</th>
               <th class="table_head_th">Receiver #</th>
               <th class="table_head_th">View Details</th>
               <th class="table_head_th">Export</th>
            </tr>
         </thead>
         <tbody>
           @foreach($data as $row)
             <tr>
               <td>{{ $row->batch_number }}</td>
               <td>{{ $row->expiration_date ? date('Y-m-d', strtotime($row->expiration_date)) : "N/A" }}</td>
               <td>{{ $row->created_at }}</td>
               <td>{{ $row->sku }}</td>
               <td>{{ $row->company_name }}</td>
               <td class="text-center">{{ $row->master_kits }}</td>
               <td class="text-center">{{ $row->kits }}</td>
               <td class="text-center">{{ $row->boxes }}</td>
               <td class="text-center">{{ $row->batch_status }}</td>
               <td class="text-center">{{ $row->receiver_id }}</td>
               <td class="text-center"><a href="/kit-return-sync/summary/{{ $row->id }}">Details</a></td>
               <td class="text-center"><a href="/kit-return-sync/summary/{{ $row->id }}/export">Export</a></td>
             </tr>
           @endforeach
         </tbody>
      </table>
   </div>
   <!-- /.box-body -->
</div>
<!-- /.box -->

@stop
@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
    <link rel="stylesheet" href="/css/users.css">
    <link rel="stylesheet" href="/css/spectrumoversee.tables.css">
@stop

@section('js')
<script src="{{ asset('js/jquery/users.jquery.js') }}?{{preg_replace('/[^0-9]/', '', \Tremby\LaravelGitVersion\GitVersionHelper::getNameAndVersion())}}" defer></script>
<script type="text/javascript" src="/js/jquery/topbarsearch.jquery.js?{{preg_replace('/[^0-9]/', '', \Tremby\LaravelGitVersion\GitVersionHelper::getNameAndVersion())}}"></script>
@stop
