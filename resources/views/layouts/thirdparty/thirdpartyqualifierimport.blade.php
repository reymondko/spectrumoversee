@extends('adminlte::page')
@section('title', 'Third Party Item Qualifier Import')
@section('content_header')
<!-- CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">
<h1 class="header-text">
    THIRD PARTY ITEM QUALIFIER IMPORT
</h1>
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
<div class="box inventory-box">
   <div class="box-header">
      <h3 class="box-title">Third Party Qualifier Import</h3>
      <!-- <div class="box-tools pull-right">
            <input type="text" name="search" class="third-party-inventory-search" placeholder="Search by SKU" id="thirdparty_search"/>
            <button type="button"  class="btn btn-default so-btn" id="thirdparty_search_btn"/>Search</button>
        </div> -->
      </br>
      <!-- /.box-tools -->
   </div>
   <!-- /.box-header -->
   <div class="box-body">
        <div class="row">
        <div class="col-md-4 col-md-offset-4">
        <div class="box box-solid box-primary import-box" style="border: 1px solid #455576;">
            <div class="box-header import-box-header" style="color: #fff;background: #455576;background-color: #455576;">
                <h3 class="box-title">Import Files</h3>
            </div>
            <div class="box-body">
                <form method="POST" action="#" enctype="multipart/form-data" id="import_form">
                    @csrf                     
                    <div class="form-group">
                        <div>
                            <input type="file" class="form-control" id="import_file" name="import_file" placeholder="File" required="">
                        </div>
                        <br>
                        <div>
                            <input type="number" class="form-control" id="customer_id" name="customer_id" placeholder="Customer Id" required="">
                        </div>
                        <br>
                        <div class="form-group import-btn-container">
                            <button type="submit" class="btn btn-flat so-btn" id="sub_btn">submit</button>
                        </div>
                        <div id="totalImport" style="display:none">
                            <span id="total_imported">0</span> of <span id="total_count">0</span> imported
                        </div>
                    </div>
                </form>
            </div><!-- /.box-body -->
        </div><!-- /.box -->
    </div>
        </div>
   </div>
   <!-- /.box-body -->
</div>
@stop
@section('css')
<link rel="stylesheet" href="/css/admin_custom.css">
<link rel="stylesheet" href="/css/spectrumoversee.tables.css">
<link rel="stylesheet" href="/css/jquery-ui.css">

@stop
@section('js')
<script src="{{ asset('js/jquery-ui.js') }}" defer></script>
<script src="{{ asset('js/moment.js') }}" defer></script>
<script>
    $('#import_form').submit(function(e){
    var form = $(this);
    var formdata = false;
    if (window.FormData){
        formdata = new FormData(form[0]);
    }

    var formAction = form.attr('action');
    $.ajax({
        url         : '/thirdparty/qualifier/import/save',
        data        : formdata ? formdata : form.serialize(),
        cache       : false,
        contentType : false,
        processData : false,
        type        : 'POST',
        success     : function(data){
            console.log(data)
            if(data.success == true){
                console.log('success');
                $('#sub_btn').hide();
                $('#total_count').text(data.totalCount);
                $('#totalImport').show();
                importQualifiers();
            }else{
                alert('fail')
            }
        }
    });
    return false;
});

function importQualifiers(){
    $.get( "{{route('thirdparty_qualifier_import_api')}}", function( data ) {
        var totalCount = parseInt($('#total_count').text());
        $('#total_imported').text(totalCount - data.totalCount);
        if(data.totalCount > 0 && data.import_result == true){
            importQualifiers();
        }else{
            alert('done');
        }
    });
}
</script>
@stop