@extends('adminlte::page')



@section('title', 'Settings')



@section('content_header')
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <h1 class="header-text">SETTINGS</h1>
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
  <div class="row">

     <div class="col-md-4">
        <form method="POST" action="{{ route('update_user_settings') }}">
            @csrf
            <div class="box box-solid box-primary account-settings-box">
                <div class="box-header account-settings-box-header">
                    <h3 class="box-title">Account Details</h3>
                </div><!-- /.box-header -->
                <div class="box-body">
                <div class="form-group">
                    <input type="text" class="form-control" id="name" name="name" placeholder="Name" value="{{ $data['user']['name'] }}" required>
                </div>
                <div class="form-group">
                    <input type="email" class="form-control" id="email" name="email" placeholder="Email"  value="{{ $data['user']['email'] }}" required>
                </div>
                <div class="form-group">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password">
                </div>
                <div class="form-group settings-btn-container">
                    <button type="submit" class="btn btn-flat so-btn" data-dismiss="modal">submit</button>
                </div>
                </div><!-- /.box-body -->
            </div><!-- /.box -->
        </form>
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
    </script>
    <script src="{{ asset('js/jquery/settings.jquery.js') }}?v={{preg_replace('/[^0-9]/', '', \Tremby\LaravelGitVersion\GitVersionHelper::getNameAndVersion())}}" defer></script>
    <script type="text/javascript" src="/js/jquery/topbarsearch.jquery.js?v={{preg_replace('/[^0-9]/', '', \Tremby\LaravelGitVersion\GitVersionHelper::getNameAndVersion())}}"></script>
@stop
