$('#add-inventory-field').click(function(){
    let invField = $('.custom-field-input:input').length;
    console.log(invField);
    if(invField > 21){
        
    }else{
        let idArray = [];
        $('.custom-field-input').each(function(){
            idArray.push(this.id);
        })
        for(let x = 1;x < 21; x++){
            let elemId = 'customfield_'+x;
            let checkboxId = 'customfield_checkbox_'+x;
            let divId  = 'customfield_div'+x;
            if(!idArray.includes(elemId)){
                let fieldToAppend = '<tr id="'+divId+'">';
                    fieldToAppend += '<td><div class="form-group"><div class="col-md-12 inventory-field-input-div">';
                    fieldToAppend += '<input id="'+elemId+'" name="'+elemId+'" type="text" placeholder="Custom Field" class="form-control input-md custom-field-input" required="">';
                    fieldToAppend += '</div></div></div></td>';
                    fieldToAppend += '<td class="center-element"><input type="checkbox" class="form-check-input barcode-box" name="'+checkboxId+'""></td>';
                    fieldToAppend += '<td><a href="#" onClick="removeInput('+x+')" class="inventory-field-delete"><i class="fa fa-trash"></i></a></td>'
                    fieldToAppend += '</tr>';
                    $('#custom_field_table tr:last').after(fieldToAppend);
                break;
            }


        }
    }
});

function removeInput(fieldId){
   if(confirm("Removing this field will also remove all associated data")) {
        $('#customfield_div'+fieldId).remove()
   }
}