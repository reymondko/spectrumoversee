@extends('adminlte::page')


@section('title', 'Kit Return Sync Details')



@section('content_header')
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <h1 class="header-text">Kit Return Sync Details</h1>
    <!--<button type="button" id="topbar_search" class="search-bar-btn btn btn-default so-btn">
        <i class="fa fa-search"></i> Search
    </button>
    <input type="text" class="search-bar col-md-2" id="global_search" aria-describedby="search" placeholder="Search">-->
@stop


@section('content')
@if(isset($_GET['status']) && $_GET['status'] == 'created')
  <div class="alert alert-info alert-dismissible alert-saved">
      <button type="button" class="close alert-close-btn" data-dismiss="alert" aria-hidden="true">×</button>
      <h4><i class="icon fa fa-check"></i>Receiver Created!</h4>
  </div>
@endif

<div class="box user-box">
    
    @if($data['batch']->batch_status=="open")
      <div class="box-header">
          <h3 class="box-title"></h3>
          <!--<div class="box-tools pull-right">
            @if (!is_numeric($data['batch']->receiver_id))
              <a href="/kit-return-sync/summary/{{ $data['id'] }}/create-receiver" class="btn btn-primary">Create Receiver</a>
            @endif
          </div>!-->
          <!-- /.box-tools -->
          
          <div class="text-right" >
            <form action="/kit-return-sync/closebatch" method="POST">
              @csrf
              <input type="hidden" value="<?=$data['id']?>" id="batch_id" name="batch_id">
              <button type="submit" class="btn btn-flat so-btn" data-toggle="modal" data-target="#closeBatch"  id="close_batch_btn">Close Batch</button>
            </form>
          </div>
      </div>
    @endif  
   <!-- /.box-header -->
   <div class="box-body">
      <a href="/kit-return-sync/logiwa/summary/<?=$data['id']?>/export" class="btn btn-primary so-btn" style="position:absolute">Export Logiwa Spreadsheets</a>
      
      <form id="deletebatchItemsForm" type="POST">
        @csrf
        <!--<button type="button" onclick="deleteBI()" style="position: absolute;"><i class="fa fa-trash"></i>Delete Batch Item</button>
        <button type="button" onclick="deleteBN()" style="position: absolute;"><i class="fa fa-trash"></i>Delete Batch Number</button>!-->
          <table id="details_table" class="table table-striped table-bordered" style="width:100%">
            <thead class="table_head">
                <tr>
                  <!--<th><input type="checkbox"  name="deleteAll" id="deleteAll" Alt="Delete All"></th>!-->
                  <th class="table_head_th"><button class="pull-left" style="color:black" type="button" onclick="deleteBN()" title="Delete Batch"><i class="fa fa-trash"></i></button>Batch ID</th>
                  <th class="table_head_th">Batch Date</th>
                  <th class="table_head_th">SKU</th>
                  <th class="table_head_th">Company</th>
                  <th class="table_head_th">Master Kit #</th>
                  <th class="table_head_th">Subkit #</th>
                  <th class="table_head_th">Return Tracking #</th>
                  <th class="table_head_th">Box #</th>
                  <th class="table_head_th">Subkit Date</th>
                  <th class="table_head_th">Actions</th>
                </tr>
            </thead>
            <tbody>
              @foreach($data['data'] as $row)
                <tr>
                  <!--<td><input type="checkbox" class=" deleteThis" name="deleteid[]" id="deleteid" Alt="Delete " value={{ $row->bi_id }}></td>!-->
                  <td>{{ $row->batch_number }}</td>
                  <td>{{ $row->created_at }}</td>
                  <td>{{ $row->sku }}</td>
                  <td>{{ $row->company_name }}</td>
                  <td class="text-center">{{ $row->master_kit_id }}</td>
                  <td class="text-center">{{ $row->subkit_id }}</td>
                  <td class="text-center">{{ $row->return_tracking }}</td>
                  <td class="text-center">{{ $row->box_id }}</td>
                  <td class="text-center">{{ $row->subkit_created_at }}</td>
                  <td class="text-center">
                    <a href="#" onclick="editBI({{ $row->bi_id }},'{{ $row->master_kit_id }}')"><i class="fa fa-pencil"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;
                    <a href="#" onclick="deleteBI({{ $row->bi_id }})"><i class="fa fa-trash"></i></a></td>

                </tr>
              @endforeach
            </tbody> 
          </table>

          <input type="hidden" name="batchid" id="batchid" value="{{ $row->id }}">
          <!--<button type="button" onclick="deleteBI()"><i class="fa fa-trash"></i>Delete Batch Item</button>
          <button type="button" onclick="deleteBN()" style="position: absolute;"><i class="fa fa-trash"></i>Delete Batch Number</button>!-->
      </form>
          <div style="text-align: center">{!! $data['data']->render() !!}</div>

          
   </div>
   <!-- /.box-body -->
