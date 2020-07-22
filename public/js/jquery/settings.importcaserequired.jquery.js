$(document).ready(function() {
    $('#import_required_fields').DataTable({
        "sDom": '<"row view-filter"<"col-sm-12"<"pull-left"l><"pull-right"f><"clearfix">>>t<"row view-pager"<"col-sm-12"<"text-center"ip>>>',
        "bLengthChange": false,
        "bFilter": false,
        "bInfo": false,
        "language": {
            "paginate": {
                "previous": '<i class="fa fa-chevron-left paginate-button"></i>',
                "next": '<i class="fa fa-chevron-right paginate-button"></i>',
            },
            "searchPlaceholder": "Search Skus"
        },
        "order": [[ 2, "desc" ]]


    });

    $('input.case-number-field').on('change', function() {
        $('input.case-number-field').not(this).prop('checked', false);  
    });
    
});