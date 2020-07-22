@extends('adminlte::page')

@section('title', 'Shipper Address')


@section('content_header')
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <h1 class="header-text">THIRD PARTY SHIPPER ADDRESS</h1>
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


    @include('partials.simple-error-list')


    <div class="row center">
        <div class="box companies-box md-med-box">
            <div class="box-header">
                <h3 class="box-title"></h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-flat so-btn" data-toggle="modal" data-target="#addShippingClient">
                        <i class="fa fa-plus"></i> Add Shipper Clients
                    </button>
                </div>
            </div>
            <div class="box-body">
                <table id="s_table" class="table table-striped table-bordered" style="width:100%">
                    <thead class="table_head">
                        <tr>
                        <th class="table_head_th">ID</th>
                        <th class="table_head_th">Name</th>
                        <th class="table_head_th">3PL Client Number</th>
                        <th class="table_head_th">Shipping Markup %</th>
                        <th class="table_head_th">Scan Serial Number</th>
                        <th class="table_head_th">Shipping Carriers</th>
                        <th class="table_head_th">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($shipping_clients->reverse() as $sc)
                            <tr>
                                <td>{{ $sc->id }}</td>
                                <td>{{ $sc->name }}</td>
                                <td>{{ $sc->tpl_client_id }}</td>
                                <td>{{ $sc->ShippingMarkupWithPercent }}</td>
                                <td>@if($sc->require_scan_serial_number == 1) {!! $sc->RequiredScanSerial !!} @else {{'Not Required'}} @endif</td>
                                <td>{{ $sc->carriers }}</td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-flat action-button">Action</button>
                                        <button type="button" class="btn btn-flat dropdown-toggle action-button" data-toggle="dropdown" aria-expanded="false">
                                            <span class="caret"></span>
                                            <span class="sr-only">Actions</span>
                                        </button>
                                        <ul class="dropdown-menu" role="menu">
                                            <li><a class="action-list-a" href="#" data-toggle="modal" data-target="#editShippingClient" onClick="editShippingClient({{ $sc->id }}, '{{ $sc->name }}', {{ $sc->tpl_client_id }}, {{ $sc->shipping_markup }}, {{ $sc->require_scan_serial_number }},'{{$sc->carriers}}')">Edit</a></li>
                                            <li><a class="action-list-a" href="" data-toggle="modal" data-target="#removeShippingClient" onClick="removingShippingClient({{ $sc->id }}, '{{ $sc->name }}')">Delete</a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>


    <!--Create Modal -->
    <div class="modal fade" id="addShippingClient" tabindex="-1" role="dialog" aria-labelledby="addShippingClientModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="addShippingClientModalLabel">Add Shipping Client</h4>
                </div>
                <form id="add_shipper_address" class="form-horizontal" method="POST" action="{{ route('shippingclients.store') }}" >
                    @csrf
                    <div class="modal-body">
                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-8 col-md-offset-2">
                                    <label class="control-label" for="name">Name</label>
                                    <input id="name" name="name" type="text"
                                    value="{{ old('name') }}" placeholder="Enter Client Name"
                                    class="form-control input-md" required><br/>
                                </div>

                                <div class="col-md-8 col-md-offset-2">
                                    <label class="control-label" for="tpl_client_id">3pl Client Number</label>
                                    <input id="tpl_client_id" name="tpl_client_id" type="number"
                                    value="{{ old('tpl_client_id') }}" placeholder="Enter 3pl Client Number"
                                    class="form-control input-md" required><br/>
                                </div>

                                <div class="col-md-8 col-md-offset-2">
                                    <label class="control-label" for="shipping_markup">Shipping Markup</label>
                                    <input id="shipping_markup" name="shipping_markup" type="number" step="0.01"
                                    value="{{ old('shipping_markup') }}" placeholder="Enter Shipping Markup"
                                    class="form-control input-md" required><br/>
                                </div>
                                <div class="col-md-8 col-md-offset-2">
                                    <label class="control-label" for="shipping_carriers">Shipping Carriers</label>
                                    <select id="shipping_carriers" multiple="multiple" name="shipping_carriers[]"
                                    class="form-control input-md">
                                      @foreach($carriers as $carrier)
                                      <option value="{{$carrier->name}}">{{$carrier->name}}</option>
                                      @endforeach
                                    </select>
                                </div>


                                <div class="col-md-8 col-md-offset-2 form-check">
                                    <input id="require_scan_serial_number1" name="require_scan_serial_number" class="form-check-input" type="radio" checked="checked" value="0">
                                    <label class="form-check-label" for="require_scan_serial_number1">
                                        Default
                                    </label>
                                </div>
                                <div class="col-md-8 col-md-offset-2 form-check">
                                    <input id="require_scan_serial_number2"  name="require_scan_serial_number" class="form-check-input" type="radio" value="1">
                                    <label class="form-check-label" for="require_scan_serial_number2">
                                        Require Scan Serial #
                                    </label>
                                </div>
                                <div class="col-md-8 col-md-offset-2 form-check">
                                    <input id="require_scan_serial_number3" name="require_scan_serial_number" class="form-check-input" type="radio" value="2">
                                    <label class="form-check-label" for="require_scan_serial_number3">
                                        Visual Scan Only
                                    </label>
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
    <div class="modal fade" id="editShippingClient" tabindex="-1" role="dialog" aria-labelledby="editShippingClientLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="editShippingClientLabel">Edit Shipper Address - <span id="edit_shipper_label"></span></h4>
                </div>
                <form id="edit_shipper_address" class="form-horizontal" method="POST" action="{{ route('shippingclients.patch') }}" >
                    <input type="hidden" id="edit_id" name="id" value="" />
                    @csrf
                    <div class="modal-body">
                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-8 col-md-offset-2">
                                    <label class="control-label" for="edit_name">Name</label>
                                    <input id="edit_name" name="name" type="text"
                                    value="{{ old('name') }}" placeholder="Enter Client Name"
                                    class="form-control input-md" required><br/>
                                </div>

                                <div class="col-md-8 col-md-offset-2">
                                    <label class="control-label" for="edit_tpl_client_id">3pl Client Number</label>
                                    <input id="edit_tpl_client_id" name="tpl_client_id" type="number"
                                    value="{{ old('tpl_client_id') }}" placeholder="Enter 3pl Client Number"
                                    class="form-control input-md" required><br/>
                                </div>

                                <div class="col-md-8 col-md-offset-2">
                                    <label class="control-label" for="edit_shipping_markup">Shipping Markup</label>
                                    <input id="edit_shipping_markup" name="shipping_markup" type="number" step="0.01"
                                    value="{{ old('shipping_markup') }}" placeholder="Enter Shipping Markup"
                                    class="form-control input-md" required><br/>
                                </div>
                                <div class="col-md-8 col-md-offset-2">
                                    <label class="control-label" for="edit_shipping_carriers">Shipping Carriers</label>
                                    <select id="edit_shipping_carriers" multiple="multiple" name="shipping_carriers[]"
                                    class="form-control input-md">
                                      @foreach($carriers as $carrier)
                                      <option  value="{{$carrier->name}}">{{$carrier->name}}</option>
                                      @endforeach
                                    </select>
                                </div>

                                <div class="col-md-8 col-md-offset-2 form-check">
                                    <input id="edit_require_scan_serial_number1" name="require_scan_serial_number" class="form-check-input" type="radio" value="0">
                                    <label class="form-check-label" for="edit_require_scan_serial_number1">
                                        Default
                                    </label>
                                </div>
                                <div class="col-md-8 col-md-offset-2 form-check">
                                    <input id="edit_require_scan_serial_number2" name="require_scan_serial_number" class="form-check-input" type="radio" value="1">
                                    <label class="form-check-label" for="edit_require_scan_serial_number2">
                                        Require Scan Serial #
                                    </label>
                                </div>
                                <div class="col-md-8 col-md-offset-2 form-check">
                                    <input id="edit_require_scan_serial_number3" name="require_scan_serial_number" class="form-check-input" type="radio" value="2">
                                    <label class="form-check-label" for="edit_require_scan_serial_number3">
                                        Visual Scan Only
                                    </label>
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


    <!--Remove Modal -->
    <div class="modal fade" id="removeShippingClient" tabindex="-1" role="dialog" aria-labelledby="removeShippingClientLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title text-primary" id="removeShippingClientLabel">
                        Removing Shipping Client: <strong><span id="remove_name"></span></strong>
                    </h4>
                </div>
                <form id="edit_shipper_address" class="form-horizontal" method="POST" action="{{ route('shippingclients.remove') }}" >
                    <input type="hidden" id="remove_id" name="id" />
                    @csrf
                    <div class="modal-body">
                        <h4 class="text-danger">Confirming will Permanetly Remove this Shipping Client!</h4>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default close-btn so-btn-close pull-left" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-danger so-btn">Confirm <i class="fa fa-exclamation"></i></button>
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
        function editShippingClient(id, name, tpl_client_id, shipping_markup, required_scan_serial, carriers) {
            console.log("Hello world", carriers);
            document.querySelector('#edit_id').value = id;
            document.querySelector('#edit_name').value = name;
            document.querySelector('#edit_tpl_client_id').value = tpl_client_id;
            document.querySelector('#edit_shipping_markup').value = shipping_markup;
            if(required_scan_serial == 1){
              document.querySelector('#edit_require_scan_serial_number2').checked = 1;
            }else if(required_scan_serial == 2){
              document.querySelector('#edit_require_scan_serial_number3').checked = 1;
            }else if(required_scan_serial == 0){
              document.querySelector('#edit_require_scan_serial_number1').checked = 1;
            }
            console.log(carriers.split(","));
            $("#edit_shipping_carriers").val( carriers.split(",") );


        }

        function removingShippingClient(id, name) {
            // console.log('removingShippingClient:', id, name);

            document.querySelector('#remove_id').value = id;
            document.querySelector('#remove_name').innerText = name;
        }
    </script>

    <!-- <script>
        var spackages = json(data['shipper']);
    </script>
    <script src=" asset('js/jquery/shipperadress.jquery.js') }}?v=preg_replace('/[^0-9]/', '', \Tremby\LaravelGitVersion\GitVersionHelper::getNameAndVersion())}}" defer></script> -->
@stop
