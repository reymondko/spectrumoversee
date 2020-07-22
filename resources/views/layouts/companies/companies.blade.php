@extends('adminlte::page')



@section('title', 'Companies')



@section('content_header')
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <h1 class="header-text">COMPANIES</h1>
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
@elseif(session('status') == 'deleted')
<div class="alert alert-info alert-dismissible alert-saved">
    <button type="button" class="close alert-close-btn" data-dismiss="alert" aria-hidden="true">×</button>
    <h4><i class="icon fa fa-check"></i>Deleted Successfully!</h4>
</div>
@endif
<div class="box companies-box">
   <div class="box-header">
      <h3 class="box-title"></h3>
      <div class="box-tools pull-right">
        <button type="button" class="btn btn-flat add-company-btn" data-toggle="modal" data-target="#myModal">
            <i class="fa fa-plus"></i> Add Company
        </button>
      </div>
      <!-- /.box-tools -->
   </div>
   <!-- /.box-header -->
   <div class="box-body">
      <table id="companies_table" class="table table-striped table-bordered" style="width:100%">
         <thead class="table_head">
            <tr>
               <th class="table_head_th">Company ID</th>
               <th class="table_head_th">Company Name</th>
               <th class="table_head_th">End to End Tracking</th>
               <th class="table_head_th">Actions</th>
            </tr>
         </thead>
         <tbody>
            @foreach($companies as $c)
                <tr>
                    <td>{{$c->id}}</td>
                    <td>{{$c->company_name}}</td>
                        <td>
                            <a class="action-list-a" href="{{ route('toggle_tracking',['company_id' => $c->id]) }}">
                                {{$c->end_to_end_tracking == 0 ? 'Disabled':'Enabled'}}
                            </a>
                        </td>
                    <td>
                    <div class="btn-group">
                            <button type="button" class="btn btn-flat action-button">Action</button>
                            <button type="button" class="btn btn-flat dropdown-toggle action-button" data-toggle="dropdown" aria-expanded="false">
                                <span class="caret"></span>
                                <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu" role="menu">
                                <li><a class="action-list-a" href="{{ route('company_locations',['companies_id' => $c->id]) }}">Manage Locations</a></li>
                                <li><a class="action-list-a" href="{{ route('manage_apikeys',['companies_id' => $c->id]) }}">Manage API keys</a></li>
                                <li><a class="action-list-a" href="#" data-toggle="modal" onClick="getCompanyPermissionDetails({{$c->id}},{{$c->can_manual_fulfill}})" data-target="#manageDepositorIDsModal">Manage Permissions</a></li>
                                <li><a class="action-list-a" href="#" onClick="getCompanyFulfillmentDetails({{$c->id}})" data-toggle="modal" data-target="#manageFulfillmentIdsModal">Manage Fulfillment Ids</a></li>
                                <li><a class="action-list-a" href="#" onClick="getCompanyDepositorID({{$c->id}})" data-toggle="modal" data-target="#manageDepositorIDsModal">Manage Depositor IDs</a></li>
                                <li><a class="action-list-a" href="#" onClick="deleteCompany({{$c->id}})">Delete</a></li>
                            </ul>
                        </div>
                    </td>
                </tr>
            @endforeach
      </table>
   </div>
   <!-- /.box-body -->
</div>
<!-- /.box -->

<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
   <div class="modal-dialog" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="myModalLabel">Add Company</h4>
         </div>
         <form id="add_company_form" class="form-horizontal" method="POST" action="{{ route('add_company') }}" >
            @csrf
            <div class="modal-body">
               <fieldset>
                  <!-- Form Name -->
                  <!-- Text input-->
                  <div class="form-group">
                     <label class="col-md-4 control-label" for="company_name"></label>
                     <div class="col-md-4">
                        <input id="company_name" name="company_name" type="text" placeholder="Company Name" class="form-control input-md" required="">
                     </div>
                  </div>
                  <!-- Text input-->
                  <div class="form-group">
                     <label class="col-md-4 control-label" for="company_name"></label>
                     <div class="col-md-4">
                        <input id="name" name="name" type="text" placeholder="Name" class="form-control input-md" required="">
                     </div>
                  </div>
                  <!-- Text input-->
                  <div class="form-group">
                     <label class="col-md-4 control-label" for="company_name"></label>
                     <div class="col-md-4">
                        <input id="company_admin_location" name="company_admin_location" type="text" placeholder="Company Admin Location" class="form-control input-md" required="">
                     </div>
                  </div>
                  <!-- Text input-->
                  <div class="form-group">
                     <label class="col-md-4 control-label" for="email"></label>
                     <div class="col-md-4">
                        <input id="email" name="email" type="email" placeholder="User Email" class="form-control input-md" required="">
                     </div>
                  </div>
                  <!-- Password input-->
                  <div class="form-group">
                     <label class="col-md-4 control-label" for="password"></label>
                     <div class="col-md-4">
                        <input id="password" name="password" type="password" placeholder="Password" class="form-control input-md" required="">
                     </div>
                  </div>
                  <!-- Password input-->
                  <div class="form-group">
                     <label class="col-md-4 control-label" for="password_confirm"></label>
                     <div class="col-md-4">
                        <input id="password_confirm" name="password_confirm" type="password" placeholder="Confirm Password" class="form-control input-md" required="">
                        <span class="pass_error">passwords do not match</span>
                     </div>
                  </div>
                  <!-- Button -->
                  <div class="form-group">
                     <label class="col-md-4 control-label" for="submit"></label>
                  </div>
               </fieldset>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-default close-btn" data-dismiss="modal">Close</button>
               <button type="submit" class="btn btn-flat add-company-btn">Submit</button>
            </div>
         </form>
      </div>
   </div>
