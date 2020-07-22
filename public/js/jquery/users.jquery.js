$(document).ready(function() {
    $('#users_table').DataTable({
        "sDom": '<"row view-filter"<"col-sm-12"<"pull-left"l><"pull-right"f><"clearfix">>>t<"row view-pager"<"col-sm-12"<"text-center"ip>>>',
        "bLengthChange": false,
        "bFilter": true,
        "bInfo": false,
        "pageLength": 50,
        "language": {
            "paginate": {
                "previous": '<i class="fa fa-chevron-left paginate-button"></i>',
                "next": '<i class="fa fa-chevron-right paginate-button"></i>',
            }
        }
    });
    $('#kit_summary_table').DataTable({
        "sDom": '<"row view-filter"<"col-sm-12"<"pull-left"l><"pull-right"f><"clearfix">>>t<"row view-pager"<"col-sm-12"<"text-center"ip>>>',
        "bLengthChange": false,
        "bFilter": false,
        "order": [[ 1, "desc" ]],
        "bInfo": false,
        "language": {
            "paginate": {
                "previous": '<i class="fa fa-chevron-left paginate-button"></i>',
                "next": '<i class="fa fa-chevron-right paginate-button"></i>',
            }
        }
    });

    $('#details_table').DataTable( {
        "paging":   false,
        "info":     false,
        'columnDefs': [ {
            'targets': [0,1], /* column index */
            'orderable': false, /* true or false */
         }]
    } );


    $("#deleteAll").click(function(){
        $('input:checkbox').not(this).prop('checked', this.checked);
    });


});

function deleteBI(id){
    var prompt = window.confirm("Are you sure you want to delete the selected Batch Item?");
    if(prompt){
        window.location.href = "/kit-return-sync/delete-batch-item/"+id;
    }
}

function deleteBN(){
    var prompt = window.confirm("Are you sure you want to delete the entire Batch?");
    if(prompt){

        var batchid= $("#batchid").val();
        $.ajaxSetup({
            headers: {
                'X-CSRF-Token': $('input[name=_token]').val()
            }
        });
        $.ajax({
            url: "/kit-return-sync/delete-batch",
            type: 'POST',
            data: {batchid: batchid, _token: $('meta[name="_token"]').val()},
            success: function (data) {
                console.log(data);
                window.location.href = "/kit-return-sync/summary/";
            }
        });
    }
}
$('#deletebatchItemsForm').submit(function(){;
    event.preventDefault();
    var formData =new FormData($(this)[0]);
    $.ajaxSetup({
        headers: {
            'X-CSRF-Token': $('input[name=_token]').val()
        }
    });
    $.ajax({
        url: "/kit-return-sync/delete-batch-item",
        type: 'POST',
        data: formData,
        contentType: false, // The content type used when sending data to the server.
        cache: false, // To unable request pages to be cached
        processData: false,
        success: function (data) {
            console.log(data);
            window.location.href = "/kit-return-sync/summary/"+data;
        }
    });
    return false;
});
$('#add_user_form').submit(function(){
    let pass1 = $('#password').val();
    let pass2 = $('#password_confirm').val();
    if(pass1 != pass2){
        $('.pass_error').show();
        return false;
    }
})

$('#editUserModal').on('hidden.bs.modal', function () {
    $('#id_edit').val('');
    $('#name_edit').val('');
    $('#email_edit').val('');
    $('#location_edit').val('');
    $('#location_edit').html('');
    $('.edit-user-fieldset').hide();
    $('.update-user-loader').show();
    $("input.permission-checkbox").prop('checked',false);
});

$('#userReactivateModal').on('hidden.bs.modal', function () {
    $('#ru_user_id').val('');
    $('#ru_user_name').val('');
    $('#ru_user_email').val('');
    $('.pass_error').hide();
});

function ReactivateUser(user_id, user_name , user_email){
    $('.update-user-loader').hide();
    $('.reactivate-user-fieldset').show();
    $('#ru_user_id').val(user_id);
    $('#ru_user_name').val(user_name);
    $('#ru_user_email').val(user_email);

}

$('#reactivate_user_form').submit(function(){
    let pass1 = $('#ru_password').val();
    let pass2 = $('#ru_password_confirm').val();
    if(pass1 != pass2){
        $('.pass_error').show();
        return false;
    }
})
function editUser(id,name,email,location,role){
    $('#id_edit').val(id);
    $('#name_edit').val(name);
    $('#email_edit').val(email);
    $('#location_edit').val(location);
    $('#role').val(role);

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

function getUserLogs(name,id){
    $('#user_log_name').html(name);
    $('#user_log_tbody').html('');
    $.get('/users/getLogs?id='+id, function( data ) {
        if(data.success == true){
            $(data.logs).each(function(key,value){
            var dataToAppend =  '<tr>';
                dataToAppend +=  '<td>';
                dataToAppend += value.formatted_date;
                dataToAppend += '</td>';
                dataToAppend +=  '<td>';
                dataToAppend += (value.log_type == 'login' ? 'Log In' : 'Log Out');
                dataToAppend += '</td>';
                dataToAppend +=  '<td>';
                dataToAppend += (value.ip);
                dataToAppend += '</td>';
                dataToAppend += '</tr>';
                $('#user_log_tbody').append(dataToAppend)
            });
        }
    });
}

function deleteUser(id){
    var prompt = window.confirm("Are you sure you want to delete this user?");
    if(prompt){
        window.location.href = "/users/delete?user_id="+id;
    }
}
