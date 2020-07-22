<template>
  <section class="carrier-methods-section" >
    <div class="row center">
        <div class="box carriers-box-box md-med-box">
            <div class="box-header">
                <h3 class="box-title"></h3>
                <div class="box-tools pull-right">
                   
                </div>
            </div>
            <div class="box-body">
                    <div class="carriers-select-div">
                        <label class="carriers-select-lbl">Carrier:</label>
                        <select class="carriers-select " v-model="selectedCarrier">
                            <option value="-1">Select a Carrier</option>
                            <option v-for="(carrier,index) in carriers" :value="index">{{carrier.name}}</option>
                        </select>
                        <!-- <button type="button" class="btn btn-flat so-btn carrier-actions" v-if="selectedCarrier != -1" data-toggle="modal" data-target="#editCarrierModal">Edit Carrier &nbsp;<i class="fa fa-edit"></i></button>
                        <a v-if="selectedCarrier != -1"  :href="'/carriers/delete/'+carriers[selectedCarrier].id" onClick="javascript:return confirm('Are you sure you want to delete this carrier?')"><button type="button" class="btn btn-flat so-btn-close carrier-actions" >Delete Carrier &nbsp;<i class="fa fa-trash"></i></button></a> -->
                    </div>
                    <div class="carriers-methods-tbl-div">
                        <button type="button" class="btn btn-flat so-btn so-btn-edt btn-left-mgn" data-toggle="modal" data-target="#addCarrierMethodModal">Add Carrier Method &nbsp;<i class="fa fa-plus"></i></button>&nbsp;
                        <button type="button" class="btn btn-flat so-btn so-btn-edt" data-toggle="modal" data-target="#addCarrierModal">Add Carrier &nbsp;<i class="fa fa-plus"></i></button>
                        <table class="table table-striped table-bordered carrier-table">
                            <thead class="table_head">
                                <th class="carrier-th">Method</th>
                                <th class="carrier-th">Carrier</th>
                                <th class="carrier-th">Value</th>
                                <th class="carrier-th">Shipping Vendor</th>
                                <th class="carrier-th">Account Number</th>
                                <th class="carrier-th">Markup</th>
                                <th class="carrier-th">Action</th>
                            </thead>
                            <tbody v-if="selectedCarrier != -1">
                                <tr v-for="(method,mIndex) in carriers[selectedCarrier].methods">
                                    <td>{{method.name}}</td>
                                    <td>{{carriers[selectedCarrier].name}}</td>
                                    <td>{{method.value}}</td>
                                    <!-- <td>{{vendors[method.shipping_vendor_id].vendor_name}}</td> -->
                                    <td>{{getVendorName(method.shipping_vendor_id)}}</td>
                                    <td>{{method.account_number}}</td>
                                    <td>{{method.markup}}</td>
                                    <td>
                                    <button type="button" class="btn btn-flat so-btn" data-toggle="modal" data-target="#editCarrierMethodModal" @click="editCarrierMethod(mIndex)">Edit &nbsp;<i class="fa fa-edit"></i></button>
                                    <a :href="'/carriers/method/delete/'+method.id" onClick="javascript:return confirm('Are you sure you want to delete this carrier method?')"><button type="button" class="btn btn-flat so-btn-close">Delete &nbsp;<i class="fa fa-trash"></i></button></a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
            </div>
        </div>
    </div>

    <!-- ADD CARRIER METHOD -->
    <div class="modal fade" id="addCarrierMethodModal" tabindex="-1" role="dialog" aria-labelledby="addCarrierMethodModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="addCarrierModalModalLabel">Add Carrier Method</h4>
                </div>
                <form id="add_carrier" class="form-horizontal" method="POST" action="/carriers/method/save" >
                    <input type="hidden" name="_token" :value="csrf">
                    <div class="modal-body">
                    <fieldset>
                        <!-- Text input-->
                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-2">
                                <label class="control-label" for="carrier">Carrier</label> 
                                <select class="form-control input-md" name="carrier_id" required>
                                    <option value="">Select a Carrier</option>
                                    <option v-for="(carrier,index) in carriers" :value="carrier.id" :selected="selectedCarrier == index">{{carrier.name}}</option>
                                </select>
                                </br>
                            </div>
                            <div class="col-md-8 col-md-offset-2">
                            <label class="control-label" for="length">Method Name</label>  
                                <input id="method" name="method" type="text" placeholder="Method Name" class="form-control input-md" required><br/>
                            </div>
                            <div class="col-md-8 col-md-offset-2">
                                <label class="control-label" for="width">Value</label>  
                                <input id="val" name="val" type="text" placeholder="Value" class="form-control input-md" required><br/>
                            </div>
                            <div class="col-md-8 col-md-offset-2">
                                <label class="control-label" for="carrier">Vendor</label> 
                                <select class="form-control input-md" name="vendor_id" required>
                                    <option value="">Select a Vendor</option>
                                    <option v-for="(vendor,index) in vendors" :value="vendor.id">{{vendor.vendor_name}}</option>
                                </select>
                                </br>
                            </div>
                            <div class="col-md-8 col-md-offset-2">
                                <label class="control-label" for="length">Account Number</label>  
                                <input id="account_number" name="account_number" type="text" placeholder="Account Number" class="form-control input-md" required><br/>
                            </div>
                            <div class="col-md-8 col-md-offset-2">
                                <label class="control-label" for="length">Markup</label>  
                                <input id="markup" name="markup" type="number" step="0.01" placeholder="Markup" class="form-control input-md" required><br/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-6 control-label" for="submit"></label>
                        </div>
                    </fieldset>
                    </div>
                    <div class="modal-footer">
                    <button type="button" class="btn btn-default close-btn so-btn-close" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-flat so-btn">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- EDIT CARRIER METHOD -->
    <div class="modal fade" id="editCarrierMethodModal" tabindex="-1" role="dialog" aria-labelledby="editCarrierMethodModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="editCarrierMethodModalLabel">Edit Method</h4>
                </div>
                <form id="add_carrier" class="form-horizontal" method="POST" action="/carriers/method/update" >
                    <input type="hidden" name="_token" :value="csrf">
                    <div class="modal-body">
                    <fieldset>
                        <!-- Text input-->
                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-2">
                                <label class="control-label" for="carrier">Carrier</label> 
                                <select class="form-control input-md" name="carrier_id" required>
                                    <option value="">Select a Carrier</option>
                                    <option v-for="(carrier,index) in carriers" :value="carrier.id" :selected="selectedCarrier == index">{{carrier.name}}</option>
                                </select>
                                </br>
                            </div>
                            <div class="col-md-8 col-md-offset-2">
                            <label class="control-label" for="length">Method Name</label>  
                                <input id="method" name="method" type="text" placeholder="Method Name" class="form-control input-md" :value="editMethod.name" required><br/>
                            </div>
                            <div class="col-md-8 col-md-offset-2">
                                <label class="control-label" for="width">Value</label>  
                                <input id="val" name="val" type="text" placeholder="Value" class="form-control input-md" :value="editMethod.value" required><br/>
                            </div>

                            <div class="col-md-8 col-md-offset-2">
                                <label class="control-label" for="carrier">Vendor</label> 
                                <select class="form-control input-md" name="vendor_id" required>
                                    <option value="">Select a Vendor</option>
                                    <option v-for="(vendor,index) in vendors" :value="vendor.id" :selected="editMethod.shipping_vendor_id == vendor.id">{{vendor.vendor_name}}</option>
                                </select>
                                </br>
                            </div>
                            <div class="col-md-8 col-md-offset-2">
                                <label class="control-label" for="length">Account Number</label>  
                                <input id="account_number" name="account_number" type="text" placeholder="Account Number" class="form-control input-md"  :value="editMethod.account_number" required><br/>
                            </div>
                            <div class="col-md-8 col-md-offset-2">
                                <label class="control-label" for="length">Markup</label>  
                                <input id="markup" name="markup" type="number" step="0.01" placeholder="Markup" class="form-control input-md" :value="editMethod.markup" required><br/>
                            </div>

                            <input type="hidden" name="id" :value="editMethod.id">
                        </div>
                        <div class="form-group">
                            <label class="col-md-6 control-label" for="submit"></label>
                        </div>
                    </fieldset>
                    </div>
                    <div class="modal-footer">
                    <button type="button" class="btn btn-default close-btn so-btn-close" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-flat so-btn">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ADD CARRIER -->
    <div class="modal fade" id="addCarrierModal" tabindex="-1" role="dialog" aria-labelledby="addCarrierModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="addCarrierModalLabel">Add Carrier</h4>
                </div>
                <form id="add_carrier" class="form-horizontal" method="POST" action="/carriers/save" >
                    <input type="hidden" name="_token" :value="csrf">
                    <div class="modal-body">
                    <fieldset>
                        <!-- Text input-->
                        <div class="form-group">
                            <div class="col-md-12">
                                <label class="control-label" for="length">Carrier Name</label>  
                                <input id="carrier_name" name="carrier_name" type="text" placeholder="Method Name" class="form-control input-md"  required><br/>
                            </div>
                            <div v-for="(method,index)  in addtlCarrierMethods" >
                                <div class="col-md-5">
                                    <label class="control-label" for="length">Method Name</label>  
                                    <input name="method[]" type="text" placeholder="Method Name" class="form-control input-md" v-model="method.name" required>
                                </div>
                                <div class="col-md-5">
                                    <label class="control-label" for="length">Value</label>  
                                    <input name="val[]" type="text" placeholder="Value" class="form-control input-md" v-model="method.value"  required>
                                </div>
                                <div class="col-md-2">
                                    <label class="control-label" for="length">&nbsp;<br/><br/></label>  
                                    <button type="button" class="btn btn-flat so-btn" style="margin-top: 11px;" @click="deleteAddCarrierMethod(index)">Delete</button>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-flat so-btn float-left" @click="addCarrierAddMethod()">Add Method</button>
                        <button type="button" class="btn btn-default close-btn so-btn-close" data-dismiss="modal" @click="closeAddCarrier()">Close</button>
                        <button type="submit" class="btn btn-flat so-btn">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- EDIT CARRIER  -->
    <div class="modal fade" id="editCarrierModal" tabindex="-1" role="dialog" aria-labelledby="editCarrierModalLabel"  v-if="selectedCarrier != -1">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="editCarrierModalLabel">Edit Carrier</h4>
                </div>
                <form id="edit_carrier" class="form-horizontal" method="POST" action="/carriers/update" >
                    <input type="hidden" name="_token" :value="csrf">
                    <div class="modal-body">
                    <fieldset>
                        <!-- Text input-->
                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-2">
                            <label class="control-label" for="length">Carrier Name</label>  
                                <input id="name" name="name" type="text" placeholder="Carrier Name" class="form-control input-md" :value="carriers[selectedCarrier].name" required><br/>
                            </div>
                            <input type="hidden" name="id" :value="carriers[selectedCarrier].id">
                        </div>
                        <div class="form-group">
                            <label class="col-md-6 control-label" for="submit"></label>
                        </div>
                    </fieldset>
                    </div>
                    <div class="modal-footer">
                    <button type="button" class="btn btn-default close-btn so-btn-close" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-flat so-btn">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


  </section>
