@extends('adminlte::page')

@section('title', 'Shipper Address')

@section('content_header')
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <h1 class="header-text">THIRD PARTY SHIPPER ADDRESS</h1>
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
@elseif(session('status') == 'error_deleting')
<div class="alert alert-info alert-dismissible alert-error">
   <button type="button" class="close alert-close-btn" data-dismiss="alert" aria-hidden="true">×</button>
   <h4><i class="icon fa fa-warning"></i>Error Deleting Data!</h4>
</div>
@endif

<div class="row center">
    <div class="box companies-box md-med-box">
        <div class="box-header">
            <h3 class="box-title"></h3>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-flat so-btn" data-toggle="modal" data-target="#addShipperAddress">
                    <i class="fa fa-plus"></i> Add Shipper Address
                </button>
            </div>
        </div>
        <div class="box-body">
            <table id="s_table" class="table table-striped table-bordered" style="width:100%">
                <thead class="table_head">
                    <tr>
                    <th class="table_head_th">Customer ID</th>
                    <th class="table_head_th">Name</th>
                    <th class="table_head_th">Address</th>
                    <th class="table_head_th">City</th>
                    <th class="table_head_th">State</th>
                    <th class="table_head_th">Country</th>
                    <!--<th class="table_head_th">Postal Code</th>-->
                    <th class="table_head_th">Phone Number</th>
                    <th class="table_head_th">Zip</th>
                    <th class="table_head_th">Account Number</th>
                    <th class="table_head_th">Minimum Package Weight</th>
                    <th class="table_head_th">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($data['shipper']))
                        @foreach($data['shipper'] as $sp)
                            <tr>
                                <td>{{$sp->tpl_customer_id}}</td>
                                <td>{{$sp->first_name}} {{$sp->last_name}}</td>
                                <td>{{$sp->address}}</td>
                                <td>{{$sp->city}}</td>
                                <td>{{$sp->state}}</td>
                                <td>{{$sp->country}}</td>
                                <!--<td>{{$sp->postal_code}}</td>-->
                                <td>{{$sp->phone_number}}</td>
                                <td>{{$sp->zip}}</td>
                                <td>{{$sp->account_number}}</td>
                                <td>{{$sp->minimum_package_weight}}</td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-flat action-button">Action</button>
                                        <button type="button" class="btn btn-flat dropdown-toggle action-button" data-toggle="dropdown" aria-expanded="false">
                                        <span class="caret"></span>
                                        <span class="sr-only">Actions</span>
                                        </button>
                                        <ul class="dropdown-menu" role="menu">
                                            <li><a class="action-list-a" href="#" data-toggle="modal" data-target="#editShipperAddress" onClick="editShipperAddress({{$sp->id}})" >Edit</a></li>
                                            <li><a class="action-list-a" href="{{ route('shipper_delete', ['id' => $sp->id]) }}" data-toggle="modal" data-target="#" onClick="javascript:return confirm('Are you sure you want to delete this shipper address?')">Delete</a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>


<!--Create Modal -->
<div class="modal fade" id="addShipperAddress" tabindex="-1" role="dialog" aria-labelledby="addShipperAddressModalLabel">
   <div class="modal-dialog" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="addShipperAddressModalLabel">Add Shipper Address</h4>
         </div>
         <form id="add_shipper_address" class="form-horizontal" method="POST" action="{{route('shipper_save')}}" >
            @csrf
            <div class="modal-body">
               <fieldset>
                  <!-- Form Name -->
                  <!-- Text input-->
                  <div class="form-group">
                     <div class="col-md-8 col-md-offset-2">
                        <label class="control-label" for="tpl_customer_id">3pl Customer Id</label>
                        <input id="tpl_customer_id" name="tpl_customer_id" type="number" placeholder="3pl Customer Id" class="form-control input-md" required><br/>
                     </div>
                     <div class="col-md-8 col-md-offset-2">
                         <label class="control-label" for="first_name">First Name</label>
                        <input id="first_name" name="first_name" type="text" placeholder="First Name" class="form-control input-md" required><br/>
                     </div>
                     <div class="col-md-8 col-md-offset-2">
                         <label class="control-label" for="last_name">Last Name</label>
                        <input id="last_name" name="last_name" type="text" placeholder="Last Name" class="form-control input-md" required><br/>
                     </div>
                     <div class="col-md-8 col-md-offset-2">
                         <label class="control-label" for="address">Address</label>
                        <input id="address" name="address" type="text" placeholder="Address" class="form-control input-md" ><br/>
                     </div>
                     <div class="col-md-8 col-md-offset-2">
                         <label class="control-label" for="city">City</label>
                        <input id="city" name="city" type="text" placeholder="City" class="form-control input-md" ><br/>
                     </div>
                     <div class="col-md-8 col-md-offset-2">
                         <label class="control-label" for="state">State</label>
                        <input id="state" name="state" type="text" placeholder="State" class="form-control input-md" ><br/>
                     </div>
                     <div class="col-md-8 col-md-offset-2">
                         <label class="control-label" for="country">Country</label>
                        <input id="country" name="country" type="text" placeholder="Country" class="form-control input-md" ><br/>
                     </div>
                     <!--<div class="col-md-8 col-md-offset-2">
                         <label class="control-label" for="postal_code">Postal Code</label>
                        <input id="postal_code" name="postal_code" type="text" placeholder="Postal Code" class="form-control input-md" ><br/>
                     </div>-->
                     <div class="col-md-8 col-md-offset-2">
                         <label class="control-label" for="phone_number">Phone Number</label>
                        <input id="phone_number" name="phone_number" type="number" placeholder="Phone Number" class="form-control input-md" ><br/>
                     </div>
                     <div class="col-md-8 col-md-offset-2">
                         <label class="control-label" for="zip">Zip Code</label>
                        <input id="zip" name="zip" type="number" placeholder="Zip Code" class="form-control input-md"><br/>
                     </div>
                     <div class="col-md-8 col-md-offset-2">
                         <label class="control-label" for="phone_number">Account Number</label>
                        <input id="account_number" name="account_number" type="text" placeholder="Account Number" class="form-control input-md" ><br/>
                     </div>
                     <div class="col-md-8 col-md-offset-2">
                         <label class="control-label" for="minimum_package_weight">Minimum Package Weight</label>
                        <input id="minimum_package_weight" name="minimum_package_weight" type="text" placeholder="Min. Package Weight" class="form-control input-md" ><br/>
                     </div>
                  </div>
                  <div class="form-group">
                     <label class="col-md-6 control-label" for="submit"></label>
                  </div>
               </fieldset>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-default close-btn so-btn-close" data-dismiss="modal">Close</button>
               <button type="submit" class="btn btn-flat so-btn">Submit</button>
            </div>
         </form>
      </div>
   </div>
