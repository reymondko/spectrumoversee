@extends('adminlte::page')


@section('title', 'Users')



@section('content_header')
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <h1 class="header-text">USERS</h1>
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
@elseif(session('status') == 'email_deleted_error')
<div class="alert alert-info alert-dismissible alert-error">
   <button type="button" class="close alert-close-btn" data-dismiss="alert" aria-hidden="true">×</button>
   <h4><i class="icon fa fa-warning"></i>Email Already Exists but was deleted, do want to reactivate this email? <a href="#"
      data-toggle="modal"
      data-target="#userReactivateModal" onclick="ReactivateUser('{{session('user_id')}}','{{session('user_name')}}','{{session('user_email')}}')">Yes</a></h4>
</div>

@elseif(session('status') == 'reactivated')
    <div class="alert alert-info alert-dismissible alert-saved">
        <button type="button" class="close alert-close-btn" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-check"></i>User has been reactivated!</h4>
    </div>

@elseif(session('status') == 'reset_successful')
<div class="alert alert-info alert-dismissible alert-saved">
    <button type="button" class="close alert-close-btn" data-dismiss="alert" aria-hidden="true">×</button>
    <h4><i class="icon fa fa-check"></i>Email reset instructions has been sent</h4>
</div>
@elseif(session('status') == 'deleted')
<div class="alert alert-info alert-dismissible alert-saved">
    <button type="button" class="close alert-close-btn" data-dismiss="alert" aria-hidden="true">×</button>
    <h4><i class="icon fa fa-check"></i>Deleted Successfully!</h4>
</div>
@endif
<div class="box user-box">
   <div class="box-header">
      <h3 class="box-title"></h3>
      <div class="box-tools pull-right">
         @if(Gate::allows('company-only', auth()->user()) || Gate::allows('admin-only', auth()->user()))
         <button type="button" class="btn btn-flat add-user-btn" data-toggle="modal" data-target="#addUserModal">
               <i class="fa fa-plus"></i> Add User
         </button>
         @endif
      </div>
      <!-- /.box-tools -->
   </div>
   <!-- /.box-header -->
   <div class="box-body">
      <table id="users_table" class="table table-striped table-bordered" style="width:100%">
         <thead class="table_head">
            <tr>
               <th class="table_head_th">User ID</th>
               <th class="table_head_th">Name</th>
               <th class="table_head_th">Email</th>
               <th class="table_head_th">Role</th>
               <th class="table_head_th">Company</th>
               <th class="table_head_th">Location</th>
               <th class="table_head_th">Last Login Date</th>
               <th class="table_head_th">Actions</th>
            </tr>
         </thead>
         <tbody>
            @foreach($data['users'] as $u)
                <tr>
                    <td>{{$u->id}}</td>
                    <td>{{$u->name}}</td>
                    <td>{{$u->email}}</td>
                    <td>
                        @if($u->role == 1)
                            {{'Super Admin'}}
                        @elseif($u->role == 2)
                            {{'Company Admin'}}
                        @elseif($u->role == 3)
                            {{'User'}}
                        @elseif($u->role == 4)
                            {{'Customer'}}
                        @else
                            {{'N/A'}}
                        @endif
                    </td>
                    <td>
                        @if(isset($u->companies->company_name))
                            {{$u->companies->company_name}}
                        @else
                            {{'N/A'}}
                        @endif
                    </td>
                    <td>
                        @if(isset($u->locations->name))
                            {{$u->locations->name}}
                        @else
                            {{'N/A'}}
                        @endif
                    </td>
                    <td>
                        @if(isset($u->latestLogIn->created_at))
                            <a href="#" data-toggle="modal" data-target="#userLogsModal" onClick="getUserLogs('{{$u->name}}','{{$u->id}}')">{{date('M d, Y - H:i', strtotime($u->latestLogIn->created_at))}}</a>
                        @else
                            {{'N/A'}}
                        @endif
                    </td>
                    <td>
                        <div class="btn-group">
                            <button type="button" class="btn btn-flat action-button">Action</button>
                            <button type="button" class="btn btn-flat dropdown-toggle action-button" data-toggle="dropdown" aria-expanded="false">
                                <span class="caret"></span>
                                <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu" role="menu">
                                @if(Gate::allows('admin-only', auth()->user()) || (Gate::allows('can_login_as', auth()->user()) && $u->role != 1))
                                <li><a class="action-list-a" href="{{route('login_as',['id' => $u->id])}}">Login As</a></li>
                                @endif
                                @if(Gate::allows('admin-only', auth()->user()))
                                <li><a class="action-list-a" href="{{route('clear_login_attempts',['id' => $u->id])}}">Clear Login Attempts</a></li>

                                <li><a class="action-list-a" href="{{route('send_reset_email',['id' => $u->id])}}">Reset password</a></li>
                                <li>
                                    <a class="action-list-a"
                                       href="#"
                                       data-toggle="modal"
                                       data-target="#editUserModal"  userReactivateModal
                                       onClick="editUser('{{ $u->id }}','{{ $u->name }}','{{ $u->email }}','{{ isset($u->locations->id) ? $u->locations->id:''}}','{{ $u->role }}')">
                                        Edit User
                                    </a>
                                </li>
                                @endif
                                @if(Gate::allows('admin-only', auth()->user()))
                                <li><a class="action-list-a" href="#" onClick="deleteUser({{$u->id}})">Delete</a></li>
                                @endif
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

