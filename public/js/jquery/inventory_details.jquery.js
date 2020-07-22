$(document).ready(function() {
    $('#inventory_scans_table').DataTable({
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
    $('#inventory_notes_table').DataTable({
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

    $('#inventory_status_table').DataTable({
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
});