</div>

<!--Edit Modal -->
<div class="modal fade" id="editShipperAddress" tabindex="-1" role="dialog" aria-labelledby="editShipperAddressLabel">
   <div class="modal-dialog" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="editShipperAddressLabel">Edit Shipper Address - <span id="edit_shipper_label"></span></h4>
         </div>
         <form id="edit_shipper_address" class="form-horizontal" method="POST" action="{{route('shipper_edit')}}" >
            @csrf
            <div class="modal-body">
               <fieldset>
                  <!-- Form Name -->
                  <!-- Text input-->
                  <div class="form-group">
                     <div class="col-md-8 col-md-offset-2">
                        <label class="control-label" for="edit_tpl_customer_id">3pl Customer Id</label>
                        <input id="edit_tpl_customer_id" name="edit_tpl_customer_id" type="number" placeholder="3pl Customer Id" class="form-control input-md" required><br/>
                     </div>
                     <div class="col-md-8 col-md-offset-2">
                         <label class="control-label" for="edit_first_name">First Name</label>
                        <input id="edit_first_name" name="edit_first_name" type="text" placeholder="First Name" class="form-control input-md" required><br/>
                     </div>
                     <div class="col-md-8 col-md-offset-2">
                         <label class="control-label" for="last_name">Last Name</label>
                        <input id="edit_last_name" name="edit_last_name" type="text" placeholder="Last Name" class="form-control input-md" required><br/>
                     </div>
                     <div class="col-md-8 col-md-offset-2">
                         <label class="control-label" for="address">Address</label>
                        <input id="edit_address" name="edit_address" type="text" placeholder="Address" class="form-control input-md" ><br/>
                     </div>
                     <div class="col-md-8 col-md-offset-2">
                         <label class="control-label" for="edit_city">City</label>
                        <input id="edit_city" name="edit_city" type="text" placeholder="City" class="form-control input-md" ><br/>
                     </div>
                     <div class="col-md-8 col-md-offset-2">
                         <label class="control-label" for="edit_state">State</label>
                        <input id="edit_state" name="edit_state" type="text" placeholder="State" class="form-control input-md" ><br/>
                     </div>
                     <div class="col-md-8 col-md-offset-2">
                         <label class="control-label" for="edit_country">Country</label>
                        <input id="edit_country" name="edit_country" type="text" placeholder="Country" class="form-control input-md" ><br/>
                     </div>
                     <!--<div class="col-md-8 col-md-offset-2">
                         <label class="control-label" for="edit_postal_code">Postal Code</label>
                        <input id="edit_postal_code" name="edit_postal_code" type="text" placeholder="Postal Code" class="form-control input-md"><br/>
                     </div>-->
                     <div class="col-md-8 col-md-offset-2">
                         <label class="control-label" for="edit_phone_number">Phone Number</label>
                        <input id="edit_phone_number" name="edit_phone_number" type="number" placeholder="Phone Number" class="form-control input-md" required><br/>
                        <input id="edit_id" name="edit_id" type="hidden"  required>
                     </div>
                     <div class="col-md-8 col-md-offset-2">
                         <label class="control-label" for="edit_zip">Zip Code</label>
                        <input id="edit_zip" name="edit_zip" type="number" placeholder="Zip Code" class="form-control input-md"><br/>
                     </div>
                     <div class="col-md-8 col-md-offset-2">
                         <label class="control-label" for="edit_account_number">Account Number</label>
                        <input id="edit_account_number" name="edit_account_number" type="text" placeholder="Account Number" class="form-control input-md"><br/>
                     </div>
                     <div class="col-md-8 col-md-offset-2">
                         <label class="control-label" for="edit_minimum_package_weight">Minimum Package Weight</label>
                        <input id="edit_minimum_package_weight" name="edit_minimum_package_weight" type="text" placeholder="Min. Package Weight" class="form-control input-md" ><br/>
                     </div>
                  </div>
                  <div class="form-group">
                     <label class="col-md-6 control-label" for="submit"></label>
                  </div>
               </fieldset>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-default close-btn so-btn-close" data-dismiss="modal">Close</button>
               <button type="submit" class="btn btn-flat so-btn">Submit</button>
            </div>
         </form>
      </div>
   </div>
</div>
@stop
@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
    <link rel="stylesheet" href="/css/spectrumoversee.tables.css">
    <link rel="stylesheet" href="/css/ship_package.css">
@stop
@section('js')
    <script>
        var spackages = @json($data['shipper']);
    </script>
    <script src="{{ asset('js/jquery/shipperadress.jquery.js') }}?v={{preg_replace('/[^0-9]/', '', \Tremby\LaravelGitVersion\GitVersionHelper::getNameAndVersion())}}" defer></script>
@stop
