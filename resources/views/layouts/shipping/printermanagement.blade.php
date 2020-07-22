@extends('adminlte::page')


@section('title', 'Printer Management')


@section('content_header')
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <h1 class="header-text">PRINTER MANAGEMENT</h1>
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
			<div class="col">
				@if ($errors->any())
					<div class="alert alert-danger">
						<ul>
							@foreach ($errors->all() as $error)
								<li>{{ $error }}</li>
							@endforeach
						</ul>
					</div>
				@endif
			</div>
		</div>

        <div class="row">
            <div class="box inventory-box">
                @can('admin-only')
                    <div class="box-header">
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-default so-btn" data-toggle="modal" data-target="#addPrinterModal" >
                                <i class="fa fa-plus"></i> Add Printer
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
                            <th class="table_head_th col-md-2">ID</th>
                            <th class="table_head_th col-md-2">Name</th>
                            <th class="table_head_th col-md-5">Description</th>
                            <th class="table_head_th col-md-2">Actions</th>
                        </thead>
                        <tbody>
							@foreach ($printers as $p)
								<tr>
									<td class="text-center">{{ $p->id }}</td>
									<td>{{ $p->name }}</td>
									<td>{!! $p->description !!}</td>
									<td>
										<div class="btn-group">
											<button type="button" class="btn btn-flat action-button">Action</button>
											<button type="button" class="btn btn-flat dropdown-toggle action-button" data-toggle="dropdown" aria-expanded="false">
												<span class="caret"></span>
												<span class="sr-only">Toggle Dropdown</span>
											</button>
											<ul class="dropdown-menu" role="menu">
												<li><a class="action-list-a" data-toggle="modal" data-target="#editPrinterModal" onClick="populateEditModal('{{$p->id}}','{{$p->name}}','{{$p->description}}')">Edit</a></li>
												<li><a class="action-list-a" data-toggle="modal" data-target="#removePrinterModal" onClick="removeModal('{{$p->id}}','{{$p->name}}')">Remove</a></li>
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

	<!--Add Printer Modal -->
    <div class="modal fade" id="addPrinterModal" tabindex="-1" role="dialog" aria-labelledby="addPrinterModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-content-box" role="document">
            <div class="modal-content">
				<form method="POST" action="{{ route('shipping_automation.printer.store') }}">
					@csrf
                    <div class="modal-header">
                        <h4 class="modal-title" id="addRuleModalLabel">Adding Shipping Printer</h4>
                    </div>
                    <div class="modal-body">
						<fieldset id="add_printer_fieldset">
							<div class="form-group">
								<div class="col-md-10 col-md-offset-1">
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-10 col-md-offset-1">
									<label for="printer_name">Printer Name</label>
									<input type="text" name="name" id="printer_name" value="{{ old('name') }}" class="form-control" placeholder="Printer name" required>
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-10 col-md-offset-1">
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-10 col-md-offset-1">
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-10 col-md-offset-1">
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-10 col-md-offset-1">
									<label for="printer_desc">Printer Description</label>
									<textarea class="form-control" name="description" id="printer_desc" placeholder="Printer description" required>{{ old('description') }}</textarea>
								</div>
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

	<!--Edit Printer Modal -->
    <div class="modal fade" id="editPrinterModal" tabindex="-1" role="dialog" aria-labelledby="addPrinterModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-content-box" role="document">
            <div class="modal-content">
				<form method="POST" action="{{ route('shipping_automation.printer.patch') }}">
					@csrf
					<input type="hidden" name="id" id="editing_printer_id" value="">
                    <div class="modal-header">
                        <h4 class="modal-title">Editing Shipping Printer</h4>
                    </div>
                    <div class="modal-body">
						<fieldset id="add_printer_fieldset">
							<div class="form-group">
								<div class="col-md-10 col-md-offset-1">
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-10 col-md-offset-1">
									<label for="edit_printer_name">Printer Name</label>
									<input type="text" name="name" id="edit_printer_name" value="{{ old('name') }}" class="form-control" placeholder="Printer name" required>
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-10 col-md-offset-1">
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-10 col-md-offset-1">
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-10 col-md-offset-1">
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-10 col-md-offset-1">
									<label for="edit_printer_desc">Printer Description</label>
									<textarea class="form-control" name="description" id="edit_printer_desc" placeholder="Printer description" required>{{ old('description') }}</textarea>
								</div>
							</div>
						</fieldset>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-flat so-btn">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

	<!--Remove Printer Modal -->
	<div class="modal fade" id="removePrinterModal" tabindex="-1" role="dialog" aria-labelledby="removePrinterModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-content-box" role="document">
			<div class="modal-content">
				<form method="POST" action="{{ route('shipping_automation.printer.remove') }}">
					@csrf
					<input type="hidden" name="id" id="removing_printer_id" value="">
					<div class="modal-header">
						<h4 class="modal-title">Are you sure you want to remove this printer</h4>
					</div>
					<div class="modal-body">
						<fieldset id="add_printer_fieldset">
							<div class="form-group">
								<div class="col-md-10 col-md-offset-1">
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-10 col-md-offset-1">
									<p class="form-control-static">Printer Name: <strong><span id="removing_printer_name"></span></strong></p>
								</div>
							</div>
						</fieldset>
					</div>
					<div class="modal-footer">
						<button type="submit" class="btn btn-flat so-btn">Remove Permanently</button>
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
		function populateEditModal(id, name, description) {
			$('#editing_printer_id').val(id);
			$('#edit_printer_name').val(name);
			$('#edit_printer_desc').val(description);
		}

		function removeModal(id, name) {
			console.log('removeModal:', id, name);
			$('#removing_printer_id').val(id);
			$('#removing_printer_name').text(name);
		}
	</script>
    <!-- <script type="text/javascript" src="/js/jquery/topbarsearch.jquery.js?v={{preg_replace('/[^0-9]/', '', \Tremby\LaravelGitVersion\GitVersionHelper::getNameAndVersion())}}"></script>
    <script type="text/javascript" src="/js/jquery/shipping.jquery.js?v={{preg_replace('/[^0-9]/', '', \Tremby\LaravelGitVersion\GitVersionHelper::getNameAndVersion())}}"></script> -->
@stop
