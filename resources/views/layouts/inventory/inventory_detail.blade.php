@extends('adminlte::page')

@section('title', 'Inventory')



@section('content_header')
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <h1 class="header-text">
        INVENTORY
        @if(isset($data['barcode_id']))
            <span class="inventory-detail-no">#{{$data['barcode_id']}}</span>
        @endif
    </h1>
    <!--<button type="button" id="topbar_search" class="search-bar-btn btn btn-default so-btn">
        <i class="fa fa-search"></i> Search
    </button>
    <input type="text" class="search-bar col-md-2" id="global_search" aria-describedby="search" placeholder="Search">-->
@stop



@section('content')
<div class="box inventory-box">
   <div class="box-header">
      <h3 class="box-title">Inventory Details</h3></br>
      <div class="box-tools pull-right">
        <a href="#" >
            <!-- <button type="button" class="btn btn-default so-btn" >
                <i class="fa fa-file-excel-o"></i> Export to Excel
            </button> -->
            <button type="button"
                    class="btn btn-default so-btn"
                    data-toggle="modal"
                    data-target="#addNoteModal">
                <i class="fa fa-plus"></i> Add Note
          </button>
        </a>
      </div>
      <!-- /.box-tools -->
   </div>
   <!-- /.box-header -->
   <div class="box-body">
   <div class="row in-detail-row" >
        <div class="col-md-4">
            <div>Barcode ID: <span class="in-detail-span">{{$data['barcode_id']}}</span></div>
        </div>
        <div class="col-md-4">
            <div>SKU: <span class="in-detail-span">{{$data['inventory']['sku']}}</div>
        </div>
        <div class="col-md-4">
            <div>Last Scan by:
                @if(isset($data['inventory_scans']))
                    <span class="in-detail-span">{{$data['inventory_scans'][0]['user']['name']}}</span>
                @else
                    <span class="in-detail-span">N/A</span>
                @endif
            </div>
        </div>
        <div class="col-md-4">
            <div>Last Scan Location:
                @if(isset($data['inventory_scans']))
                    <span class="in-detail-span">{{$data['inventory_scans'][0]['scanned_location']}}</span>
                @else
                    <span class="in-detail-span">N/A</span>
                @endif
            </div>
        </div>
        <div class="col-md-4">
            <div>Order #:
                @if(isset($data['inventory']['reference_number']))
                    <span class="in-detail-span"><a href="/thirdparty/orders/details/{{$data['inventory']['transaction_id']}}">{{$data['inventory']['transaction_id']}}</a></span>
                @else
                    <span class="in-detail-span">N/A</span>
                @endif
            </div>
        </div>
        <div class="col-md-4">
            <div>Outbound Tracking #:
                @if(isset($data['inventory']['tracking_number']))
                    <span class="in-detail-span">{{$data['inventory']['tracking_number']}}</span>
                @else
                    <span class="in-detail-span">N/A</span>
                @endif
            </div>
            <div>Return Tracking #:
                @if(isset($data['inventory']['return_tracking_number']))
                    <span class="in-detail-span">{{$data['inventory']['return_tracking_number']}}</span>
                @else
                    <span class="in-detail-span">N/A</span>
                @endif
            </div>
        </div>
   </div>
   <div class="row in-detail-row" >
        <div class="col-md-4">
            <div>Status:
                <span class="in-detail-span">{{strtoupper($data['inventory']['status'])}}</span>
            </div>
        </div>
       @foreach($data['inventory_fields'] as $i)
        <div class="col-md-4">
            <div>{{$i->field_name}}:
                @if(isset($data['inventory']['custom_field'.$i->field_number]))
                    <span class="in-detail-span">{{$data['inventory']['custom_field'.$i->field_number]}}</span>
                @else
                    <span class="in-detail-span">N/A</span>
                @endif
            </div>
        </div>
       @endforeach
   </div>
