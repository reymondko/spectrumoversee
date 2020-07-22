

$(document).ready(function() {
    $('#spackages_table').DataTable({
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


function editPackageSize(id){
    $('#edit_package_size')[0].reset();
    $('#edit_package_label').val('');
    spackages.map((val,idx) => {
        if(val.id == id){
            $('#edit_package_label').val(val.package_name);
            $('#edit_package_name').val(val.package_name);
            $('#edit_length').val(val.length);
            $('#edit_width').val(val.width);
            $('#edit_height').val(val.height);
            $('#edit_weight').val(val.weight);
            $('#edit_id').val(val.id);
        }
    })
}