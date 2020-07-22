$(document).ready(function() {
    $('#orders_table').DataTable({
        "sDom": '<"row view-filter"<"col-sm-12"<"pull-left"l><"pull-right"f><"clearfix">>>t<"row view-pager"<"col-sm-12"<"text-center"ip>>>',
        "bLengthChange": false,
        "bFilter": false,
        "bInfo": false,
        "language": {
            "paginate": {
                "previous": '<i class="fa fa-chevron-left paginate-button"></i>',
                "next": '<i class="fa fa-chevron-right paginate-button"></i>',
            }
        },
        "columnDefs": [
            {
                "targets": [ 0 ],
                "visible": false
            }
        ],
        "order": [[ 0, "desc" ]],
        "pageLength": 75
    });

    // $('#scan_barcode_no').bind("enterKey",function(e){
    // });
    // $('input').keyup(function(e){
    //   if(e.keyCode == 13)
    //   {
    //    $('#scan_btn').trigger('click');
    //   }
    // });
    $('#scan_barcode_no').on('keypress', function (e) {
        if(e.which === 13){
            $('#scan_tracking_no').focus();
        }
    });

    $('#scan_tracking_no').on('keypress', function (e) {
        if(e.which === 13){
            $('#scan_btn').trigger('click');
        }
    });

    $("#editOrderModal").on("hidden.bs.modal", function () {
        $('#edit_select_sku_field').html('');
        $('#edit_id').val('');
    });
} );

$('#add_order_item').click(function(){
    let index = $('.item-input:input').length + 1;
    let fieldToAppend = '<div class="form-group" id="itemdiv_'+index+'">';
        fieldToAppend += '<div class="col-md-12">';
        fieldToAppend += '<div class="form-group row">';
        fieldToAppend += '<div class="col-md-5 col-md-offset-1">';
        fieldToAppend += '<select class="form-control select-md"  name="item_name[]" required>';
        fieldToAppend += select_sku_options
        fieldToAppend += '</select>';
        fieldToAppend += '</div>';
        fieldToAppend += '<div class="col-md-4">';
        fieldToAppend += '<input type="number" class="form-control item-input" id="quantity" placeholder="Quantity" name="item_quantity[]" required>';
        fieldToAppend += '</div>';
        fieldToAppend += '<div class="col-md-2">';
        fieldToAppend += `<a href="#" onClick="removeElement('itemdiv_${index}')" class="order-field-delete"><i class="fa fa-trash"></i></a>`;
        fieldToAppend += '</div>';
        fieldToAppend += '</div>';
        fieldToAppend += '</div>';
        fieldToAppend += '</div>';
        $('#select_sku_field').append(fieldToAppend);
})

$('#edit_add_order_item').click(function(){
    let index = $('.item-input:input').length + 1;
    let fieldToAppend = '<div class="form-group" id="edit_itemdiv_'+index+'">';
        fieldToAppend += '<div class="col-md-12">';
        fieldToAppend += '<div class="form-group row">';
        fieldToAppend += '<div class="col-md-5 col-md-offset-1">';
        fieldToAppend += '<select class="form-control select-md"  name="edit_item_name[]" required>';
        fieldToAppend += select_sku_options
        fieldToAppend += '</select>';
        fieldToAppend += '</div>';
        fieldToAppend += '<div class="col-md-4">';
        fieldToAppend += '<input type="number" class="form-control item-input" id="quantity" placeholder="Quantity" name="edit_item_quantity[]" required>';
        fieldToAppend += '</div>';
        fieldToAppend += '<div class="col-md-2">';
        fieldToAppend += `<a href="#" onClick="removeElement('edit_itemdiv_${index}')" class="order-field-delete"><i class="fa fa-trash"></i></a>`;
        fieldToAppend += '</div>';
        fieldToAppend += '</div>';
        fieldToAppend += '</div>';
        fieldToAppend += '</div>';
        $('#edit_select_sku_field').append(fieldToAppend);
})

function editOrder(id){
    $.ajax({
        url: "/orders/data",
        type: "post",
        data: {id:id, _token: $('meta[name="csrf-token"]').attr('content')},
        success: function(data) {
            var order = data.data.order;
            $('#edit_id').val(id);
            $('#edit_customer_name').val(order.order_by_name);
            $('#edit_address_1').val(order.address_1);
            $('#edit_address_2').val(order.address_2);
            $('#edit_city').val(order.city);
            $('#edit_zip').val(order.zip);
            $('#edit_state').val(order.state);
            $('#edit_country').val(order.country);
            $('#edit_order_fieldset').show();
            $(order.order_items).each(function(index,value){
                let fieldToAppend = '<div class="form-group" id="edit_itemdiv_'+index+'">';
                fieldToAppend += '<div class="col-md-12">';
                fieldToAppend += '<div class="form-group row">';
                fieldToAppend += '<div class="col-md-5 col-md-offset-1">';
                fieldToAppend += '<select class="form-control select-md" id="edit_sku_select'+index+'"  name="edit_item_name[]" required>';
                fieldToAppend += select_sku_options
                fieldToAppend += '</select>';
                fieldToAppend += '</div>';
                fieldToAppend += '<div class="col-md-4">';
                fieldToAppend += '<input type="number" class="form-control item-input" id="quantity"value="'+value.quantity+'" placeholder="Quantity" name="edit_item_quantity[]" required>';
                fieldToAppend += '</div>';
                fieldToAppend += '<div class="col-md-2">';
                if(index != 0){
                    fieldToAppend += `<a href="#" onClick="removeElement('edit_itemdiv_${index}')" class="order-field-delete"><i class="fa fa-trash"></i></a>`;
                }
                fieldToAppend += '</div>';
                fieldToAppend += '</div>';
                fieldToAppend += '</div>';
                fieldToAppend += '</div>';
                $('#edit_select_sku_field').append(fieldToAppend);
                $('#edit_sku_select'+index).val(value.sku);
            });
        }
    });
}

function removeElement(element_id){
    $('#'+element_id).remove();
}


