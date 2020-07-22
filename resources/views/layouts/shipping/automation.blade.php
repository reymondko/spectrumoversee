@extends('adminlte::page')


@section('title', 'Shipping Automation')


@section('content_header')
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <h1 class="header-text">SHIPPING AUTOMATION RULES</h1>
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
            <h4><i class="icon fa fa-check"></i>Deleted!</h4>
        </div>
    @endif

    <div class="container-fluid account-settings-container">
        <div class="row">
            <div class="box inventory-box">
                @can('admin-only')
                    <div class="box-header">
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-default so-btn" data-toggle="modal" data-target="#addRuleModal" >
                                <i class="fa fa-plus"></i> Add Rule
                            </button>
                        </div>
                        <!-- /.box-tools -->
                    </div>
                @endcan
                <!-- /.box-header -->
                <div class="box-body">
                    </br>
                    </br>
                    <table id="automation_rules_table" class="table table-striped table-bordered" style="width:100%">
                        <thead class="table_head">
                            @if(Gate::allows('admin-only', auth()->user()))
                            <th class="table_head_th col-md-2">Company Name</th>
                            @endif
                            <th class="table_head_th col-md-5">Name</th>
                            <th class="table_head_th">Min Shipping Days</th>
                            <th class="table_head_th">Max Shipping Days</th>
                            <th class="table_head_th col-md-2">Actions</th>
                        </thead>
                        <tbody>
                            @foreach($data['automation_rules'] as $r)
                                <tr>
                                    @if(Gate::allows('admin-only', auth()->user()))
                                        <th class="table_head_th col-md-2">{{$r->company->company_name}}</th>
                                    @endif
                                    <td>{{$r->name}}</td>
                                    <td>{{$r->min_shipping_days}}</td>
                                    <td>{{$r->max_shipping_days}}</td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-flat action-button">Action</button>
                                            <button type="button" class="btn btn-flat dropdown-toggle action-button" data-toggle="dropdown" aria-expanded="false">
                                                <span class="caret"></span>
                                                <span class="sr-only">Toggle Dropdown</span>
                                            </button>
                                            <ul class="dropdown-menu" role="menu">
                                                <li><a class="action-list-a" href="#" data-toggle="modal" data-target="#editRuleModal" onClick="editRule({{$r->id}})">Edit</a></li>
                                                <li><a class="action-list-a"  href="{{route('shiping_automation_rules_delete',['id' => $r->id])}}">Delete</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- /.box-body -->
            </div>
        </div>
    <!-- /.box-body -->
    </div>
    </div>
    </div>

    <!--Add Rule Modal -->
    <div class="modal fade" id="addRuleModal" tabindex="-1" role="dialog" aria-labelledby="addRuleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-content-box" role="document">
            <div class="modal-content">
                <form method="POST" action="{{route('shiping_automation_rules_save')}}">
                    @csrf
                    <div class="modal-header">
                        <h4 class="modal-title" id="addRuleModalLabel">Shipping Automation Rules</h4>
                    </div>
                    <div class="modal-body">
                    <fieldset id="add_rule_fieldset">
                        <div class="form-group">
                            <div class="col-md-10 col-md-offset-1">
                                @if(Gate::allows('admin-only', auth()->user()))
                                <select class="form-control select-md cmd" name="company" required="">
                                    <option value="">
                                        Company
                                    </option>
                                    @foreach($data['companies'] as $c)
                                        <option value="{{$c->id}}">{{$c->company_name}}</option>
                                    @endforeach
                                </select></br>
                                @endif
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-10 col-md-offset-1">
                                <input id="rule_name" name="rule_name" type="text" placeholder="Automation Rule Name" class="form-control input-md" required></br>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-10 col-md-offset-1">
                                <input id="threepl_customer_id" name="threepl_customer_id" type="text" placeholder="3PL Customer ID" class="form-control input-md" required></br>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-10 col-md-offset-1">
                                <select id="automation_method" name="automation_method"  placeholder="Automation Method" class="form-control input-md" required>
                                    <option value="">Automation Method</option>
                                    <option value="least_expensive">Least Expensive</option>
                                    <option value="fastest_delivery_time">Fastest Delivery Time</option>
                                </select></br>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-10 col-md-offset-1">
                                <input id="min_shipping_days" name="min_shipping_days" type="number" placeholder="Min Shipping Days" class="form-control input-md" required></br>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-10 col-md-offset-1">
                                <input id="max_shipping_days" name="max_shipping_days" type="number" placeholder="Max Shipping Days" class="form-control input-md" required></br>
                                </br>
                            </div>
                        </div>

                        <!-- Domestic Carriers -->
                        <div class="row carrier-row">
                            <div class="form-group section-label carrier-div">
                                Allowed Shipping Carriers(Domestic):
                            </div>
                            <div id="shipping_dom">
                                <div class="form-group select-carrier-div">
                                    <div class="col-md-12">
                                        <div class="form-group row">
                                            <div class="col-md-5 col-md-offset-1">
                                                <select class="form-control select-md cs" name="carrier_name_dom[]" required="" data-target="cmd">
                                                    <option value="">
                                                        Carrier
                                                    </option>

                                                    @foreach($data['shipping_carriers'] as $s)
                                                        <option value="{{$s->name}}" data-tag="{{$s->id}}">
                                                            {{$s->name}}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-5">
                                                <select class="form-control select-md cmd" name="carrier_method_dom[]" required="">
                                                    <option value="">
                                                        Method
                                                    </option>

                                                    @foreach($data['shipping_carrier_methods'] as $c)
                                                        <option value="{{$c->value}}" class="cm cmd-option cmd-cm-{{$c->shipping_carriers_id}}">
                                                            {{$c->name}}
                                                        </option>
                                                    @endforeach
                                                    <option value="ALLAVAILABLE">
                                                        All Available Services
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <span class="col-md-2 inventory-field-btn-right">
                                <button id="add_carrier_dom" type="button" class="btn btn-flat so-btn"><i class="fa fa-plus"></i>&nbsp;&nbsp;Add Carrier</button>
                            </span>
                        </div>

                        <!-- International Carriers -->
                        <div class="row carrier-row">
                            <div class="form-group section-label carrier-div">
                                Allowed Shipping Carriers(International):
                            </div>
                            <div id="shipping_int">
                                <div class="form-group select-carrier-div">
                                    <div class="col-md-12">
                                        <div class="form-group row">
                                            <div class="col-md-5 col-md-offset-1">
                                                <select class="form-control select-md csi" name="carrier_name_int[]" required="" data-target="cmi">
                                                    <option value="">
                                                        Carrier
                                                    </option>

                                                    @foreach($data['shipping_carriers'] as $s)
                                                        <option value="{{$s->name}}" data-tag="{{$s->id}}">
                                                            {{$s->name}}
                                                        </option>
                                                    @endforeach

                                                </select>
                                            </div>
                                            <div class="col-md-5">
                                                <select class="form-control select-md cmi" name="carrier_method_int[]" required="">
                                                    <option value="">
                                                        Method
                                                    </option>

                                                    @foreach($data['shipping_carrier_methods'] as $c)
                                                        <option value="{{$c->value}}" class="cm cmi-option cmi-cm-{{$c->shipping_carriers_id}}">
                                                            {{$c->name}}
                                                        </option>
                                                    @endforeach

                                                    <option value="ALLAVAILABLE">
                                                        All Available Services
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <span class="col-md-2 inventory-field-btn-right">
                                <button id="add_carrier_int" type="button" class="btn btn-flat so-btn"><i class="fa fa-plus"></i>&nbsp;&nbsp;Add Carrier</button>
                            </span>
                        </div>

                        <!-- Printing Carriers -->
                        <div class="row carrier-row" style="display:none">
                            <div class="form-group section-label carrier-div">
                                Allowed Shipping Label Printer:
                            </div>
                            <div id="printers_selection">
                                <div class="form-group select-printer-div">
                                    <div class="col-md-12">
                                        <div class="form-group row">
                                            <div class="col-md-10 col-md-offset-1">
                                                <select class="form-control select-md printer_select" name="printer_ids[]" required1="" data-target="pdt">
                                                    <option value="">
                                                        Select Printer
                                                    </option>

                                                    @foreach($data['printers'] as $printer)
                                                        <option value="{{ $printer['PrinterName'] }} -|- {{ $printer['WebShippingPrinterId'] }}" data-tag="{{ $printer['WebShippingPrinterId'] }}">
                                                            {{ $printer['PrinterName'] }}
                                                        </option>
                                                    @endforeach

                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <span class="col-md-2 inventory-field-btn-right">
                                <button id="add_printer" type="button" class="btn btn-flat so-btn"><i class="fa fa-plus"></i>&nbsp;&nbsp;Add Printer</button>
                            </span>
                        </div>

                    </fieldset>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-flat so-btn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!--Edit Rule Modal -->
    <div class="modal fade" id="editRuleModal" tabindex="-1" role="dialog" aria-labelledby="editRuleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-content-box" role="document">
            <div class="modal-content">
                <form method="POST" action="{{route('shiping_automation_rules_update')}}">
                    @csrf
                    <div class="modal-header">
                        <h4 class="modal-title" id="editRuleModalLabel">Shipping Automation Rules</h4>
                    </div>
                    <div class="modal-body">
                        <fieldset id="edit_rule_fieldsset">
                            <div class="form-group">
                                <div class="col-md-10 col-md-offset-1">
                                    <input id="edit_rule_name" name="edit_rule_name" type="text" placeholder="Automation Rule Name" class="form-control input-md" required></br>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-10 col-md-offset-1">
                                    <input id="edit_threepl_customer_id" name="edit_threepl_customer_id" type="text" placeholder="3PL Customer ID" class="form-control input-md" required></br>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-10 col-md-offset-1">
                                    <select id="edit_automation_method" name="edit_automation_method"  placeholder="Automation Method" class="form-control input-md" required>
                                        <option value="">Automation Method</option>
                                        <option value="least_expensive">Least Expensive</option>
                                        <option value="fastest_delivery_time">Fastest Delivery Time</option>
                                    </select></br>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-10 col-md-offset-1">
                                    <input id="edit_min_shipping_days" name="edit_min_shipping_days" type="number" placeholder="Min Shipping Days" class="form-control input-md" required></br>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-10 col-md-offset-1">
                                    <input id="edit_max_shipping_days" name="edit_max_shipping_days" type="number" placeholder="Max Shipping Days" class="form-control input-md" required></br>
                                    </br>
                                </div>
                            </div>

                            <!-- Domestic Carriers -->
                            <div class="row carrier-row">
                                <div class="form-group section-label carrier-div">
                                    Allowed Shipping Carriers(Domestic):
                                </div>
                                <div id="edit_shipping_dom">
                                </div>
                                <span class="col-md-2 inventory-field-btn-right">
                                    <button id="edit_add_carrier_dom" type="button" class="btn btn-flat so-btn"><i class="fa fa-plus"></i>&nbsp;&nbsp;Add Carrier</button>
                                </span>
                            </div>

                            <!-- International Carriers -->
                            <div class="row carrier-row">
                                <div class="form-group section-label carrier-div">
                                    Allowed Shipping Carriers(International):
                                </div>
                                <div id="edit_shipping_int">
                                </div>
                                <span class="col-md-2 inventory-field-btn-right">
                                    <button id="edit_add_carrier_int" type="button" class="btn btn-flat so-btn"><i class="fa fa-plus"></i>&nbsp;&nbsp;Add Carrier</button>
                                </span>
                            </div>

                            <!-- Shiprush Printers -->
                            <div class="row carrier-row" style="display:none">
                                <div class="form-group section-label carrier-div">
                                    Allowed Shipping Label Printer:
                                </div>
                                <div id="edit_printer">
                                </div>
                                <span class="col-md-2 inventory-field-btn-right">
                                    <button id="edit_add_printer" type="button" class="btn btn-flat so-btn"><i class="fa fa-plus"></i>&nbsp;&nbsp;Add Printer</button>
                                </span>
                            </div>
                        </fieldset>
                    </div>
                    <div class="modal-footer">
                        <input id="shipping_automation_rule_id" name="shipping_automation_rule_id" type="hidden">
                        <button type="submit" class="btn btn-flat so-btn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop


