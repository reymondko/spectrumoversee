@extends('adminlte::page')
 <!-- CSRF Token -->
 <meta name="csrf-token" content="{{ csrf_token() }}">
@section('title', 'Shipping Cost Report')
@section('content_header')
    <h1 class="header-text">SHIPPING COST REPORT</h1>
   
@stop
@section('content')
    <section id="app">
        <shipping-cost-report :companies="{{json_encode($companies)}}"></shipping-cost-report>
    </section>
@stop
@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
    <link rel="stylesheet" href="/css/jquery-ui.css">
    <link rel="stylesheet" href="/css/shippack.css">
    <link rel="stylesheet" href="/css/returnlabel.css">
@stop
@section('js')
    <script src="{{ asset('js/app.js') }}?v={{preg_replace('/[^0-9]/', '', \Tremby\LaravelGitVersion\GitVersionHelper::getNameAndVersion())}}" defer></script>
    <script  src="https://code.jquery.com/jquery-3.4.1.min.js"  integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="  crossorigin="anonymous"></script>
@stop