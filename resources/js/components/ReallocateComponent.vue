<template>
    <section class="ship-pack-input-section" >
        <!-- SEARCH TRANSACTION -->
        <center v-if="(!this.transaction_id)">
            <div class="alert alert-info alert-dismissible alert-saved" style="width:30%" v-if="saved">
                <button type="button" class="close alert-close-btn" data-dismiss="alert" aria-hidden="true">×</button>
                <h4><i class="icon fa fa-check"></i>Saved!</h4>
            </div>
            <div class="box box-solid box-primary ship-pack-input-box"  v-if="!saving">
                <div class="box-header ship-pack-box-header">
                    <h3 class="box-title">Search Order By Transaction ID</h3>
                </div>
                <div class="box-body">
                    <div class="form-group">
                        <input type="text" class="form-control" placeholder="Transaction ID" id="transaction_id_input" v-model="transaction_id_from_input" v-on:keyup="searchTransactionIdByEnter">
                        <span class="err" v-if="show_not_found_err">TRANSACTION ID NOT FOUND</span>
                        <span class="err" v-if="show_duplicate_err">{{error_message}}</span>
                    </div>
                    <div class="form-group settings-btn-container">
                        <button type="button" class="btn btn-flat so-btn" data-dismiss="modal" id="transaction_id_btn" @click="searchTransactionId()">Search</button>
                    </div>
                </div>
            </div>
        </center>
        <!--REALLOCATE MODAL-->
        <div class="modal-v" tabindex="-1" role="dialog" id="serialform" aria-labelledby="serialform" v-if="show_modal">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"  @click="closeModal()"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" >Please enter a new serial number</h4>
                    </div>
                    <div class="modal-body" v-if="!show_modal_loader">
                        <h5>Current Serial Number: <strong>{{selected_item_serial}}</strong></h5>
                        <h5>Current SKU: <strong>{{selected_item_sku}}</strong></h5><br/>
                        <label>SKU</label><br/>
                        <input v-on:keyup="updateProceedToSerial" v-model="sku_from_input" ref="input_sku" type="text" class="form-control serial-scan-input" placeholder="SKU"><br/><br/>
                        <label>SERIAL NUMBER</label><br/>
                        <input v-on:keyup="updateOrderItemEnter" v-model="serial_number_from_input" ref="input_serial" type="text" class="form-control serial-scan-input" placeholder="Serial Number">
                        <p class="modal-saved" v-if="modal_saved">Saved!</p>
                        <p class="modal-error" v-if="modal_error">{{modal_error}}</p>
                    </div>
                    <div class="modal-body" v-if="show_modal_loader">
                        <center>
                            <div class="loader">
                            </div>
                            Saving
                        </center>
                    </div>
                    <div class="modal-footer">
                        <button @click="updateOrderItem()" class="btn btn-flat so-btn">Save</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- SEARCH TRANSACTION LOADER-->
        <center v-if="show_search_loader">
            <div class="loader"></div>
            Searching
        </center>
        <!-- SEARCH TRANSACTION LOADER-->
        <center v-if="saving">
            <div class="loader"></div>
            Saving
        </center>
        <!-- FORMS: MAIN CONTENT -->
        <div class="order-body" v-if="this.order.ref_number && this.saving == false">
            <div class="box inventory-box">
                <div class="box-body">
                    <!-- ORDER DETAILS -->
                    <div class="ship-pack-content">
                        <table class="details-table">
                            <tr>
                                <td style="width:33%;" class="detail-text">
                                    <span style="display:block" >{{this.order.name}}</span>
                                    <span style="display:block" >{{this.order.address1}}</span>
                                    <span style="display:block">{{this.order.address2}}</span>
                                    {{this.order.city}},
                                    {{this.order.state}},
                                    {{this.order.zip}}
                                    <span style="display:block"> {{this.order.country}}</span>
                                </td>
                                <td style="width:33%;width: 33%;vertical-align: text-bottom;" class="detail-text">
                                    <span class="detail-title" >Customer:</span>
                                    <span class="detail-company">
                                        <b>{{this.order.company}}</b>
                                    </span>
                                </td>
                                <td style="width:33%;width: 33%;vertical-align: text-bottom;" class="detail-text">
                                    <span class="detail-title">Transaction ID: <u><b> {{this.transaction_id}} </b></u></span>
                                    <span class="detail-title">Reference #: <b>{{this.order.ref_number}}</b> </span>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="ship-pack-content">
                        <hr class="divider">
                    </div>
                    <!-- ORDER ITEMS -->
                    <div class="ship-pack-content" style="text-align:left" >
                        <table class="table table-striped table-bordered">
                            <thead>
                                <th class="sp-table-th th-mini">SKU</th>
                                <th class="sp-table-th th-mini">Serial #</th>
                                <th class="sp-table-th th-mini">QTY Ordered</th>
                                <th class="sp-table-th th-mini">Actions</th>
                            </thead>
                            <tbody class="center">
                                <tr v-for="(item, index) in order_items" :key="index" >
                                    <td>{{item.sku}}</td>
                                    <td>{{item.serial_number}}</td>
                                    <td>
                                        {{item.qty_remaining}}
                                    <td>
                                        <button class="btn btn-primary" @click="reallocate(index)">Reallocate</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="ship-pack-content" style="text-align:right;">
                        <button type="button" class="btn btn-flat so-btn rate-btn" @click="saveReallocation()">Done</button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
