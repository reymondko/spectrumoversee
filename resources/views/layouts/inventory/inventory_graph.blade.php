@extends('adminlte::page')

@section('title', 'Inventory')



@section('content_header')
<!-- CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">
    <h1 class="header-text">INVENTORY</h1>
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
@endif
<div class="box inventory-box">
   <div class="box-header">
      <h3 class="box-title">Inventory results</h3>
      @if(Gate::allows('company-only', auth()->user()) || Gate::allows('can_see_delete_inventory', auth()->user()))
        <span id="delete_selected">
            <button type="button" class="btn btn-default so-btn-danger" data-toggle="modal" data-target="#deleteInventoryModal">
                <i class="fa fa-trash"></i> Delete Selected
            </button>
            </br>
        </span>
      @endif
      </br>
      @if(Gate::allows('company-only', auth()->user()) || Gate::allows('can_see_delete_inventory', auth()->user()))
        <span><a href="#" id="check_all" class="check-links">Check all</a></span>
        <span><a href="#" id="uncheck_all" class="check-links">Uncheck all</a></span>
      @endif
      <!-- /.box-tools -->
   </div>
   <!-- /.box-header -->
   <div class="box-body">
   <table id="inventory_fields_table" class="table table-striped table-bordered" style="width:100%">
         <thead class="table_head">
            <tr>

               <th class="table_head_th">ID</th>
               @if(Gate::allows('company-only', auth()->user()) || Gate::allows('can_see_delete_inventory', auth()->user()))
               <th class="table_head_th"></th>
               @endif
               @if(!in_array('barcode_id',$data['hidden_inventory_fields']))
                    <th class="table_head_th" id="barcode_id">Barcode Number</th>
               @endif

               <th class="table_head_th" id="status">Status</th>
               @if(!in_array('sku',$data['hidden_inventory_fields']))
                    <th class="table_head_th" id="sku">SKU</th>
               @endif

               @foreach($data['inventory_fields'] as $i)
                    @if(!in_array('custom_field'.$i['field_number'],$data['hidden_inventory_fields']))
                        <th id="custom_field{{$i['field_number']}}" class="table_head_th">{{$i['field_name']}}</th>
                    @endif
               @endforeach

               @if(!in_array('created_at',$data['hidden_inventory_fields']))
                    <!--<th class="table_head_th" id="created_at">Created Date</th>-->
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

        </tbody>
      </table>
   </div>
   <!-- /.box-body -->
</div>


<!--Confirm Delete Modal -->
<div class="modal fade" id="deleteInventoryModal" tabindex="-1" role="dialog" aria-labelledby="deleteInventoryModalLabel" data-backdrop="static" data-keyboard="false">
   <div class="modal-dialog scan-modal" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title scan-modal-title" id="deleteInventoryModalLabel">DELETE INVENTORY</h4>
         </div>
         <div class="modal-body">
             <div class="form-group delete-prompt scan-modal-title">
                <h4>Are you sure you want to delete </br><span id="delete_count"></span> item(s)?</h4>
            </div>
            <div class="delete-loader-div">
                <center>
                <div class="loader"></div>
                <h4 id="deleting_text">Deleting</h4>
                </center>
            </div>
            </br>
            <button  type="button" class="btn btn-flat btn-block so-btn-close" id="delete_confirm_btn">
               Yes, Delete
            </button>
            </br>
         </div>
         </form>
      </div>
   </div>
</div>

<!--Update Status Modal -->
<div class="modal fade" id="changeStatusModal" tabindex="-1" role="dialog" aria-labelledby="changeStatusModalLabel" data-backdrop="static" data-keyboard="false">
   <div class="modal-dialog scan-modal" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title scan-modal-title" id="changeStatusModalLabel">UPDATE STATUS</h4>
         </div>
         <div class="modal-body">
             <div class="form-group delete-prompt scan-modal-title" id="update_status_select">
                <select class="form-control col-md-12 filter-select" id="update_status_value">
                    <option value="active">Active</option>
                    <option value="damaged">Damaged</option>
                    <option value="lost">Lost</option>
                </select>
                <input type="hidden" id="update_status_id" />
            </div>
            <div class="updatestatus-loader-div">
                <center>
                <div class="loader"></div>
                <h4 id="update_status_text">Saving</h4>
                </center>
            </div>
            </br>
            <div class="update_status_message">
                <div id="update_status_success">
                    SAVED
                </div>
            </div>
            </br>
            <button  type="button" class="btn btn-flat btn-block so-btn-close" id="update_status_save">
               Update Status
            </button>
            </br>
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
    <link rel="stylesheet" href="/css/jquery-ui.css">
