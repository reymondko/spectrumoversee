<template>
    <section class="ship-pack-input-section" >
        <!--Visual Scan Modal -->
        <div class="modal fade" id="AddReason" tabindex="-1" role="dialog" aria-labelledby="AddReasonLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" >Are you sure?</h4>
                    </div>

                    <div class="modal-body">
                        <label >Reason:</label>
                        <textarea id="failReason" v-model="failReason" placeholder="Enter reason..." rows="3" max-rows="10" class="col-md-12" ></textarea>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default close-btn so-btn-close" data-dismiss="modal">No</button>
                        <button @click="AddReasonConfirmed()" data-dismiss="modal" class="btn btn-flat so-btn">Submit</button>
                    </div>
                </div>
            </div>
        </div>

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
            <br /><br />
            <a href="quality-inspector/report">Quality Inspector Report</a>
        </center>
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

        <!-- SEARCH TRANSACTION LOADER-->
        <center v-if="zpl_printing">
            <div class="loader"></div>
            Printing
        </center>

        <!-- FORMS: MAIN CONTENT -->
        <div class="order-body" v-if="this.order.ref_number">
            <div class="box inventory-box">
                <div class="box-body">
                    <!-- ORDER DETAILS -->
                    <div class="ship-pack-content">
                        <table class="details-table">
                            <tr>
                                <td style="width:50%;" class="detail-text">
                                    <h4 >Ship-To:</h4>
                                    <span style="display:block" >{{this.order.name}}</span>
                                    <span style="display:block" >{{this.order.address1}}</span>
                                    <span style="display:block">{{this.order.address2}}</span>
                                    {{this.order.city}},
                                    {{this.order.state}},
                                    {{this.order.zip}}
                                    <span style="display:block"> {{this.order.country}}</span>
                                    <span style="display:block"><br> {{this.order.emailAddress}}</span>
                                    
                                </td>
                                <td style="width:33%;width: 33%;vertical-align: text-bottom;" class="detail-text">
                                    <h4>Transaction ID: <u><b> {{this.transaction_id}} </b></u></h4>
                                    <h4>Reference #: <b>{{this.order.ref_number}}</b> </h4>
                                    <h4>Outbound Tracking #: <b>{{this.routingInfo.trackingNumber}}</b> </h4>
                                    <h4>Ship Method: <b>{{this.routingInfo.courier}}</b> </h4>
                                    
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="ship-pack-content">
                        <hr class="divider">
                    </div>
                    <!-- ORDER ITEMS -->
                        <table class="table table-striped table-bordered" style="font-size:14px;">
                            <thead>
                                <th class="sp-table-th th-mini">Line Item #</th>
                                <th class="sp-table-th th-mini">SKU</th>
                                <th class="sp-table-th th-mini">Item Description</th>
                                <th class="sp-table-th th-mini">Serial #</th>
                                <th class="sp-table-th th-mini">Lot #</th>
                                <th class="sp-table-th th-mini">Expiration Date</th>
                                <th class="sp-table-th th-mini">Quantity</th>
                                <th class="sp-table-th th-mini">Status</th>
                            </thead> 
                            <tbody>
                                <tr v-for="(item, index) in order_items" v-bind:key="index"  >
                                    <td>{{item.item_id}}</td>
                                    <td>{{item.sku}}</td>
                                    <td>{{item.description}}</td>
                                    <td>{{item.serial_number}}</td>
                                    <td>{{item.lot_number}}</td>
                                    <td>{{item.expiration}}</td>
                                    <td>{{item.qty}}</td>
                                    <td class="col-md-2">
                                        {{getOrderQI(transaction_id,item.item_id)}} 
                                        <div v-if="!item.status || this.editing==index"  >
                                            <button :class="[item.status==0 ? ' btn btn-danger active btn_status':' btn btn-danger btn_status']" style="float:right;" data-toggle="modal" data-target="#AddReason" 
                                            @click="AddReason(item.qi_id,item.reason,index)">FAIL</button>
                                            <button :class="[item.status==1 ? ' btn btn-success active btn_status':'btn btn-success btn_status']" stype="button"  style="float:right;margin-right:20px;" 
                                            @click="passedInspect(item.qi_id,item,index)" >PASS</button>
                                        </div>
                                        <div v-else> {{item.status == 1 ? 'PASS':'FAIL'}} <a href="#" @click="toggle(item,index,this.editing)">Edit</a></div> 
                                    </td> 
                                </tr>
                            </tbody>
                        </table>
                        <table class="table table-striped table-bordered" style="font-size:14px;">
                            <tbody> 
                                <tr v-for="(item, index) in subkits" :key="index"  >
                                    <td>{{item.subkit_id}}</td>
                                    <td>{{item.return_tracking}}</td>
                                    <td >{{item.qty}}</td>
                                    <td>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-flat so-btn" @click="continueXX()">Continue</button>
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
            saved:false,
            errorSearch:null,
            status:[],
            box_weights:[],
            shippingClientCarriers : [],
            add_visually: {},
            allShippingRates: [],
            saving:false,
            scan_err:'NOT FOUND',
            carrier_filter:'',
            selected_carrier:-1,
            selected_carrier_service:-1,
            transaction_id:null,
            customer_id:null,
            error_message:'',
            transaction_id_from_input:null,
            show_search_loader:false,
            show_not_found_err:false,
            show_duplicate_err:false,
            show_scan_err:false,
            scan_value:null,
            scan_serial:null,
            selected_package:null, //selected package on the package dropdown
            current_selected_packge_idx: null, //index of the toggled package for scanning
            order_items:[],
            carriers: [],
            services: [],
            shipping_rate_btn_enabled: false,
            routingInfo:{
                carrier:null,
                trackingNumber:null,
            },
            subkits:[],
            // printers: [],
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
                emailAddress:null,
            },
            selectedPrinter:null,
            selectedPrinterIdx:null,
            scan_qty:1,
            add_package:[], //added packages to table list
            has_zpl:false,
            zpl_printing:false,
            zpl_print_url:null, ///js/zpl/zpl.html
            shipping_client: {
                status: 'ready',
                required: false,
                data: null
            },
            get_shipping_rates: {
                status: 'ready',
                can_submit: false,
                data: [],
                selected: null,
            },
            signature_required:false,
            failReason_id: null, 
            failReason: null,
            editing: null,
            prev_edit:null,
		}
    },
    computed: {
        canGetShippingRates() {
            return this.get_shipping_rates.can_submit === true;
        },
        busyGetShippingRates() {
            return this.get_shipping_rates.status === 'busy';
        },
        getShippingRatesSuccess() {
            return this.get_shipping_rates.status === 'success';
        },
        shiprushRatesData() {
            return this.get_shipping_rates.data;
        },
        selectedShiprushRate() {
            let text = '&nbsp;';
            let selected = this.get_shipping_rates.selected;
            if (selected != null) {
                text = `
                    <i>selected:</i> <br/>
                    <strong>Name:</strong> ${selected.Name}<br/>
                    <strong>Service Type:</strong> ${selected.ServiceType}<br/>
                    <strong>Packaging Type:</strong> ${selected.PackagingType}<br/>
                    <strong>Total Amount:</strong> ${selected.Total}<br/>
                    <strong>Expected Delivery:</strong> ${selected.ExpectedDelivery}<br/>
                `;

            }
            return text;
        },
        ifSerialRequired() {
            return this.shipping_client.required;
        },
    },

    mounted() {
        this.selected_package = -1;
        // this.checkPrinterFromCookie();
        // this.getCarriers();
    },

    methods: {
        toggle(item,index) {
        
            console.log("togle "+item.item_id+" - "+index  + "===" + this.editing);
            
            if(this.editing == index){
                this.editing = index;
                this.prev_edit = index;
                this.order_items[index].status=null;
            }
            else{
                this.prev_edit = null;
                this.editing = index;
                this.order_items[index].status=null;
            }
            
        },
        continueXX(){
            this.transaction_id = null;
            this.show_search_loader = false;
            this.show_not_found_err = false;
            this.show_duplicate_err = false;
            this.transaction_id_from_input = null;
            this.order.ref_number = null;
        },
        
        printDate(date_string){
          var timestamp = Date.parse(date_string);

          if (isNaN(timestamp) == false) {
            return '-';
          }
        },
        passedInspect(qi_id,item,index) {
            console.log(qi_id+" === "+index)
            var ref=this;
            console.log(ref.order_items[index]);
            
            axios.get('/quality-inspector/approve/'+qi_id)
            .then(response => {
                //this.carriers = response.data;
                // console.log('carriers:', this.carriers)
                ref.order_items[index].status=1;
            })
            .catch(err => {
                //console.log('list carriers error:', error)
            });
        },
        /**
        * Add Fail Reason
        **/
        AddReason(qi_id, sku,index){
          this.failReason_id = qi_id;
          this.prev_edit = index;
        },
        AddReasonConfirmed(){
            //console.log(this.failReason_id);
            var ref = this;
            axios.post('/quality-inspector/fail', {
                    qi_id: this.failReason_id,
                    reason: this.failReason,
                }).then(function (response) {
                    // console.log('searchTransactionId:',response)
                   // var status = response.data.status;
                        console.log(ref.prev_edit);
                    //if(status == 'ok') {
                    console.log("test "+ref.order_items[ref.prev_edit].status);
                        ref.order_items[ref.prev_edit].status = "fail";
                        ref.editing = null;
                        ref.failReason = null;
                        
                    //} 
                    //else{
                       
                    //}
                });
          ;
        },
        searchTransactionId(){
            if(this.transaction_id_from_input != null) {
                this.show_search_loader = true;
                this.show_not_found_err = false;
                this.show_duplicate_err = false;
                this.error_message = '';

                //Remove Previous ZPL response
                this.has_zpl=false;
                this.zpl_print_url=null;

                var ref = this;

                axios.post('/quality-inspector/search', {
                    'transaction_id': this.transaction_id_from_input,
                }).then(function (response) {
                    // console.log('searchTransactionId:',response)
                    var status = response.data.status;
                    if(status == 'ok') {
                        var data = response.data.result;
                        console.log(data);
                        ref.transaction_id = ref.transaction_id_from_input;
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
                            emailAddress:order_details.shipTo.emailAddress,                            
                        };
                        ref.routingInfo = { 
                            courier:order_details.routingInfo.carrier,
                            trackingNumber:order_details.routingInfo.trackingNumber,
                        }
                        //ref.getSubkitIDs();
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
        getOrderQI(transaction_id,item_id) {
            axios.get('/quality-inspector/quality-inspect/'+transaction_id+'/'+item_id)
            .then(response => {
                return response.data;
                 console.log('QI:', response.data)
            })
            .catch(err => {
                console.log('OrderQI:', error)
            });
        },
        getSubkitIDs() {
            axios.get('/quality-inspector/getSubkitIds')
            .then(response => {
                this.subkits = response.data;
                // console.log('carriers:', this.carriers)
            })
            .catch(err => {
                console.log('list carriers error:', error)
            });
        },
        getQualityInspectorDetails() {
            this.shipping_client.data = null;
            this.shipping_client.required = false;
            this.shipping_client.status = 'busy';
            var vm = this;
            axios.post('/quality-inspector/qualityinspector_details', {'customer_id':this.customer_id})
            .then(response => {
                if (response.data.found === true) {
                    vm.shipping_client.data = response.data.client;
                    vm.shippingClientCarriers  =vm.shipping_client.data.carriers.split(",");
                    if (response.data.client.require_scan_serial_number == 1) {
                        vm.shipping_client.required = true;
                    }
                }

                vm.shipping_client.status = 'success';
            })
            .catch(error => {
                console.log('getQualityInspectorDetails error:', error);
                vm.shipping_client.status = 'error';
            });
        },
        notFoundErr(){
            this.show_search_loader = false;
            this.show_not_found_err = true;
            this.show_duplicate_err = false;
            this.transaction_id = null;
            this.transaction_id_from_input = null;
            this.order.ref_number = null;
        },
        searchTransactionIdByEnter(e){
            if (e.keyCode === 13) {
                return this.searchTransactionId();
            }
            return false;
        },
    }


}
</script>