<style>
.shipping_rate_filter{
    width: 70%;
    text-align: center;
    margin: 0 auto;
    clear: both;
    margin-bottom: 20px;
    overflow: hidden;
}
.shipping_rate_filter select{
  float:left;
}
</style>

<script>
export default {
    props: ['package_size'],

    data() {
		return {
            modal_saved:false,
            saved:false,
            scan_err:'NOT FOUND',
            transaction_id:null,
            saving:false,
            customer_id:null,
            error_message:'',
            transaction_id_from_input:null,
            show_search_loader:false,
            show_modal_loader:false,
            modal_error:null,
            show_not_found_err:false,
            show_duplicate_err:false,
            show_scan_err:false,
            order_items:[],
            order:{
                name:null,
                address1:null,
                address2:null,
                city:null,
                state:null,
                zip:null,
                country:null,
                company:null,
                ref_number:null,
                phoneNumber:null,
            },
            selected_item_serial:null,
            selected_item_sku:null,
            selected_item_index:null,
            serial_number_from_input: null,
            sku_from_input: null,
            show_modal:false
		}
    },
    computed: {
    },

    mounted() {
    },

    methods: {

        searchTransactionId(){
            if(this.transaction_id_from_input != null) {
                this.show_search_loader = true;
                this.transaction_id = this.transaction_id_from_input;
                this.show_not_found_err = false;
                this.show_duplicate_err = false;
                this.error_message = '';

                var ref = this;

                axios.post('/thirdparty-reallocate-orders/search', {
                    'transaction_id': this.transaction_id,
                }).then(function (response) {
                    // console.log('searchTransactionId:',response)
                    var status = response.data.status;
                    if(status == 'ok') {
                        var data = response.data.result;
                        var order_details = data.order_details;
                        ref.customer_id = order_details.readOnly.customerIdentifier.id;
                        ref.show_search_loader = false;
                        ref.order_items = data.order_items;
                        ref.order = {
                            name:order_details.shipTo.name,
                            address1:order_details.shipTo.address1,
                            address2: (order_details.shipTo.address2 !== undefined ? order_details.shipTo.address2:null),
                            city:order_details.shipTo.city,
                            state:order_details.shipTo.state,
                            zip:order_details.shipTo.zip,
                            country:order_details.shipTo.country,
                            company: (order_details.shipTo.companyName !== undefined ? order_details.shipTo.companyName:order_details.shipTo.name),
                            ref_number:order_details.referenceNum,
                            phoneNumber:order_details.shipTo.phoneNumber,
                        }

                    } else if (status == 'error') {
                      ref.show_search_loader = false;
                      ref.show_not_found_err = false;
                      ref.show_duplicate_err = true;
                      ref.transaction_id = null;
                      ref.transaction_id_from_input = null;
                      ref.error_message = response.data.error_message;
                    }
                    else{
                        ref.notFoundErr();
                    }
                });
            }
        },

        searchTransactionIdByEnter(e){
            if (e.keyCode === 13) {
                return this.searchTransactionId();
            }
            return false;
        },

        notFoundErr(){
            this.show_search_loader = false;
            this.show_not_found_err = true;
            this.show_duplicate_err = false;
            this.transaction_id = null;
            this.transaction_id_from_input = null;
        },

        reallocate(idx){

            this.show_modal = true;
            this.selected_item_serial = this.order_items[idx].serial_number;
            this.selected_item_sku = this.order_items[idx].sku;
            this.selected_item_index = idx;
            this.focusToSku();

        },

        updateOrderItem(){
            // Show loader
            this.modal_saved = false;
            this.show_modal_loader = true;
            this.modal_error = null;

            // Define Request payload
            let data = {
                order_id:this.transaction_id,
                serial_number:this.serial_number_from_input,
                current_serial_number:this.selected_item_serial,
                sku:this.sku_from_input,
                order_item_id:this.order_items[this.selected_item_index].item_id,
                quantity:this.order_items[this.selected_item_index].qty
            }
            var ref = this;

            if(this.sku_from_input != this.selected_item_sku){
                this.modal_error = 'SKUs does not match';
                this.show_modal_loader = false;
                this.focusToSku;
                return;
            }
            // Save
            axios.post('/thirdparty-reallocate-orders/save', {
                    'data': data,
            }).then(function (response) {
                // Hide loader
                ref.show_modal_loader = false;
                // Proceed to next item if success
                console.log(response);
                if(response.data.status == 'error'){
                    ref.modal_error = response.data.error_message;
                }else{
                    ref.order_items[ref.selected_item_index].serial_number = ref.serial_number_from_input;
                    ref.modal_saved = true;
                    let next_index = ref.selected_item_index + 1;
                    if(typeof ref.order_items[next_index] !== 'undefined'){
                        ref.serial_number_from_input = null;
                        ref.sku_from_input = null;
                        ref.reallocate(next_index);
                        ref.focusToSku();
                    }else{
                        ref.selected_item_index = null;
                        ref.closeModal();
                    }
                }


            });
        },

        updateOrderItemEnter(e){
            if (e.keyCode === 13) {
                return this.updateOrderItem();
            }
            return false;
        },

        updateProceedToSerial(e){
            if (e.keyCode === 13) {
                this.setFocusToSerial();
            }
        },

        saveReallocation(){
            this.transaction_id = null;
            this.transaction_id_from_input = null;
            this.serial_number_from_input = null;
            this.sku_from_input = null;
            this.selected_item_serial =null;
            this.selected_item_sku = null;
            this.selected_item_index = null;
            this.modal_saved = false;
            this.order = {
                name:null,
                address1:null,
                address2:null,
                city:null,
                state:null,
                zip:null,
                country:null,
                company:null,
                ref_number:null,
                phoneNumber:null,
            }
        },

        closeModal(){
            this.show_modal = false;
            this.serial_number_from_input = null;
            this.sku_from_input = null;
            this.selected_item_serial =null;
            this.selected_item_sku = null;
            this.selected_item_index = null;
            this.modal_saved = false;
        },

        setFocusToSku: function() {
            this.$refs.input_sku.focus();
        },

        focusToSku() {
            setTimeout(x => {
                this.$nextTick(() => this.setFocusToSku());
            }, 300);
        },
        
        setFocusToSerial: function() {
            this.$refs.input_serial.focus();
        }

    }

}
</script>