@stop
@section('js')
    <script>
        $('#scan_btn').click(function(){
            $('.scan-loader-div').show();
            $('#scan_btn').hide();
            $('#scan_barcode_no').hide();
            $('#scan_success').hide();
            $('#scan_fail').hide();

            $.post( "{{ route('inventory_scan') }}", { barcode_id: $('#scan_barcode_no').val(), _token: "{{ csrf_token() }}" },function( data ) {
                if(data.success){
                    $('.scan-loader-div').hide();
                    $('#scan_btn').show();
                    $('#scan_barcode_no').show();
                    $('#scan_success').show();
                    $('#scan_barcode_no').val('');
                    $('#scan_count').html(parseInt($('#scan_count').html()) + 1);
                    $('#item_scans').html(parseInt($('#item_scans').html()) + data.total_items_scanned);
                }else{

                    $('.scan-loader-div').hide();
                    $('#scan_btn').show();
                    $('#scan_barcode_no').show();
                    $('#scan_fail').html(data.message);
                    $('#scan_fail').show();
                    $('#scan_barcode_no').val('');
                }

                $('#scan_barcode_no').focus();
            });
        });

        //delete inventory
        $('#delete_confirm_btn').click(function(){
            $('.delete-loader-div').show();
            $('.delete-prompt').hide();
            $('#delete_confirm_btn').hide();

            var ids = [];

            $('input.inventory-item-check:checked').each(function(i, e) {
                ids.push($(this).val());
            });

            $.ajax({
                url: "{{ route('inventory_delete') }}",
                type: "post",
                data: {inventory_ids:ids, _token: "{{ csrf_token() }}"},
                success: function(data) {
                    if(data.success){
                        $('#deleting_text').html('Deleted successfully');
                        $('.loader').hide();
                        setTimeout(function () {
                            location.reload();
                        }, 2);
                    }
                }
            });
        })

        //filter location values set to global
        @if(isset($data['inventory_locations']))
            var inventory_location_options = '';
            @foreach($data['inventory_locations'] as $l)
                inventory_location_options +="<option value='{{$l['name']}}'>{{$l['name']}}</option>"
            @endforeach
        @endif

        //filter user values set to global
        @if(isset($data['inventory_users']))
            var inventory_user_options = '';
            @foreach($data['inventory_users'] as $i)
                inventory_user_options +="<option value='{{$i['id']}}'>{{$i['name']}}</option>"
            @endforeach
        @endif

        function updateStatus(status,id){
            $('#update_status_success').hide();
            $('#update_status_value').val($(status).data('id'));
            $('#update_status_id').val(id);
        }

        $(document).ready(function() {
            $.fn.dataTable.ext.errMode = 'none';
            var inventory_fields_table =  $('#inventory_fields_table').DataTable({
                processing: true,
                serverSide: true,
                // ajax: '/inventory/data/paginate',
                ajax:'{!!"/inventory/graph/data?type=".$_GET["type"]."&location=".$_GET["location"]!!}',
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
                "columnDefs": [
                    {
                        "targets": [ 0 ],
                        "visible": false
                    }
                ],
                columns: [

                    { data: 'id', name: 'id' },

                    @if(Gate::allows('company-only', auth()->user()) || Gate::allows('can_see_delete_inventory', auth()->user()))
                        {
                            data: 'id',
                            name: 'id',
                            render: function(data,type,row){
                                return `<input type="checkbox" class="form-check-input inventory-item-check" name="inventoryItem[]" value="${data.id}" />`;
                            }
                        },
                    @endif

                    @if(!in_array('barcode_id',$data['hidden_inventory_fields']))
                            {
                                data: 'barcode_id',
                                name: 'barcode_id' ,
                                render: function(data,type,row){
                                    return `<a href="/inventory/details?id=${row.id}" >${data}</a>`;
                                }
                            },
                    @endif

                    {
                        data: 'status',
                        name: 'status',
                        render: function(data,type,row){
                            return `<a href="#" class="inv-status-row" data-toggle="modal" data-target="#changeStatusModal" id="${row.id}_status" data-id="${data}" onClick="updateStatus(this,${row.id})">${data}</a>`;
                        }
                    },

                    @if(!in_array('sku',$data['hidden_inventory_fields']))
                        { data: 'sku',  name: 'sku' },
                    @endif



                    @foreach($data['inventory_fields'] as $i)
                            @if(!in_array('custom_field'.$i['field_number'],$data['hidden_inventory_fields']))
                                { data: 'custom_field{{$i['field_number']}}', name: 'custom_field{{$i['field_number']}}' },
                            @endif
                    @endforeach

                    @if(!in_array('last_scan_date',$data['hidden_inventory_fields']))
                                {
                                    data: 'last_scan_date',
                                    name: 'last_scan_date',
                                    render: function(data,type,row){
                                        return moment(data).format('LLL');
                                    }
                                 },
                    @endif

                    @if(!in_array('last_scan_location',$data['hidden_inventory_fields']))
                                { data: 'last_scan_location', name: 'last_scan_location' },
                    @endif

                    @if(!in_array('last_scan_by',$data['hidden_inventory_fields']))
                                { data: 'latest_scan.user.name', name: 'last_scan_by' },
                    @endif
                ],
                "order": [[ 0, "desc" ]],
                "pageLength": 75
            });
        });

    </script>
    <script src="{{ asset('js/jquery/inventory.jquery.js') }}?v={{preg_replace('/[^0-9]/', '', \Tremby\LaravelGitVersion\GitVersionHelper::getNameAndVersion())}}" defer></script>
    <script src="{{ asset('js/jquery-ui.js') }}" defer></script>
    <script src="{{ asset('js/moment.js') }}" defer></script>
    <script type="text/javascript" src="/js/jquery/topbarsearch.jquery.js?v={{preg_replace('/[^0-9]/', '', \Tremby\LaravelGitVersion\GitVersionHelper::getNameAndVersion())}}"></script>
@stop
