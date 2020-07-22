<template>
    <section class="ship-pack-input-section" >


        <!--Visual Scan Modal -->
        <div class="modal fade" id="closeBatch" tabindex="-1" role="dialog" aria-labelledby="closeBatchLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <h4 class="modal-title" >Are you sure you want to close this batch?</h4>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default close-btn so-btn-close" data-dismiss="modal">No</button>
                        <button @click="closeBatch()" data-dismiss="modal" class="btn btn-flat so-btn">Yes</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enter Batch ID -->
        <center v-if="(!this.batch_num )">
            <div class="alert alert-info alert-dismissible alert-saved" style="width:30%" v-if="saved">
                <button type="button" class="close alert-close-btn" data-dismiss="alert" aria-hidden="true">×</button>
                <h4><i class="icon fa fa-check"></i>Saved!</h4>
            </div>
            <div class="box box-solid box-primary ship-pack-input-box"  v-if="!saving">
                <div class="box-header ship-pack-box-header">
                    <h3 class="box-title">Kit Return Sync</h3>
                </div>
                <div class="box-body">
                    <div class="form-group" v-if="(this.createnew)">
                        <input type="text" autocomplete="off" class="form-control" placeholder="Batch #" id="batch_num_input" rel="Batchnum" v-model="batch_num_from_input"  v-on:keyup="createBatchByEnter" >
                    </div>
                    <div class="form-group"  v-if="(this.createnew)">
                        <label>Select SKU:</label>
                        <select class='form-control' v-model='sku_select' @change="checkExpiration()" >
                            <option v-for='data in skus' :value='data.id'>{{ data.sku }}</option>
                        </select>
                    </div>
                    <div class="form-group" v-if="(this.requires_expiration_date && this.createnew)">
                        <label>Expiration Date:</label>
                       <center><Datepicker :wrapper-class="col-md-12" :value="formatDate" :required="true" :bootstrap-styling="true"  name="expirationdate" id="expirationdate" v-model="expirationdate"  ></Datepicker></center>
                    </div>
                    <div class="form-group settings-btn-container"  v-if="(this.createnew)">
                        <button type="button" class="btn btn-flat so-btn" data-dismiss="modal" id="batch_num_btn" @click="createBatch()">Start Batch</button>
                    </div>
                    <div class="form-group" v-if="(this.continue)">
                        <label>Select Batch #:</label>
                        <select class='form-control' v-model='batch_select'  >
                            <option v-for='data in batches' :value='data.id'>{{ data.batch_number }}</option>
                        </select>
                    </div>
                    <div class="form-group settings-btn-container" v-if="(this.continue)">
                        <button type="button" class="btn btn-flat so-btn" data-dismiss="modal" id="batch_num_btn" @click="continueBatch()">Continue Batch</button>
                    </div>
                    <a v-on:click="showCreateNew()" href="#" v-if="(this.continue)">Create New Batch</a>
                    <a v-on:click="showContinue()" href="#" v-if="(this.createnew)">Continue Existing Batch</a>
                </div>
            </div>
        </center>
        <div class="order-body" v-else>
            <div class="box inventory-box" >
                <div class="box-body  text-center">
                    <div class="ship-pack-content">
                        <div class="text-left" style="font-weight: bold;">
                            Batch #: {{this.batch_num}} <br>
                            Box #: <a href="#" @click="scanBoxNum()">{{this.box_number}}</a> - {{this.box_count}}/{{this.box_limit}}
                        </div>
                        <div v-if="validate_error" style="color:red;font-weight:bold;">Master Kit already exists!</div>
                        <div v-if="validate_error2" style="color:red;font-weight:bold;">Master Kit doesnt match!</div>
                        <div v-if="box_limit_error" style="color:red;font-weight:bold;">Box is full! Click next to continue.</div>
                        <div v-if="validate_error_returntracking" style="color:red;font-weight:bold;">Return Tracking Number already in used!</div>
                        <div v-if="validate_error_returntracking_required" style="color:red;font-weight:bold;">Return Tracking Number is required!</div>
                        <div v-if="validate_error_returntracking2_required" style="color:red;font-weight:bold;">Return Tracking Number is required!</div>
                        <div v-if="validate_error_returntracking2" style="color:red;font-weight:bold;">Return Tracking Number did not match!</div>
                        
                        
                        <center  v-if="this.editBoxNum">
                            <div v-if="this.editBoxError" style="color:red">Box ID already full!</div>
                            <div style="margin: 20px;">
                                <input type="text" autocomplete="off" class="form-control" placeholder="Box #" id="edit_box_number" ref="edit_box_number" v-model="edit_box_number" @keyup.enter="changeboxNum()"   />
                            </div>
                        </center>
                        <center  v-else>
                            <input type="text" autocomplete="off" class="form-control" placeholder="Kit #" id="master_kit_input" ref="mkit1" v-model="master_kit_from_input" @change="masterkitValidate()"  />
                            <div v-if="(!this.multibarcode)">
                                <div  style="margin-top: 20px;" >
                                    <input type="text" autocomplete="off" class="form-control" placeholder="Tube #" id="master_kit_input2" ref="mkit2" v-model="master_kit_from_input2" @change="masterkitValidate2()"  />
                                </div>
                                <!--<div  style="margin-top: 20px;" >
                                    <input type="text" autocomplete="off" class="form-control" placeholder="Card #" id="master_kit_input3" ref="mkit3" v-model="master_kit_from_input3" @change="masterkitValidate3()"   />
                                </div>!-->
                            </div>
                            <div style="margin-top: 20px;"  v-if="(this.multibarcode)">
                                <input type="text" autocomplete="off" class="form-control" placeholder="Card #" id="master_kit_input4" ref="mkit4" v-model="master_kit_from_input4" @change="masterkitValidate4()"  />
                            </div>
                            <div style="margin-top: 20px;"  v-if="(!this.multibarcode)">
                                <input type="text" autocomplete="off" class="form-control single_barcode" @change="returnTrackingValidate(1)" :ref="'r1'" placeholder="Return Tracking Number" rel="batcheskit_items" id="return_tracking_number1_input" v-model="return_tracking_number_from_input[1]"   />
                            </div>
                            <div style="margin-top: 20px;"  v-if="(!this.multibarcode)">
                                <input type="text" autocomplete="off" class="form-control single_barcode" @change="returnTrackingValidate2()" ref="r2" placeholder="Return Tracking Number" rel="batcheskit_items" id="return_tracking_number2_input" v-model="return_tracking_number2_input"   />
                            </div> 
                            <div v-else >
                                <table class="SubkitBarcodes-table " >
                                    <tr>
                                        <th colspan=3>Subkit Barcodes
                                            <div v-if="validate_error_subkit" style="color:red"><br>SubKit # already exists!</div>
                                            <div v-if="validate_error_subkit2" style="color:red"><br>SubKit doesn't match!</div>
                                        </th>
                                    </tr>
                                    <tr  v-for="(n,index) in multi_barcode_count" :key="index" >
                                        <td class="detail-text">
                                            <input type="text" autocomplete="off" class="form-control" :ref="'s'+n" :id="'s'+n" @change="subKitValidate(n)" placeholder="Subkit Number" rel="batcheskit_items"  v-model="subkit_number_from_input[n]"   /> 
                                        </td>
                                        <td class="detail-text">
                                            <input type="text" autocomplete="off" class="form-control" :ref="'s2'+n" :id="'s2'+n" @change="subKitValidate2(n)" placeholder="Subkit Number" rel="batcheskit_items2"  v-model="subkit_number_from_input2[n]"   /> 
                                        </td>
                                        <td class="detail-text">
                                            <input type="text" autocomplete="off" class="form-control" :ref="'r'+n" @change="returnTrackingValidate(n)"  placeholder="Return Tracking Number" rel="batcheskit_items"  v-model="return_tracking_number_from_input[n]"   />
                                        </td>
                                    </tr>
                                </table>
                                <!--<table class="SubkitBarcodes-table "  v-for="(n,index) in multi_barcode_count" :key="index" >
                                   
                                    <tr>
                                        <td class="detail-text">
                                            <input type="text" autocomplete="off" class="form-control" ref="s1" @change="subKitValidate(1)" placeholder="Subkit Number" rel="batcheskit_items" id="subkit_number1_input" v-model="subkit_number1_from_input"   />
                                        </td>
                                        <td class="detail-text">
                                            <input type="text" autocomplete="off" class="form-control" ref="r1" @change="returnTrackingValidate(1)"  placeholder="Return Tracking Number" rel="batcheskit_items" id="return_tracking_number1_input" v-model="return_tracking_number1_from_input"   />
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="detail-text">
                                            <input type="text" autocomplete="off" class="form-control" ref="s2" @change="subKitValidate(2)" placeholder="Subkit Number" rel="batcheskit_items" id="subkit_number2_input" v-model="subkit_number2_from_input"   />
                                        </td>
                                        <td class="detail-text">
                                            <input type="text" autocomplete="off" class="form-control" ref="r2" @change="returnTrackingValidate(2)"  placeholder="Return Tracking Number" rel="batcheskit_items" id="return_tracking_number2_input" v-model="return_tracking_number2_from_input"   />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="detail-text">
                                            <input type="text" autocomplete="off" class="form-control" ref="s3" @change="subKitValidate(3)" placeholder="Subkit Number" rel="batcheskit_items" id="subkit_number3_input" v-model="subkit_number3_from_input"   />
                                        </td>
                                        <td class="detail-text">
                                            <input type="text" autocomplete="off" class="form-control" ref="r3" @change="returnTrackingValidate(3)" placeholder="Return Tracking Number" rel="batcheskit_items" id="return_tracking_number3_input" v-model="return_tracking_number3_from_input"   />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="detail-text">
                                        <input type="text" autocomplete="off" class="form-control" ref="s4" @change="subKitValidate(4)" placeholder="Subkit Number" rel="batcheskit_items" id="subkit_number4_input" v-model="subkit_number4_from_input"   />
                                        </td>
                                        <td class="detail-text">
                                            <input type="text" autocomplete="off" class="form-control" ref="r4" @change="returnTrackingValidate(4)" placeholder="Return Tracking Number" rel="batcheskit_items" id="return_tracking_number4_input" v-model="return_tracking_number4_from_input"   />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="detail-text">
                                            <input type="text" autocomplete="off" class="form-control" ref="s5" @change="subKitValidate(5)" placeholder="Subkit Number" rel="batcheskit_items" id="subkit_number5_input" v-model="subkit_number5_from_input"   />
                                        </td>
                                        <td class="detail-text">
                                            <input type="text" autocomplete="off" class="form-control" ref="r5" @change="returnTrackingValidate(5)" placeholder="Return Tracking Number" rel="batcheskit_items" id="return_tracking_number15_input" v-model="return_tracking_number5_from_input"   />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="detail-text">
                                            <input type="text" autocomplete="off" class="form-control" ref="s6" @change="subKitValidate(6)" placeholder="Subkit Number" rel="batcheskit_items" id="subkit_number6_input" v-model="subkit_number6_from_input"   />
                                        </td>
                                        <td class="detail-text">
                                            <input type="text" autocomplete="off" class="form-control" ref="r6" @change="returnTrackingValidate(6)" placeholder="Return Tracking Number" rel="batcheskit_items" id="return_tracking_number6_input" v-model="return_tracking_number6_from_input"   />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="detail-text">
                                            <input type="text" autocomplete="off" class="form-control" ref="s7" @change="subKitValidate(7)" placeholder="Subkit Number" rel="batcheskit_items" id="subkit_number7_input" v-model="subkit_number7_from_input"   />
                                        </td>
                                        <td class="detail-text">
                                            <input type="text" autocomplete="off" class="form-control" ref="r7" @change="returnTrackingValidate(7)" placeholder="Return Tracking Number" rel="batcheskit_items" id="return_tracking_number7_input" v-model="return_tracking_number7_from_input"   />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="detail-text">
                                            <input type="text" autocomplete="off" class="form-control" ref="s8" @change="subKitValidate(8)" placeholder="Subkit Number" rel="batcheskit_items" id="subkit_number8_input" v-model="subkit_number8_from_input"   />
                                        </td>
                                        <td class="detail-text">
                                            <input type="text" autocomplete="off" class="form-control" ref="r8" @keyup.enter="$refs.r8.focus()" @change="returnTrackingValidate(8)" placeholder="Return Tracking Number" rel="batcheskit_items" id="return_tracking_number8_input" v-model="return_tracking_number8_from_input"   />
                                        </td>
                                    </tr>

                                </table>!-->
                            </div>
                            <div class="form-group settings-btn-container">
                                <button type="button" class="btn btn-flat so-btn" data-dismiss="modal" id="next_kit_btn" @click="nextKit()">Next Kit</button>
                            </div>
                        </center>

                        <!--<div class="text-right" >
                            <button type="button" class="btn btn-flat so-btn" data-toggle="modal" data-target="#closeBatch"  id="close_batch_btn">Close Batch</button>
                        </div>
                        !-->
                    </div>
                </div>
            </div>

        </div>

        <!-- SEARCH TRANSACTION LOADER-->
        <center v-if="saving">
            <div class="loader"></div>
            Saving
        </center>

    </section>
