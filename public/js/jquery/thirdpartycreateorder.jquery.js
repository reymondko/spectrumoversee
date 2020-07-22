$(document).ready(function(){
    $('#add_line_items').submit(function(){
        $('#line_item_error').hide();
        $('#addLineItemModal').modal('toggle');

        
        $('#customer_id').val(skuCids[$("#l_item_sku option:selected").text()].customer_id);
        $('#customer_name').val(skuCids[$("#l_item_sku option:selected").text()].customer_name);

        var lineItemQty =  $('#l_item_qty').val();
        var lineItemSku =  $('#l_item_sku option:selected').text();
        var lineItemId =  $('#l_item_sku').val();
        var lineItemDesc = $('#l_item_desc').val();
        //appendto line_items_div

        var fieldToAppend = `<div class="col-md-12 line-itms"><div class="form-group col-md-3">
                                <label for="system_calculated_handling">SKU</label>
                                <input type="hidden" class="form-control" name="line_item_id[]" value="${lineItemId}" readonly>
                                <input type="text" class="form-control" name="line_item_sku[]" value="${lineItemSku}" readonly>
                            </div>
                            <div class="form-grou col-md-3">
                            <label >Description</label>
                                <input type="text" class="form-control"  value="${lineItemDesc}" readonly>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="other_handling">Quantity</label>
                                <input type="number" class="form-control"  name="line_item_qty[]" value="${lineItemQty}" readonly> &nbsp;&nbsp;&nbsp; 
                                
                            </div>
                            <div class="form-group col-md-3">
                               <i onClick="removeParent(this) "class="fa fa-trash" aria-hidden="true" style="font-size: 18px;margin-top: 31px;color: #dd4b39;cursor:pointer;"></i>
                            </div>
                            </div>`;

        $('#line_items_div').append(fieldToAppend);
        $('#l_item_qty').val('');
        $('#l_item_sku').val('');
        $('#l_item_desc').val('');

        return false;
    })


    $('#create_tpl_order_form').submit(function(){
        $('#line_item_error').hide();
        if($('.line-itms').length < 1){
            $('#line_item_error').show();
            $('#line_item_error').focus();
            return false
        }
        
    })

    $( ".datepicker" ).datepicker();

    
})

function removeParent(child){
    $(child).parents().eq(1).remove();
    return false;
}