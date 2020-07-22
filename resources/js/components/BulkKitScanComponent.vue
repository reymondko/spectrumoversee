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
                    <h3 class="box-title">Bulk Kit Sync</h3>
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
                            Sku #: {{this.sku_name}} - {{this.count}}/{{this.bulk_count}}
                            <p v-if="this.masterkit">LP Case: {{this.masterkit}}</p>
                        </div>
                        <div v-if="validate_error" style="color:red;font-weight:bold;">Master Kit already exists!</div>
                        <div v-if="validate_error2" style="color:red;font-weight:bold;">Master Kit doesnt match!</div>
                        <div v-if="validate_error_subkit" style="color:red;font-weight:bold;">Subkit already exists!</div>
                        <div v-if="validate_error_subkit2" style="color:red;font-weight:bold;">Subkit doesnt match!</div>
                        
                        <center v-if="!this.masterkit">
                            <p style="font-size:20px;font-weight:bold;">Scan Case LP (Data Ninja Tag)</p>
                            <input type="text" autocomplete="off" class="form-control" placeholder="Scan Barcode" id="master_kit_input" ref="mkit1" v-model="master_kit_from_input" @change="masterkitValidate()"  />
                            
                            <div  style="margin-top: 20px;" >
                                <input type="text" autocomplete="off" class="form-control" placeholder="Scan Barcode Again" id="master_kit_input2" ref="mkit2" v-model="master_kit_from_input2" @change="masterkitValidate2()"  />
                            </div>
                            <div class="form-group settings-btn-container">
                                <button type="button" class="btn btn-flat so-btn" data-dismiss="modal" id="next_kit_btn" @click="nextKit()">Next Kit</button>
                            </div>
                        </center>

                        <center v-else>
                            <center v-if="this.count <= this.bulk_count">
                                <p style="font-size:20px;font-weight:bold;">Scan {{this.count}} of {{this.bulk_count}}</p>
                                <div  style="margin-top: 20px;" >
                                    <input type="text" autocomplete="off" class="form-control" ref="s1" id="s1" @change="subKitValidate()" placeholder="Subkit Number" rel="batcheskit_items"  v-model="subkit_number_from_input1"   /> 
                                </div>
                                <div  style="margin-top: 20px;" >
                                    <input type="text" autocomplete="off" class="form-control" ref="s2" id="s2" @change="subKitValidate2()" placeholder="Subkit Number" rel="batcheskit_items2"  v-model="subkit_number_from_input2"   /> 
                                </div>
                                <div class="form-group settings-btn-container">
                                    <button type="button" class="btn btn-flat so-btn" data-dismiss="modal" id="next_kit_btn" @click="nextKit()">Next Kit</button>
                                </div>

                            </center>
                            <center v-else>
                                <p style="font-size:20px;font-weight:bold;">Return Tracking Numbers</p>
                                <div  style="margin-top: 20px;"  v-for="(n,index) in 8" :key="index">
                                     <input type="text" autocomplete="off" class="form-control" :ref="'r'+index" :id="'r'+index"  @change="returnTrackingValidate(index)" placeholder="Return Tracking Number" rel="batcheskit_items"  v-model="return_tracking_number_from_input[index]"   />
                                </div>
                                <div class="form-group settings-btn-container">
                                    <button type="button" class="btn btn-flat so-btn" data-dismiss="modal" id="next_kit_btn" @click="updateTracking()">Close batch</button>
                                </div>
                            </center>
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
input#master_kit_input,.single_barcode,#edit_box_number, #master_kit_input2, #s1, #s2,#r0,#r1,#r2,#r3,#r4,#r5,#r6,#r7,#r8{
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
            masterkit:null,
            master_kit:null,
            customer_id:null,
            batchnum_selected:null,
            batch_num_from_input:null,
            master_kit_from_input:null,
            master_kit_from_input2:null,
            subkit_number_from_input1:null,
            subkit_number_from_input2:null,
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
            count:1,
            bulk_count:50, 
            sku_name:null,
                       
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
              axios.get('/bulk-kit-scan/getskus')
              .then(function (response) {
                 this.skus = response.data;
              }.bind(this));

        },
        getOpenBatches: function(){
              axios.get('/bulk-kit-scan/openbatches')
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
            if(this.sku_select != null){
                var ref = this;
                axios.post('/bulk-kit-scan/checkexpirtaion', {
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
                 axios.post('/bulk-kit-scan/add', {
                    'batch_number': this.batch_num,
                    'sku': this.sku_select,
                    'expirationdate':this.expirationdate,
                    }).then(function (response) {
                        // console.log('searchTransactionId:',response)
                        var data = response.data.last_insert_id;
                        ref.batch_id  = response.data.last_insert_id;
                        console.log("sku: "+ref.sku_select);
                        ref.bulk_count = Number(response.data.bulk_count);
                        ref.sku_name = response.data.sku_name;
                        console.log("bulk_count="+ref.bulk_count);
                        this.$refs.mkit1.focus();
                       
                        //ref.setFocus();
                        this.expirationdate = null;
                    }).catch(function (error) {
                        ref.output = error;
                });
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
                axios.get('/kit-boxing/batchnumber',{
                    params: {
                        batch_id: this.batch_id
                    }
                }).then(function (response) {
                    ref.batch_num = response.data[0].batch_number;
                    ref.box_limit = response.data[0].box_limit;
                    ref.sku_select = response.data[0].sku;
                    ref.sku_name = response.data[0].sku_name;
                    ref.bulk_count = response.data[0].bulk_count;
                    
                    //ref.multi_barcode_count = Number(response.data[0].multi_barcode_count);
                    //console.log("multi_barcode_count="+ref.multi_barcode_count);
                    
                    this.$refs.mkit1.focus();
                    
                    console.log("sku: "+ref.sku_select);
                }.bind(this));

                if(this.editBoxNum==true){
                    this.$refs.edit_box_number.focus();
                }
            }
                
        },
        masterkitValidate(){
            //console.log(this.master_kit_from_input);
            var val = this;
            console.log('multi='+this.multibarcode);
            axios.post('/bulk-kit-scan/validate', {
                    'master_kit_id': this.master_kit_from_input,
                    'batch_id': this.batch_id,
                    'sku_id': this.sku_select,
                    }).then(function (response) {
                        console.log('exist:'+response.data);
                        //if exist
                        if(response.data =="valid"){
                            val.validate_error=false;                        
                            val.$refs.mkit2.focus();
                        }
                        else{
                            val.master_kit_from_input ="";
                            val.master_kit_from_input2 ="";
                            val.validate_error=true;
                            val.setFocus();
                            return false;
                        }
                    });
        },
        masterkitValidate2(){
            if(this.master_kit_from_input == this.master_kit_from_input2){
                console.log("masterkitsame 2");
                this.validate_error2=false;
                this.nextKit();
            }
            else{
                this.master_kit_from_input2 ="";
                this.validate_error2=true;
                this.$refs.mkit2.focus();
                console.log("masterkit invalid 2");
                return false;
            }
        },

        scanBoxNum(){
            this.editBoxNum=true;
            this.edit_box_number=null;
        },
        subKitValidate(){
            this.subkit_id=this.subkit_number_from_input1;
           
            var addthis= 0;
            if(this.subkit_id=="" || this.subkit_id == null){
                console.log("nullled");
                this.subkit_number_from_input1 = "";
                this.$nextTick(() => this.$refs.s1.focus());
                return false;
            }

            var val = this;
             axios.post('/bulk-kit-scan/validatesubkit', {
                'subkit_id': this.subkit_id,
                'sku_id': this.sku_select,
                }).then(function (response) {
                    console.log('exist:',response.data);
                    //if exist
                    if(response.data =="valid"){
                        val.validate_error_subkit=false;
                        //val.$refs['r' + num][0].focus(); removed since we added a new line
                        val.$refs.s2.focus();
                        
                        //val.$nextTick(() => val.$refs.s2.focus());
                        //val.box_count++;

                    }
                    else{
                        val.validate_error_subkit=true;
                        val.$refs.s1.focus();
                        val.subkit_number_from_input1 = "";
                    }
                });

        },
        subKitValidate2(){   
            if(this.subkit_number_from_input1 != this.subkit_number_from_input2){                
                this.subkit_number_from_input2 = null;          
                this.validate_error_subkit2=true; 
                this.$refs.s2.focus();   
                return false;
            }
            else{              
                this.validate_error_subkit2=false;
                this.nextKit();
            }
        },
        returnTrackingValidate(num){
            var val = this;
            
            this.return_tracking = this.return_tracking_number_from_input[num];
            console.log("return_tracking : "+this.subkit_id);
            var next=num+1;
            axios.post('/bulk-kit-scan/validatereturntracking', {
                    'return_tracking': this.return_tracking,
                    'batch_id': this.batch_id,
                    //'sku_id': this.sku_select,return_tracking batch_id
                    }).then(function (response) {
                        console.log('exist:',response.data);
                        //if exist
                        if(response.data =="valid"){
                            val.validate_error_returntracking=false;
                                
                            if(num == 7){
                                val.updateTracking();
                            }
                            else{
                                val.$refs['r' + next][0].focus();
                            }
                                
                        }
                        else{
                            val.validate_error_returntracking=true;
                            val.return_tracking_number_from_input[num] = "";
                            val.$refs['r' + num][0].focus();

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
        async updateTracking(e){    
               var ref = this;
                axios.post('/bulk-kit-scan/updatereturntracking', {
                    'master_kit' : this.master_kit,
                    'subkit_numbers' : this.subkit_number_from_input1,
                    'return_tracking_numbers' : this.return_tracking_number_from_input,
                    'batch_id' : this.batch_id,
                    'bulk_count': this.bulk_count,
                }).then(function (response) {
                    // console.log('searchTransactionId:',response)
                    ref.closeBatch();

                }).catch(function (error){
                    ref.output = error;
                });
                this.return_tracking_number_from_input = [];
                this.subkit_number_from_input1="";
                this.subkit_number_from_input2="";
        },       
        
        nextKit(e){            
            if(!this.masterkit){
                //if blank create
                this.master_kit = this.master_kit_from_input;
                this.masterkit = this.master_kit_from_input;
                this.$nextTick(() => this.$refs.s1.focus())
                //this.$refs.s1.focus();
                this.master_kit = this.master_kit_from_input;
                this.masterkit = this.master_kit_from_input;
            }
            else{
                var ref = this;
                axios.post('/bulk-kit-scan/addkit', {
                    'master_kit' : this.master_kit,
                    'subkit_numbers' : this.subkit_number_from_input1,
                    'batch_id' : this.batch_id,
                }).then(function (response) {
                    // console.log('searchTransactionId:',response)
                    var data = response.data.last_insert_id;
                    //ref.box_number=response.data.box_id;
                    // ref.checkBoxNum();
                    if(ref.count == ref.bulk_count){
                        ref.$nextTick(() =>val.$refs.r1.focus());
                        console.log("equal");
                    }
                    else{
                        console.log("here");
                        ref.$nextTick(() => ref.$refs.s1.focus());
                    }
                    ref.count++;
                    

                }).catch(function (error){
                    ref.output = error;
                });
                this.subkit_number_from_input1="";
                this.subkit_number_from_input2="";
            }
        
        },
        closeBatch() {
            //this.nextKit();
            console.log(this.batch_id);
            axios.post('/bulk-kit-scan/closebatch', {
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
