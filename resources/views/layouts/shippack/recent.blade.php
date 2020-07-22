@extends('adminlte::page')

@section('title', 'Recent Shipments')


@section('content_header')
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <h1 class="header-text">RECENT SHIPMENTS</h1>
@stop


@section('content')
    @include('partials.simple-error-list')

    <div class="row center">
        <div class="box companies-box md-med-box">
            <div class="box-header">
                <h3 class="box-title"></h3>
                <div class="box-tools pull-right">

                </div>
            </div>
            <div class="box-body">
                <table id="s_table" class="table table-striped table-bordered" style="width:100%">
                    <thead class="table_head">
                        <tr>
                        <th class="table_head_th">ID</th>
                        <th class="table_head_th">3PL Customer</th>
                        <th class="table_head_th">ShipTo Name</th>
                        <th class="table_head_th">ShipTo Address</th>
                        <th class="table_head_th">Date Shipped</th>
                        <th class="table_head_th">Carrier/Service</th>
                        <th class="table_head_th"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($shipments as $shipment)
                            <tr>
                                <td>{{ $shipment->id }}</td>
                                <td>{{ $shipment->name }}</td>
                                <td>{{ $shipment->shipto_name }}</td>
                                <td>{{ $shipment->shipto_address }}, {{ $shipment->shipto_zip }}</td>
                                <td>{{ date('Y-m-d', strtotime($shipment->created_at)) }}</td>
                                <td>{{ $shipment->carrier }}</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="/ship-pack/reprint/{{ $shipment->id }}" target="_blank" type="button" class="btn btn-flat action-button">Reprint Label(s)</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
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

@stop