<!--Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" role="dialog" aria-labelledby="addUserModallLabel">
   <div class="modal-dialog" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="addUserModalLabel">Add User</h4>
         </div>
         <form id="add_user_form" class="form-horizontal" method="POST" action="{{ route('add_user') }}" >
            @csrf
            <div class="modal-body">
               <fieldset>
                  <!-- Form Name -->
                  <!-- Text input-->

                  @if(Gate::allows('admin-only', auth()->user()))
                  <div class="form-group">
                     <label class="col-md-4 control-label" for="company_name">Company</label>
                     <div class="col-md-4">
                     <select class="form-control select-md" data-show-subtext="true" data-live-search="true" id="company" name="company">
                            <option value="0">---</option>
                            @foreach($data['companies'] as $c)
                                <option value="{{ $c->id }}">{{$c->company_name}}</option>
                            @endforeach
                      </select>
                     </div>
                  </div>
                  <div class="form-group">
                     <label class="col-md-4 control-label" for="company_name">Role</label>
                     <div class="col-md-4">
                     <select class="form-control select-md"  id="role" name="role">
                        @foreach ($data['roles'] as $r)
                        <option value="{{ $r->id }}" {{ $r->id == 3 ? ' selected' : ''}}>{{$r->title}}</option>
                        @endforeach
                      </select>
                     </div>
                  </div>
                  @endif
                  <!-- Text input-->
                  <div class="form-group">
                     <label class="col-md-4 control-label" for="company_name"></label>
                     <div class="col-md-4">
                        <input id="name" name="name" type="text" placeholder="Name" class="form-control input-md" required="">
                     </div>
                  </div>
                  <!-- Text input-->
                  <div class="form-group">
                     <label class="col-md-4 control-label" for="email"></label>
                     <div class="col-md-4">
                        <input id="email" name="email" type="email" placeholder="User Email" class="form-control input-md" required="">
                     </div>
                  </div>
                  <!-- Text input-->
                  @if(Gate::allows('company-only', auth()->user()))
                  <div class="form-group">
                     <label class="col-md-4 control-label" for="location">Location</label>
                     <div class="col-md-4">
                        <select class="form-control" name="location" id="location" required>
                            @foreach($data['locations'] as $l)
                                <option value="{{$l['id']}}">{{ $l['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                  </div>
                  @endif
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
                  @if(Gate::allows('company-only', auth()->user()) || Gate::allows('admin-only', auth()->user()))
                     <h4>Permissions</h4>
                     <hr>
                     <div class="col-md-6">
                        <div class="form-group">
                           <div class="col-md-12">
                              INVENTORY
                           </div>
                        </div>
                        <div class="form-group">
                           <div class="col-md-12">
                              <input type="checkbox" class="form-check-input permission-checkbox" name="add_permissions[]" value="can_see_inventory">
                              <label class="form-check-label">Inventory Page</label>
                           </div>
                           <div class="col-md-12">
                                 <input type="checkbox" class="form-check-input permission-checkbox"  name="add_permissions[]" value="can_see_inventory_import">
                                 <label class="form-check-label">Inventory Import</label>
                           </div>
                           <div class="col-md-12">
                                 <input type="checkbox" class="form-check-input permission-checkbox"  name="add_permissions[]" value="can_see_inventory_scan">
                                 <label class="form-check-label">Inventory Receive/Scan</label>
                           </div>
                           <div class="col-md-12">
                                 <input type="checkbox" class="form-check-input permission-checkbox"  name="add_permissions[]" value="can_see_delete_inventory">
                                 <label class="form-check-label">Inventory Delete</label>
                           </div>
                        </div><div class="form-group">
                           <div class="col-md-12">
                              ORDERS
                           </div>
                        </div>
                        <div class="form-group">
                           <div class="col-md-12">
                                 <input type="checkbox" class="form-check-input permission-checkbox"  name="add_permissions[]" value="can_see_orders">
                                 <label class="form-check-label">Orders</label>
                           </div>
                           <div class="col-md-12">
                                 <input type="checkbox" class="form-check-input permission-checkbox"  name="add_permissions[]" value="can_see_fulfill_orders">
                                 <label class="form-check-label">Fulfill Orders</label>
                           </div>
                           <div class="col-md-12">
                                 <input type="checkbox" class="form-check-input permission-checkbox"  name="add_permissions[]" value="can_see_add_orders">
                                 <label class="form-check-label">Add Orders</label>
                           </div>
                           <div class="col-md-12">
                                 <input type="checkbox" class="form-check-input permission-checkbox"  name="add_permissions[]" value="can_see_thirdparty_orders">
                                 <label class="form-check-label">Third Party Orders</label>
                           </div>
                           <div class="col-md-12">
                                 <input type="checkbox" class="form-check-input permission-checkbox"  name="permissions[]" value="can_see_reallocate">
                                 <label class="form-check-label">Reallocate Third Party Orders</label>
                           </div>
                           <div class="col-md-12">
                              <input type="checkbox" class="form-check-input permission-checkbox"  name="permissions[]" value="can_quality_inspector">
                              <label class="form-check-label">Quality Inspector</label>
                           </div>
                        </div>

                     </div>
                     <div class="col-md-6">
                        <div class="form-group">
                           <div class="col-md-12">
                              SHIP PACK
                           </div>
                        </div>
                        <div class="form-group">
                           <div class="col-md-12">
                              <input type="checkbox" class="form-check-input permission-checkbox"  name="permissions[]" value="can_see_ship_pack">
                              <label class="form-check-label">Ship Pack</label>
                           </div>
                           <div class="col-md-12">
                              <input type="checkbox" class="form-check-input permission-checkbox"  name="permissions[]" value="can_see_kit_return_sync">
                              <label class="form-check-label">Kit Return Sync</label>
                           </div>
                              <!--<div class="col-md-12">
                                 <input type="checkbox" class="form-check-input permission-checkbox"  name="permissions[]" value="can_see_kit_boxing">
                                 <label class="form-check-label">Kit Boxing</label>
                              </div>!-->
                        </div>
                        <div class="form-group">
                           <div class="col-md-12">
                              REPORTING
                        </div>
                        </div>
                        <div class="form-group">
                           <div class="col-md-12">
                              <input type="checkbox" class="form-check-input permission-checkbox"  name="add_permissions[]" value="can_see_kpi_report">
                              <label class="form-check-label">KPI Report</label>
                        </div>
                           <div class="col-md-12">
                              <input type="checkbox" class="form-check-input permission-checkbox"  name="permissions[]" value="can_see_kit_sync_report">
                              <label class="form-check-label">Kit Return Sync Report</label>
                           </div>
                        </div>
                        <div class="form-group">
                           <div class="col-md-12">
                              Users
                           </div>
                        </div>
                        <div class="form-group">
                           <div class="col-md-12">
                              <input type="checkbox" class="form-check-input permission-checkbox"  name="permissions[]" value="can_login_as">
                              <label class="form-check-label">Login As</label>
                           </div>
                        </div>
                     </div>
                  @endif
                  <!-- Button -->
                  <div class="form-group">
                     <label class="col-md-4 control-label" for="submit"></label>
                  </div>
               </fieldset>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-flat close-btn" data-dismiss="modal">Close</button>
               <button type="submit" class="btn btn-flat add-user-btn">Submit</button>
            </div>
         </form>
      </div>
   </div>
</div>

<!--Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-labelledby="editUserModalLabel">
   <div class="modal-dialog" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h3 class="modal-title" id="editUserModalLabel">Edit User</h3>
         </div>
         <form id="edit_user_form" class="form-horizontal" method="POST" action="{{ route('update_user') }}" >
            @csrf
            <div class="modal-body">
                <center>
                    <div class="update-user-loader"></div>
                </center>
               <fieldset class="edit-user-fieldset">
                  <!-- Text input-->
                  <input type="hidden" name="id_edit" id="id_edit" />
                  <div class="form-group">
                     <label class="col-md-4 control-label" for="company_name"></label>
                     <div class="col-md-4">
                        <input id="name_edit" name="name_edit" type="text" placeholder="Name" class="form-control input-md" required="">
                     </div>
                  </div>
                  <!-- Text input-->
                  <div class="form-group">
                     <label class="col-md-4 control-label" for="email"></label>
                     <div class="col-md-4">
                        <input id="email_edit" name="email_edit" type="email" placeholder="User Email" class="form-control input-md" required="">
                     </div>
                  </div>
                  <!-- Text input-->
                  <div class="form-group">
                     <label class="col-md-4 control-label" for="location">Location</label>
                     <div class="col-md-4">
                        <select class="form-control" name="location_edit" id="location_edit">

                        </select>
                    </div>
                  </div>
                  <div class="form-group">
                     <label class="col-md-4 control-label" for="company_name">Role</label>
                     <div class="col-md-4">
                     <select class="form-control select-md"  id="role" name="role">
                        @foreach ($data['roles'] as $r)
                        <option value="{{ $r->id }}" >{{$r->title}}</option>
                        @endforeach
                        </select>
                     </div>
                  </div>
                  </br>
                  <h4>Permissions</h4>
                  <hr>
                  <div class="col-md-6">
                     <div class="form-group">
                        <div class="col-md-12">
                           INVENTORY
                     </div>
                     </div>
                     <div class="form-group">
                        <div class="col-md-12">
                           <input type="checkbox" class="form-check-input permission-checkbox" name="permissions[]" value="can_see_inventory">
                           <label class="form-check-label">Inventory Page</label>
                        </div>
                        <div class="col-md-12">
                              <input type="checkbox" class="form-check-input permission-checkbox"  name="permissions[]" value="can_see_inventory_import">
                              <label class="form-check-label">Inventory Import</label>
                        </div>
                        <div class="col-md-12">
                              <input type="checkbox" class="form-check-input permission-checkbox"  name="permissions[]" value="can_see_inventory_scan">
                              <label class="form-check-label">Inventory Receive/Scan</label>
                        </div>
                        <div class="col-md-12">
                              <input type="checkbox" class="form-check-input permission-checkbox"  name="permissions[]" value="can_see_delete_inventory">
                              <label class="form-check-label">Inventory Delete</label>
                        </div>
                     </div>
                     <div class="form-group">
                        <div class="col-md-12">
                           ORDERS
                     </div>
                     </div>
                     <div class="form-group">
                        <div class="col-md-12">
                           <input type="checkbox" class="form-check-input permission-checkbox"  name="permissions[]" value="can_see_orders">
                           <label class="form-check-label">Orders</label>
                        </div>
                        <div class="col-md-12">
                              <input type="checkbox" class="form-check-input permission-checkbox"  name="permissions[]" value="can_see_fulfill_orders">
                              <label class="form-check-label">Fulfill Orders</label>
                        </div>
                        <div class="col-md-12">
                              <input type="checkbox" class="form-check-input permission-checkbox"  name="permissions[]" value="can_see_add_orders">
                              <label class="form-check-label">Add Orders</label>
                        </div>
                        <div class="col-md-12">
                              <input type="checkbox" class="form-check-input permission-checkbox"  name="permissions[]" value="can_see_thirdparty_orders">
                              <label class="form-check-label">Third Party Orders</label>
                        </div>
                        <div class="col-md-12">
                              <input type="checkbox" class="form-check-input permission-checkbox"  name="permissions[]" value="can_see_reallocate">
                              <label class="form-check-label">Reallocate Third Party Orders</label>
                        </div>
                        <div class="col-md-12">
                           <input type="checkbox" class="form-check-input permission-checkbox"  name="permissions[]" value="can_quality_inspector">
                           <label class="form-check-label">Quality Inspector</label>
                        </div>
                     </div>
                  </div>
                  <div class="col-md-6">
                     <div class="form-group">
                        <div class="col-md-12">
                           SHIP PACK
                     </div>
                     </div>
                     <div class="form-group">
                        <div class="col-md-12">
                           <input type="checkbox" class="form-check-input permission-checkbox"  name="permissions[]" value="can_see_ship_pack">
                           <label class="form-check-label">Ship Pack</label>
                        </div>
                        <div class="col-md-12">
                           <input type="checkbox" class="form-check-input permission-checkbox"  name="permissions[]" value="can_see_kit_return_sync">
                           <label class="form-check-label">Kit Return Sync</label>
                        </div>
                        <!-- <div class="col-md-12">
                           <input type="checkbox" class="form-check-input permission-checkbox"  name="permissions[]" value="can_see_kit_boxing">
                           <label class="form-check-label">Kit Boxing</label>
                        </div> !-->
                     </div>
                     <div class="form-group">
                        <div class="col-md-12">
                           REPORTING
                     </div>
                     </div>
                     <div class="form-group">
                        <div class="col-md-12">
                           <input type="checkbox" class="form-check-input permission-checkbox"  name="permissions[]" value="can_see_kpi_report">
                           <label class="form-check-label">KPI Report</label>
                        </div>
                        <div class="col-md-12">
                           <input type="checkbox" class="form-check-input permission-checkbox"  name="permissions[]" value="can_see_kit_sync_report">
                           <label class="form-check-label">Kit Return Sync Report</label>
                        </div>
                     </div>
                     <div class="form-group">
                        <div class="col-md-12">
                           Users
                        </div>
                     </div>
                     <div class="form-group">
                        <div class="col-md-12">
                           <input type="checkbox" class="form-check-input permission-checkbox"  name="permissions[]" value="can_login_as">
                           <label class="form-check-label">Login As</label>
                        </div>
                     </div>
                  </div>
                  <div class="form-group">
                     <label class="col-md-4 control-label" for="submit"></label>
                  </div>
               </fieldset>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-flat close-btn" data-dismiss="modal">Close</button>
               <button type="submit" class="btn btn-flat add-user-btn">Update</button>
            </div>
         </form>
      </div>
   </div>
</div>

<!--User Logs Modal -->
<div class="modal fade" id="userLogsModal" tabindex="-1" role="dialog" aria-labelledby="userLogsModalLabel">
   <div class="modal-dialog" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h3 class="modal-title" id="editUserModalLabel">User Logs - <span id="user_log_name"></span></h3>
         </div>
         <div class="modal-body">
         <table id="users_logs_table" class="table table-striped table-bordered" style="width:100%">
         <thead class="table_head">
            <tr>
               <th class="table_head_th">Date</th>
               <th class="table_head_th">Type</th>
               <th class="table_head_th">IP</th>
            </tr>
         </thead>
         <tbody id="user_log_tbody">
        </tbody>
        </table>
        </div>
      </div>
   </div>
</div>

<!--Reactivate Logs Modal -->
<div class="modal fade" id="userReactivateModal" tabindex="-1" role="dialog" aria-labelledby="userReactivateModalLabel">
      <div class="modal-dialog" role="document">
         <div class="modal-content">
            <div class="modal-header">
               <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
               <h3 class="modal-title" id="userReactivateModalLabel">Reactivate User </h3>
            </div>
            <div class="modal-body">
                  <form id="reactivate_user_form" class="form-horizontal" method="POST" action="{{ route('reactivate_user') }}" >
                        @csrf
                        <div class="modal-body">
                            <center>
                                <div class="update-user-loader"></div>
                            </center>
                           <fieldset class="reactivate-user-fieldset">
                              <!-- Text input-->
                              <input type="hidden" name="ru_user_id" id="ru_user_id" />
                              <div class="form-group">
                                    <label class="col-md-4 control-label" for="ru_user_name">Name: </label>
                                 <div class="col-md-4">
                                    <input id="ru_user_name" name="ru_user_name" type="text" placeholder="Name" class="form-control input-md" required="">
                                 </div>
                              </div>
                              <!-- Text input-->
                              <div class="form-group">
                                 <label class="col-md-4 control-label" for="email">Email: </label>
                                 <div class="col-md-4">
                                    <input id="ru_user_email" readonly name="ru_user_email" type="email" placeholder="User Email" class="form-control input-md" required="">
                                 </div>
                              </div>
                              <!-- Password input-->
                              <div class="form-group">
                                    <label class="col-md-4 control-label" for="ru_password">New Password: </label>
                                    <div class="col-md-4">
                                       <input id="ru_password" name="ru_password" type="password" placeholder="Password" class="form-control input-md" required="">
                                    </div>
                                 </div>
                                 <!-- Password input-->
                                 <div class="form-group">
                                    <label class="col-md-4 control-label" for="ru_password_confirm">Confirm Password: </label>
                                    <div class="col-md-4">
                                       <input id="ru_password_confirm" name="ru_password_confirm" type="password" placeholder="Confirm Password" class="form-control input-md" required="">
                                       <span class="pass_error">passwords do not match</span>
                                    </div>
                                 </div>

                              <div class="form-group">
                                 <label class="col-md-4 control-label" for="submit"></label>
                              </div>
                           </fieldset>
                        </div>
                        <div class="modal-footer">
                           <button type="button" class="btn btn-flat close-btn" data-dismiss="modal">Close</button>
                           <button type="submit" class="btn btn-flat add-user-btn">Reactivate</button>
                        </div>
                     </form>
           </div>
         </div>
      </div>
   </div>
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
