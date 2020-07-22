@extends('adminlte::page')
@section('title', 'Orders')
@section('content_header')
<!-- CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">
<h1 class="header-text">
   ORDERS
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
      <h3 class="box-title">Orders List</h3>
      </br>
      <div class="box-tools pull-right">
         @if(Gate::allows('company-only', auth()->user()) || Gate::allows('can_see_add_orders', auth()->user()))
         <button type="button" class="btn btn-default so-btn" data-toggle="modal" data-target="#addOrderModal" >
         <i class="fa fa-plus"></i> Add Orders
         </button>
         @endif
         <a href="#" >
         <button type="button" class="btn btn-default so-btn" >
         <i class="fa fa-file-excel-o"></i> Export to Excel
         </button>
         </a>
      </div>
      <!-- /.box-tools -->
   </div>
   <!-- /.box-header -->
   <div class="box-body">
      <table id="orders_table" class="table table-striped table-bordered" style="width:100%">
         <thead class="table_head">
            <th>id</th>
            <th>Order Date</th>
            <th>Order #</th>
            <th>Customer Name</th>
            <th>Total Items Ordered</th>
            <th>Status</th>
            @if(Gate::allows('company-only', auth()->user()) || Gate::allows('can_see_fulfill_orders', auth()->user()))
                <th>Actions</th>
            @endif
         </thead>
         <tbody>
            @foreach($data['orders'] as $o)
            <tr>
            <td>{{$o->id}}</td>
               <td>{{date('M d, Y - H:i', strtotime($o->created_at))}}</td>
               <td><a href="{{ route('order_details', ['id' => $o->id]) }}" >#{{$o->order_number}}</a></td>
               <td>{{$o->order_by_name}}</td>
               <td>
                  @php($total = 0)
                  @foreach($o->orderItems as $i)
                  @php ($total += $i->quantity)
                  @endforeach
                  {{$total}}
               </td>
               <td>
                  @if($o->status == 0)
                  Received
                  @elseif($o->status == 1)
                  In Process
                  @elseif($o->status == 2)
                  Completed
                  @else
                  N/A
                  @endif
               </td>
               @if(Gate::allows('company-only', auth()->user()) || Gate::allows('can_see_fulfill_orders', auth()->user()))
               <td>
                  <div class="btn-group">
                     <button type="button" class="btn btn-flat action-button">Action</button>
                     <button type="button" class="btn btn-flat dropdown-toggle action-button" data-toggle="dropdown" aria-expanded="false">
                     <span class="caret"></span>
                     <span class="sr-only">Toggle Dropdown</span>
                     </button>
                     <ul class="dropdown-menu" role="menu">
                        <li><a class="action-list-a" href="#" data-toggle="modal" data-target="#fulfillOrderModal" onClick="fulfillOrder({{$o->id}},{{$o->order_number}})">Fullfill Order</a></li>
                        @if($o->status != 2)
                        <li><a class="action-list-a" href="#" data-toggle="modal" data-target="#editOrderModal" onClick="editOrder('{{$o->id}}')">Edit Order</a></li>
                        @endif
                     </ul>
                  </div>
               </td>
               @endif
            </tr>
            @endforeach
         </tbody>
      </table>
   </div>
   <!-- /.box-body -->