</div>


<!-- Modal -->
<div class="modal fade" id="manageFulfillmentIdsModal" tabindex="-1" role="dialog" aria-labelledby="manageFulfillmentIdsModalLabel">
   <div class="modal-dialog" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="manageFulfillmentIdsModalLabel">Fulfillment Ids - <span id="fulfillment_company_name"></span></h4>
         </div>
         <form id="fulfillment_ids_form" class="form-horizontal" method="POST" action="{{route('save_fulfillment_ids')}}" >
            @csrf
            <div class="modal-body">
               <fieldset>
                  <!-- Form Name -->
                  <!-- Text input-->
                  <div class="form-group">
                     <label class="col-md-12  control-label" for="fulfillment_ids"></label>
                     <div class="col-md-6 col-md-offset-3">
                        <input id="fulfillment_ids" name="fulfillment_ids" type="text" placeholder="Ids eg.(1,3,4)" class="form-control input-md" >
                     </div>
                    <div class="col-md-6 col-md-offset-3"></br>
                        <label for="tpl_only_check">Only allow 3pl access</label>
                        <input type="checkbox" id="tpl_only_check" name="tpl_only_check" value="1">
                     </div>
                     <input type="hidden" id="tpl_only_check" name="tpl_only_check" value="1" />
                     <input type="hidden" name="companies_id" id="fulfillment_companies_id"/>
                  </div>
                  <div class="form-group">
                     <label class="col-md-6 control-label" for="submit"></label>
                  </div>
               </fieldset>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-default close-btn" data-dismiss="modal">Close</button>
               <button type="submit" class="btn btn-flat add-company-btn">Submit</button>
            </div>
         </form>
      </div>
   </div>
</div>

<div class="modal fade" id="managePermissionsModal" tabindex="-1" role="dialog" aria-labelledby="managePermissionsModalLabel">
   <div class="modal-dialog" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
         </div>
         <form id="companies_permissions_form" class="form-horizontal" method="POST" action="{{route('save_company_permissions')}}" >
            @csrf
            <div class="modal-body">
               <fieldset>
                  <!-- Form Name -->
                  <!-- Text input-->
                  <div class="form-group">
                     <label class="col-md-12  control-label" for="fulfillment_ids"></label>
                    <div class="col-md-6 col-md-offset-4"></br>
                        <label for="tpl_only_check">Allow Manual Fulfillment</label>&nbsp;&nbsp;
                        <input type="checkbox" id="permission_can_manual_fulfill" name="permission_can_manual_fulfill" value="1">
                     </div>
                     <input type="hidden" name="permission_companies_id" id="permission_companies_id"/>
                  </div>
                  <div class="form-group">
                     <label class="col-md-6 control-label" for="submit"></label>
                  </div>
               </fieldset>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-default close-btn" data-dismiss="modal">Close</button>
               <button type="submit" class="btn btn-flat add-company-btn">Submit</button>
            </div>
         </form>
      </div>
   </div>
</div>

<div class="modal fade" id="manageDepositorIDsModal" tabindex="-1" role="dialog" aria-labelledby="manageDepositorIDsLabel">
   <div class="modal-dialog" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
         </div>
         <form id="companies_depositor_id_form" class="form-horizontal" method="POST" action="{{route('save_depositor_ids')}}" >
            @csrf
            <div class="modal-body">
               <fieldset>
                  <!-- Form Name -->
                  <!-- Text input-->
                  <div class="form-group">
                     <label class="col-md-12  control-label" for="depositor_id_companies_id"></label>
                     <div id="depositor_ids_div">
                     </div>
                     <input type="hidden" name="depositor_id_companies_id" id="depositor_id_companies_id"/>
                  </div>
                  <div class="form-group">
                     <label class="col-md-6 control-label" for="submit"></label>
                  </div>
               </fieldset>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-default close-btn" data-dismiss="modal" style="float:left">Close</button>
               <button type="button" class="btn btn-flat add-company-btn" onClick="addDepositorID()">Add</button>
               <button type="submit" class="btn btn-flat add-company-btn">Submit</button>
            </div>
         </form>
      </div>
   </div>
</div>

@stop
@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
    <link rel="stylesheet" href="/css/spectrumoversee.tables.css">
    <link rel="stylesheet" href="/css/companies.css">
