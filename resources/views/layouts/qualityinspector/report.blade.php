@extends('adminlte::page')

@section('title', 'Quality Inspector Report')

@section('content_header')
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <h1 class="header-text">Quality Inspector Report</h1>
@stop

@section('content')
    @include('partials.simple-error-list')

    <div class="row center">
        <div class="box companies-box md-med-box">
            <div class="box-header">
                <h3 class="box-title"></h3>
                <div class="box-tools pull-right"><a href="/quality-inspector/download" download> Download Excel File</div>
            </div>
            <div class="box-body">
                <table id="qi_table" class="table table-striped table-bordered" style="width:100%">
                    <thead class="table_head">
                        <tr>
                        <th class="table_head_th">ID</th>
                        <th class="table_head_th">Customer Name</th>
                        <th class="table_head_th">Transaction ID</th>
                        <th class="table_head_th">Reference Number</th>
                        <th class="table_head_th">Status</th>
                        <th class="table_head_th">QI by</th>
                        <th class="table_head_th">Date QI</th>
                        <th class="table_head_th">Fail Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($qis as $qi)
                            <tr>
                                <td>{{ $qi->line_number }}</td>
                                <td>{{ $qi->company_name }}</td>
                                <td>{{ $qi->transaction_id }}</td>
                                <td>{{ $qi->reference_number }}</td>
                                <td style="color:#FFF;background-color:@if($qi->status==1) green @else red @endif" >@if($qi->status==1) PASS @else FAIL @endif </td>
                                <td>{{ $qi->name }}</td>
                                <td>{{ date('Y-m-d', strtotime($qi->updated_at)) }}</td>
                                <td>{{ $qi->notes }}</td>
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
    <link rel="stylesheet" href="//cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="//cdn.datatables.net/buttons/1.6.1/css/buttons.dataTables.min.css">
    
@stop

@section('js')
<script src="//code.jquery.com/jquery-3.3.1.js"></script>
<script src="//cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
<script src="//cdn.datatables.net/buttons/1.6.1/js/dataTables.buttons.min.js"></script>
<script>
    $(document).ready(function() { 
        $('#qi_table').css("background-color","pink");
        $('#qi_table').DataTable( {
            dom: 'Bfrtip',
            buttons: [
                'copyHtml5',
                'excelHtml5',
                'csvHtml5',
                'pdfHtml5'
            ]
        } );
    });
<script>
@stop

