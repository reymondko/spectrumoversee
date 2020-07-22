$(document).ready(function() {
    $('#KitSkus_table').DataTable({
        "sDom": '<"row view-filter"<"col-sm-12"<"pull-left"l><"pull-right"f><"clearfix">>>t<"row view-pager"<"col-sm-12"<"text-center"ip>>>',
        "bLengthChange": false,
        "bFilter": true,
        "bInfo": false,
        "language": {
            "paginate": {
                "previous": '<i class="fa fa-chevron-left paginate-button"></i>',
                "next": '<i class="fa fa-chevron-right paginate-button"></i>',
            }
        }
    });
});


$('#editUserModal').on('hidden.bs.modal', function () {
    $('#id_edit').val('');
    $('#company_edit').val('');
    $('#sku_edit').val('');
    $("#multi_barcode_edit2").prop("checked", false);
    $("#multi_barcode_edit").prop("checked", false);
    $('#hs_code_edit').val('');
    $('#box_limit_edit').val('');
    
})


function editKitSku(id,company,sku,multibarcode,hscode,expiration_date,box_limit,multi_barcode_count,bulk_count){
    $('#id_edit').val(id);
    $('#company_edit').val(company);
    $('#sku_edit').val(sku);
    $('#box_limit_edit').val(box_limit);
    $('#multi_barcode_count_edit').val(multi_barcode_count);
    $('#bulk_count_edit').val(bulk_count);
    
    
    if(multibarcode==1){
        $("#multi_barcode_edit2").prop("checked", true);
    }
    else if(multibarcode==2){
        $("#multi_barcode_edit3").prop("checked", true);
    }
    else{
        $("#multi_barcode_edit").prop("checked", true);
    }

    $.ajax({
        url: "/users/details",
        type: "post",
        data: {user_id:id, _token: $('meta[name="csrf-token"]').attr('content')},
        success: function(data) {
            if(data.success){
                $(data.result.permissions).each(function(index,value){
                    $("input[value='" + value.permission_name + "']").prop('checked', true);
                })

                $(data.result.locations).each(function(index,value){
                    $('#location_edit').append(`<option value="${value.id}">${value.name}</option>`);
                });

                $('.update-user-loader').hide();
                $('.edit-user-fieldset').show();
            }
        }
    });
}


function deleteKitSku(id){
    var prompt = window.confirm("Are you sure you want to delete this SKU?");
    if(prompt){
        window.location.href = "/kit-sku/delete?sku_id="+id;
    }
}
