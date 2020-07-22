    $('#add-filter').click(function(){
    let invField = $('.filter-input').length;
    let limit = 4;
    let fieldsSelected = [];


    $(".filter-input").each(function() {
        fieldsSelected.push($(this).val());
    });

    if(invField < limit){
        console.log(fieldsSelected.indexOf("status"));
        var fieldToAppend ='<div id="filter_div'+invField+'">'
         fieldToAppend += '<div class="form-group col-md-3" >';
         fieldToAppend += '<select class="form-control tpo-filter-field filter-input" onChange="filterFieldForm('+invField+')" id="filterField'+invField+'" name="filterField[]" required>';
         fieldToAppend += '<option value="">Select value to filter</option>';

         if(fieldsSelected.indexOf("date_created") == -1){
            fieldToAppend += '<option value="date_created">Date Created</option>';
         }

         if(fieldsSelected.indexOf("status") == -1){
            fieldToAppend += '<option value="status">Status</option>';
         }

         if(fieldsSelected.indexOf("ref") == -1){
            fieldToAppend += '<option value="ref">Reference Number</option>';
         }

         if(fieldsSelected.indexOf("ship_to_name") == -1){
            fieldToAppend += '<option value="ship_to_name">Ship to Name</option>';
         }

         fieldToAppend += '</select>';
         fieldToAppend += '</div>';
         fieldToAppend += '<div class="form-group col-md-6" id="filterVal'+invField+'">';
         fieldToAppend += '</div>';
         fieldToAppend += '</br></br>'
         fieldToAppend += '</div>';
        $('#filter-field-set').append(fieldToAppend);
    }
});

function removeFilterInput(elem_id){
    $('#filter_div'+elem_id).remove();
}

function filterFieldForm(id){
    var filterField = $('#filterField'+id).val();

    if(filterField == 'date_created'){
        $('#filterVal'+id).html('');
        var fieldToAppend = '<div class="form-group">';
            fieldToAppend += '<input type="text" class="form-control tpo-dates datepicker" name="from_date" placeholder="From Date" required ><span style="margin:20px;"> to </span>';
            fieldToAppend += '<input type="text" class="form-control tpo-dates datepicker" name="to_date" placeholder="To Date" required >';
            fieldToAppend += '</div> <a href="#" onClick="removeFilterInput('+id+')" class="filter-div-delete"><i class="fa fa-trash filter-div-delete-icn"></i></a>';
            $('#filterVal'+id).append(fieldToAppend);
    }else if(filterField == 'status'){
        $('#filterVal'+id).html('');
        var fieldToAppend = '<select class="form-control tpo-filter-field-m" name="tpl_status" required >';
            fieldToAppend += "<option value='Entered'  >Entered</option>"
            fieldToAppend += "<option value='Approved'  >Approved</option>"
            fieldToAppend += "<option value='CheckInventory'  >CheckInventory</option>"
            fieldToAppend += "<option value='Started'  >Started</option>"
            fieldToAppend += "<option value='Completed'  >Completed</option>"
            fieldToAppend += "<option value='Shipped'  >Shipped</option>"
            fieldToAppend += "<option value='Delivered'  >Delivered</option>"
            fieldToAppend += "<option value='Freezed'  >Freezed</option>"
            fieldToAppend += "<option value='Cancelled'  >Cancelled</option>"
            fieldToAppend += "<option value='Suspended'  >Suspended</option>"
            fieldToAppend += '</select> <a href="#" onClick="removeFilterInput('+id+')" class="filter-div-delete"><i class="fa fa-trash filter-div-delete-icn"></i></a>';
            $('#filterVal'+id).append(fieldToAppend);
    }else if(filterField == 'ref'){
        $('#filterVal'+id).html('');
        var fieldToAppend = '<input type="text" class="form-control tpo-filter-field-m" name="ref" placeholder="Reference Number" required >';
            fieldToAppend += '<a href="#" onClick="removeFilterInput('+id+')" class="filter-div-delete"><i class="fa fa-trash filter-div-delete-icn"></i></a>';
            $('#filterVal'+id).append(fieldToAppend);
    }else if(filterField == 'ship_to_name'){
        $('#filterVal'+id).html('');
        var fieldToAppend = '<input type="text" class="form-control tpo-filter-field-m" name="ship_to_name" placeholder="Ship to Name" required >';
            fieldToAppend += '<a href="#" onClick="removeFilterInput('+id+')" class="filter-div-delete"><i class="fa fa-trash filter-div-delete-icn"></i></a>';
            $('#filterVal'+id).append(fieldToAppend);
    }else{
        $('#filterVal'+id).html('');
    }

    $(function() {
        $( ".datepicker" ).datepicker();
    });

}