</template>
<style>
input#master_kit_input,.single_barcode,#edit_box_number, #master_kit_input2, #master_kit_input3, #master_kit_input4{
    height: 50px;
    width: 50%;
    font-size: 20px;
}

.SubkitBarcodes-table{
    border: 1px solid;
    width: 50%;
    margin-top: 20px;
}

.SubkitBarcodes-table th{
   border: 1px solid;
       padding: 20px;
    text-align: center;
}

.SubkitBarcodes-table td{
    padding: 10px 20px;
}

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
#next_kit_btn{
    height: 50px;
    width: 200px;
    font-size: 20px;
    border-radius: 8px;
    margin-top: 20px;
}
</style>

<script>

import Datepicker from 'vuejs-datepicker';
export default {
    props: ['package_size'],

    data() {
		return {
            validate_error:false,
            validate_error2:false,
            validate_error_subkit:false,
            validate_error_subkit2:false,
            validate_error_returntracking:false,
            validate_error_returntracking_required:false,
            validate_error_returntracking2:false,
            validate_error_returntracking2_required:false,
            createnew:null,
            continue:1,
            batch_select:null,
            multibarcode:null,
            saved:false,
            saving:false,
            scan_err:'NOT FOUND',
            carrier_filter:'',
            selected_carrier:-1,
            selected_carrier_service:-1,
            batch_num:null,
            batchnum_select:null,
            batch_id:null,
            skus: [],
            batches: [],
            sku_select: null,
            master_kit:null,
            customer_id:null,
            batchnum_selected:null,
            batch_num_from_input:null,
            master_kit_from_input:null,
            master_kit_from_input2:null,
            master_kit_from_input3:null,
            master_kit_from_input4:null,
            show_search_loader:false,
            show_not_found_err:false,
            show_scan_err:false,
            scan_value:null,
            scan_serial:null,
            return_tracking_number2_input:null,
            subkit_number1_from_input:null,
            subkit_number2_from_input:null,
            subkit_number3_from_input:null,
            subkit_number4_from_input:null,
            subkit_number5_from_input:null,
            subkit_number6_from_input:null,
            subkit_number7_from_input:null,
            subkit_number8_from_input:null,
            return_tracking_number1_from_input:null,
            return_tracking_number2_from_input:null,
            return_tracking_number3_from_input:null,
            return_tracking_number4_from_input:null,
            return_tracking_number5_from_input:null,
            return_tracking_number6_from_input:null,
            return_tracking_number7_from_input:null,
            return_tracking_number8_from_input:null,
            subkit_number_from_input:[],
            subkit_number_from_input2:[],
            
            return_tracking_number_from_input:[],
            subkit_numbers:null,
            return_tracking_numbers:null,
            batch_num_text:null,
            sku_id:null,
            batch_status:null,
            subkit_id:null,
            temp_num:null,
            requires_expiration_date:null,
            expirationdate:null,
            box_number:null,
            editBoxNum:true,
            editBoxError:false,
            edit_box_number:null,
            box_count:0,
            box_limit:0,
            box_limit_error:false,
            multi_barcode_count:0,
            valsub:1,    
                       
		}
    },
    computed: {
        canGetShippingRates() {
            return this.get_shipping_rates.can_submit === true;
        },
        ifSerialRequired() {
            return this.shipping_client.required;
        },
    },

    components: {
        Datepicker
    },

    mounted() {
        this.selected_package = -1;
        // this.checkPrinterFromCookie();
        // this.getCarriers();
    },

    methods: {
        counter(i) {
            return new Array(i);
        },
        formatDate(date){
                return moment(date).format('YYYY-MM-D');
            },
        getSkus: function(){
              axios.get('/kit-return-sync/getskus')
              .then(function (response) {
                 this.skus = response.data;
              }.bind(this));

        },

        async checkmultiplebarcode(){
            console.log("pass here" + this.sku_select)
            if(this.sku_select != null){
                var ref = this;
                 await axios.post('/kit-return-sync/checkbarcode', {
                    'sku_id': this.sku_select
                    }).then(function (response) {
                     console.log('zz:',response.data[0].multi_barcode);
                    ref.multibarcode = response.data[0].multi_barcode;
                    if(ref.multibarcode==0){
                        ref.multibarcode = null;
                    }
                    ref.editBoxNum=true;
                    console.log("multibarcode: "+ref.multibarcode);

                    }).catch(function (error) {
                        ref.output = error;
                });
                
                if(this.editBoxNum==true){
                    this.$refs.edit_box_number.focus();
                }
            }
        },
        getOpenBatches: function(){
              axios.get('/kit-return-sync/openbatches')
              .then(function (response) {
                 this.batches = response.data;
              }.bind(this));

        },
        setFocus(){
            if(this.editBoxNum=false){
                this.$refs.mkit1.focus();
            }
        },
        showContinue(){
            this.continue="1";
            this.createnew=null;
        },
        showCreateNew(){
            this.continue=null;
            this.createnew="1";
        },
        setFocusBatchnum(){
            this.$refs.Batchnum.focus();
        },
        printDate(date_string){
          var timestamp = Date.parse(date_string);

          if (isNaN(timestamp) == false) {
            return '-';
          }
        },
        checkExpiration(){
            console.log("pass here" + this.sku_select)
            if(this.sku_select != null){
                var ref = this;
                axios.post('/kit-return-sync/checkexpirtaion', {
                    'sku_id': this.sku_select
                    }).then(function (response) {
                     console.log('zz:',response.data[0].requires_expiration_date);
                    ref.requires_expiration_date = response.data[0].requires_expiration_date;
                    if(ref.requires_expiration_date==0){
                        ref.requires_expiration_date = null;
                    }
                    console.log("requires_expiration_date: "+ref.requires_expiration_date);

                    }).catch(function (error) {
                        ref.output = error;
                });
            }
        },
        createBatch(){
            if(this.batch_num_from_input != null && this.sku_select != null ) {
                if(this.requires_expiration_date!=null){
                    if(this.expirationdate==null){
                        alert("Expiration Date is required");
                        return false;
                    }
                }
                this.batch_num = this.batch_num_from_input;
                //this.box_number = this.batch_num_from_input+"-1";
                var ref = this;
                 axios.post('/kit-return-sync/add', {
                    'batch_number': this.batch_num,
                    'sku': this.sku_select,
                    'expirationdate':this.expirationdate,
                    }).then(function (response) {
                    // console.log('searchTransactionId:',response)
                        var data = response.data.last_insert_id;
                        ref.batch_id  = response.data.last_insert_id;
                        console.log("sku: "+ref.sku_select);
                        ref.box_limit = response.data.box_limit;
                        ref.multi_barcode_count = Number(response.data.multi_barcode_count);
                        console.log("multi_barcode_count="+ref.multi_barcode_count);
                        ref.editBoxNum=true;
                        ref.checkmultiplebarcode();
                        
                        //ref.setFocus();
                        this.expirationdate = null;
                    }).catch(function (error) {
                        ref.output = error;
                });
                if(this.editBoxNum==true){
                    this.$refs.edit_box_number.focus();
                }
            }
            else{
                alert("Please select SKU.");
                return false;
            }
        },
        async continueBatch(){
            if(this.batch_select != null) {
                this.batch_num="temp";
                this.batch_id = this.batch_select;
                var ref = this;
                await axios.get('/kit-boxing/batchnumber',{
                    params: {
                        batch_id: this.batch_id
                    }
                }).then(function (response) {
                    ref.batch_num = response.data[0].batch_number;
                    ref.box_limit = response.data[0].box_limit;
                    ref.sku_select = response.data[0].sku;
                    ref.multi_barcode_count = Number(response.data[0].multi_barcode_count);
                    console.log("multi_barcode_count="+ref.multi_barcode_count);
                    /*
                    removed since they need to change box number everytime they continue batch
                    
                    ref.box_number = response.data[0].box_id;
                    ref.box_limit = response.data[0].box_limit;
                    ref.sku_select = response.data[0].sku;
                    */
                    ref.checkmultiplebarcode();
                    //ref.checkBoxNum(); remove since we have to edit always box num
                    ref.editBoxNum=true;
                    console.log("sku: "+ref.sku_select);
                }.bind(this));

                if(this.editBoxNum==true){
                    this.$refs.edit_box_number.focus();
                }
            }
                
        },
        async checkBoxNum(){
            var ref = this;
             await axios.post('/kit-return-sync/getboxnum', {
                'batch_id': this.batch_id,
                }).then(function (response) {
                    console.log('exist:',response.data);
                    if(response.data.box_id=="new" || response.data.box_id=="" || response.data.box_id==null || response.data.box_count == response.data.box_limit ){
                        ref.editBoxNum=true;
                        ref.box_count=0;
                        ref.edit_box_number=null;
                        ref.multi_barcode_count = Number(response.data.multi_barcode_count);
                        console.log("multi_barcode_count="+ref.multi_barcode_count);
                        console.log("dri");
                    }
                    else{
                        ref.box_number = response.data.box_id;
                        ref.editBoxNum=false;
                        ref.box_count = response.data.box_count;
                        ref.multi_barcode_count = Number(response.data.multi_barcode_count);
                        console.log("multi_barcode_count="+ref.multi_barcode_count);
                        console.log("dri 2");
                        //ref.setFocus();
                        //ref.$refs.mkit1.focus();
                    }
                }.bind(this));
                if(this.editBoxNum==false){
                    this.$refs.mkit1.focus();
                }
                else{
                    this.$refs.edit_box_number.focus();
                }
                this.validate_error=false;
                this.validate_error2=false;
                this.box_limit_error=false;
                

        },
        masterkitValidate(){
            //console.log(this.master_kit_from_input);
            var val = this;
            console.log('multi='+this.multibarcode);
            axios.post('/kit-return-sync/validate', {
                    'master_kit_id': this.master_kit_from_input,
                    'batch_id': this.batch_id,
                    'sku_id': this.sku_select,
                    }).then(function (response) {
                        console.log('exist:'+response.data);
                        //if exist
                        if(response.data =="valid"){
                            val.validate_error=false;
                            /*if(val.multibarcode !=null){
                                val.$refs.s1.focus();
                            }
                            else{
                                val.$refs.r1.focus();
                            } */
                            if(val.multibarcode !=null || val.multibarcode ==1){
                                val.$refs.mkit4.focus();
                            }
                            else{
                                val.$refs.mkit2.focus();
                            }

                        }
                        else{
                            val.master_kit_from_input ="";
                            val.validate_error=true;
                            val.setFocus();
                            return false;
                        }
                    });
        },
        
        masterkitValidate2(){
            if(this.master_kit_from_input == this.master_kit_from_input2){
                this.$refs.r1.focus();
                console.log("masterkitsame 2");

                this.validate_error2=false;
            }
            else{
                this.master_kit_from_input2 ="";
                this.validate_error2=true;
                this.$refs.mkit2.focus();
                console.log("masterkit invalid 2");
                return false;
            }
        },
        /*
        masterkitValidate3(){
            if(this.master_kit_from_input ==this.master_kit_from_input3){
                this.$refs.r1.focus();
                console.log("masterkitsame 3");
                this.validate_error2=false;
            }
            else{
                this.master_kit_from_input3 ="";
                this.validate_error2=true;
                this.$refs.mkit3.focus();
                console.log("masterkit invalid 3");
            }
        },*/
        async masterkitValidate4(){
            console.log(this.$refs.s1);
            if(this.master_kit_from_input ==this.master_kit_from_input4){
                this.$refs.s1[0].focus();
                this.validate_error2=false;
                console.log("masterkitsame 4");
            }
            else{
                this.master_kit_from_input4 ="";
                this.validate_error2=true;
                this.$refs.mkit4.focus();
                console.log("masterkit invalid 4");
            }
        },

        scanBoxNum(){
            this.editBoxNum=true;
            this.edit_box_number=null;
        },
        async changeboxNum(){
            var ref = this;
            await axios.post('/kit-return-sync/validatboxnum', {
                'batch_id': this.batch_id,
                'edit_box_number': this.edit_box_number,
                }).then(function (response) {
                    console.log('exist:',response.data);
                    if(response.data.box_id=="full" || response.data.box_id=="" || response.data.box_id==null || response.data.box_count == response.data.box_limit ){
                        ref.editBoxNum=true;
                        ref.editBoxError=true;
                        ref.box_count=0;
                        ref.edit_box_number=null;
                        console.log("full here");
                        ref.$refs.edit_box_number.focus();
                        return false;
                    }
                    else{
                        //ref.box_number = ref.data.box_id;
                        ref.box_number = ref.edit_box_number;
                        ref.editBoxNum=false;
                        ref.editBoxError=false;
                        ref.box_count = response.data.box_count;
                        console.log("dri 2");
                        ref.setFocus();
                    }
                });
                
                if(this.editBoxNum==false){
                    this.$refs.mkit1.focus();
                }
        },
        async subKitValidate(num){
            console.log(num);
            this.subkit_id=this.subkit_number_from_input[num];
            /*switch (num) {
                case 1:
                    this.subkit_id = this.subkit_number1_from_input;
                    break;
                case 2:
                    this.subkit_id = this.subkit_number2_from_input;
                    break;
                case 3:
                    this.subkit_id = this.subkit_number3_from_input;
                    break;
                case 4:
                    this.subkit_id = this.subkit_number4_from_input;
                    break;
                case 5:
                    this.subkit_id = this.subkit_number5_from_input;
                    break;
                case 6:
                    this.subkit_id = this.subkit_number6_from_input;
                    break;
                case 7:
                    this.subkit_id = this.subkit_number7_from_input;
                    break;
                case 8:
                    this.subkit_id = this.subkit_number8_from_input;
            }*/
            var addthis= 0;
            var totals= this.box_count;
            this.valsub= num;
            var next= num+1;
            var test='s'+num;
            var test2='s'+next;
            console.log(this.subkit_id);

             if(this.subkit_id=="" || this.subkit_id == null){
                console.log("nullled");
                val.subkit_number_from_input[num] = "";
                val.$refs['s' + num][0].focus();
                 
                /*switch (num) {
                    case 1:
                        this.$refs.s+num.focus();
                        break;
                    case 2:
                        this.$refs.s2.focus();
                        break;
                    case 3:
                        this.$refs.s3.focus();
                        break;
                    case 4:
                        this.$refs.s4.focus();
                        break;
                    case 5:
                        this.$refs.s5.focus();
                        break;
                    case 6:
                        this.$refs.s6.focus();
                        break;
                    case 7:
                        this.$refs.s7.focus();
                        break;
                    case 8:
                        this.$refs.s8.focus();
                }*/
                return false;
             }
            /*if(this.subkit_id!="" && this.box_limit <= this.box_count){
                console.log("exceeeded");
                this.box_limit_error=true;
                switch (num) {
                    case 1:
                        this.subkit_number1_from_input = "";
                        this.$refs.s1.focus();
                        break;
                    case 2:
                        this.subkit_number2_from_input = "";
                        this.$refs.s2.focus();
                        break;
                    case 3:
                        this.subkit_number3_from_input = "";
                        this.$refs.s3.focus();
                        break;
                    case 4:
                        this.subkit_number4_from_input = "";
                        this.$refs.s4.focus();
                        break;
                    case 5:
                        this.subkit_number5_from_input = "";
                        this.$refs.s5.focus();
                        break;
                    case 6:
                        this.subkit_number6_from_input = "";
                        this.$refs.s6.focus();
                        break;
                    case 7:
                        this.subkit_number7_from_input = "";
                        this.$refs.s7.focus();
                        break;
                    case 8:
                        this.subkit_number8_from_input = "";
                        this.$refs.s8.focus();
                }
                return false;
            }*/

            var val = this;
            axios.post('/kit-return-sync/validatesubkit', {
                    'subkit_id': this.subkit_id,
                    //'batch_id': this.batch_id,
                    'sku_id': this.sku_select,
                    }).then(function (response) {
                        console.log('exist:',response.data);
                        //if exist
                        if(response.data =="valid"){
                            val.validate_error_subkit=false;
                            /*switch (num) {
                                    case 1:
                                        val.$refs.r1.focus();
                                        break;
                                    case 2:
                                        val.$refs.r2.focus();
                                        break;
                                    case 3:
                                        val.$refs.r3.focus();
                                        break;
                                    case 4:
                                        val.$refs.r4.focus();
                                        break;
                                    case 5:
                                        val.$refs.r5.focus();
                                        break;
                                    case 6:
                                        val.$refs.r6.focus();
                                        break;
                                    case 7:
                                        val.$refs.r7.focus();
                                        break;
                                    case 8:
                                       val.$refs.r8.focus();
                                }*/
                                //val.$refs['r' + num][0].focus(); removed since we added a new line
                                val.$refs['s2' + num][0].focus();
                                //val.box_count++;

                        }
                        else{
                            val.validate_error_subkit=true;
                                val.$refs['s' + num][0].focus();
                                val.subkit_number_from_input[num] = "";
                            /*
                            switch (num) {
                                    case 1:
                                        val.subkit_number1_from_input = "";
                                        val.$refs.s1.focus();
                                        break;
                                    case 2:
                                        val.subkit_number2_from_input = "";
                                        val.$refs.s2.focus();
                                        break;
                                    case 3:
                                        val.subkit_number3_from_input = "";
                                        val.$refs.s3.focus();
                                        break;
                                    case 4:
                                        val.subkit_number4_from_input = "";
                                        val.$refs.s4.focus();
                                        break;
                                    case 5:
                                        val.subkit_number5_from_input = "";
                                        val.$refs.s5.focus();
                                        break;
                                    case 6:
                                        val.subkit_number6_from_input = "";
                                        val.$refs.s6.focus();
                                        break;
                                    case 7:
                                        val.subkit_number7_from_input = "";
                                        val.$refs.s7.focus();
                                        break;
                                    case 8:
                                        val.subkit_number8_from_input = "";
                                        val.$refs.s8.focus();
                                }
                            */
                        }
                    });

        },
        subKitValidate2(num){   
            if(this.subkit_number_from_input[num] != this.subkit_number_from_input2[num]){
                console.log("nulllezzd = "+num+ "==" + this.subkit_number_from_input[num]+ " ---"+this.subkit_number_from_input2[num]);
                this.subkit_number_from_input2[num] = null;          
                this.validate_error_subkit2=true; 
                this.$refs['s2' + num][0].focus();   
                return false;
            }
            else{              
                this.validate_error_subkit2=false;
                this.$refs['r' + num][0].focus();
            }
        },
        returnTrackingValidate(num){
            //console.log(num);
            /*switch (num) {
                case 1:
                    this.return_tracking = this.return_tracking_number1_from_input;
                    break;
                case 2:
                    this.return_tracking = this.return_tracking_number2_from_input;
                    break;
                case 3:
                    this.return_tracking = this.return_tracking_number3_from_input;
                    break;
                case 4:
                    this.return_tracking = this.return_tracking_number4_from_input;
                    break;
                case 5:
                    this.return_tracking = this.return_tracking_number5_from_input;
                    break;
                case 6:
                    this.return_tracking = this.return_tracking_number6_from_input;
                    break;
                case 7:
                    this.return_tracking = this.return_tracking_number7_from_input;
                    break;
                case 8:
                    this.return_tracking = this.return_tracking_number8_from_input;
            }*/
            var val = this;
            
            this.return_tracking = this.return_tracking_number_from_input[num];
            console.log("return_tracking : "+this.subkit_id);
            var next=num+1;
            axios.post('/kit-return-sync/validatereturntracking', {
                    'return_tracking': this.return_tracking,
                    'batch_id': this.batch_id,
                    //'sku_id': this.sku_select,return_tracking batch_id
                    }).then(function (response) {
                        console.log('exist:',response.data);
                        //if exist
                        if(response.data =="valid"){
                            val.validate_error_returntracking=false;
                            /*switch (num) {
                                    case 1:
                                        if(val.multibarcode==1){
                                            val.$refs.s2.focus();
                                        }
                                        else{
                                            val.$refs.r2.focus();
                                        }
                                        break;
                                    case 2:
                                        val.$refs.s3.focus();
                                        break;
                                    case 3:
                                        val.$refs.s4.focus();
                                        break;
                                    case 4:
                                        val.$refs.s5.focus();
                                        break;
                                    case 5:
                                        val.$refs.s6.focus();
                                        break;
                                    case 6:
                                        val.$refs.s7.focus();
                                        break;
                                    case 7:
                                        val.$refs.s8.focus();
                                        break;
                                    case 8:
                                       val.$refs.r8.focus();
                                }*/
                                if( val.multibarcode==1){
                                    if(num == val.multi_barcode_count){
                                        val.nextKit();
                                    }
                                    else{
                                        val.$refs['s' + next][0].focus();
                                    }
                                }
                                else{
                                    val.$refs.r2.focus();
                                }
                                
                        }
                        else{
                            val.validate_error_returntracking=true;
                            val.return_tracking_number_from_input[num] = "";
                            val.$refs['r' + num][0].focus();
                            /*switch (num) {
                                    case 1:
                                        val.return_tracking_number1_from_input = "";
                                        val.$refs.r1.focus();
                                        break;
                                    case 2:
                                        val.return_tracking_number2_from_input = "";
                                        val.$refs.r2.focus();
                                        break;
                                    case 3:
                                        val.return_tracking_number3_from_input = "";
                                        val.$refs.r3.focus();
                                        break;
                                    case 4:
                                        val.return_tracking_number4_from_input = "";
                                        val.$refs.r4.focus();
                                        break;
                                    case 5:
                                        val.return_tracking_number5_from_input = "";
                                        val.$refs.r5.focus();
                                        break;
                                    case 6:
                                        val.return_tracking_number6_from_input = "";
                                        val.$refs.r6.focus();
                                        break;
                                    case 7:
                                        val.return_tracking_number7_from_input = "";
                                        val.$refs.r7.focus();
                                        break;
                                    case 8:
                                        val.return_tracking_number8_from_input = "";
                                        val.$refs.r8.focus();
                                }*/

                        }
                    });

        },
        
        returnTrackingValidate2(){
            if(this.return_tracking_number_from_input[1] == this.return_tracking_number2_input){
                this.$refs.r2.focus();
                console.log("masterkitsame 3");
                this.validate_error_returntracking2=false;
                this.nextKit();
            }
            else{
                this.return_tracking_number2_input ="";
                this.validate_error_returntracking2=true;
                this.$refs.r2.focus();
                console.log("masterkit invalid 3");
            }
        },
        async nextKit(e){
            this.validate_error_returntracking=false;
            var inval=0;
            if(!this.return_tracking_number_from_input[1] && this.multibarcode==1){
                this.validate_error_returntracking_required=true; 
                this.return_tracking_number_from_input[1] = "";
                this.$refs.r1.focus();
                return false;
            }
            else if(!this.return_tracking_number2_input && this.multibarcode!=1){
                this.validate_error_returntracking2_required=true; 
                this.return_tracking_number2_input = "";
                this.$refs.r2.focus();
                return false;
            }
            else{
                /*//console.log("luh");
                if(this.multibarcode!=1){
                var val = this;
                 await axios.post('/kit-return-sync/validatereturntracking', {
                    'return_tracking': this.return_tracking_number_from_input[1],
                    'batch_id': this.batch_id,
                    //'sku_id': this.sku_select,return_tracking batch_id
                    }).then(function (response) {
                        console.log('existed:',response.data);

                        //if exist
                        if(response.data =="valid"){
                            val.validate_error_returntracking=false;
                        }
                        else{
                            val.validate_error_returntracking=true;
                            val.return_tracking_number1_from_input = "";
                            val.$refs.r1.focus();
                            inval =1;
                            //console.log('invalid22 -> ', inval);
                        }
                    });}*/
                
            }
            if(inval==1 || this.validate_error_returntracking==true){
                //console.log("inbalid");
                return false;
            }
            console.log("bypassed");
            /*if(this.multi_barcode==1){
                foreach(this.subkit_number_from_input)
                if(this.subkit_number2_from_input!="" && !this.return_tracking_number2_from_input){
                    this.validate_error_returntracking_required=true;
                    this.return_tracking_number2_from_input = "";
                    this.$refs.r2.focus();
                    return false;
                }
                if(this.subkit_number3_from_input!="" && !this.return_tracking_number3_from_input){
                    this.validate_error_returntracking_required=true;
                    this.return_tracking_number3_from_input = "";
                    this.$refs.r3.focus();
                    return false;
                }
                if(this.subkit_number4_from_input!="" && !this.return_tracking_number4_from_input){
                    this.validate_error_returntracking_required=true;
                    this.return_tracking_number4_from_input = "";
                    this.$refs.r4.focus();
                    return false;
                }
                if(this.subkit_number5_from_input!="" && !this.return_tracking_number5_from_input){
                    this.validate_error_returntracking_required=true;
                    this.return_tracking_number5_from_input = "";
                    this.$refs.r5.focus();
                    return false;
                }
                if(this.subkit_number6_from_input!="" && !this.return_tracking_number6_from_input){
                    this.validate_error_returntracking_required=true;
                    this.return_tracking_number6_from_input = "";
                    this.$refs.r6.focus();
                    return false;
                }
                if(this.subkit_number7_from_input!="" && !this.return_tracking_number7_from_input){
                    this.validate_error_returntracking_required=true;
                    this.return_tracking_number7_from_input = "";
                    this.$refs.r7.focus();
                    return false;
                }
                if(this.subkit_number8_from_input!="" && !this.return_tracking_number8_from_input){
                    this.validate_error_returntracking_required=true;
                    this.return_tracking_number8_from_input = "";
                    this.$refs.r8.focus();
                    return false;
                }
            }*/
        
            if(this.master_kit_from_input != null) {

                this.master_kit  = this.master_kit_from_input;
                if(!this.multibarcode){
                    this.subkit_numbers=[this.master_kit];
                    //this.return_tracking_numbers=[this.return_tracking_number1_from_input];                    
                    this.return_tracking_numbers=[this.return_tracking_number_from_input[1]];
                    if( this.master_kit_from_input != this.master_kit_from_input2){ // &&  this.master_kit_from_input != this.master_kit_from_input3
                        return false;
                    }
                }
                else{
                    if( this.master_kit_from_input != this.master_kit_from_input4){
                        return false;
                    }
                    this.subkit_numbers=this.subkit_number_from_input;
                    this.return_tracking_numbers=this.return_tracking_number_from_input;
                    //this.subkit_numbers=[this.subkit_number1_from_input,this.subkit_number2_from_input,this.subkit_number3_from_input,this.subkit_number4_from_input,this.subkit_number5_from_input,this.subkit_number6_from_input,this.subkit_number7_from_input,this.subkit_number8_from_input];
                    //this.return_tracking_numbers=[this.return_tracking_number1_from_input,this.return_tracking_number2_from_input,this.return_tracking_number3_from_input,this.return_tracking_number4_from_input,this.return_tracking_number5_from_input,this.return_tracking_number6_from_input,this.return_tracking_number7_from_input,this.return_tracking_number8_from_input];
                }

                /*console.log(this.master_kit);
                console.log(this.subkit_numbers);
                console.log(this.return_tracking_numbers);
                console.log(this.batch_id);*/
                var ref = this;

                axios.post('/kit-return-sync/addkit', {
                    'master_kit' : this.master_kit,
                    'subkit_numbers' : this.subkit_numbers,
                    'return_tracking_numbers' : this.return_tracking_numbers,
                    'batch_id' : this.batch_id,
                    'box_number' : this.box_number,
                }).then(function (response) {
                    // console.log('searchTransactionId:',response)
                    var data = response.data.last_insert_id;
                    ref.box_number=response.data.box_id;
                    ref.checkBoxNum();

                }).catch(function (error) {
                    ref.output = error;
                });
                
                this.subkit_number_from_input =[];
                this.subkit_number_from_input2 =[];
                this.return_tracking_number_from_input = [];
                this.return_tracking_number2_input="";
                this.master_kit_from_input ="";
                this.master_kit_from_input2 = "";
                this.master_kit_from_input3 = "";
                this.master_kit_from_input4 = "";
                /*
                this.subkit_number1_from_input = "";
                this.subkit_number2_from_input = "";
                this.subkit_number3_from_input = "";
                this.subkit_number4_from_input = "";
                this.subkit_number5_from_input = "";
                this.subkit_number6_from_input = "";
                this.subkit_number7_from_input = "";
                this.subkit_number8_from_input = "";
                this.return_tracking_number1_from_input = "";
                this.return_tracking_number2_from_input = "";
                this.return_tracking_number3_from_input = "";
                this.return_tracking_number4_from_input = "";
                this.return_tracking_number5_from_input = "";
                this.return_tracking_number6_from_input = "";
                this.return_tracking_number7_from_input = "";
                this.return_tracking_number8_from_input = "";*/
                this.validate_error_returntracking = null;
                this.validate_error_returntracking_required = null;
                this.validate_error_returntracking2=null;
                this.$refs.mkit1.focus();
            }
            else{ this.setFocus(); return false;}
        },
        closeBatch() {
            this.nextKit();
            console.log(this.batch_id);
            axios.post('/kit-return-sync/closebatch', {
                    'batch_id' : this.batch_id
                }).then(function (response) {
                    // console.log('searchTransactionId:',response)
                    var data = response.data.last_insert_id;
                }).catch(function (error) {
                    ref.output = error;
                });
            this.batch_num  = "";
            this.batch_num_from_input = "";
            this.continue = "";
            this.batch_select = "";
            this.sku_select ="";
            this.getOpenBatches();

        },
        createBatchByEnter(e){
            if (e.keyCode === 13) {
                return this.createBatch();
            }
            return false;
        },

        notFoundErr(){
            this.show_search_loader = false;
            this.show_not_found_err = true;
            this.batch_num = null;
            this.batch_num_from_input = null;
        },
    },
    created: function(){
        this.getSkus();
        this.getOpenBatches();
    }

}
</script>
<style >
.vdp-datepicker .input-group{ width: 100% !important;}
</style>