</div>
<!-- /.box -->

<!--Add Printer Modal -->
  <div class="modal fade" id="editSubkitModal" tabindex="-1" role="dialog" aria-labelledby="editSubkitModalz" aria-hidden="true">
    <div class="modal-dialog modal-content-box" role="document">
        <div class="modal-content">
            <form method="POST" id="editSubKits" action="{{ route('editMasterKit') }}">
              <input type="hidden" name="edit_batch_item_id" id="edit_batch_item_id" >
              @csrf
                <div class="modal-header">
                    <h4 class="modal-title" id="addRuleModalLabel">Edit <span id="masterkit"></span></h4>
                </div>
                <div class="modal-body"> 
                    <fieldset id="add_printer_fieldset"> 
                      <div class="form-group">
                        <div class="col-md-10 col-md-offset-1">
                        </div>
                      </div>
                      <div class="form-group">
                        <div class="col-md-10 col-md-offset-1">
                          <label for="printer_name">Master Kit #</label> 
                          <input type="text" autocomplete="off" placeholder="Master Kit #" name="edit_master_kit_input" id="edit_master_kit_input" class="form-control" required>
                        </div>
                      </div>
                      <div class="form-group">
                        <div class="col-md-10 col-md-offset-1">
                        </div>
                      </div>
                      <div class="form-group">
                        <div class="col-md-10 col-md-offset-1">
                          <label for="printer_desc">Sub Kit #</label>
                          <input type="text" autocomplete="off" placeholder="Subkit Number" name="edit_sub_kit_number" id="edit_sub_kit_number" class="form-control single_barcode" required>
                        </div>
                      </div>
                      <div class="form-group">
                        <div class="col-md-10 col-md-offset-1">
                        </div>
                      </div>
                      <div class="form-group">
                        <div class="col-md-10 col-md-offset-1">
                          <label for="printer_desc">Return Tracking Number</label>
                          <input type="text" autocomplete="off" placeholder="Return Tracking Number"  name="edit_return_tracking_number" id="edit_return_tracking_number" class="form-control single_barcode" required>
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

@stop
@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
    <link rel="stylesheet" href="/css/users.css">
    <link rel="stylesheet" href="/css/spectrumoversee.tables.css">
@stop

@section('js')
<script src="{{ asset('js/jquery/users.jquery.js') }}?{{preg_replace('/[^0-9]/', '', \Tremby\LaravelGitVersion\GitVersionHelper::getNameAndVersion())}}" defer></script>
<script type="text/javascript" src="/js/jquery/topbarsearch.jquery.js?{{preg_replace('/[^0-9]/', '', \Tremby\LaravelGitVersion\GitVersionHelper::getNameAndVersion())}}"></script>
<script>
    //Press Enter in INPUT moves cursor to next INPUT
    $(document).ready(function(){
        $('#editSubKits').find('.input').keypress(function(e){
            if ( e.which == 13 ) // Enter key = keycode 13
            {
                $(this).next().focus();  //Use whatever selector necessary to focus the 'next' input
                return false;
            }
        });
       
    });
      
    function editBI(id,masterkit){
      
      $("#edit_batch_item_id").val(id);
      $("#masterkit").html(masterkit);
      
      $("#editSubkitModal").modal("show");

    }

    


    function download_files(files) {
        function download_next(i) {
            if (i >= files.length) {
            return;
            }
            var a = document.createElement('a');
            a.href = files[i].download;
            a.target = '_parent';
            // Use a.download if available, it prevents plugins from opening.
            if ('download' in a) {
            a.download = files[i].filename;
            }
            // Add a to the doc for click to work.
            (document.body || document.documentElement).appendChild(a);
            if (a.click) {
            a.click(); // The click method is supported by most browsers.
            } else {
            $(a).click(); // Backup using jquery
            }
            // Delete the temporary link.
            a.parentNode.removeChild(a);
            // Download the next file with a small timeout. The timeout is necessary
            // for IE, which will otherwise only download the first file.
            setTimeout(function() {
            download_next(i + 1);
            }, 500);
        }
        // Initiate the first download.
        download_next(0);
    }

    // function exportFiles() {
    //     download_files([
    //     { download: "/kit-return-sync/logiwa/summary/<?=$data['id']?>/export?type=serial", filename: "Receipt Serials Export-<?= date('m.d.y'); ?>.xlsx" },
    //     { download: "/kit-return-sync/logiwa/summary/<?=$data['id']?>/export?type=order", filename: "Receipt Order Export-<?= date('m.d.y'); ?>.xlsx" },
    //     ]);
    // };

    // $('#export').click(function(){
    //     window.open('/kit-return-sync/logiwa/summary/<?=$data['id']?>/export?type=serial', '_blank');
    //     setTimeout(function(){
    //        window.location.href = '/kit-return-sync/logiwa/summary/<?=$data['id']?>/export?type=order';
    //     }, 500);
    // });
</script>
@stop
