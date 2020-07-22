@extends('adminlte::page')
@section('title', 'Orders')
@section('content_header')
<!-- CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">
<h1 class="header-text">
<h1 class="header-text">
        <a href=" {{ route('orders') }} " >ORDERS</a>
        <i class="fa fa-chevron-right"></i>
        DETAILS <i class="fa fa-chevron-right"></i> ORDER #{{$data['order']['order_number']}}

    </h1>
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
<div class="box inventory-box">
   <div class="box-header">
      <h3 class="box-title">Customer - {{$data['order']['order_by_name']}}</h3>
      </br>
      <div class="box-tools pull-right">
          @if($data['order']['status'] != 2)
            <button type="button"
                    class="btn btn-default so-btn"
                    id="add-filter"
                    onClick="fulfillOrder({{$data['order']['id']}},{{$data['order']['order_number']}})"
                    data-toggle="modal"
                    data-target="#fulfillOrderModal">
                <i class="fa fa-check"></i> Fulfill Order
            </button>
          @endif
          <button type="button"
                    class="btn btn-default so-btn"
                    data-toggle="modal"
                    data-target="#addNoteModal">
                <i class="fa fa-plus"></i> Add Note
            </button>
      </div>
      <!-- /.box-tools -->
   </div>
   <!-- /.box-header -->
   <div class="box-body">
        <div class="col-md-6 order-details-div" >
            <div>Order Address: </div>
            <div class="order-details">{{$data['order']['address_1']}}</div>
            <div class="order-details">{{$data['order']['address_2']}}</div>
            <div class="order-details">{{$data['order']['city']}}</div>
            <div class="order-details">{{$data['order']['state']}}</div>
            <div class="order-details">{{$data['order']['zip']}}</div>
            <div class="order-details">{{$data['order']['country']}}</div>
        </div>
        <div class="col-md-6 order-details-div">
            Order Date: <span class="order-details">{{date('M d, Y - H:i', strtotime($data['order']['created_at']))}}</span>
        </div>
        <div class="clear-fix">
        </div>
        <div class="col-md-12">
            <div class="col-md-12 order-detail-table">
                Line Items
            </div>
            <table class="table table-striped table-bordered order-items-table" style="width:100%">
                <thead class="table_head">
                    <th class="table_head_th" id="sku">SKU</th>
                    <th class="table_head_th" id="quantity">Quantity</th>
                </thead>
                <tbody>
                    @php($total = 0)
                    @foreach($data['order']['orderItems'] as $o)
                    @php ($total += $o->quantity)
                    <tr>
                        <td>{{$o->sku}}</a></td>
                        <td><a href="#" data-toggle="modal" data-target="#{{$o->id}}LineItemModal">{{$o->quantity}}</a></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="col-md-12">
            <div class="col-md-12 order-detail-table">
                Order Notes
            </div>
            <table class="table table-striped table-bordered order-notes-table" style="width:100%">
                <thead class="table_head">
                    <th class="table_head_th col-sm-2" id="sku">Date</th>
                    <th class="table_head_th col-sm-2" >Note By</th>
                    <th class="table_head_th">Note</th>
                </thead>
                <tbody>
                    @foreach($data['order_notes'] as $o)
                        <tr>
                            <td>{{$o->created_at}}</td>
                            <td>{{$o->user->name}}</td>
                            <td>{{$o->note}}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
   </div>
   <!-- /.box-body -->

   <!-- order item modal -->
   @foreach($data['order']['orderItems'] as $o)
   <div class="modal fade bd-example-modal-lg" id="{{$o->id}}LineItemModal" tabindex="-1" role="dialog" aria-labelledby="{{$o->id}}LineItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content line-item-table-div">
        <div class="modal-header order-items-table-header">
            <h3>{{$o->sku}}</h3>
        </div>
        <table class="table table-striped table-bordered order-items-table" style="width:100%">
                    <thead class="table_head">
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
                        <th class="table_head_th" id="tracking_number">Tracking Number</th>
                    </thead>
                    <tbody>
                        @foreach($o['inventories'] as $iv)
                        <tr>
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

                                <td id="tracking_no_for{{$iv['id']}}">
                                    @if(isset($iv['tracking_number']))
                                        {{$iv['tracking_number']}}
                                    @else
                                        <a href="#" onClick="addTrackingNo({{$iv['id']}},'{{$iv['barcode_id']}}')"/>Add</a>
                                    @endif
                                </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
        </div>
    </div>
    </div>
    @endforeach
   <!-- /.order item modal -->
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
            <form id="add_note_form" method="POST" action="{{route('add_order_note')}}">
            @csrf
            <div class="modal-body ">
                <div class="row">
                    <div class="col-md-12">
                        <textarea class="form-control input-md" id="note" name="note" placeholder="Note" value="" rows="6" required></textarea>
                        <input type="hidden" value="{{$data['order']['id']}}" name="order_id" />
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

