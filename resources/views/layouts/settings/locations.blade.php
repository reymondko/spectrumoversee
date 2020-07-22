@extends('adminlte::page')


@section('title', 'Locations')



@section('content_header')
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <h1 class="header-text">
        <a href=" {{ route('settings') }} " >SETTINGS</a>
        <i class="fa fa-chevron-right"></i>
        LOCATIONS
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
@elseif(session('status') == 'deleted')
    <div class="alert alert-info alert-dismissible alert-error">
        <button type="button" class="close alert-close-btn" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-warning"></i>Deleted Successfully!</h4>
    </div>
@endif
<div class="box user-box">
   <div class="box-header">
      <h3 class="box-title"></h3>
      <div class="box-tools pull-right">
        <button type="button" class="btn btn-flat so-btn" data-toggle="modal" data-target="#addLocationModal">
            <i class="fa fa-plus"></i> Add Location
        </button>
      </div>
      <!-- /.box-tools -->
   </div>
   <!-- /.box-header -->
   <div class="box-body">
    <div class="col-md-5 col-md-offset-3">
        <table id="locations" class="table table-striped table-bordered" style="width:100%">
         <thead class="table_head">
            <tr>
               <th class="table_head_th ">Location</th>
               <th class="table_head_th col-md-3">Actions</th>
            </tr>
         </thead>
         <tbody>
            @foreach($data['locations'] as $loc)
                <tr>
                    <td id="col_locid_{{ $loc->id }}">{{ $loc->name }}</td>
                    <td class="column-center">
                        <div class="btn-group">
                            <button type="button" class="btn btn-flat action-button">Action</button>
                            <button type="button" class="btn btn-flat dropdown-toggle action-button" data-toggle="dropdown" aria-expanded="false">
                                <span class="caret"></span>
                                <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu" role="menu">
                                <li><a class="action-list-a" href="#"  data-toggle="modal" data-target="#editLocationModal" onClick="editLocation('{{$loc->id}}')">Edit</a></li>
                                <li><a class="action-list-a" href="#"  data-toggle="modal" data-target="#deleteLocationModal" onClick="deleteLocation('{{$loc->id}}')">Delete</a></li>
                            </ul>
                        </div>
                    </td>
                </tr>
            @endforeach
         </tbody>
        </table>
    </div>
   </div>
   <!-- /.box-body -->
</div>
<!-- /.box -->

<!--Add Location Modal -->
<div class="modal fade" id="addLocationModal" tabindex="-1" role="dialog" aria-labelledby="addLocationModallLabel">
   <div class="modal-dialog" role="document">
      <div class="modal-content">
      <form method="POST" action="{{ route('locations_save') }}">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="addLocationModallLabel">Add location</h4>
         </div>
         <div class="modal-body">
            @csrf
            <fieldset id="add-location-fieldset">
                <div class="form-group" >
                    <div class="col-md-8 col-md-offset-2 inventory-field-input-div">
                        <input name="locations[]" type="text" placeholder="Location" class="form-control input-md add-location-field-input" required>

                    </div>
                </div>
            </fieldset>
         </div>
         <div class="modal-footer">
            <span class="col-md-4 inventory-field-btn-left">
                <button id="add_more_location" type="button" class="btn btn-flat so-btn"><i class="fa fa-plus"></i>&nbsp;&nbsp;Add More</button>
            </span>
            <span class="col-md-8 inventory-field-btn-right">
                <button type="submit" class="btn btn-flat so-btn">Save</button>
            </span>
         </div>
         </form>
      </div>
   </div>
</div>

<!--Edit Location Modal -->
<div class="modal fade" id="editLocationModal" tabindex="-1" role="dialog" aria-labelledby="editLocationModallLabel" data-backdrop="static" data-keyboard="false">
   <div class="modal-dialog" role="document">
      <div class="modal-content">
      <form method="POST" action="{{ route('locations_update') }}">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="editLocationModallLabel">Edit location</h4>
         </div>
         <div class="modal-body">
            @csrf
            <fieldset>
                <div class="form-group" >
                    <div class="col-md-8 col-md-offset-2 inventory-field-input-div">
                        <input type="hidden" name="edit_location_id" id="edit_location_id"/>
                        <input name="edit_location_name" id="edit_location_name" type="text" placeholder="Location" class="form-control input-md add-location-field-input" required>
                    </div>
                </div>
            </fieldset>
         </div>
         <div class="modal-footer">
            <span class="col-md-12 inventory-field-btn-right">
                <button type="button" class="btn btn-flat so-btn-close" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-flat so-btn">Save</button>
            </span>
         </div>
         </form>
      </div>
   </div>
</div>

<!--Delete Location Modal -->
<div class="modal fade" id="deleteLocationModal" tabindex="-1" role="dialog" aria-labelledby="deleteLocationModalLabel" data-backdrop="static" data-keyboard="false">
   <div class="modal-dialog" role="document">
      <div class="modal-content">
      <form method="POST" action="{{ route('locations_delete') }}">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="deleteLocationModallLabel">Delete location</h4>
         </div>
         <div class="modal-body">
            @csrf
            <fieldset>
                <input type="hidden" name="delete_location_id" id="delete_location_id"/>
                <div class="delete-location-text">
                    Are you sure you want to delete
                    " <span class="delete-location-text" id="delete_location_name"></span>" ?
                </div>
            </fieldset>
         </div>
         <div class="modal-footer">
            <span class="col-md-12 inventory-field-btn-right">
                <button type="button" class="btn btn-flat so-btn-close" data-dismiss="modal">No</button>
                <button type="submit" class="btn btn-flat so-btn">Yes</button>
            </span>
         </div>
         </form>
      </div>
   </div>
</div>
@stop
@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
    <link rel="stylesheet" href="/css/settings.css">
    <link rel="stylesheet" href="/css/spectrumoversee.tables.css">
@stop

@section('js')
<script src="{{ asset('js/jquery/settings.locations.jquery.js') }}?v={{preg_replace('/[^0-9]/', '', \Tremby\LaravelGitVersion\GitVersionHelper::getNameAndVersion())}}" ></script>
<script type="text/javascript" src="/js/jquery/topbarsearch.jquery.js?v={{preg_replace('/[^0-9]/', '', \Tremby\LaravelGitVersion\GitVersionHelper::getNameAndVersion())}}"></script>
@stop
