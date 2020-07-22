<template>
    <section class="ship-pack-input-section" >



        <!--Visual Scan Modal -->
        <div class="modal fade" id="visualScan" tabindex="-1" role="dialog" aria-labelledby="visualScanLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" >Are you sure?</h4>
                    </div>

                    <div class="modal-body">

                                    <label >Selected Package Size:</label>
                                    <select class="form-control sp-pack-sel" @change="togglePackageFromSelect($event)">
                                        <option value="-1">None</option>
                                        <option v-for="(package,index) in add_package" :key="index" :selected="package.selected" :value="index">
                                            {{ package.pseudo_package_name }}
                                        </option>
                                    </select>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default close-btn so-btn-close" data-dismiss="modal">No</button>
                        <button @click="AddVisuallyConfirmed()" data-dismiss="modal" class="btn btn-flat so-btn">Yes</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- SEARCH TRANSACTION -->
        <center v-if="(!this.transaction_id) && (!this.show_zpl_print_success)">
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
            <a href="/ship-pack/recent">Recent Shipments</a>
        </center>

        <!-- SUCCESS MESSAGE -->
        <center v-if="(!this.zpl_printing) && (this.show_zpl_print_success)">
            <div class="box box-solid box-primary ship-pack-input-box"  v-if="!saving">
                <div class="box-header ship-pack-box-header"></div>
                <div class="box-body">
                    <div class="form-group">
                        <h3>Printed Successfully</h3>
                    </div>
                    <div class="form-group settings-btn-container">
                        <button type="button" class="btn btn-flat so-btn" @click="zplPrintAgain()" >Print Again</button>
                        <button type="button" class="btn btn-flat so-btn" @click="continueShipPack()">Continue</button>
                    </div>
                </div>
            </div>
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

                    <!-- PACKAGE SIZES -->
                    <div class="ship-pack-content">
                        <div style="width: 40%;display:inline-block;margin-bottom: -50px;">
                            <select id="order_items_select" class="form-control weight-fields" style="
                                    width: 50%;
                                    display: inline-block;
                                    " v-model="selected_package">
                                    <option value="-1">Select Package</option>
                                    <option v-for="(p,index) in package_size" :key="index" v-bind:value="index">{{p.package_name}}</option>
                                </select>
                                <button @click="addPackage()"
                                type="button"
                                class="btn btn-flat so-btn weight-fields"
                                style="margin-top: unset;margin-top: -4px;">Add Package</button>
                        </div>
                        <div style="width:50%;display:inline-block;">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <th class="sp-table-th th-mini">Package</th>
                                    <th class="sp-table-th th-mini">Length</th>
                                    <th class="sp-table-th th-mini">Width</th>
                                    <th class="sp-table-th th-mini">Height</th>
                                    <th class="sp-table-th th-mini">Weight</th>
                                    <th class="sp-table-th th-mini">Content QTY</th>
                                     <th class="sp-table-th th-mini">Action</th>
                                </thead>
                                <tbody>
                                    <tr v-for="(p,index) in add_package" :key="index" class="sp-table-tr" @click="togglePackage(index)" :class="[add_package[index].selected ? 'pack-sel-tr':'pack-tr']">
                                        <td class="sp-table-td th-mini">{{add_package[index].pseudo_package_name}}</td>
                                        <td class="sp-table-td th-mini">{{add_package[index].length}}</td>
                                        <td class="sp-table-td th-mini">{{add_package[index].width}}</td>
                                        <td class="sp-table-td th-mini">{{add_package[index].height}}</td>
                                        <td class="sp-table-td th-mini">{{add_package[index].weight}}</td>
                                        <td class="sp-table-td th-mini">{{add_package[index].content_qty}}</td>
                                        <td class="sp-table-td th-mini">
                                            <button type="button" class="btn btn-flat so-btn-close scan-btn" @click="removePackage(index)">Delete</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="ship-pack-content">
                        <hr class="divider">
                    </div>

                    <!-- ORDER ITEMS -->
                    <div class="ship-pack-content" style="text-align:left" >
                        <div style="
                            text-align: center;
                            width: 122px;
                            padding: 15px;
                            background: #cc8181ad;
                            color: #ff0000ba;
                            font-weight: 700;
                            display:block;"
                            v-if="show_scan_err">{{scan_err}}</div>
                            <div v-if="shipping_client.data == null || ( shipping_client.data.require_scan_serial_number!=2 && shipping_client.data.require_scan_serial_number!='2')" >
                            <input v-model="scan_value" v-on:keyup="scanItemEnter" type="text" class="form-control scan-input" placeholder="SKU or UPC">
                            <label v-if="ifSerialRequired" class="scan-input-qty-lbl">Serial #:</label>
                            <input v-if="ifSerialRequired" v-model="scan_serial" @keyup.enter="scanItem()" type="text" class="form-control scan-input" id="serial_number" placeholder="Serial #">
                            <label class="scan-input-qty-lbl">Quantity:</label>
                            <input v-model="scan_qty" type="text" class="form-control scan-input-qty" placeholder="Quantity" >
                            <label class="scan-input-qty-lbl">Selected Package Size:</label>
                            <select class="form-control sp-pack-sel" @change="togglePackageFromSelect($event)">
                                <option value="-1">None</option>
                                <option v-for="(package,index) in add_package" :key="index" :selected="package.selected" :value="index">
                                    {{ package.pseudo_package_name }}
                                </option>
                            </select>
                            <button @click="scanItem()" type="button" class="btn btn-flat so-btn scan-btn">Scan</button>
                            </div>
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <th class="sp-table-th th-mini">SKU</th>
                                    <th class="sp-table-th th-mini">Item Description</th>
                                    <th class="sp-table-th th-mini">Serial #</th>
                                    <th class="sp-table-th th-mini">Lot #</th>
                                    <th class="sp-table-th th-mini">Expiration Date</th>
                                    <th class="sp-table-th th-mini">QTY Ordered</th>
                                    <th class="sp-table-th th-mini">QTY Packed</th>
                                    <th class="sp-table-th th-mini">QTY Remaining</th>
                                </thead>
                                <tbody>
                                    <tr v-for="(item, index) in order_items" :key="index" :class="[item.done ? 'scan-tr-com':'scan-tr-inc']" >
                                        <td>{{item.sku}}</td>
                                        <td>{{item.description}}</td>
                                        <td>{{item.serial_number}}</td>
                                        <td>{{item.lot_number}}</td>
                                        <td>{{item.expiration}}</td>
                                        <td>{{item.qty}}</td>
                                        <td>{{item.qty_packed}}</td>
                                        <td>
                                        {{item.qty_remaining}}
                                        <button class="btn btn-primary" style="float:right;" v-if="shipping_client.data && shipping_client.data.require_scan_serial_number==2" data-toggle="modal" data-target="#visualScan" @click="AddVisually(item.qty, item.sku)">ADD</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- SHIP PACKAGE WITH ITEMS -->
                        <div v-if="package.items.length > 0" v-for="(package,index) in add_package" :key="index"
                        class="ship-pack-content package-item-tbl"
                        style="text-align:left">
                            <div><h4><b>Package #{{index + 1}} ({{package.pseudo_package_name}})</b></h4></div>
                            <table class="table table-striped table-bordered tbl_mini">
                                <thead>
                                    <th class="sp-table-th th-mini">SKU</th>
                                    <th class="sp-table-th th-mini">Item Description</th>
                                    <th class="sp-table-th th-mini ">Serial #</th>
                                    <th class="sp-table-th th-mini">Lot #</th>
                                    <th class="sp-table-th th-mini">Expiration Date</th>
                                    <th class="sp-table-th th-mini">QTY Packed</th>
                                </thead>
                                <tbody>
                                    <tr v-for="(p,i) in package.items" :key="i" class="sp-table-tr-b">
                                        <td class="sp-table-td">{{p.sku}}</td>
                                        <td class="sp-table-td">{{p.description}}</td>
                                        <td class="sp-table-td"><a href="javascript:void(0)" @click="updateAddedPackageField(index,i,'serial_number',p.serial_number)">{{p.serial_number}}</a></td>
                                        <td class="sp-table-td"><a href="javascript:void(0)" @click="updateAddedPackageField(index,i,'lot_number',p.lot_number)">{{p.lot_number}}</a></td>
                                        <td class="sp-table-td">{{p.expiration}}</td>
                                        <td class="sp-table-td">{{p.qty_packed}}</td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="sp-form-btm" style="text-align: left;text-align: left;height: 60px;margin-top: -30px;">
                                <label>Package Weight: &nbsp;</label>
                                <input
                                    type="text"
                                    class="form-control scan-input weight-fields"
                                    placeholder="Enter weight: (Lbs.)"
                                    style="width: 30%;"
                                    v-model="package.weight"
                                    v-on:input="updatePackageWeight()"
                                >
                                <button class="btn btn-flat so-btn weight-fields" style="margin-top:0px;margin-bottom:3px;" @click="UpdateWeights('add',index)">UPDATE</button>
                            </div>

                            <div class="ship-pack-content" style="text-align: left;" v-if="order.country != 'US'">
                                <label style="display: block;">Commodity Description</label>
                                <textarea placeholder="Commodity Description" rows="6" cols="61" v-model="package.commodity_description"></textarea>
                            </div>

                        </div>


                        <div v-if="false" class="ship-pack-content">
                            <hr class="divider">
                        </div>

                        <!-- GET SHIPPING RATES -->

                        <div class="ship-pack-content w-border" style="padding-top:20px">
                            <div class="shipping_rate_filter" v-show="getShippingRatesSuccess && shippingClientCarriers.length">
                                <select v-model="carrier_filter" @change="ChangeCarrierFilter" class="form-control sp-pack-sel">
                                  <option value="">Filter Carrier</option>
                                  <option :value="carrier" v-for="carrier in shippingClientCarriers">{{carrier}}</option>
                                </select>
                            </div>
                            <div class="sp-rates-div" style="text-align:center"  v-show="getShippingRatesSuccess">
                                    <table class="table table-striped table-bordered sp-rates-table">
                                        <thead>
                                            <th class="sp-table-th th-mini">Account</th>
                                            <th class="sp-table-th th-mini">Name</th>
                                            <th class="sp-table-th th-mini">Service Type</th>
                                            <!--<th class="sp-table-th th-mini">Package Type</th>-->
                                            <th class="sp-table-th th-mini">Total Amount</th>
                                            <th class="sp-table-th th-mini">Action</th>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(data,index) in get_shipping_rates.data" :key="index">
                                                <td class="sp-table-td th-mini">{{data.AccountName}}</td>
                                                <td class="sp-table-td th-mini">{{data.Name}}</td>
                                                <td class="sp-table-td th-mini">{{data.ServiceType}}</td>
                                                <!--<td class="sp-table-td th-mini">{{data.PackagingType}}</td>-->
                                                <td class="sp-table-td th-mini">${{data.Total}}</td>
                                                <td class="sp-table-td th-mini">
                                                    <button type="button" class="btn btn-flat so-btn" @click="shipPackage(index)">
                                                        Ship
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="sp-form-btm" style="text-align:center">
                                <div>
                                    <button :disabled="!shipping_rate_btn_enabled" @click="getShiprushRates()"
                                    type="button"
                                    class="btn btn-flat so-btn override-btn weight-fields">
                                        Get Shipping Rates <i v-if="busyGetShippingRates" class="fa fa-spinner fa-spin"></i>
                                    </button>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" v-model="signature_required">
                                    <label class="form-check-label" >
                                        Signature Required
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- MANUAL OVERRIDE -->
                        <div v-if="false" class="ship-pack-content w-border" >
                            <div class="sp-form-btm" style="text-align:center">
                                <h3>Manual Override</h3>
                                <div>
                                    <select v-model="selected_carrier" @change="onCarrierSelect()" id="order_items_select" class="form-control" style="width: 20%;display: inline-block;height: 50px;">
                                        <option value="-1">Select Carrier</option>
                                        <option v-for="(carrier,index) in carriers" :key="index" v-bind:value="index">{{carrier.nickname}}</option>
                                    </select>
                                    <select v-if="this.selected_carrier !== -1"  v-model="selected_carrier_service" id="order_items_select" class="form-control" style="width: 20%;display: inline-block;height: 50px;">
                                        <option value="-1">Select Service</option>
                                        <option  v-for="(service,index) in services" :key="index" v-bind:value="index" >{{service.name}}</option>
                                    </select>

                                    <select class="form-control" style="width: 20%;display: inline-block;height: 50px;" v-if="this.selected_carrier === -1">
                                        <option value="-1">Select Service</option>
                                    </select>
                                </div>
                                <div>
                                    <button type="button" class="btn btn-flat so-btn override-btn weight-fields" @click="saveData('carrier')">Complete & Manual Override</button>
                                </div>
                            </div>
                        </div>

                        <div v-if="false" class="ship-pack-content">
                            <hr class="divider">
                        </div>

                        <div v-if="false" class="ship-pack-content" style="text-align:left;">
                            <button type="button" class="btn btn-flat so-btn rate-btn" @click="saveData()">Complete & Rate Shop</button>
                        </div>
                </div>
            </div>
        </div>

        <div style="display:none;border:none" v-if="this.has_zpl == true">
            <iframe :src="zpl_print_url" @load="zplPrintFrameHasLoaded()"></iframe>
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
            },
            selectedPrinter:null,
            selectedPrinterIdx:null,
            scan_qty:1,
            add_package:[], //added packages to table list
            show_zpl_print_success:false,
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
            signature_required:false
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
        ChangeCarrierFilter(){
          if(this.carrier_filter == ''){
            this.get_shipping_rates.data = this.allShippingRates;
          }else{
            this.get_shipping_rates.data = this.allShippingRates.filter(s => s.Name.includes(this.carrier_filter));
          }
        },
        printDate(date_string){
          var timestamp = Date.parse(date_string);

          if (isNaN(timestamp) == false) {
            return '-';
          }
        },
        getCarriers() {
            axios.get('/ship-pack/get-carriers')
            .then(response => {
                this.carriers = response.data;
                // console.log('carriers:', this.carriers)
            })
            .catch(err => {
                console.log('list carriers error:', error)
            });
        },

        onCarrierSelect() {
            this.services = [];

            axios.get('/ship-pack/get-services/'+this.carriers[this.selected_carrier].code)
            .then(response => {
                this.services = response.data;
                // console.log('services:', this.services)
            })
            .catch(err => {
                console.log('list services error:', error)
            });
        },

        searchTransactionId(){
            if(this.transaction_id_from_input != null) {
                this.show_search_loader = true;
                this.transaction_id = this.transaction_id_from_input;
                this.show_not_found_err = false;
                this.show_duplicate_err = false;
                this.error_message = '';

                //Remove Previous ZPL response
                this.has_zpl=false;
                this.zpl_print_url=null;

                var ref = this;

                axios.post('/ship-pack/search', {
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
                        ref.getShippingClient();
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

        getShippingClient() {
            this.shipping_client.data = null;
            this.shipping_client.required = false;
            this.shipping_client.status = 'busy';

            var vm = this;
            axios.post('/ship-pack/get-shipping-client', {'customer_id':this.customer_id})
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
                console.log('getShippingClient error:', error);
                vm.shipping_client.status = 'error';
            });
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
            
            this.order['ref_number'] = null;
        },

        addPackage(){
            if(this.selected_package > -1){

                // Add Numbering for package types
                var package_type_count = 1
                this.add_package.map((val,index) => {
                    if(val.id == this.package_size[this.selected_package].id){
                        package_type_count++;
                    }
                })


                var tmp = {
                    'content_qty':0,
                    'height':this.package_size[this.selected_package].height,
                    'id':this.package_size[this.selected_package].id,
                    'length':this.package_size[this.selected_package].length,
                    'package_name':this.package_size[this.selected_package].package_name,
                    'pseudo_package_name': this.package_size[this.selected_package].package_name+' #'+package_type_count,
                    'selected':this.package_size[this.selected_package].selected,
                    'weight':this.package_size[this.selected_package].weight,
                    'width':this.package_size[this.selected_package].width,
                    'items':[]
                }

                this.add_package.push(tmp);
                // this.selected_package = -1;

                var recent_idx = this.add_package.length - 1;
                this.togglePackage(recent_idx);
                this.checkEnableShippingRate();
            }
        },

        togglePackage(idx){
          if(this.add_package[idx]){
            this.add_package[idx].selected = true;
            this.current_selected_packge_idx = idx;
            for(var x = 0; x < this.add_package.length; x++){
                if(idx != x){
                    this.add_package[x].selected = false;
                }
            }
          }
        },

        togglePackageFromSelect(e){
            var idx = e.target.value;
            this.add_package[idx].selected = true;
            this.current_selected_packge_idx = idx;
            for(var x = 0; x < this.add_package.length; x++){
                if(idx != x){
                    this.add_package[x].selected = false;
                }
            }
        },

        togglePrinterFromSelect(e){
            document.cookie = "selectedPrinterIdx=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
            var idx = e.target.value;
            this.selectedPrinterIdx = idx;
            this.selectedPrinter = this.printers[idx];
            document.cookie = "selectedPrinterIdx="+idx;
        },

        /*
            checkPrinterFromCookie(){
                var selectedPrinterIdxFromCookie = this.getCookie('selectedPrinterIdx');
                if(selectedPrinterIdxFromCookie > -1){
                    this.selectedPrinterIdx = selectedPrinterIdxFromCookie;
                    this.selectedPrinter = this.printers[selectedPrinterIdxFromCookie];
                }
            },
        */


        getCookie(cname) {
            var name = cname + "=";
            var ca = document.cookie.split(';');
            for(var i = 0; i < ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) == ' ') {
                c = c.substring(1);
                }
                if (c.indexOf(name) == 0) {
                return c.substring(name.length, c.length);
                }
            }
            return "";
        },


        scanItem() {
            this.show_scan_err = false;
            this.saved = false;
            var found = false;

            var valid = true;
            var valid_serial = true;
            // console.log('this.order_items[ctr].sku:',this.current_selected_packge_idx)

            if (this.ifSerialRequired == true && this.scan_serial == null) {
                valid = false;
            }
            var serialNumbers = [];
            for(var ctr = 0; ctr < this.order_items.length; ctr++) {
                if(this.order_items[ctr].serial_number && this.order_items[ctr].serial_number != '' && this.order_items[ctr].serial_number != 'N/A'){
                  serialNumbers.push(this.order_items[ctr].serial_number);
                }
            }
            //console.log("Scan", serialNumbers);
            if(this.scan_value != null && this.current_selected_packge_idx != null && valid == true) {
                for(var ctr = 0; ctr < this.order_items.length; ctr++) {
                    //console.log(this.order_items[ctr].sku + " -- >" + this.order_items[ctr].upc);
                    if((this.order_items[ctr].sku == this.scan_value || this.order_items[ctr].upc == this.scan_value) && this.order_items[ctr].qty_remaining > 0) {
                        if (this.ifSerialRequired == true) {

                            if (this.scan_serial != this.order_items[ctr].serial_number) {
                                //valid_serial = false;
                                continue;
                            }

                            /*
                            if(serialNumbers.indexOf(this.scan_serial) <= -1){
                                valid_serial = false;
                                break;
                            }*/
                        }
                        found = true;
                        this.scan_value=null;
                        var qty_packed = this.order_items[ctr].qty_packed;
                        var qty_remaining = this.order_items[ctr].qty_remaining;
                        var current_qty_packed = parseInt(this.scan_qty);

                        if(this.scan_qty > qty_remaining){
                            alert("Cannot add more than remaining quantity");
                            return false;
                            //this.order_items[ctr].qty_packed = qty_packed + qty_remaining;
                            //this.order_items[ctr].qty_remaining = 0;
                            //current_qty_packed =  parseInt(qty_remaining);
                        }
                        else{
                            qty_packed = parseInt(qty_packed) + parseInt(this.scan_qty);
                            qty_remaining = qty_remaining - this.scan_qty;
                            this.order_items[ctr].qty_packed =  qty_packed;
                            this.order_items[ctr].qty_remaining = qty_remaining;
                        }

                        if(this.order_items[ctr].qty_remaining == 0){
                            this.order_items[ctr].done = true;
                        }

                        var items = {
                            'sku': this.order_items[ctr].sku,
                            'description':this.order_items[ctr].description,
                            'serial_number':this.order_items[ctr].serial_number,
                            'lot_number':this.order_items[ctr].lot_number,
                            'exp':this.order_items[ctr].expiration,
                            'qty_packed':current_qty_packed
                        }



                        var total_packed = 0;

                        this.add_package[this.current_selected_packge_idx].items.push(items);

                        this.add_package[this.current_selected_packge_idx].items.map((val,index) => {
                            total_packed += val.qty_packed;
                        });

                        if(total_packed > 0){
                            this.add_package[this.current_selected_packge_idx].content_qty = total_packed;
                        }

                        break;
                    }
                }

                // flag that allow 'get shipping rates' to be triggered
                this.get_shipping_rates.can_submit = true;
            }


            this.scan_value = null;
            if (this.ifSerialRequired == true && this.scan_serial == null) {
                this.show_scan_err = true;
                this.scan_err = "SERIAL NUMBER IS REQUIRED";
            }
            else if (valid_serial == false) {
                this.show_scan_err = true;
                this.scan_err = "SERIAL NUMBER IS INCORRECT";
            }
            else if(this.current_selected_packge_idx == null){
                this.show_scan_err = true;
                this.scan_err = "INVALID PACKAGE SIZE";
            }
            else if(!found ){
                this.show_scan_err = true;
                this.scan_err = "NOT FOUND";
            }
            this.UpdateWeights('check',0);
        },

        scanItemEnter(e){
            if (e.keyCode === 13 && !this.ifSerialRequired) {
                return this.scanItem();
            }else if(e.keyCode === 13 && this.ifSerialRequired){
                $('#serial_number').focus();
            }
            return false;
        },

        saveData(type = 'default'){
            var carrier_name = null;
            var carrier_service = null;
            var carrier_service_id = null;
            var verified = true;

            if(type != 'default'){
                if( this.selected_carrier !== -1 && this.selected_carrier_service !== -1){
                    carrier_name = this.carriers[this.selected_carrier].name;
                    carrier_service = this.services[this.selected_carrier_service].name;
                    carrier_service_id = this.carriers[this.selected_carrier].code;
                }
            }

            this.order_items.map((val,index) => {
                if(val.done == false){
                    verified = false;
                }
            });

            if(verified == true){
                if(type != 'default' ){
                    if(this.selected_carrier == -1 || this.selected_carrier_service == -1){
                        alert('Please select a carrier and a service');
                        return;
                    }
                }

                if(this.selectedPrinterIdx == ''){
                    alert('Please select a shipping label printer');
                    return;
                }

                this.saving = true;
                var tmp_order = this.order;
                //tmp_order.phoneNumber = '1234567890';
                this.order = {
                    name:null,
                    address1:null,
                    address2:null,
                    city:null,
                    state:null,
                    zip:null,
                    country:null,
                    company:null,
                    ref_number:null
                };

                var ref = this;
                var request_fields = {
                    'transaction_id': this.transaction_id,
                    'customer_id':this.customer_id,
                    'ship_package_data': this.add_package,
                    'carrier_name':carrier_name,
                    'carrier_service':carrier_service,
                    'carrier_service_id':carrier_service_id,
                    'zip':tmp_order.zip,
                    'order_details':tmp_order,
                    'shipping_label_printer':this.selectedPrinter,
                    'carrier_code': type == 'default' ? null : this.carriers[this.selected_carrier].code,
                    'carrier_service_code': type == 'default' ? null : this.carriers[this.selected_carrier].code,
                };
                console.log('request fields:',request_fields);
                // return;
                axios.post('/ship-pack/save', request_fields)
                .then(function (response) {
                    console.log('response:',response)
                    // ref.saving = false;
                    ref.saved=true;
                    // return
                    var status = response.data.status;
                    if(status == 'saved'){

                        ref.saving = false;
                        ref.selected_carrier=-1;
                        ref.selected_carrier_service=-1;
                        // ref.transaction_id=null;
                        ref.customer_id=null;
                        ref.transaction_id_from_input=null;
                        ref.show_search_loader=false;
                        ref.show_not_found_err=false;
                        ref.show_scan_err=false;
                        ref.scan_value=null;
                        ref.selected_package=null; //selected package on the package dropdown
                        ref.current_selected_packge_idx= null; //index of the toggled package for scanning
                        ref.order_items=[];
                        ref.order={
                            name:null,
                            address1:null,
                            address2:null,
                            city:null,
                            state:null,
                            zip:null,
                            country:null,
                            company:null,
                            ref_number:null
                        };
                        ref.scan_qty=1;
                        ref.add_package=[]; //added packages to table list
                        ref.saved=true;
                        ref.has_zpl=true;
                        ref.zpl_print_url='/js/zpl/zpl.html?zpl='+response.data.shiprush_response.zpl;
                        ref.zpl_printing = true;
                        ref.get_shipping_rates = null;
                        ref.get_shipping_rates = {
                            status: 'ready',
                            can_submit: false,
                            data: [],
                            selected: null,
                        };
                        ref.shippingClientCarriers = [];
                        ref.shipping_rate_btn_enabled = false;

                    }else{
                        ref.saving = false;
                        ref.order = tmp_order;
                        alert('Error Saving: '+response.data.message);
                    }
                });

            }else{
                alert('Incomplete package assignment');
            }

        },

        updateAddedPackageField(packageIdx,itemIdx,fieldToUpdate,fieldValue){
            var currValue = (fieldValue == 'N/A'?'':fieldValue);
            var newValue = prompt('Please enter a new value',currValue);
            if(newValue != fieldValue && newValue != null){
                this.add_package[packageIdx].items[itemIdx][fieldToUpdate] = newValue;
                return;
            }

            return false;
        },

        continueShipPack(){
            this.show_zpl_print_success = false;
            this.transaction_id = null;
            localStorage.removeItem('zpl_datastring'); // Destroy Stored Value
        },

        zplPrintFrameHasLoaded(){
            console.log(this.zpl_print_url);
            this.zpl_printing = false;
            this.show_zpl_print_success = true;
        },

        zplPrintAgain(){
            this.zpl_printing = true;
            this.zpl_print_url = this.zpl_print_url+'&dt='+new Date();
        },

        getShiprushRates() {
            
            //check to see if all items have been scanned
            var verified = true;
            this.order_items.map((val,index) => {
                if(val.done == false){
                    verified = false;
                }
            });
            if (!verified) {
              alert('Please scan all items before rate shopping.');
              return false;
            }

            this.get_shipping_rates.selected = null;
            this.get_shipping_rates.can_submit = false;
            this.get_shipping_rates.status = 'busy';

            var vm = this;
            axios.post('/ship-pack/get-shiprush-rates', {
                customer_id: this.customer_id,
                order: this.order,
                packages: this.add_package,
                signature_required:this.signature_required
            })
            .then(response => {
                if (response.data.IsSuccess === "true") {
                    var shipping_rate_data = response.data.AvailableServices.AvailableService;
                    console.log("Shipping rates", shipping_rate_data);
                    // Sort by service name
                    shipping_rate_data.sort(function(a, b){
                        if(parseFloat(a.Total) < parseFloat(b.Total)) { return -1; }
                        if(parseFloat(a.Total) > parseFloat(b.Total)) { return 1; }
                        return 0;
                    })

                    this.allShippingRates = shipping_rate_data;

                    this.get_shipping_rates.data = shipping_rate_data;
                }
                else {
                    this.get_shipping_rates.status = 'failed';
                }

                vm.get_shipping_rates.status = 'success';
                vm.get_shipping_rates.can_submit = true;
            })
            .catch(err => {
                console.log('getting shiprush rate error:', err)

                vm.get_shipping_rates.status = 'error';
                vm.get_shipping_rates.can_submit = true;
            });
        },

        shiprushRatesSelectChange($event) {
            this.get_shipping_rates.selected = this.get_shipping_rates.data[$event.target.value];
        },

        // Update Package Weight
        updatePackageWeight(){
            this.checkEnableShippingRate();
        },

        // Check if all packages have weight
        // Set shipping_rate_btn_enabled if true otherwise set to false
        checkEnableShippingRate(){
            for(var x = 0; x < this.add_package.length; x++){
                var enable_btn = true;
                if(this.add_package[x].weight < 0 || this.add_package[x].weight == ''){
                    enable_btn = false;
                }
            }
            //this.shipping_rate_btn_enabled = enable_btn;
        },

        /*
        * Ship the selected package based on the selected carrier & service type
        * @param $idx = int | index of the selected shipping service `this.get_shipping_rates.data`
        */
        shipPackage(idx){

            var verified = true;
            this.order_items.map((val,index) => {
                if(val.done == false){
                    verified = false;
                }
            });

            if(verified == true){
                this.saving = true;
                var tmp_order = this.order;
                //tmp_order.phoneNumber = '1234567890';
                this.order = {
                    name:null,
                    address1:null,
                    address2:null,
                    city:null,
                    state:null,
                    zip:null,
                    country:null,
                    company:null,
                    ref_number:null
                };

                var ref = this;
                var request_fields = {
                    'transaction_id': this.transaction_id,
                    'customer_id':this.customer_id,
                    'ship_package_data': this.add_package,
                    'zip':tmp_order.zip,
                    'order_details':tmp_order,
                    'carrier': this.get_shipping_rates.data[idx],
                    'signature_required':this.signature_required
                };

                axios.post('/ship-pack/save', request_fields)
                .then(function (response) {
                    ref.saved=true;
                    var status = response.data.status;
                    if(status == 'saved'){
                        var zpl_data = response.data.shiprush_response.zpl;
                        var zpl_datastring = zpl_data.join();

                        localStorage.setItem('zpl_datastring',zpl_datastring); //Temporarily store on local storage for use on zpl printing

                        ref.saving = false;
                        ref.selected_carrier=-1;
                        ref.selected_carrier_service=-1;
                        ref.customer_id=null;
                        ref.transaction_id_from_input=null;
                        ref.show_search_loader=false;
                        ref.show_not_found_err=false;
                        ref.show_scan_err=false;
                        ref.scan_value=null;
                        ref.selected_package=null; //selected package on the package dropdown
                        ref.current_selected_packge_idx= null; //index of the toggled package for scanning
                        ref.order_items=[];
                        ref.order={
                            name:null,
                            address1:null,
                            address2:null,
                            city:null,
                            state:null,
                            zip:null,
                            country:null,
                            company:null,
                            ref_number:null
                        };
                        ref.scan_qty=1;
                        ref.add_package=[]; //added packages to table list
                        ref.saved=true;
                        ref.has_zpl=true;
                        ref.zpl_print_url='/js/zpl/zpl.html?zpl=1';
                        ref.zpl_printing = true;
                        ref.get_shipping_rates.data = null;
                        ref.shipping_rate_btn_enabled = false;

                    }else{
                        ref.saving = false;
                        ref.order = tmp_order;
                        alert('Error Saving: '+response.data.message);
                    }
                });
            }else{
                alert('Incomplete package assignment');
            }


        },

        /**
         * Delete Package From Package list
         * @param idx = int | index of the selected shipping service `this.add_package`
         */
        removePackage(idx){
            var c = confirm("Are you sure you want to remove this package?");
            if(c){
                this.add_package[idx].items.map((val,index) => {
                    this.updateRemainingPackedBySku(val.sku,val.qty_packed);
                });
            }

            this.add_package.splice(idx,1);
            this.UpdateWeights('remove',idx);
        },

        /**
         * Update order item packed and remaining by sku
         * @param sku = str | the sku of the selected package
         * @param count = int | value to add on remaining and remove from packed
         */
        updateRemainingPackedBySku(sku,count){
            for(var ctr = 0; ctr < this.order_items.length; ctr++) {
                if(this.order_items[ctr].sku == sku) {
                    this.order_items[ctr].qty_remaining = this.order_items[ctr].qty_remaining + count;
                    this.order_items[ctr].qty_packed = this.order_items[ctr].qty_packed - count;
                    this.order_items[ctr].done = false;
                    break;
                }
            }
        },

        /**
        *Update the weights, make sure every box weight is updated
        ***/
        UpdateWeights(action, index){
          if(action == 'add'){
            this.box_weights[index] = 1;
          }else if(action=='remove'){
            this.box_weights.splice(index,1);
          }

          var literalLength = this.box_weights.filter(i => i === 1).length;
          console.log("Literal", literalLength);
          if(literalLength == this.add_package.length ){
            this.shipping_rate_btn_enabled = true;
          }else{
            this.shipping_rate_btn_enabled = false;
          }
          console.log("Box weights", this.box_weights);
        },
        /**
        * Add Visually
        **/
        AddVisually(qty, sku){
          this.add_visually.qty = qty;
          this.add_visually.sku = sku;
        },
        AddVisuallyConfirmed(){
          this.scan_value = this.add_visually.sku;
          this.scan_qty = this.add_visually.qty;
          this.scanItem();
        }
    }


}
</script>