<!--Fulfill Order Modal -->
<div class="modal fade" id="fulfillOrderModal" tabindex="-1" role="dialog" aria-labelledby="fulfillOrderModalLabel" data-backdrop="static" data-keyboard="false">
   <div class="modal-dialog scan-modal" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title scan-modal-title" id="fulfillOrderModalLabel">FULFILL ORDER #<span id="fulfill_order_number"></span></h4>
         </div>
         <div class="modal-body">
            <div id="order_fulfilled">
                <h4 class="modal-title scan-modal-title" id="fulfillOrderModalLabel">ORDER HAS BEEN FULFILLED</h4>
            </div>
            <div id="order_unfulfilled">
            <div class="form-group fulfill-count">
               <div class="fulfill-class-text-1">Item to fulfill</div>
               </br>
               <div class="scan-loader-div-init">
                  <center>
                     <div class="loader"></div>
                  </center>
               </div>
               <select class="form-control" id="order_items_select">
                    <option value="">Select Item to fulfill</option>
               </select>
            </div>
            <div class="scan-loader-div-form">
                <center>
                    <div class="loader"></div>
                </center>
            </div>
            <div id="fulfill_form">
               <div class="form-group fulfill-count">
                  <div class="fulfill-class-text-1">Fulfilling</div>
                  <div class="fulfill-class-text-2">
                     <span id="total_fulfilled"></span>
                     Of
                     <span id="total_to_fulfill"></span>
                     </br></br>
                  </div>
                    <div class="form-group">
                        <label for="barcode_no" class="barcode-no-label">Barcode</label>
                        <input type="text" class="form-control" id="scan_barcode_no" placeholder="Type">
                    </div>
                    <div class="form-group">
                        <label for="barcode_no" class="barcode-no-label">Tracking #</label>
                        <input type="text" class="form-control" id="scan_tracking_no" placeholder="Type">
                    </div>
               </div>
               <div class="scan-loader-div">
                  <center>
                     <div class="loader"></div>
                  </center>
               </div>
               <div class="scan-message">
                  <div id="scan_success">
                     ITEM ADDED TO ORDER SUCCESSFULLY
                  </div>
                  <div id="scan_fail">
                     BARCODE NOT FOUND
                  </div>
               </div>
               </br>
               <input type="hidden" id="current_order_number" />
               <input type="hidden" id="current_order_sku" />
               <button  type="button" class="btn btn-flat btn-block so-btn-close" id="scan_btn">
               NEXT
               </button>
               </br>
            </div>
            <div id="fulfill_form_completed">
                <h4 class="modal-title scan-modal-title" id="fulfillOrderModalLabel">ORDER ITEM HAS BEEN FULFILLED</h4>
            </div>
         </div>
         </form>
      </div>
   </div>
</div>


@stop
@section('css')
<link rel="stylesheet" href="/css/admin_custom.css">
<link rel="stylesheet" href="/css/orders.css">
<link rel="stylesheet" href="/css/spectrumoversee.tables.css">
@stop
@section('js')
<script>
    $(document).ready(function() {
    $('.order-items-table').DataTable({
        "sDom": '<"row view-filter"<"col-sm-12"<"pull-left"l><"pull-right"f><"clearfix">>>t<"row view-pager"<"col-sm-12"<"text-center"ip>>>',
        "bLengthChange": false,
        "bFilter": false,
        "bInfo": false,
        "language": {
            "paginate": {
                "previous": '<i class="fa fa-chevron-left paginate-button"></i>',
                "next": '<i class="fa fa-chevron-right paginate-button"></i>',
            }
        },

        "order": [[ 0, "desc" ]]
    });

    $('.order-notes-table').DataTable({
        "sDom": '<"row view-filter"<"col-sm-12"<"pull-left"l><"pull-right"f><"clearfix">>>t<"row view-pager"<"col-sm-12"<"text-center"ip>>>',
        "bLengthChange": false,
        "bFilter": false,
        "bInfo": false,
        "language": {
            "paginate": {
                "previous": '<i class="fa fa-chevron-left paginate-button"></i>',
                "next": '<i class="fa fa-chevron-right paginate-button"></i>',
            }
        },

        "order": [[ 0, "desc" ]]
    });
} );

