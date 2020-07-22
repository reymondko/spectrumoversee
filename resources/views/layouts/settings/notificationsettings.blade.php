@extends('adminlte::page')



@section('title', 'Settings')



@section('content_header')
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <h1 class="header-text">SETTINGS
    <i class="fa fa-chevron-right"></i>
        NOTIFICATIONS</h1>
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

<div class="container-fluid account-settings-container">
   <div class="container">
      </br>
      <div class="col-md-12 ">
         <form method="POST" action="{{route('notification_settings_save')}}">
            @csrf
            <div class="box box-solid box-primary account-settings-box">
               <div class="box-header account-settings-box-header">
                  <h3 class="box-title">Notifications Settings</h3>
               </div>
               <!-- /.box-header -->
               <div class="box-body">

                  <div class="form-group section-label">
                    Inventory Levels By SKU and Location:
                  </div>
                  <fieldset id="notification_settings_fieldset">
                    @if(count($data['notification_settings']) == 0)
                        <div class="form-group form-inline col-md-12 notification-settings-div" id="notification-settings-div1">
                            <div class="form-group col-md-3" >
                                <select class="col-md-12 notification-settings-inputs" name="sku[]" required>
                                    <option value="">
                                        SKU
                                    </option>
                                    @foreach($data['skus'] as $s)
                                        <option value="{{$s->sku}}">{{$s->sku}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-2" >
                                <select class="col-md-12 notification-settings-inputs" name="location[]" required>
                                    <option value="" >
                                        Location
                                    </option>
                                    @foreach($data['locations'] as $l)
                                        <option value="{{$l->name}}">{{$l->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-2" >
                                <input class="notification-settings-inputs" type="text" placeholder=" Threshold"  name="threshold[]" required/>
                            </div>
                            <div class="form-group col-md-4" >
                                <input class="notification-settings-inputs col-md-12" type="text" placeholder="Notification Emails (separate by comma)" name="notification_emails[]" required/>
                            </div>
                            <div class="form-group col-md-1">
                                <a href="#" onclick="removeElement('notification-settings-div1')" class="inventory-field-delete notification-settings-delete"><i class="fa fa-trash"></i></a>
                            </div>
                        </div>
                    @else
                        @foreach($data['notification_settings'] as $key=>$value)
                        <div class="form-group form-inline col-md-12 notification-settings-div" id="notification-settings-div{{$key+1}}">
                            <div class="form-group col-md-3" >
                                <select class="col-md-12 notification-settings-inputs" name="sku[]" required>
                                    <option value="">
                                        SKU
                                    </option>
                                    @foreach($data['skus'] as $s)
                                        <option value="{{$s->sku}}" {{$s->sku == $value->sku ? "selected":''}}>{{$s->sku}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-2" >
                                <select class="col-md-12 notification-settings-inputs" name="location[]" required>
                                    <option value="" >
                                        Location
                                    </option>
                                    @foreach($data['locations'] as $l)
                                        <option value="{{$l->name}}" {{$l->name == $value->location ? "selected":''}}>{{$l->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-2" >
                                <input class="notification-settings-inputs" type="text" placeholder=" Threshold" class="col-md-12" name="threshold[]" value="{{$value->threshold}}" required/>
                            </div>
                            <div class="form-group col-md-4" >
                                <input class="notification-settings-inputs col-md-12" type="text" placeholder="Notification Emails (separate by comma)" value="{{$value->notification_emails}}" name="notification_emails[]" required/>
                            </div>
                            <div class="form-group col-md-1">
                                <a href="#" onclick="removeElement('notification-settings-div{{$key+1}}')" class="inventory-field-delete notification-settings-delete"><i class="fa fa-trash"></i></a>
                            </div>
                        </div>
                        @endforeach
                    @endif
                  </fieldset>
                  <div class="modal-footer">
                        <span class="col-md-4 inventory-field-btn-left">
                            <button id="add_notification" type="button" class="btn btn-flat so-btn"><i class="fa fa-plus"></i>&nbsp;&nbsp;Add Notification</button>
                        </span>
                        <span class="col-md-8 inventory-field-btn-right">
                            <button type="submit" class="btn btn-flat so-btn">Save</button>
                        </span>
                    </div>
                  </form>
                  <form method="POST" action="{{route('notification_order_settings_save')}}">
                     @csrf
                  <div class="form-group section-label">
                    Incoming Orders:
                  </div>
                        <div class="form-group col-md-12" >
                            @if(isset($data['notification_order_settings']))
                            <div class="form-check">

                                <input type="checkbox" class="form-check-input" name="notification_order_enabled" {{$data['notification_order_settings']->enabled == 1 ? 'checked':null}}>
                                <label>Enabled</label>
                            </div>
                                <input class="notification-settings-inputs col-md-12" type="text" placeholder="Notification Emails (separate by comma)" name="notification_order_emails" value="{{$data['notification_order_settings']->notification_emails}}" required/>
                            @else

                                <input type="checkbox" class="form-check-input" name="notification_order_enabled" >
                                <label>Enabled</label>
                                </div>
                                <input class="notification-settings-inputs col-md-12" type="text" placeholder="Notification Emails (separate by comma)" name="notification_order_emails"  required/>

                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <span class="col-md-12 inventory-field-btn-right">
                            <button type="submit" class="btn btn-flat so-btn">Save</button>
                        </span>
                    </div>
                </form>
               </div>
               <!-- /.box-body -->
            </div>
            <!-- /.box -->

      </div>
   </div>
</div>

@stop



@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
    <link rel="stylesheet" href="/css/settings.css">
@stop

@section('js')

    <script>

        var skuOptions = `@foreach($data['skus'] as $s)<option value="{{$s->sku}}">{{$s->sku}}</option>@endforeach`;
        var locationOptions =  `@foreach($data['locations'] as $l)<option value="{{$l->name}}">{{$l->name}}</option>@endforeach`;

        $('#add_notification').click(function(){
            var fieldLength = $('.notification-settings-div').length + 1;
            var fieldToAppend = `<div class="form-group form-inline col-md-12 notification-settings-div" id="notification-settings-div${fieldLength}">
                                        <div class="form-group col-md-3" >
                                            <select class="col-md-12 notification-settings-inputs" name="sku[]" required>
                                                <option value="">
                                                    SKU
                                                </option>
                                                ${skuOptions}
                                            </select>
                                        </div>
                                        <div class="form-group col-md-2" >
                                            <select class="col-md-12 notification-settings-inputs" name="location[]" required>
                                                <option value="" >
                                                    Location
                                                </option>
                                                ${locationOptions}
                                            </select>
                                        </div>
                                        <div class="form-group col-md-2" >
                                            <input class="notification-settings-inputs" type="text" placeholder=" Threshold" class="col-md-12" name="threshold[]" required/>
                                        </div>
                                        <div class="form-group col-md-4" >
                                            <input class="notification-settings-inputs col-md-12" type="text" placeholder="Notification Emails (separate by comma)" name="notification_emails[]" required/>
                                        </div>
                                        <div class="form-group col-md-1">
                                            <a href="#" onclick="removeElement('notification-settings-div${fieldLength}')" class="inventory-field-delete notification-settings-delete"><i class="fa fa-trash"></i></a>
                                        </div>
                                    </div>`;

            $("#notification_settings_fieldset").append(fieldToAppend);
        });

        function removeElement(elem){
            $('#'+elem).remove();
        }
    </script>
    <script src="{{ asset('js/jquery/settings.jquery.js') }}?v={{preg_replace('/[^0-9]/', '', \Tremby\LaravelGitVersion\GitVersionHelper::getNameAndVersion())}}" defer></script>
    <script type="text/javascript" src="/js/jquery/topbarsearch.jquery.js?v={{preg_replace('/[^0-9]/', '', \Tremby\LaravelGitVersion\GitVersionHelper::getNameAndVersion())}}"></script>
@stop