</div>
<!--Add Order Modal -->
<div class="modal fade" id="addOrderModal" tabindex="-1" role="dialog" aria-labelledby="addOrderModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-content-box" role="document">
      <div class="modal-content">
         <form method="POST" action="{{ route('orders_save') }}">
            @csrf
            <div class="modal-header">
               <h4 class="modal-title" id="addOrderModalLabel">Add New Order</h4>
            </div>
            <div class="modal-body">
               <fieldset id="add_order_fieldset">
                    @if($data['custom_order_pages'])
                        <div class="form-group section-label">
                            Custom Order:
                        </div>
                        <div class="form-group">
                            <div class="col-md-10 col-md-offset-1">
                                <select class="form-control select-md"  name="custom_orders" id="custom_orders" >
                                        <option value="">
                                            Select Custom Order Data
                                        </option>
                                    @foreach($data['custom_order_pages'] as $key => $value)
                                        <option value="{{$key}}">
                                            {{$value->custom_order_name}}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endif
                  <div class="form-group section-label">
                     Customer Information:
                  </div>
                  <div class="form-group">
                     <div class="col-md-10 col-md-offset-1">
                        <input id="customer_name" name="customer_name" type="text" placeholder="Customer Name" class="form-control input-md" required>
                     </div>
                  </div>
                  <div class="form-group">
                     <div class="col-md-10 col-md-offset-1">
                        <input id="address_1" name="address_1" type="text" placeholder="Address 1" class="form-control input-md" required>
                     </div>
                  </div>
                  <div class="form-group">
                     <div class="col-md-10 col-md-offset-1">
                        <input id="address_2" name="address_2" type="text" placeholder="Address 2" class="form-control input-md">
                     </div>
                  </div>
                  <div class="form-group">
                     <div class="col-md-10 col-md-offset-1">
                        <input id="city" name="city" type="text" placeholder="City" class="form-control input-md" required>
                     </div>
                  </div>
                  <div class="form-group">
                     <div class="col-md-10 col-md-offset-1">
                        <input id="zip" name="zip" type="text" placeholder="Zip" class="form-control input-md" required>
                     </div>
                  </div>
                  <div class="form-group">
                     <div class="col-md-10 col-md-offset-1">
                        <input id="state" name="state" type="text" placeholder="State" class="form-control input-md" required>
                     </div>
                  </div>
                  <div class="form-group">
                     <div class="col-md-10 col-md-offset-1">
                        <input id="country" name="country" type="text" placeholder="Country" class="form-control input-md" required>
                     </div>
                  </div>
                  </br>
                  <div class="form-group section-label">
                     Orders:
                  </div>
                  <div class="form-group" id="select_sku_field">
                     <div class="col-md-12">
                        <div class="form-group row">
                           <div class="col-md-5 col-md-offset-1">
                                <select class="form-control select-md co-item-init"  name="item_name[]" required>
                                        <option value="">
                                            SKU
                                        </option>
                                    @foreach($data['skus'] as $s)
                                        <option value="{{$s->sku}}">
                                            {{$s->sku}}
                                        </option>
                                    @endforeach
                                </select>
                           </div>
                           <div class="col-md-4">
                              <input type="number" class="form-control" id="inputValue" placeholder="Quantity" name="item_quantity[]" required>
                           </div>
                        </div>
                     </div>
                  </div>
               </fieldset>
            </div>
            <div class="modal-footer">
               <span class="col-md-2 inventory-field-btn-left">
               <button id="add_order_item" type="button" class="btn btn-flat so-btn"><i class="fa fa-plus"></i>&nbsp;&nbsp;Add Items</button>
               </span>
               <span class="col-md-10 inventory-field-btn-right">
               <button type="submit" class="btn btn-flat so-btn">Save</button>
               </span>
            </div>
         </form>
      </div>
   </div>
</div>