function addTrackingNo(id,name){
    var trackingNo = prompt("Enter the tracking number for "+name);
    if(trackingNo != ''){
        $.ajax({
            url: "{{ route('update_tracking_number') }}",
            type: "post",
            data: {id:id,tracking_number:trackingNo, _token: "{{ csrf_token() }}"},
            success: function(data) {
                if(data.success){
                    $('#tracking_no_for'+id).html(trackingNo);
                }
            }
        });
    }
}


    function fulfillOrder(id,order_number){
        $('#current_order_number').val(id);
        $('#fulfill_order_number').html(order_number)
        $.post( "{{ route('fulfillment_details') }}", { order_id: id, _token: "{{ csrf_token() }}" },function( data ) {
            if(data.success){
                var order = data.data.orders;
                var order_items = order.order_items
                if(order.status == 2){
                    $('#order_fulfilled').show();
                    $('#order_unfulfilled').hide();
                }else{
                    console.log(order_items);
                    $.each(order_items,function(key,value){
                        let itemToAppend = `<option value="${value.id}">${value.sku}</option>`;
                        $('#order_items_select').append(itemToAppend);
                    });
                    $('#order_items_select').val(order_items[0].id);
                    $("#order_items_select").trigger("change");
                    $('#order_items_select').show();
                    $('.scan-loader-div-init').hide();
                }

            }
        });
    }

    $('#order_items_select').change(function(){
        $('#scan_barcode_no').val('');
        $('#fulfill_form').hide();
        $('#fulfill_form_completed').hide();
        $('.scan-loader-div-form').show();
        if($('#order_items_select').val() != ''){
            $.post( "{{ route('fulfillment_item_details') }}", {
                order_item_id: $('#order_items_select').val(),
                _token: "{{ csrf_token() }}" },
            function( data ) {
                if(data.success){
                    var order_item = data.data.order_items;
                    $('#current_order_sku').val(order_item.sku)
                    if(order_item.inventories.length == order_item.quantity){
                        $('.scan-loader-div-form').hide();
                        $('#fulfill_form_completed').show();
                    }else{
                        $('#total_fulfilled').html(order_item.inventories.length)
                        $('#total_to_fulfill').html(order_item.quantity);
                        $('.scan-loader-div-form').hide();
                        $('#fulfill_form_completed').hide();
                        $('#fulfill_form').show();
                        $('#scan_barcode_no').focus();
                    }

                }
            });
        }
    });


    $('#scan_btn').click(function(){
        $('.scan-loader-div-form').show();
        $('#fulfill_form').hide();

         $.post( "{{ route('fulfillment_item_scan') }}", {
            barcode_id: $('#scan_barcode_no').val(),
            order_id: $('#current_order_number').val(),
            order_item_id: $('#order_items_select').val(),
            tracking_no:$('#scan_tracking_no').val(),
            sku: $('#current_order_sku').val(),
            _token: "{{ csrf_token() }}" },
          function( data ) {
            $('#scan_tracking_no').val('');
            if(data.success){
                var order_item = data.data.order_items;
                var status = data.data.status;
                if(status == 2){
                    $('#order_fulfilled').show();
                    $('#order_unfulfilled').hide();
                }else if(order_item.inventories.length == order_item.quantity){
                        $('#fulfill_form_completed').show();
                        $('.scan-loader-div-form').hide();
                }else{
                    $('#total_fulfilled').html(order_item.inventories.length)
                    $('#total_to_fulfill').html(order_item.quantity);
                    $('#fulfill_form').show();
                    $('#scan_success').show();
                    $('#scan_fail').hide();
                    $('.scan-loader-div-form').hide();
                    $('#scan_barcode_no').val('');
                }

            }else{
                $('.scan-loader-div-form').hide();
                $('#scan_success').hide();
                $('#fulfill_form').show();
                $('#scan_fail').show();
                $('#scan_barcode_no').val('');
            }

            $('#scan_barcode_no').focus();
        });
    });

    $("#fulfillOrderModal").on("hidden.bs.modal", function () {
        window.location.reload();
    });
</script>
<script type="text/javascript" src="/js/jquery/topbarsearch.jquery.js?v={{preg_replace('/[^0-9]/', '', \Tremby\LaravelGitVersion\GitVersionHelper::getNameAndVersion())}}"></script>
<script src="{{ asset('js/jquery/orders.jquery.js') }}?v={{preg_replace('/[^0-9]/', '', \Tremby\LaravelGitVersion\GitVersionHelper::getNameAndVersion())}}" defer></script>
@stop
