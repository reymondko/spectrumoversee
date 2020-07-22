$(document).ready(function() {
    $('#skus').DataTable({
        "sDom": '<"row view-filter"<"col-sm-12"<"pull-left"l><"pull-right"f><"clearfix">>>t<"row view-pager"<"col-sm-12"<"text-center"ip>>>',
        "bLengthChange": false,
        "bFilter": true,
        "bInfo": false,
        "language": {
            "paginate": {
                "previous": '<i class="fa fa-chevron-left paginate-button"></i>',
                "next": '<i class="fa fa-chevron-right paginate-button"></i>',
            },
            "searchPlaceholder": "Search Skus"
        }
    });
});


$('#add_more_sku').click(function(){
    let invField = $('.add-location-field-input:input').length;
    invField++;
    let inputId = `loc_${invField}`;
    let fieldToAppend = '<div class="form-group loc-div" id="loc_'+invField+'"></br></br>';
        fieldToAppend += '<div class="col-md-8 col-md-offset-2 inventory-field-input-div">';
        fieldToAppend += '<input name="sku[]" type="text" placeholder="SKU" class="form-control input-md add-location-field-input" required>';
        fieldToAppend += `<a href="#" onClick="removeElement('${inputId}')" class="inventory-field-delete"><i class="fa fa-trash"></i></a>`;
        fieldToAppend += '</div>';
        fieldToAppend += '</div>';
        $('#add-location-fieldset').append(fieldToAppend); 
});

$('#editLocationModal').on('hidden.bs.modal', function () {
    $('#edit_location_name').val('');
    $('#edit_location_id').val('');
})

$('.sku-toggle').click(function(){
    $.ajax({
        url: "/settings/skus/toggle",
        type: "post",
        data: {id:$(this).val(), _token: $('meta[name="csrf-token"]').attr('content')},
        success: function(data) {
            //do something
        }
    });
});

function removeElement(elem_id){
    $('#'+elem_id).remove();
}

function editSku(id){
    $('#edit_sku_name').val($('#sku_'+id).html());
    $('#edit_sku_id').val(id);
}

function deleteLocation(id){
    $('#delete_location_name').html($('#col_locid_'+id).html());
    $('#delete_location_id').val(id);
}