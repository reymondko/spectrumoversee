@extends('adminlte::page')
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
@section('title', 'Kit Return Sync')


@section('content_header')
    <h1 class="header-text">
        Kit Boxing
    </h1>
    <style>
        .tab-content{
        padding-top:20px;
        }

        .form-sub-title{
            border-bottom-style: solid;
            border-width: thin;
            border-color: #d2d6de;
            margin-top:10px;
            font-size:18px;
            padding-bottom: 10px;
        }
    </style>
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
    <section id="app">
        <!-- :printers = " json_encode($printers,true) "  -->
        <kit-boxing></kit-boxing>
    </section>
@stop
@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
    <link rel="stylesheet" href="/css/jquery-ui.css">
    <link rel="stylesheet" href="/css/shippack.css">
@stop
@section('js')
    <script src="{{ asset('js/app.js') }}?v={{preg_replace('/[^0-9]/', '', \Tremby\LaravelGitVersion\GitVersionHelper::getNameAndVersion())}}" defer></script>
@stop