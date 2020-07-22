

$(document).ready(function() {
    $('#s_table').DataTable({
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
        "pageLength": 50
    });
});


function editShipperAddress(id){
    $('#edit_shipper_address')[0].reset();
    $('#edit_shipper_label').val('');
    spackages.map((val,idx) => {
        if(val.id == id){
            $('#edit_shipper_label').text(val.first_name+' '+val.last_name);
            $('#edit_tpl_customer_id').val(val.tpl_customer_id);
            $('#edit_first_name').val(val.first_name);
            $('#edit_last_name').val(val.last_name);
            $('#edit_address').val(val.address);
            $('#edit_city').val(val.city);
            $('#edit_state').val(val.state);
            $('#edit_country').val(val.country);
            $('#edit_postal_code').val(val.postal_code);
            $('#edit_phone_number').val(val.phone_number);
            $('#edit_id').val(val.id);
            $('#edit_zip').val(val.zip);
            $('#edit_account_number').val(val.account_number);
            $('#edit_minimum_package_weight').val(val.minimum_package_weight);
        }
    })
}