@extends('adminlte::page')

@section('title', 'Ship Package')

@section('content_header')
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <h1 class="header-text">SHIP PACKAGES</h1>
    <button type="button" id="topbar_search" class="search-bar-btn btn btn-default so-btn">
        <i class="fa fa-search"></i> Search
    </button>
    <input type="text" class="search-bar col-md-2" id="global_search" aria-describedby="search" placeholder="Search">
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
                <button type="button" class="btn btn-flat so-btn" data-toggle="modal" data-target="#addPackageSize">
                    <i class="fa fa-plus"></i> Add Package Size
                </button>
            </div>
        </div>
        <div class="box-body">
            <table id="spackages_table" class="table table-striped table-bordered" style="width:100%">
                <thead class="table_head">
                    <tr>
                    <th class="table_head_th">Package Name</th>
                    <th class="table_head_th">Length</th>
                    <th class="table_head_th">Width</th>
                    <th class="table_head_th">Height</th>
                    <th class="table_head_th">Weight</th>
                    <th class="table_head_th_act">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($data['ship_package']))
                        @foreach($data['ship_package'] as $sp)
                            <tr>
                                <td>{{$sp->package_name}}</td>
                                <td>{{$sp->length}}</td>
                                <td>{{$sp->width}}</td>
                                <td>{{$sp->height}}</td>
                                <td>{{$sp->weight}}</td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-flat action-button">Action</button>
                                        <button type="button" class="btn btn-flat dropdown-toggle action-button" data-toggle="dropdown" aria-expanded="false">
                                        <span class="caret"></span>
                                        <span class="sr-only">Actions</span>
                                        </button>
                                        <ul class="dropdown-menu" role="menu">
                                            <li><a class="action-list-a" href="#" data-toggle="modal" data-target="#editPackageSize" onClick="editPackageSize({{$sp->id}})" >Edit</a></li>
                                            <li><a class="action-list-a" href="{{ route('shippackage_delete', ['id' => $sp->id]) }}" data-toggle="modal" data-target="#" onClick="javascript:return confirm('Are you sure you want to delete this ship package?')">Delete</a></li>
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
<div class="modal fade" id="addPackageSize" tabindex="-1" role="dialog" aria-labelledby="addPackageSizeModalLabel">
   <div class="modal-dialog" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="addPackageSizeModalLabel">Add Package Size</h4>
         </div>
         <form id="add_package_size" class="form-horizontal" method="POST" action="{{route('shippackage_save')}}" >
            @csrf
            <div class="modal-body">
               <fieldset>
                  <!-- Form Name -->
                  <!-- Text input-->
                  <div class="form-group">
                     <div class="col-md-8 col-md-offset-2">
                        <label class="control-label" for="package_name">Package Name</label>  
                        <input id="package_name" name="package_name" type="text" placeholder="Package Name" class="form-control input-md" required><br/>
                     </div>
                     <div class="col-md-8 col-md-offset-2">
                     <label class="control-label" for="length">Length</label>  
                        <input id="length" name="length" type="number" step="any" placeholder="Length" class="form-control input-md" required><br/>
                     </div>
                     <div class="col-md-8 col-md-offset-2">
                        <label class="control-label" for="width">Width</label>  
                        <input id="width" name="width" type="number" step="any" placeholder="Width" class="form-control input-md" required><br/>
                     </div>
                     <div class="col-md-8 col-md-offset-2">
                        <label class="control-label" for="height">Height</label>  
                        <input id="height" name="height" type="number" step="any" placeholder="Height" class="form-control input-md" required><br/>
                     </div>
                     <div class="col-md-8 col-md-offset-2">
                        <label class="control-label" for="weight">Weight</label>  
                        <input id="weight" name="weight" type="number" step="any" placeholder="Weight" class="form-control input-md" required><br/>
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
<div class="modal fade" id="editPackageSize" tabindex="-1" role="dialog" aria-labelledby="editPackageSizeModalLabel">
   <div class="modal-dialog" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="editPackageSizeModalLabel">Edit Package - <span id="edit_package_label"></span></h4>
         </div>
         <form id="edit_package_size" class="form-horizontal" method="POST" action="{{route('shippackage_edit')}}" >
            @csrf
            <div class="modal-body">
               <fieldset>
                  <!-- Form Name -->
                  <!-- Text input-->
                  <div class="form-group">
                     <div class="col-md-8 col-md-offset-2">
                        <label class="control-label" for="package_name">Package Name</label>  
                        <input id="edit_package_name" name="edit_package_name" type="text" placeholder="Package Name" class="form-control input-md" required><br/>
                     </div>
                     <div class="col-md-8 col-md-offset-2">
                        <label class="control-label" for="edit_length">Length</label>  
                        <input id="edit_length" name="edit_length" type="number" step="any" placeholder="Length" class="form-control input-md" required><br/>
                     </div>
                     <div class="col-md-8 col-md-offset-2">
                        <label class="control-label" for="edit_width">Width</label>
                        <input id="edit_width" name="edit_width" type="number" step="any" placeholder="Width" class="form-control input-md" required><br/>
                     </div>
                     <div class="col-md-8 col-md-offset-2">
                        <label class="control-label" for="edit_height">Height</label>
                        <input id="edit_height" name="edit_height" type="number" step="any" placeholder="Height" class="form-control input-md" required><br/>
                     </div>
                     <div class="col-md-8 col-md-offset-2">
                        <label class="control-label" for="edit_weight">Weight</label>
                        <input id="edit_weight" name="edit_weight" type="number" step="any" placeholder="Weight" class="form-control input-md" required><br/>
                        <input id="edit_id" name="edit_id" type="hidden"  required>
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
        var spackages = @json($data['ship_package']);
    </script>
    <script src="{{ asset('js/jquery/shippackage.jquery.js') }}?v={{preg_replace('/[^0-9]/', '', \Tremby\LaravelGitVersion\GitVersionHelper::getNameAndVersion())}}" defer></script>
@stop