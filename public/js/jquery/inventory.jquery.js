$(document).ready(function() {
    $(function() {
        $( ".datepicker" ).datepicker();
    });

    $('#scan_barcode_no').bind("enterKey",function(e){
      });
      $('input').keyup(function(e){
        if(e.keyCode == 13)
        {
         $('#scan_btn').trigger('click');
        }
      });
    
      $('#scanInventoryModal').on('show.bs.modal', function(e) {
        setTimeout(function (){
            $('#scan_barcode_no').focus();
        }, 1000);
      })

      $('#check_all').click(function() {
        $("input.inventory-item-check").prop('checked',true);
        $('#delete_selected').show();
      });

      $('#uncheck_all').click(function() {
        $("input.inventory-item-check").prop('checked',false);
        $('#delete_selected').hide();
      });

      $('.inventory-item-check').change(function() {
        var count = $("input.inventory-item-check:checked").length;
        if(count > 0){
            $('#delete_selected').show();
        }else{
            $('#delete_selected').hide();
        }
      });

      $('#delete_selected').click(function(){
        var count = $("input.inventory-item-check:checked").length;
        $('#delete_count').html(count);
      });

      $(document).on('draw.dt', function () {
           $("input.inventory-item-check").prop('checked',false);
           $('.inventory-item-check').change(function() {
            var count = $("input.inventory-item-check:checked").length;
            if(count > 0){
                $('#delete_selected').show();
            }else{
                $('#delete_selected').hide();
            }
          });
      });

      $('#scanInventoryModal').on('hidden.bs.modal', function () {
            $('#scan_success').hide();
            $('#scan_fail').hide();
            $('#scan_count').html("0");
            $('#item_scans').html("0");
      })

      $('#update_status_save').click(function(){
       
        
        $('#update_status_select').hide();
        $('.updatestatus-loader-div').show();

        $.ajax({
            url: "/inventory/status/update",
            type: "post",
            data: {
                    inventory_id:$('#update_status_id').val(),
                    status:$('#update_status_value').val(), 
                    _token: $('meta[name="csrf-token"]').attr('content')
                  },
            success: function(data) {
                if(data.success = true){
                    $(`#${$('#update_status_id').val()}_status`).html($('#update_status_value').val().toUpperCase());
                    $(`#${$('#update_status_id').val()}_status`).attr('data-id',$('#update_status_value').val());
                    $('#inventory_fields_table').DataTable().rows().invalidate().draw();
                    $('.updatestatus-loader-div').hide();
                    $('#update_status_select').show();
                    $('#update_status_success').show();
                }
            }
        });

      });
    
});

$('#add-filter').click(function(){
    let invField = $('.filter-input').length;
    let limit = $('#inventory_fields_table > thead > tr > th').length;
    let fieldsSelected = [];

    $('.filter-select-field').each(function(){
       fieldsSelected.push($(this).val());
    });
    
    if(invField < limit){

        var fieldToAppend ='<div id="filter_div'+invField+'">'
         fieldToAppend += '<div class="form-group col-md-4" >';
         fieldToAppend += '<select class="form-control col-md-12 filter-select filter-select-field" onchange="selectFilterField('+invField+')" name="filterField[]" id="filter_select_field'+invField+'">';
         $('#inventory_fields_table > thead > tr > th').each(function(){
             if(!fieldsSelected.includes($(this).attr('id'))){
                if($(this).attr('id')){
                    fieldToAppend += '<option value="'+$(this).attr('id')+'">';
                    fieldToAppend += $(this).text();
                    fieldToAppend += '</option>';
                }
             }
        })

         fieldToAppend += '</select>';
         fieldToAppend += '</div>';
         fieldToAppend += '<div class="form-group col-md-4">';
         fieldToAppend += '<select class="col-md-4 form-control filter-select" name="filterType[]" id="filter_select_type'+invField+'">';
            fieldToAppend += '<option value="is">Is';
            fieldToAppend += '</option>';
            fieldToAppend += '<option value="is_not">Is Not';
            fieldToAppend += '</option>';
            fieldToAppend += '<option value="has">Has';
            fieldToAppend += '</option>';
         fieldToAppend += '</select>';
         fieldToAppend += '</div>';
         fieldToAppend += '<div class="form-group col-md-4">';
         fieldToAppend += '<input type="text" class="form-control col-md-8 filter-input" placeholder="value" name="filterValue[]" id="filter_input_value'+invField+'" required>';
         fieldToAppend += '<a href="#" onClick="removeFilterInput('+invField+')" class="filter-div-delete"><i class="fa fa-trash filter-div-delete-icn"></i></a>';
         fieldToAppend += '</div>';
         fieldToAppend += '</br></br>'
         fieldToAppend += '</div>';
       

        $('#filter-field-set').append(fieldToAppend);
    }
      
});