@stop
@section('js')
    <script>
        $(document).ready(function() {
            $('#companies_table').DataTable({
                "sDom": '<"row view-filter"<"col-sm-12"<"pull-left"l><"pull-right"f><"clearfix">>>t<"row view-pager"<"col-sm-12"<"text-center"ip>>>',
                "bLengthChange": false,
                "bFilter": true,
                "bInfo": false,
                "pageLength": 50,
                "language": {
                    "paginate": {
                        "previous": '<i class="fa fa-chevron-left paginate-button"></i>',
                        "next": '<i class="fa fa-chevron-right paginate-button"></i>',
                    }
                }
            });

            $('#manageFulfillmentIdsModal').on('hidden.bs.modal', function () {
                $('#fulfillment_companies_id').val('');
                $('#fulfillment_ids').val('');
                $('#fulfillment_company_name').html('')
            })

        } );

        $('#add_company_form').submit(function(){
            let pass1 = $('#password').val();
            let pass2 = $('#password_confirm').val();
            if(pass1 != pass2){
                $('.pass_error').show();
                return false;
            }
        })

        function getCompanyFulfillmentDetails(id){
            $.post( "{{ route('get_fulfillment_ids') }}", {
            companies_id: id,
            _token: "{{ csrf_token() }}" },
            function( data ) {
                if(data.success){
                    console.log(data);
                }
            });
        }

        function getCompanyPermissionDetails(company_id,can_manual_fulfill){
            $('#permission_companies_id').val(company_id);
            if(can_manual_fulfill == 1){
                $("#permission_can_manual_fulfill").prop("checked", true);
            }else{
                $("#permission_can_manual_fulfill").prop("checked", false);
            }
        }

        function deleteCompany(id){
            var prompt = window.confirm("Are you sure you want to delete this company?");
            if(prompt){
                window.location.href = "/companies/delete?company_id="+id;
            }
        }

         function getCompanyDepositorID(id){
            $('#depositor_ids_div').empty();
            $('#depositor_id_companies_id').val(id);
            $.post( "{{ route('get_depositor_ids') }}", {
            companies_id: id,
            _token: "{{ csrf_token() }}" },
            function( data ) {
                if(data.success){
                    if(data.data.length > 0){
                        var ctr = 0;
                        data.data.forEach(function(e){
                            var nextId = 'customer-code'+ctr;
                            var depositor_id_field = '<div class="form-group customer-code-fieldset" id="'+nextId+'">';
                            depositor_id_field += '<div class="col-md-4 col-md-offset-1">';
                            depositor_id_field += '<label class="control-label" for="company_name">Depositor Code</label>';
                            depositor_id_field += '<input id="depositor_code" name="depositor_code[]" type="text" value="'+e.logiwa_depositor_code+'" placeholder="Depositor Code" class="form-control input-md" required="">';
                            depositor_id_field += '</div>'
                            depositor_id_field += '<div class="col-md-4">';
                            depositor_id_field += '<label class="control-label" for="company_name">Depositor ID</label>';
                            depositor_id_field += '<input id="depositor_id" name="depositor_id[]" type="text" value="'+e.logiwa_depositor_id+'" placeholder="Depositor ID" class="form-control input-md" required="">';
                            depositor_id_field += '</div>';
                            depositor_id_field += '<div class="col-md-2">';
                            depositor_id_field += '</br>';
                            depositor_id_field += '<button type="button" class="btn btn-flat close-btn" style="margin-top:10px;" onClick="removeElem('+ctr+')">Delete</button>';
                            depositor_id_field += '</div></div>';
                            $('#depositor_ids_div').append(depositor_id_field);
                            ctr++;
                        })
                    }
                }
            });
        }

        function removeElem(id){
            $('#customer-code'+id).remove();
        }

        function addDepositorID(){
            // Count classes
            var numItems = $('.customer-code-fieldset').length;
            var nextId = 'customer-code'+numItems;

            var depositor_id_field = '<div class="form-group customer-code-fieldset" id="'+nextId+'">';
                            depositor_id_field += '<div class="col-md-4 col-md-offset-1">';
                            depositor_id_field += '<label class="control-label" for="company_name">Depositor Code</label>';
                            depositor_id_field += '<input id="depositor_code" name="depositor_code[]" type="text" value="" placeholder="Depositor Code" class="form-control input-md" required="">';
                            depositor_id_field += '</div>'
                            depositor_id_field += '<div class="col-md-4">';
                            depositor_id_field += '<label class="control-label" for="company_name">Depositor ID</label>';
                            depositor_id_field += '<input id="depositor_id" name="depositor_id[]" type="text" value="" placeholder="Depositor ID" class="form-control input-md" required="">';
                            depositor_id_field += '</div>';
                            depositor_id_field += '<div class="col-md-2">';
                            depositor_id_field += '</br>';
                            depositor_id_field += '<button type="button" class="btn btn-flat close-btn" style="margin-top:10px;" onClick="removeElem('+numItems+')">Delete</button>';
                            depositor_id_field += '</div></div>';

            $('#depositor_ids_div').append(depositor_id_field);
        }
    </script>
    <script type="text/javascript" src="/js/jquery/topbarsearch.jquery.js?v={{preg_replace('/[^0-9]/', '', \Tremby\LaravelGitVersion\GitVersionHelper::getNameAndVersion())}}"></script>
@stop