</br></br>
    <div class="form-group section-label">
        Inventory Scans
    </div>
   <table id="inventory_scans_table" class="table table-striped table-bordered" style="width:100%">
        <thead class="table_head">
           <th></th>
           <th>Date/Time</th>
           <th>Description</th>
           <th>Scan Type</th>
           <th>Scan Location</th>
           <th>Scanned By</th>
        </thead>
        <tbody>
            @if(isset($data['inventory_scans']))
                @foreach($data['inventory_scans'] as $is)
                    <tr>
                        <td>{{$is->id}}</td>
                        <td>{{date('M d, Y - H:i', strtotime($is->created_at))}}</td>
                        <td>{{($is['description'] ? $is['description']:'N/A')}}</td>
                        <td>{{str_replace('incoming', 'Delivered To', str_replace('outgoing', 'Shipped From', $is['scan_type']))}}</td>
                        <td>{{$is['scanned_location']}}</td>
                        <td>{{$is['user']['name']}}</td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>
    <div class="form-group section-label">
        Inventory Notes
    </div>
    <table id="inventory_notes_table" class="table table-striped table-bordered" style="width:100%">
        <thead class="table_head">
            <th class="table_head_th"></th>
            <th class="table_head_th col-sm-2" id="sku">Date</th>
            <th class="table_head_th col-sm-2" >Note By</th>
            <th class="table_head_th">Note</th>
        </thead>
        <tbody>
             @if(isset($data['inventory_notes']))
                @foreach($data['inventory_notes'] as $i)
                    <tr>
                        <td>{{$i->id}}</td>
                        <td>{{date('M d, Y - H:i', strtotime($i->created_at))}}</td>
                        <td>{{$i->user->name}}</td>
                        <td>{{$i->note}}</td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>
    <div class="form-group section-label">
        Status Logs
    </div>
    <table id="inventory_status_table" class="table table-striped table-bordered" style="width:100%">
        <thead class="table_head">
            <th class="table_head_th"></th>
            <th class="table_head_th col-sm-2" id="sku">Date</th>
            <th class="table_head_th  col-sm-2" >Changed By</th>
            <th class="table_head_th ">Changed To</th>
        </thead>
        <tbody>
            @if(isset($data['inventory_status_logs']))
                @foreach($data['inventory_status_logs'] as $i)
                    <tr>
                        <td>{{$i->id}}</td>
                        <td>{{date('M d, Y - H:i', strtotime($i->created_at))}}</td>
                        <td>{{$i->user->name}}</td>
                        <td>{{strtoupper($i->status)}}</td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>
   </div>
   <!-- /.box-body -->
</div>

<!--Add Note Modal -->
<div id="addNoteModal" class="modal fade bd-example-modal-lg" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title scan-modal-title">ADD NOTE</h4>
            </div>
            <form id="add_note_form" method="POST" action="{{route('add_inventory_note')}}">
            @csrf
            <div class="modal-body ">
                <div class="row">
                    <div class="col-md-12">
                        <textarea class="form-control input-md" id="note" name="note" placeholder="Note" value="" rows="6" required></textarea>
                        <input type="hidden" value="{{$data['inventory']['id']}}" name="inventory_id" />
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="request-help-btn btn btn-default so-btn" >Save</button>
            </div>
            </form>
        </div>
    </div>
</div>
@stop
@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
    <link rel="stylesheet" href="/css/inventory.css">
    <link rel="stylesheet" href="/css/spectrumoversee.tables.css">
@stop
@section('js')
    <script>
    </script>
    <script src="{{ asset('js/jquery-ui.js') }}?v={{preg_replace('/[^0-9]/', '', \Tremby\LaravelGitVersion\GitVersionHelper::getNameAndVersion())}}" ></script>
    <script src="{{ asset('js/jquery/inventory_details.jquery.js') }}?v={{preg_replace('/[^0-9]/', '', \Tremby\LaravelGitVersion\GitVersionHelper::getNameAndVersion())}}" ></script>
    <script type="text/javascript" src="/js/jquery/topbarsearch.jquery.js?v={{preg_replace('/[^0-9]/', '', \Tremby\LaravelGitVersion\GitVersionHelper::getNameAndVersion())}}"></script>
@stop