<!--Edit Order Modal -->
<div class="modal fade" id="editOrderModal" tabindex="-1" role="dialog" aria-labelledby="editOrderModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-content-box" role="document">
      <div class="modal-content">
         <form method="POST" action="{{ route('orders_update') }}">
            @csrf
            <div class="modal-header">
               <h4 class="modal-title" id="editOrderModalLabel">Edit Order</h4>
            </div>
            <div class="modal-body">
                <input id="edit_id" name="edit_id" type="hidden" placeholder="Customer Name" class="form-control input-md" required>
               <fieldset id="edit_order_fieldset">
                  <div class="form-group section-label">
                     Customer Information:
                  </div>
                  <div class="form-group">
                     <div class="col-md-10 col-md-offset-1">
                        <input id="edit_customer_name" name="edit_customer_name" type="text" placeholder="Customer Name" class="form-control input-md" required>
                     </div>
                  </div>
                  <div class="form-group">
                     <div class="col-md-10 col-md-offset-1">
                        <input id="edit_address_1" name="edit_address_1" type="text" placeholder="Address 1" class="form-control input-md" required>
                     </div>
                  </div>
                  <div class="form-group">
                     <div class="col-md-10 col-md-offset-1">
                        <input id="edit_address_2" name="edit_address_2" type="text" placeholder="Address 2" class="form-control input-md">
                     </div>
                  </div>
                  <div class="form-group">
                     <div class="col-md-10 col-md-offset-1">
                        <input id="edit_city" name="edit_city" type="text" placeholder="City" class="form-control input-md" required>
                     </div>
                  </div>
                  <div class="form-group">
                     <div class="col-md-10 col-md-offset-1">
                        <input id="edit_zip" name="edit_zip" type="text" placeholder="Zip" class="form-control input-md" required>
                     </div>
                  </div>
                  <div class="form-group">
                     <div class="col-md-10 col-md-offset-1">
                        <input id="edit_state" name="edit_state" type="text" placeholder="State" class="form-control input-md" required>
                     </div>
                  </div>
                  <div class="form-group">
                     <div class="col-md-10 col-md-offset-1">
                        <input id="edit_country" name="edit_country" type="text" placeholder="Country" class="form-control input-md" required>
                     </div>
                  </div>
                  </br>
                  <div class="form-group section-label">
                     Orders:
                  </div>
                  <div class="form-group" id="edit_select_sku_field">

                  </div>
               </fieldset>
            </div>
            <div class="modal-footer">
               <span class="col-md-2 inventory-field-btn-left">
               <button id="edit_add_order_item" type="button" class="btn btn-flat so-btn"><i class="fa fa-plus"></i>&nbsp;&nbsp;Add Items</button>
               </span>
               <span class="col-md-10 inventory-field-btn-right">
               <button type="submit" class="btn btn-flat so-btn">Save</button>
               </span>
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
    var select_sku_options = '<option value="">SKU</option>';

    @foreach($data['skus'] as $s)
        select_sku_options +='<option value="{{$s->sku}}">{{$s->sku}}</option>'
    @endforeach

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

    @if($data["custom_order_pages"])
        $('#custom_orders').change(function(){
            $('.co-item').remove();
            $('#customer_name').val(custom_order_data[$('#custom_orders').val()].customer_name);
            $('#address_1').val(custom_order_data[$('#custom_orders').val()].customer_address_1);
            $('#address_2').val(custom_order_data[$('#custom_orders').val()].customer_address_2);
            $('#city').val(custom_order_data[$('#custom_orders').val()].city);
            $('#zip').val(custom_order_data[$('#custom_orders').val()].zip);
            $('#state').val(custom_order_data[$('#custom_orders').val()].state);
            $('#country').val(custom_order_data[$('#custom_orders').val()].country);

            var orderData = JSON.parse(custom_order_data[$('#custom_orders').val()].order_data);

            if(Object.keys(orderData).length){
                var ctr = 0;
                $.each(orderData,function(index,value){
                    if(ctr == 0){
                        $('.co-item-init').val(index);
                    }else{
                        let fieldToAppend = '<div class="form-group co-item" id="itemdiv_'+index+'">';
                        fieldToAppend += '<div class="col-md-12">';
                        fieldToAppend += '<div class="form-group row">';
                        fieldToAppend += '<div class="col-md-5 col-md-offset-1">';
                        fieldToAppend += '<select class="form-control select-md" id="sku_select'+index+'"  name="item_name[]" required>';
                        fieldToAppend += select_sku_options
                        fieldToAppend += '</select>';
                        fieldToAppend += '</div>';
                        fieldToAppend += '<div class="col-md-4">';
                        fieldToAppend += '<input type="number" class="form-control item-input" id="quantity" value="'+value.quantity+'" placeholder="Quantity" name="item_quantity[]" required>';
                        fieldToAppend += '</div>';
                        fieldToAppend += '<div class="col-md-2">';
                        fieldToAppend += `<a href="#" onClick="removeElement('itemdiv_${index}')" class="order-field-delete"><i class="fa fa-trash"></i></a>`;
                        fieldToAppend += '</div>';
                        fieldToAppend += '</div>';
                        fieldToAppend += '</div>';
                        fieldToAppend += '</div>';
                        $('#select_sku_field').append(fieldToAppend);
                        $('#sku_select'+index).val(index);
                    }
                    ctr++;
            });
            }
        });
    @endif
</script>
<script src="{{ asset('js/jquery/orders.jquery.js') }}?v={{preg_replace('/[^0-9]/', '', \Tremby\LaravelGitVersion\GitVersionHelper::getNameAndVersion())}}" defer></script>
<script type="text/javascript" src="/js/jquery/topbarsearch.jquery.js?v={{preg_replace('/[^0-9]/', '', \Tremby\LaravelGitVersion\GitVersionHelper::getNameAndVersion())}}"></script>
<script>
    @if($data["custom_order_pages"])
        var custom_order_data = {!! $data['custom_order_pages'] !!}
    @endif
</script>
@stop
