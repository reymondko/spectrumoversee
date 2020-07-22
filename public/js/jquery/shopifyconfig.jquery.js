$(document).ready(function() {
    $('#users_table').DataTable({
        "sDom": '<"row view-filter"<"col-sm-12"<"pull-left"l><"pull-right"f><"clearfix">>>t<"row view-pager"<"col-sm-12"<"text-center"ip>>>',
        "bLengthChange": false,
        "bFilter": false,
        "bInfo": false,
        "language": {
            "paginate": {
                "previous": '<i class="fa fa-chevron-left paginate-button"></i>',
                "next": '<i class="fa fa-chevron-right paginate-button"></i>',
            }
        }
    });

    $("#deleteAll").click(function(){
        $('input:checkbox').not(this).prop('checked', this.checked);
    });


});

function deleteConf(id){
    var prompt = window.confirm("Are you sure you want to delete this configuration?");
    if(prompt){
        window.location.href = "/shopify/delete?conf_id="+id;
    }
}

$('#editShopifyModal').on('hidden.bs.modal', function () {
    $('#edit_Shopify_form #id_edit').val("");
    $('#edit_Shopify_form #logiwa_depositor_code').val("");
    $('#edit_Shopify_form #logiwa_depositor_id').val("");
    $('#edit_Shopify_form #company').val("");
    $('#edit_Shopify_form #shopify_url').val("");
    $('#edit_Shopify_form #shopify_api_key').val("");
    $('#edit_Shopify_form #shopify_password').val("");
    $('#edit_Shopify_form input[name="integration_status"]').prop('checked',false);
    $('#edit_Shopify_form input[name="integration_status"]').prop('selected',false);
});

function editConfig(id,company,shopify_url,shopify_api_key,shopify_password,integration_status,tpl_customer_id,logiwa_depositor_code,logiwa_depositor_id){
    console.log("here");
    $('#edit_Shopify_form #id_edit').val(id);
    $('#edit_Shopify_form #company').val(company);
    $('#edit_Shopify_form #tpl_customer_id').val(tpl_customer_id);
    $('#edit_Shopify_form #logiwa_depositor_code').val(logiwa_depositor_code);
    $('#edit_Shopify_form #logiwa_depositor_id').val(logiwa_depositor_id);
    $('#edit_Shopify_form #shopify_url').val(shopify_url);
    $('#edit_Shopify_form #shopify_api_key').val(shopify_api_key);
    $('#edit_Shopify_form #shopify_password').val(shopify_password);
    $('#edit_Shopify_form input[name=integration_status][value=' + integration_status + ']').attr('checked', 'checked');
}

function editIgnoredSkus(id,company,ignored_skus){
    $('#ignored_sku_checkboxes').empty();
    $('#id_edit_ignored_sku').val(id);
    $('#company_edit_ignored_sku').val(company);
    var ignored_skus = JSON.parse(ignored_skus);
    var ignored_sku_elem = '';
    if(ignored_skus.length > 0){
        for(i = 0;i < ignored_skus.length;i++){
            var bgcolor = 'odd';
            if(i%2==0){
                bgcolor = 'even';
            }
            ignored_sku_elem += `<div id="ignored_elem_div_`+i+`"class="form-group ignored-sku-form-group `+bgcolor+`">`;
            ignored_sku_elem += `<input class="ignored-sku-input `+bgcolor+`" type="text" name="ignored_skus[]" value=`+ignored_skus[i]+` readonly>`;
            // ignored_sku_elem += `<input class="ignored-checkbox" type="checkbox" name="ignored_skus[]" value='`+ignored_skus[i].sku+`' `+checked+` />`;
            ignored_sku_elem += `&nbsp;&nbsp;<button onClick="removeIgnoredSku(`+i+`)" class="btn btn-default" title="Remove"><i class="fa fa-trash"></i></button>`;
            ignored_sku_elem += `</div></div>`;
        }
    }
    $('#ignored_sku_checkboxes').append(ignored_sku_elem);
}

function addSkuToElem(){
    var sku = $('#ignored_sku_add_input').val();
    if(sku != ''){
        var next_id = $('.ignored-sku-form-group').length;
        var bgcolor = 'odd';
        if(next_id%2==0){
            bgcolor = 'even';
        }

        var ignored_sku_elem = `<div id="ignored_elem_div_`+next_id+`"class="form-group ignored-sku-form-group `+bgcolor+`">`;
        ignored_sku_elem += `<input class="ignored-sku-input `+bgcolor+`" type="text" name="ignored_skus[]" value=`+sku+` readonly>`;
        ignored_sku_elem += `&nbsp;&nbsp;<button onClick="removeIgnoredSku(`+next_id+`)" class="btn btn-default" title="Remove"><i class="fa fa-trash"></i></button>`;
        ignored_sku_elem += `</div></div>`;

        $('#ignored_sku_checkboxes').append(ignored_sku_elem);
        $('#ignored_sku_add_input').val('');
    }
}

function removeIgnoredSku(i){
    $('#ignored_elem_div_'+i).remove();
}
