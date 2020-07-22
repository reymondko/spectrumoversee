@extends('adminlte::page')



@section('title', 'Custom Orders')



@section('content_header')
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <h1 class="header-text">
        <a href=" {{ route('settings') }} " >SETTINGS</a>
        <i class="fa fa-chevron-right"></i>
        CUSTOM ORDERS
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
@elseif(session('status') == 'deleted')
<div class="alert alert-info alert-dismissible alert-saved">
    <button type="button" class="close alert-close-btn" data-dismiss="alert" aria-hidden="true">×</button>
    <h4><i class="icon fa fa-check"></i>Deleted Successfully!</h4>
</div>
@endif
</br></br>
<div class="box inventory-box">
   <div class="box-header">
      <h3 class="box-title">Custom Order Pages</h3>
      </br>
      <div class="box-tools pull-right">
         <button type="button" class="btn btn-default so-btn" data-toggle="modal" data-target="#addOrderModal" >
         <i class="fa fa-plus"></i> Add Custom Order Page
         </button>
      </div>
      <!-- /.box-tools -->
   </div>
   <!-- /.box-header -->
   <div class="box-body">
      <table id="custom_orders_table" class="table table-striped table-bordered" style="width:100%">
         <thead class="table_head">
            <th>Order Name</th>
            <th>Company Name</th>
            <th>Customer Name</th>
            <th>URL</th>
            <th>Actions</th>
         </thead>
         <tbody>
             @foreach($data['custom_orders'] as $c)
                <tr>
                    <td>{{$c->custom_order_name}}</td>
                    <td>{{$c->company_name}}</td>
                    <td>{{$c->customer_name}}</td>
                    <td><a href="/order/{{$c->url}}">Link</a></td>
                    <td>
                    <div class="btn-group">
                            <button type="button" class="btn btn-flat action-button">Action</button>
                            <button type="button" class="btn btn-flat dropdown-toggle action-button" data-toggle="dropdown" aria-expanded="false">
                                <span class="caret"></span>
                                <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu" role="menu">
                                <li><a class="action-list-a"  href="{{route('custom_orders_settings_delete',['id' => $c->id])}}">Delete</a></li>
                                <li><a class="action-list-a" href="#" onClick="editCustomOrder({{$c->id}})" data-toggle="modal" data-target="#editOrderModal">Edit</a></li>
                            </ul>
                        </div>
                    </td>
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
         <form method="POST" action="{{route('custom_orders_settings_save')}}">
            @csrf
            <div class="modal-header">
               <h4 class="modal-title" id="addOrderModalLabel">Add Custom Order</h4>
            </div>
            <div class="modal-body">
               <fieldset id="add_order_fieldset">
                  <div class="form-group section-label">
                     Custom Page Details:
                  </div>
                  <div class="form-group">
                     <div class="col-md-10 col-md-offset-1">
                        <input id="title" name="title" type="text" placeholder="Custom Order Page Title" class="form-control input-md" required>
                     </div>
                  </div>
                  <div class="form-group">
                     <div class="col-md-10 col-md-offset-1">
                        <input id="company_name" name="company_name" type="text" placeholder="Company Name" class="form-control input-md" required>

                    </br>
                     </div>
                  </div>
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
                        </br>
                     </div>
                  </div>
                  <div class="form-group section-label">
                    Custom Order Settings:
                  </div>
                  <div class="form-group" id="select_sku_field">
                     <div class="col-md-12">
                        <div class="form-group row">
                           <div class="col-md-4 ">
                                <select class="form-control select-md"  name="item_name[]" required>
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
                           <div class="col-md-3">
                              <input type="number" class="form-control" id="inputValue" placeholder="Quantities" name="quantities_of[]" required>
                           </div>
                           <div class="col-md-2">
                              <input type="number" class="form-control" id="inputValue" placeholder="Min." name="min[]" required>
                           </div>
                           <div class="col-md-2">
                              <input type="number" class="form-control" id="inputValue" placeholder="Max" name="max[]" required>
                           </div>
                        </div>
                     </div>
                  </div>
                  </br>
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
         <form method="POST" action="{{route('custom_orders_settings_update')}}">
            @csrf
            <div class="modal-header">
               <h4 class="modal-title" id="editOrderModalLabel">Edit Custom Order</h4>
            </div>
            <div class="modal-body">
               <fieldset id="add_order_fieldset">
                  <div class="form-group section-label">
                     Custom Page Details:
                  </div>
                  <div class="form-group">
                     <div class="col-md-10 col-md-offset-1">
                        <input id="edit_title" name="edit_title" type="text" placeholder="Custom Order Page Title" class="form-control input-md" required>
                     </div>
                  </div>
                  <div class="form-group">
                     <div class="col-md-10 col-md-offset-1">
                        <input id="edit_company_name" name="edit_company_name" type="text" placeholder="Company Name" class="form-control input-md" required>

                    </br>
                     </div>
                  </div>
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
                        </br>
                     </div>
                  </div>
                  <div class="form-group section-label">
                    Custom Order Settings:
                  </div>

                  <div class="form-group" id="edit_select_sku_field">

                  </div>
                  </br>
                  </div>
                  <input type="hidden" class="form-control" id="edit_id" name="edit_id">
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
@stop