</template>
<script>
export default {
    props: ['carriers','vendors','previous'],

    data() {
		return {
            csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            selectedCarrier:-1,
            selectedVendor:-1,
            editMethod:{
                id:null,
                carrierId:null,
                name:null,
                value:null,
                shipping_vendor_id:null,
                account_number:null,
                markup:null,
            },
            addtlCarrierMethods:[]
		}
    },
    
    mounted() {
        console.log(this.previous);
        if(this.previous !== undefined){
            for(let i in this.carriers){
                console.log(this.carriers[i].id);
                console.log(this.previous);

                if(this.carriers[i].id == this.previous){
                    this.selectedCarrier = i;
                }
            }
        }
    },

    methods: {
      editCarrierMethod(carrieMethodIdx){
          console.log(this.vendors)
          this.editMethod = {
            id: this.carriers[this.selectedCarrier].methods[carrieMethodIdx].id,
            carrierId:this.carriers[this.selectedCarrier].id,
            name:this.carriers[this.selectedCarrier].methods[carrieMethodIdx].name,
            value:this.carriers[this.selectedCarrier].methods[carrieMethodIdx].value,
            shipping_vendor_id:this.carriers[this.selectedCarrier].methods[carrieMethodIdx].shipping_vendor_id,
            account_number:this.carriers[this.selectedCarrier].methods[carrieMethodIdx].account_number,
            markup:this.carriers[this.selectedCarrier].methods[carrieMethodIdx].markup,
          }
      },
      addCarrierAddMethod(){
          this.addtlCarrierMethods.push({
              name:null,
              value:null,
          })
      },
      deleteAddCarrierMethod(idx){
          this.addtlCarrierMethods.splice(idx,1);
      },
      closeAddCarrier(){
          this.addtlCarrierMethods = [];
      },
      getVendorName(id){
          for(let x = 0; x < this.vendors.length;x++){
              if(this.vendors[x].id == id){
                  return this.vendors[x].vendor_name;
                  break;
              }
          }
      }
    },

}
</script>