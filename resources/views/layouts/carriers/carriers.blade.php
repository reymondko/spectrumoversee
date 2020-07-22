@extends('adminlte::page')

@section('title', 'Carriers')

@section('content_header')
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <h1 class="header-text">CARRIERS</h1>
@stop
@section('content')

@if(session('data')['status'] == 'saved')
<div class="alert alert-info alert-dismissible alert-saved">
   <button type="button" class="close alert-close-btn" data-dismiss="alert" aria-hidden="true">×</button>
   <h4><i class="icon fa fa-check"></i>Saved!</h4>
</div>
@elseif(session('status') == 'error_saving')
<div class="alert alert-info alert-dismissible alert-error">
   <button type="button" class="close alert-close-btn" data-dismiss="alert" aria-hidden="true">×</button>
   <h4><i class="icon fa fa-warning"></i>Error Saving Data!</h4>
</div>
@elseif(session('data')['status'] == 'deleted')
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
<section id="app">
    @if(isset(session('data')['previous']))
        <carriers :vendors="{{json_encode($vendors)}}" :carriers="{{json_encode($carriers)}}" :previous="{{session('data')['previous']}}"></carriers>
    @else
        <carriers :vendors="{{json_encode($vendors)}}" :carriers="{{json_encode($carriers)}}" ></carriers>
    @endif
</section>

@stop
@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
    <link rel="stylesheet" href="/css/spectrumoversee.tables.css">
    <link rel="stylesheet" href="/css/carriers.css">
@stop
@section('js')
<script src="{{ asset('js/app.js') }}?v={{preg_replace('/[^0-9]/', '', \Tremby\LaravelGitVersion\GitVersionHelper::getNameAndVersion())}}" defer></script>
@stop
