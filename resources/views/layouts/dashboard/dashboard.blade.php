@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <h1 class="header-text">DASHBOARD</h1>
    <!--<button type="button" id="topbar_search" class="search-bar-btn btn btn-default so-btn">
        <i class="fa fa-search"></i> Search
    </button>
    <input type="text" class="search-bar col-md-2" id="global_search" aria-describedby="search" placeholder="Search">-->
@stop

@section('content')
@if(Gate::allows('user-only', auth()->user()) || Gate::allows('company-only', auth()->user()))
<div class="row">
<div class="col-md-9 chart-container">
    <div id="chart_div" class="chart"></div>
  </div>
  <div class="col-md-3 chart-container">
    <div id="chart_div_pie" class="chart"></div>
  </div>
</div>

<div class="box inventory-box clear-fix">
   <div class="box-header">
      <h3 class="box-title">Recent Inventory Updates</h3>
      </br>
      <div class="box-tools pull-right">
      </div>
      <!-- /.box-tools -->
   </div>
   <!-- /.box-header -->
   <div class="box-body">
      <table id="orders_table" class="table table-striped table-bordered" style="width:100%">
         <thead class="table_head">
            <th>Date/Time</th>
            <th>SKU</th>
            <th>Barcode ID</th>
            <th>Scanned By</th>
         </thead>
         <tbody>
             @foreach($data['latest_scan'] as $l)
                <tr>

                    <td>{{date('M d, Y - H:i', strtotime($l['created_at']))}}</td>
                    <td>{{$l['inventory']['sku']}}</td>
                    <td>
                        <a href="{{ route('inventory_detail',['id' => $l['inventory']['id']]) }}" >
                            {{$l['barcode']}}
                        </a>
                    </td>
                    <td>{{$l['user']['name']}}</td>
                </tr>
             @endforeach
         </tbody>
      </table>
   </div>

   <!-- /.box-body -->
</div>
@endif
@stop
@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
    <link rel="stylesheet" href="/css/dashboard.css">
    <link rel="stylesheet" href="/css/spectrumoversee.tables.css">
    <link rel="stylesheet" href="/css/topbarsearch.css">
@stop
@section('js')
    <script type="text/javascript" src="/js/jquery/topbarsearch.jquery.js?v={{preg_replace('/[^0-9]/', '', \Tremby\LaravelGitVersion\GitVersionHelper::getNameAndVersion())}}"></script>
    @if(Gate::allows('user-only', auth()->user()) || Gate::allows('company-only', auth()->user()))
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script>
        $(document).ready(function() {

            // Load the Visualization API and the corechart package.
            google.charts.load('current', {
                'packages': ['corechart']
            });

            // Set a callback to run when the Google Visualization API is loaded.
            google.charts.setOnLoadCallback(drawChart);

            // Callback that creates and populates a data table,
            // instantiates the pie chart, passes in the data and
            // draws it.
            function drawChart() {

                // Create the data table.
                var data = new google.visualization.DataTable();
                data.addColumn('string', 'Location');
                data.addColumn('number', 'Items');
                data.addColumn({
                    type: 'string',
                    role: 'style'
                });
                data.addColumn('number', 'Items in Transit');
                data.addColumn({
                    type: 'string',
                    role: 'style'
                });
                @php($x = 1)
                @foreach($data['location_stats'] as $key => $value)
                    data.addRow(['{{$key}}', {{$value['total']}}, '#f88b01', {{$value['transit']}}, '#337ab7'],)
                @endforeach
                // Set chart options
                var options = {
                    title: 'Inventory by last scan',
                    width: "100%",
                    height: 350,
                    chartArea: {
                        'width': '80%',
                        'height': '70%'
                    },
                    bar: {
                        groupWidth: "30%",
                        width:"80%"
                    },
                    legend: {
                        position: 'none'
                    },
                    titleTextStyle: {
                        color: '#3b4864',
                        fontName: 'Helvetica',
                        fontSize: '16'
                    },
                    vAxis: {
                        format: '0',
                        color: '#777'
                    },
                    hAxis: {
                        textStyle: {
                            color: '#777'
                        }
                    },
                };

                var options_pie = {
                    title: 'Items per location',
                    width: "100%",
                    height: 350,
                    colors: ['#fcb55e', '#f88b01', '#485880', '#27304d', '#fcb55e', '#f88b01', '#485880', '#27304d', '#fcb55e', '#f88b01', '#485880', '#27304d'],
                    legend: {
                        position: 'none'
                    },
                    titleTextStyle: {
                        color: '#3b4864',
                        fontName: 'Helvetica',
                        fontSize: '16'
                    },
                    vAxis: {
                        format: '0',
                        color: '#777'
                    },
                    hAxis: {
                        textStyle: {
                            color: '#777'
                        }
                    },
                };

                var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
                var chart_pie = new google.visualization.PieChart(document.getElementById('chart_div_pie'));
                chart.draw(data, options);
                chart_pie.draw(data, options_pie);

                google.visualization.events.addListener(chart, 'select', selectHandler);

                function selectHandler() {
                    var selection = chart.getSelection();
                    var message = '';
                    for (var i = 0; i < selection.length; i++) {
                        var item = selection[i];
                        if (item.row != null && item.column != null) {
                            var str = data.getFormattedValue(item.row, item.column);
                            var location = data
                                .getValue(chart.getSelection()[0].row, 0)
                            var type
                            if (item.column == 1) {
                                type = "incoming";
                            }else{
                                type = "outgoing";
                            }

                            window.location.href = "/inventory/graph?location="+location+"&type="+type;
                            // message += '{row:' + item.row + ',column:' + item.column + '} = ' + str + '  The Category is:' + category + ' it belongs to : ' + type + '\n';
                            console.log(location);
                            console.log(type);
                        }
                    }
                }

            }

            $(window).resize(function() {
                drawChart();
            });
        });
    </script>
    @endif
@stop