$('.inventory_field_map').focus(function() {
    selId =  $(this).attr('id');
    preval = $(this).val();
    pretext = $(`#${selId} option:selected`).text();
}).change(function(){
    dropdownval = $(this).val();
    if(dropdownval != 0){
        $('.inventory_field_map').not(this).find('option[value="' + dropdownval + '"]').remove();
        
    }
    if(preval != dropdownval){
        if(preval != 0){
            $(".inventory_field_map").not(this).append(new Option(pretext, preval));
        }
    }
});


function removeFilterInput(elem_id){
    $('#filter_div'+elem_id).remove();
}

function selectFilterField(elem_index){
    var elem_val = $('#filter_select_field'+elem_index).val();
    console.log(elem_val);
    var selectToReplace = '<select class="col-md-4 form-control filter-select" name="filterType[]" id="filter_select_type'+elem_index+'">';
            selectToReplace += '<option value="is">Is';
            selectToReplace += '</option>';
            selectToReplace += '<option value="is_not">Is Not';
            selectToReplace += '</option>';
            selectToReplace += '<option value="has">Has';
            selectToReplace += '</option>';
            selectToReplace += '</select>';
    var inputToReplace =  '<input type="text"  class="form-control col-md-8 filter-input" placeholder="value" name="filterValue[]" id="filter_input_value'+elem_index+'" required>';
    
    if( elem_val == 'created_at' || elem_val == 'last_scan_date'){
        var selectToReplace = '<select class="col-md-4 form-control filter-select" name="filterType[]" id="filter_select_type'+elem_index+'">';
            selectToReplace += '<option value="greater_than">Is Greater than';
            selectToReplace += '</option>';
            selectToReplace += '<option value="less_than">Is Less Than';
            selectToReplace += '</option>';
            selectToReplace += '</select>';

        var inputToReplace =  '<input type="text" autocomplete="off" class="form-control col-md-8 filter-input datepicker" placeholder="Date" name="filterValue[]" id="filter_input_value'+elem_index+'" required>';

    }else if(elem_val == 'last_scan_location'){
        var selectToReplace = '<select class="col-md-4 form-control filter-select" name="filterType[]" id="filter_select_type'+elem_index+'">';
            selectToReplace += '<option value="is">Is';
            selectToReplace += '</option>';
            selectToReplace += '<option value="is_not">Is Not';
            selectToReplace += '</option>';
            selectToReplace += '</select>';
        var inputToReplace =  '<select class="form-control col-md-8 filter-input" name="filterValue[]" id="filter_input_value'+elem_index+'" required>';
            inputToReplace += inventory_location_options;
            inputToReplace += '</select>';
            
    }else if(elem_val == 'last_scan_by'){
        var selectToReplace = '<select class="col-md-4 form-control filter-select" name="filterType[]" id="filter_select_type'+elem_index+'">';
            selectToReplace += '<option value="is">Is';
            selectToReplace += '</option>';
            selectToReplace += '<option value="is_not">Is Not';
            selectToReplace += '</option>';
            selectToReplace += '</select>';
            var inputToReplace =  '<select class="form-control col-md-8 filter-input" name="filterValue[]" id="filter_input_value'+elem_index+'" required>';
            inputToReplace += inventory_user_options;
            inputToReplace += '</select>';
    }else if(elem_val == 'status'){
        var selectToReplace = '<select class="col-md-4 form-control filter-select" name="filterType[]" id="filter_select_type'+elem_index+'">';
            selectToReplace += '<option value="is">Is';
            selectToReplace += '</option>';
            selectToReplace += '<option value="is_not">Is Not';
            selectToReplace += '</option>';
            selectToReplace += '</select>';
            var inputToReplace =  '<select class="form-control col-md-8 filter-input" name="filterValue[]" id="filter_input_value'+elem_index+'" required>';
            inputToReplace += '<option value="active">Active</option>';
            inputToReplace += '<option value="damaged">Damaged</option>';
            inputToReplace += '<option value="lost">Lost</option>';
            inputToReplace += '</select>';
    }

    $("#filter_input_value"+elem_index).replaceWith(inputToReplace);
    $("#filter_select_type"+elem_index).replaceWith(selectToReplace);
    $(function() {
        $( ".datepicker" ).datepicker();
    });

  
}
