@extends('adminlte::page')

@section('title', 'Inventory')



@section('content_header')
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <h1 class="header-text">SEARCH</h1>
    <!--<button type="button" id="topbar_search" class="search-bar-btn btn btn-default so-btn">
        <i class="fa fa-search"></i> Search
    </button>
    <input type="text" class="search-bar col-md-2" id="global_search" aria-describedby="search" placeholder="Search">-->
@stop
@section('content')
<div class="box inventory-box">
   <div class="box-header">
      <h3 class="box-title">Search results</h3></br>
      <!-- /.box-tools -->
   </div>
   <!-- /.box-header -->
   <div class="box-body">
   <table id="inventory_fields_table" class="table table-striped table-bordered" style="width:100%">
         <thead class="table_head">
            <tr>

               <th class="table_head_th">ID</th>

               @if(!in_array('sku',$data['hidden_inventory_fields']))
                    <th class="table_head_th" id="sku">SKU</th>
               @endif

               @if(!in_array('barcode_id',$data['hidden_inventory_fields']))
                    <th class="table_head_th" id="barcode_id">Barcode Number</th>
               @endif

               @foreach($data['inventory_fields'] as $i)
                    @if(!in_array('custom_field'.$i['field_number'],$data['hidden_inventory_fields']))
                        <th id="custom_field{{$i['field_number']}}" class="table_head_th">{{$i['field_name']}}</th>
                    @endif
               @endforeach

               @if(!in_array('created_at',$data['hidden_inventory_fields']))
                    <th class="table_head_th" id="created_at">Created Date</th>
               @endif

               @if(!in_array('last_scan_date',$data['hidden_inventory_fields']))
                    <th class="table_head_th" id="last_scan_date">Last Scan Date</th>
               @endif

               @if(!in_array('last_scan_location',$data['hidden_inventory_fields']))
                    <th class="table_head_th" id="last_scan_location">Last Scan Location</th>
               @endif

               @if(!in_array('last_scan_by',$data['hidden_inventory_fields']))
                    <th class="table_head_th" id="last_scan_by">Last Scanned By</th>
               @endif
            </tr>
         </thead>
         <tbody>
               @foreach($data['inventory_values'] as $iv)
                    <tr>
                        <td>{{$iv['id']}}</td>
                        @if(!in_array('sku',$data['hidden_inventory_fields']))
                            <td>{{$iv['sku']}}</td>
                        @endif
                        @if(!in_array('barcode_id',$data['hidden_inventory_fields']))
                            <td>
                                <a href="{{ route('inventory_detail',['id' => $iv['id']]) }}" >
                                    {{$iv['barcode_id']}}
                                </a>
                            </td>
                        @endif
                        @foreach($data['inventory_fields'] as $if)
                            @if(!in_array('custom_field'.$if['field_number'],$data['hidden_inventory_fields']))
                                <td>
                                    {{ $iv['custom_field'.$if['field_number']] }}
                                </td>
                            @endif
                        @endforeach
                        @if(!in_array('created_at',$data['hidden_inventory_fields']))
                            <td>{{date('M d, Y - H:i', strtotime($iv['created_at']))}}</td>
                        @endif
                        @if(!in_array('last_scan_date',$data['hidden_inventory_fields']))
                            <td>{{ isset($iv['latestScan']) ? date('M d, Y - H:i', strtotime($iv['latestScan']['created_at'])) : 'N/A' }}</td>
                        @endif
                        @if(!in_array('last_scan_location',$data['hidden_inventory_fields']))
                            <td>{{ isset($iv['latestScan']) ? $iv['latestScan']['scanned_location'] : 'N/A' }}</td>
                        @endif
                        @if(!in_array('last_scan_by',$data['hidden_inventory_fields']))
                            <td>{{ isset($iv['latestScan']) ? $iv['latestScan']['user']['name'] : 'N/A' }}</td>
                        @endif
                    </tr>
               @endforeach
        </tbody>
      </table>
   </div>
   <!-- /.box-body -->
</div>
@stop
@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
    <link rel="stylesheet" href="/css/inventory.css">
    <link rel="stylesheet" href="/css/spectrumoversee.tables.css">
@stop
@section('js')
    <script src="{{ asset('js/jquery-ui.js') }}" defer></script>
    <script type="text/javascript" src="/js/jquery/topbarsearch.jquery.js?v={{preg_replace('/[^0-9]/', '', \Tremby\LaravelGitVersion\GitVersionHelper::getNameAndVersion())}}"></script>
@stop
