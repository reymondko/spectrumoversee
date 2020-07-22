
$(document).ready(function() {
    $('#automation_rules_table').DataTable({
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
        "pageLength": 10
    });

    $('#shipments_table').DataTable({
        "sDom": '<"row view-filter"<"col-sm-12"<"pull-left"l><"pull-right"f><"clearfix">>>t<"row view-pager"<"col-sm-12"<"text-center"ip>>>',
        "bLengthChange": false,
        "bFilter": true,
        "bInfo": false,
        "language": {
            "paginate": {
                "previous": '<i class="fa fa-chevron-left paginate-button"></i>',
                "next": '<i class="fa fa-chevron-right paginate-button"></i>',
            }
        },
        "pageLength": 75
    });
});

$(document.body).on('change',".cs",function (e) {
    console.log($(this).attr('data-target'));
    console.log($(':selected', this).attr("data-tag"));
    
    $('.'+$(this).attr('data-target')).val(''); //select element
    $('.'+$(this).attr('data-target')+'-option').hide(); //hide  options
    $('.'+$(this).attr('data-target')+'-cm-'+$(':selected', this).attr("data-tag")).show(); //show options based on selected value
 });


 $(document.body).on('change',".csi",function (e) {
    $('.'+$(this).attr('data-target')).val(''); //select element
    $('.'+$(this).attr('data-target')+'-option').hide(); //hide  options
    $('.'+$(this).attr('data-target')+'-cm-'+$(':selected', this).attr("data-tag")).show(); //show options based on selected value
 });

 $(document.body).on('change',".edit-cs",function (e) {
    console.log($(this).attr('data-target'));
    console.log($(':selected', this).attr("data-tag"));
    
    $('.edit-'+$(this).attr('data-target')).val(''); //select element
    $('.edit-'+$(this).attr('data-target')+'-option').hide(); //hide  options
    $('.edit-'+$(this).attr('data-target')+'-cm-'+$(':selected', this).attr("data-tag")).show(); //show options based on selected value
 });


 $(document.body).on('change',".edit-csi",function (e) {
    $('.edit-'+$(this).attr('data-target')).val(''); //select element
    $('.edit-'+$(this).attr('data-target')+'-option').hide(); //hide  options
    $('.edit-'+$(this).attr('data-target')+'-cm-'+$(':selected', this).attr("data-tag")).show(); //show options based on selected value
 });


 function removeElemByClass(c){
    $('.'+c).remove();
}