@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
    <link rel="stylesheet" href="/css/spectrumoversee.tables.css">
    <link rel="stylesheet" href="/css/shipping.css">
@stop



@section('js')
    <script>
        var carrierOptions = '';
        var carrierMethods = '';
        var printersOptions = '';

        @foreach($data['shipping_carriers'] as $s)
            carrierOptions += '<option value="{{$s->name}}" data-tag="{{$s->id}}">{{$s->name}}</option>';
        @endforeach

        @foreach($data['shipping_carrier_methods'] as $c)
            carrierMethods += '<option value="{{$c->value}}" class="cm #data-target#-option #data-target#-cm-{{$c->shipping_carriers_id}}">{{$c->name}}</option>';
        @endforeach
            carrierMethods += '<option value="ALLAVAILABLE">All Available Services</option>';

        @foreach($data['printers'] as $p)
            printersOptions += `<option value="{{ $printer['PrinterName'] }} -|- {{ $p['WebShippingPrinterId'] }}" data-tag="{{ $p['WebShippingPrinterId'] }}">{{ $p['PrinterName'] }}</option>`;
        @endforeach

        $('#add_carrier_dom').click(function(){
            var l = $('.cs').length+2;

            var dataToAppend = '<div class="form-group select-carrier-div slc'+l+'">';
            dataToAppend += '<div class="col-md-12">';
            dataToAppend += '<div class="form-group row">';
            dataToAppend += '<div class="col-md-5 col-md-offset-1">';
            dataToAppend += '<select class="form-control select-md cs" name="carrier_name_dom[]" required data-target="cmd'+l+'">';
            dataToAppend += '<option value="">Carrier</option>';
            dataToAppend += carrierOptions;
            dataToAppend += '</select>';
            dataToAppend += '</div>';
            dataToAppend += '<div class="col-md-5">';
            dataToAppend += '<select class="form-control select-md cmd'+l+'" name="carrier_method_dom[]" required>';
            dataToAppend += '<option value="">Method</option>';
            dataToAppend += carrierMethods.replace(new RegExp('#data-target#', 'g'),'cmd'+l);
            dataToAppend += '</select></div>';
            dataToAppend += '<div class="col-md-1">';
            dataToAppend += `<a href="#" class="carrier-delete" onClick="removeElemByClass('slc${l}')"><i class="fa fa-trash"></a>`;
            dataToAppend += '</div>';
            dataToAppend += '</div></div></div>';
            $('#shipping_dom').append(dataToAppend);
        });


        $('#add_carrier_int').click(function(){
            var l = $('.csi').length+2;

            var dataToAppend = '<div class="form-group select-carrier-div slci'+l+'">';
            dataToAppend += '<div class="col-md-12">';
            dataToAppend += '<div class="form-group row">';
            dataToAppend += '<div class="col-md-5 col-md-offset-1">';
            dataToAppend += '<select class="form-control select-md csi" name="carrier_name_int[]" required data-target="cmi'+l+'">';
            dataToAppend += '<option value="">Carrier</option>';
            dataToAppend += carrierOptions;
            dataToAppend += '</select>';
            dataToAppend += '</div>';
            dataToAppend += '<div class="col-md-5">';
            dataToAppend += '<select class="form-control select-md cmi'+l+'" name="carrier_method_int[]" required>';
            dataToAppend += '<option value="">Method</option>';
            dataToAppend += carrierMethods.replace(new RegExp('#data-target#', 'g'),'cmi'+l);
            dataToAppend += '</select></div>';
            dataToAppend += '<div class="col-md-1">';
            dataToAppend += `<a href="#" class="carrier-delete" onClick="removeElemByClass('slci${l}')"><i class="fa fa-trash"></a>`;
            dataToAppend += '</div>';
            dataToAppend += '</div></div></div>';
            $('#shipping_int').append(dataToAppend);
        });


        $('#add_printer').click(function(){
            var l = $('.printer_select').length+2;

            var dataToAppend = `
                <div class="form-group select-printer-div pi${l}">
                    <div class="col-md-12">
                        <div class="form-group row">
                            <div class="col-md-10 col-md-offset-1">
                                <select class="form-control select-md printer_select" name="printer_ids[]" required data-target="pdt${l}">
                                    <option value="">Select Printer</option>
                                    ${printersOptions}
                                </select>
                            </div>
                            <div class="col-md-1">
                                <a href="#" class="printer-delete" onClick="removeElemByClass('pi${l}')"><i class="fa fa-trash"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            $('#printers_selection').append(dataToAppend);
        });

        $('#edit_add_carrier_int').click(function(){
            var l = $('.csi').length+2;

            var dataToAppend = '<div class="form-group select-carrier-div edit-slci'+l+'">';
            dataToAppend += '<div class="col-md-12">';
            dataToAppend += '<div class="form-group row">';
            dataToAppend += '<div class="col-md-5 col-md-offset-1">';
            dataToAppend += '<select class="form-control select-md edit-cs" name="edit_carrier_name_int[]" required data-target="cmi'+l+'">';
            dataToAppend += '<option value="">Carrier</option>';
            dataToAppend += carrierOptions;
            dataToAppend += '</select>';
            dataToAppend += '</div>';
            dataToAppend += '<div class="col-md-5">';
            dataToAppend += '<select class="form-control select-md edit-cmi'+l+'" name="edit_carrier_method_int[]" required>';
            dataToAppend += '<option value="">Method</option>';
            dataToAppend += carrierMethods.replace(new RegExp('#data-target#', 'g'),'edit-cmi'+l);
            dataToAppend += '</select></div>';
            dataToAppend += '<div class="col-md-1">';
            dataToAppend += `<a href="#" class="carrier-delete" onClick="removeElemByClass('edit-slci${l}')"><i class="fa fa-trash"></a>`;
            dataToAppend += '</div>';
            dataToAppend += '</div></div></div>';
            $('#edit_shipping_int').append(dataToAppend);

        });

        $('#edit_add_carrier_dom').click(function(){
            var l = $('.cs').length+2;

            var dataToAppend = '<div class="form-group select-carrier-div slc'+l+'">';
            dataToAppend += '<div class="col-md-12">';
            dataToAppend += '<div class="form-group row">';
            dataToAppend += '<div class="col-md-5 col-md-offset-1">';
            dataToAppend += '<select class="form-control select-md cs" name="edit_carrier_name_dom[]" required data-target="cmd'+l+'">';
            dataToAppend += '<option value="">Carrier</option>';
            dataToAppend += carrierOptions;
            dataToAppend += '</select>';
            dataToAppend += '</div>';
            dataToAppend += '<div class="col-md-5">';
            dataToAppend += '<select class="form-control select-md cmd'+l+'" name="edit_carrier_method_dom[]" required>';
            dataToAppend += '<option value="">Method</option>';
            dataToAppend += carrierMethods.replace(new RegExp('#data-target#', 'g'),'cmd'+l);
            dataToAppend += '</select></div>';
            dataToAppend += '<div class="col-md-1">';
            dataToAppend += `<a href="#" class="carrier-delete" onClick="removeElemByClass('slc${l}')"><i class="fa fa-trash"></a>`;
            dataToAppend += '</div>';
            dataToAppend += '</div></div></div>';
            $('#edit_shipping_dom').append(dataToAppend);
        });

        $('#edit_add_printer').click(function(){
            var l = $('.cs').length+2;

            var dataToAppend = `
                <div class="form-group select-carrier-div pi${l}">
                    <div class="col-md-12">
                        <div class="form-group row">
                            <div class="col-md-5 col-md-offset-1">
                                <select class="form-control select-md cs" name="edit_printer_name[]" required data-target="pdt${l}>
                                    <option value="">Printers</option>
                                    ${printerOptions}
                                </select>
                            </div>
                            <div class="col-md-1">
                                <a href="#" class="printer-delete" onClick="removeElemByClass('slc${l}')"><i class="fa fa-trash"></a>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            $('#edit_printer').append(dataToAppend);
        });



        function editRule(id)
        {
            $('#edit_rule_name').val('');
            $('#edit_threepl_customer_id').val('');
            $('#edit_automation_method').val('');
            $('#edit_min_shipping_days').val('');
            $('#edit_max_shipping_days').val('');
            $('#shipping_automation_rule_id').val('');
            $('#edit_shipping_dom').html('');
            $('#edit_shipping_int').html('');


            $.get( "/shipping/automation/details?id="+id, function( data ) {
                if(data.success){
                    var rule = data.data;
                    console.log('rule:',rule.shiprush_printers)
                    $('#edit_rule_name').val(rule.name);
                    $('#edit_threepl_customer_id').val(rule.threepl_customer_id);
                    $('#edit_automation_method').val(rule.automation_method);
                    $('#edit_min_shipping_days').val(rule.min_shipping_days);
                    $('#edit_max_shipping_days').val(rule.max_shipping_days);
                    $('#shipping_automation_rule_id').val(id);

                    $(rule.shipping_carriers_domestic).each(function(key,value){
                        var dataToAppend = '<div class="form-group select-carrier-div edit-slc'+key+'">';
                        dataToAppend += '<div class="col-md-12">';
                        dataToAppend += '<div class="form-group row">';
                        dataToAppend += '<div class="col-md-5 col-md-offset-1">';
                        dataToAppend += '<select class="form-control select-md edit-cs" name="edit_carrier_name_dom[]" required data-target="cmd'+key+'">';
                        dataToAppend += '<option value="">Carrier</option>';
                        dataToAppend += carrierOptions;
                        dataToAppend += '</select>';
                        dataToAppend += '</div>';
                        dataToAppend += '<div class="col-md-5">';
                        dataToAppend += '<select class="form-control select-md edit-cmd'+key+'" name="edit_carrier_method_dom[]" required>';
                        dataToAppend += '<option value="">Method</option>';
                        dataToAppend += carrierMethods.replace(new RegExp('#data-target#', 'g'),'edit-cmd'+key);
                        dataToAppend += '</select></div>';
                        dataToAppend += '<div class="col-md-1">';
                        dataToAppend += `<a href="#" class="carrier-delete" onClick="removeElemByClass('edit-slc${key}')"><i class="fa fa-trash"></a>`;
                        dataToAppend += '</div>';
                        dataToAppend += '</div></div></div>';
                        $('#edit_shipping_dom').append(dataToAppend);

                        $('[data-target="cmd'+key+'"]').val(value.carrier).trigger('change');
                        $('.edit-cmd'+key).val(value.method);
                    });

                    $(rule.shipping_carriers_international).each(function(key,value){
                        var dataToAppend = '<div class="form-group select-carrier-div edit-slci'+key+'">';
                        dataToAppend += '<div class="col-md-12">';
                        dataToAppend += '<div class="form-group row">';
                        dataToAppend += '<div class="col-md-5 col-md-offset-1">';
                        dataToAppend += '<select class="form-control select-md edit-cs" name="edit_carrier_name_int[]" required data-target="cmi'+key+'">';
                        dataToAppend += '<option value="">Carrier</option>';
                        dataToAppend += carrierOptions;
                        dataToAppend += '</select>';
                        dataToAppend += '</div>';
                        dataToAppend += '<div class="col-md-5">';
                        dataToAppend += '<select class="form-control select-md edit-cmi'+key+'" name="edit_carrier_method_int[]" required>';
                        dataToAppend += '<option value="">Method</option>';
                        dataToAppend += carrierMethods.replace(new RegExp('#data-target#', 'g'),'edit-cmi'+key);
                        dataToAppend += '</select></div>';
                        dataToAppend += '<div class="col-md-1">';
                        dataToAppend += `<a href="#" class="carrier-delete" onClick="removeElemByClass('edit-slci${key}')"><i class="fa fa-trash"></a>`;
                        dataToAppend += '</div>';
                        dataToAppend += '</div></div></div>';
                        $('#edit_shipping_int').append(dataToAppend);

                        $('[data-target="cmi'+key+'"]').val(value.carrier).trigger('change');
                        $('.edit-cmi'+key).val(value.method);
                    });

                    $(rule.shiprush_printers).each(function(key,value){
                        var dataToAppend = `
                            <div class="form-group select-carrier-div edit-pi'${key}">
                                <div class="col-md-12">
                                    <div class="form-group row">
                                        <div class="col-md-10 col-md-offset-1">
                                            <select class="form-control select-md" name="edit_printer_name[]" required data-target="pdt${key}">
                                                <option value="">Printers</option>
                                                ${printerOptions}
                                            </select>
                                        </div>
                                        <div class="col-md-1">
                                            <a href="#" class="carrier-delete" onClick="removeElemByClass('edit-pi${key}')"><i class="fa fa-trash"></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        $('#edit_printer').append(dataToAppend);

                        $('[data-target="pdt'+key+'"]').val(value.carrier).trigger('change');
                        $('.edit-pdt'+key).val(value.method);
                    });
                }
            });
        }
    </script>
    <script type="text/javascript" src="/js/jquery/topbarsearch.jquery.js?v={{preg_replace('/[^0-9]/', '', \Tremby\LaravelGitVersion\GitVersionHelper::getNameAndVersion())}}"></script>
    <script type="text/javascript" src="/js/jquery/shipping.jquery.js?v={{preg_replace('/[^0-9]/', '', \Tremby\LaravelGitVersion\GitVersionHelper::getNameAndVersion())}}"></script>
@stop