@section('css')
    <link rel="stylesheet" href="/css/spectrumoversee.tables.css">
    <link rel="stylesheet" href="/css/admin_custom.css">
    <link rel="stylesheet" href="/css/settings_custom_order.css">
@stop

@section('js')

    <script>
        $(document).ready(function() {
            $('#custom_orders_table').DataTable({
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
            "order": [[ 0, "desc" ]],
            "pageLength": 75
            });

            $('#editOrderModal').on('hidden.bs.modal', function () {
                $('.edit-order-settings').html('');
            })
        });

        var select_sku_options = '<option value="">SKU</option>';
        @foreach($data['skus'] as $s)
            select_sku_options +='<option value="{{$s->sku}}">{{$s->sku}}</option>'
        @endforeach

        $('#add_order_item').click(function(){
            let index = $('.item-input:input').length + 1;
            let fieldToAppend = '<div class="form-group" id="itemdiv_'+index+'">';
                fieldToAppend += '<div class="col-md-12">';
                fieldToAppend += '<div class="form-group row">';
                fieldToAppend += '<div class="col-md-4">';
                fieldToAppend += '<select class="form-control select-md"  name="item_name[]" required>';
                fieldToAppend += select_sku_options
                fieldToAppend += '</select>';
                fieldToAppend += '</div>';
                fieldToAppend += '<div class="col-md-3">';
                fieldToAppend += '<input type="number" class="form-control item-input" id="quantity" placeholder="Quantities" name="quantities_of[]" required>';
                fieldToAppend += '</div>';
                fieldToAppend += '<div class="col-md-2">';
                fieldToAppend += '<input type="number" class="form-control item-input" id="quantity" placeholder="Min." name="min[]" required>';
                fieldToAppend += '</div>';
                fieldToAppend += '<div class="col-md-2">';
                fieldToAppend += '<input type="number" class="form-control item-input" id="quantity" placeholder="Max." name="max[]" required>';
                fieldToAppend += '</div>';
                fieldToAppend += '<div class="col-md-1">';
                fieldToAppend += `<a href="#" onClick="removeElement('itemdiv_${index}')" class="order-field-delete"><i class="fa fa-trash"></i></a>`;
                fieldToAppend += '</div>';
                fieldToAppend += '</div>';
                fieldToAppend += '</div>';
                fieldToAppend += '</div>';
                $('#select_sku_field').append(fieldToAppend);
        })

        $('#edit_add_order_item').click(function(){
            let index = $('.item-input-edit:input').length + 1;
            let fieldToAppend = '<div class="form-group edit-order-settings"  id="edititemdiv_'+index+'">';
                fieldToAppend += '<div class="col-md-12">';
                fieldToAppend += '<div class="form-group row">';
                fieldToAppend += '<div class="col-md-4">';
                fieldToAppend += '<select class="form-control select-md" id="edit_item_name_'+index+'"  name="edit_item_name[]" required>';
                fieldToAppend += select_sku_options
                fieldToAppend += '</select>';
                fieldToAppend += '</div>';
                fieldToAppend += '<div class="col-md-3">';
                fieldToAppend += '<input type="number" class="form-control item-input-edit" id="quantity" placeholder="Quantities" name="edit_quantities_of[]" required>';
                fieldToAppend += '</div>';
                fieldToAppend += '<div class="col-md-2">';
                fieldToAppend += '<input type="number"  class="form-control item-input-edit" id="quantity" placeholder="Min." name="edit_min[]" required>';
                fieldToAppend += '</div>';
                fieldToAppend += '<div class="col-md-2">';
                fieldToAppend += '<input type="number" class="form-control item-input-edit" id="quantity" placeholder="Max." name="edit_max[]" required>';
                fieldToAppend += '</div>';
                fieldToAppend += '<div class="col-md-1">';
                fieldToAppend += `<a href="#" onClick="removeElement('edititemdiv_${index}')" class="order-field-delete"><i class="fa fa-trash"></i></a>`;
                fieldToAppend += '</div>';
                fieldToAppend += '</div>';
                fieldToAppend += '</div>';
                fieldToAppend += '</div>';
                $('#edit_select_sku_field').append(fieldToAppend);
        })


        function removeElement(element_id){
            $('#'+element_id).remove();
        }

        function editCustomOrder(id){
            $('#edit_id').val(id);
            $.ajax({
                url: "{{ route('custom_orders_details') }}",
                type: "post",
                data: {id:id, _token: "{{ csrf_token() }}"},
                success: function(data) {
                    if(data.success){
                       var cust = data.result.custom_order;
                       var order_data = cust.order_data;
                      $('#edit_title').val(cust.custom_order_name);
                      $('#edit_company_name').val(cust.company_name);
                      $('#edit_customer_name').val(cust.customer_name);
                      $('#edit_address_1').val(cust.customer_address_1);
                      $('#edit_address_2').val(cust.customer_address_2);
                      $('#edit_city').val(cust.city);
                      $('#edit_zip').val(cust.zip);
                      $('#edit_state').val(cust.state);
                      $('#edit_country').val(cust.country);

                      $(order_data).each(function(index,value){
                        let fieldToAppend = '<div class="form-group edit-order-settings"  id="edititemdiv_'+index+'">';
                            fieldToAppend += '<div class="col-md-12">';
                            fieldToAppend += '<div class="form-group row">';
                            fieldToAppend += '<div class="col-md-4">';
                            fieldToAppend += '<select class="form-control select-md" id="edit_item_name_'+index+'"  name="edit_item_name[]" required>';
                            fieldToAppend += select_sku_options
                            fieldToAppend += '</select>';
                            fieldToAppend += '</div>';
                            fieldToAppend += '<div class="col-md-3">';
                            fieldToAppend += '<input type="number" value="'+value.quantities+'" class="form-control item-input-edit" id="quantity" placeholder="Quantities" name="edit_quantities_of[]" required>';
                            fieldToAppend += '</div>';
                            fieldToAppend += '<div class="col-md-2">';
                            fieldToAppend += '<input type="number" value="'+value.min+'" class="form-control item-input-edit" id="quantity" placeholder="Min." name="edit_min[]" required>';
                            fieldToAppend += '</div>';
                            fieldToAppend += '<div class="col-md-2">';
                            fieldToAppend += '<input type="number" value="'+value.max+'" class="form-control item-input-edit" id="quantity" placeholder="Max." name="edit_max[]" required>';
                            fieldToAppend += '</div>';
                            fieldToAppend += '<div class="col-md-1">';
                            fieldToAppend += `<a href="#" onClick="removeElement('edititemdiv_${index}')" class="order-field-delete"><i class="fa fa-trash"></i></a>`;
                            fieldToAppend += '</div>';
                            fieldToAppend += '</div>';
                            fieldToAppend += '</div>';
                            fieldToAppend += '</div>';
                            $('#edit_select_sku_field').append(fieldToAppend);
                            $('#edit_item_name_'+index).val(value.sku);
                      });

                    }
                }
            });
        }

    </script>
    <script src="{{ asset('js/jquery/settings.jquery.js') }}?v={{preg_replace('/[^0-9]/', '', \Tremby\LaravelGitVersion\GitVersionHelper::getNameAndVersion())}}" defer></script>
    <script type="text/javascript" src="/js/jquery/topbarsearch.jquery.js?v={{preg_replace('/[^0-9]/', '', \Tremby\LaravelGitVersion\GitVersionHelper::getNameAndVersion())}}"></script>
@stop
