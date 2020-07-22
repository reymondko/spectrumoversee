<template>
    <section class="ship-pack-input-section" >


        <!--Visual Scan Modal -->
        <div class="modal fade" id="closeBoxing" tabindex="-1" role="dialog" aria-labelledby="closeBoxingLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <h4 class="modal-title" >Are you sure you want to complete this box?</h4>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default close-btn so-btn-close" data-dismiss="modal">No</button>
                        <button @click="completeBatch()" data-dismiss="modal" class="btn btn-flat so-btn">Yes</button>
                    </div>
                </div>
            </div>
        </div>
        
       
        <!-- Enter Batch ID -->
        <center v-if="(!this.batch_id)">
            <div class="alert alert-info alert-dismissible alert-saved" style="width:30%" v-if="saved">
                <button type="button" class="close alert-close-btn" data-dismiss="alert" aria-hidden="true">×</button>
                <h4><i class="icon fa fa-check"></i>Saved!</h4>
            </div>
            <div class="box box-solid box-primary ship-pack-input-box"  v-if="!saving">
                <div class="box-header ship-pack-box-header">                    
                    <h3 class="box-title">Kit Boxing</h3>
                </div>
                <div class="box-body">
                    <div class="form-group">
                        <label>Select Batch #:</label>
                        <select class='form-control' v-model='batchnum_select' >
                            <option v-for='data in batches' :value='data.id'>{{ data.batch_number }}</option>
                        </select>
                    </div>
                    <div class="form-group settings-btn-container">
                        <button type="button" class="btn btn-flat so-btn" data-dismiss="modal" id="start_boxing_btn" @click="startBoxing(1)">Start Boxing</button>
                    </div>
                </div>
            </div>

            <a v-on:click="showContinue()" href="#">Box Closed Batch</a>
            <div class="box box-solid box-primary" style="width: 30%;" v-if="(this.continue)">
                <div class="box-header ship-pack-box-header">                    
                    <h3 class="box-title">Box Closed Batch</h3>
                </div>
                <div class="box-body">
                    <div class="form-group">
                        <label>Select Batch #:</label>
                        <select class='form-control' v-model='batchnum_select2' >
                            <option v-for='data in completebatches' :value='data.id'>{{ data.batch_number }}</option>
                        </select>
                    </div>
                    <div class="form-group settings-btn-container">
                        <button type="button" class="btn btn-flat so-btn" data-dismiss="modal" id="batch_num_btn" @click="startBoxing(2)">Continue Boxing</button>
                    </div>
                </div>
            </div>
        </center>

         <div class="order-body" v-else>
            <div class="box inventory-box">
                <div class="box-body  text-center">
                    <div class="ship-pack-content">
                        <div class="text-left" style="font-weight: bold;">
                            Batch #: {{this.batch_num_text}} <br>
                        </div>
                            <center>
                                <div v-if="validate_error" style="color:red">{{this.error_text}}</div>
                                <table class="SubkitBarcodes-table ">
                                    <tr>
                                        <td class="detail-text">
                                            <input type="text" class="form-control" placeholder="Master Kit #" id="master_kit_input" @change="masterkitValidate()"  ref="mkit" v-model="master_kit_from_input"  >
                                        </td>
                                        <td class="detail-text">
                                            <input type="text" class="form-control" placeholder="Box #" id="box_num_input" ref="bni" v-model="box_num_from_input"  >
                                        </td>
                                    </tr>
                                </table>
                                <div class="form-group settings-btn-container">
                                    <button type="button" class="btn btn-flat so-btn" data-dismiss="modal" id="next_kit_btn" @click="nextKit()">Next Kit</button>
                                </div>
                        </center>
                        <div class="text-right" >
                            <button type="button" class="btn btn-flat so-btn" data-toggle="modal" data-target="#closeBoxing"  id="close_batch_btn">Complete Batch</button>
                        </div>
                        
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
input#master_kit_input,#box_num_input {
    height: 80px;
    font-size: 40px;
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
export default {
    props: ['package_size'],

    data() {
		return {
            continue:null,
            validate_error:false,
            error_text:null,
            saved:false,
            saving:false,
            scan_err:'NOT FOUND',
            carrier_filter:'',
            selected_carrier:-1,
            selected_carrier_service:-1,
            batch_num:null,
            batchnum_select:null,
            batchnum_select2:null,
            batch_id:null,
            batches: [],
            completebatches: [],
            master_kit:null,
            customer_id:null,
            batchnum_selected:null,
            batch_num_from_input:null,
            master_kit_from_input:null,
            box_num_from_input:null,
            show_search_loader:false,
            show_not_found_err:false,
            show_scan_err:false,
            scan_value:null,
            scan_serial:null,
            box_num_input:null,
            subkit_numbers:null,
            return_tracking_numbers:null,
            batch_num_text:null
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

    mounted() {
        this.selected_package = -1;
        // this.checkPrinterFromCookie();
        // this.getCarriers();
    },

    methods: {
        showContinue(){
            this.continue="1";
        },
        getBatches: function(){
              axios.get('/kit-boxing/batches')
              .then(function (response) {
                 this.batches = response.data;
              }.bind(this));
         
        },
        getcompleteBatches: function(){
              axios.get('/kit-boxing/getcompletebatches')
              .then(function (response) {
                 this.completebatches = response.data;
              }.bind(this));
         
        },
        setFocus(){
            this.$refs.mkit.focus();
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
        startBoxing(num){            
            this.continue=null;
            if(num == 2){
                this.batch_id = this.batchnum_select2;
            }
            else{
                this.batch_id = this.batchnum_select;
            }
            var ref = this;
            axios.get('/kit-boxing/batchnumber',{
                 params: {
                   batch_id: this.batch_id
                 }
              })
              .then(function (response) {
                    ref.batch_num_text = response.data[0].batch_number;
                    console.log(this.batch_num_text);                 
                    ref.setFocus();
              }.bind(this));

        },
        masterkitValidate(){
            console.log(this.master_kit_from_input + " "+this.batch_id);
            var val = this;
            axios.post('/kit-boxing/validate', {
                    'master_kit_id': this.master_kit_from_input,
                    'batch_id': this.batch_id
                    }).then(function (response) {
                        console.log('exist:',response.data);
                        //if exist
                        if(response.data =="valid"){
                            val.error_text="";
                            val.$refs.bni.focus();
                        }
                        else if(response.data == "notvalid"){
                            val.master_kit_from_input ="";
                            val.validate_error=true;
                            val.error_text="Master Kit # does not exist.";
                            val.setFocus();
                        }
                        else{
                            val.master_kit_from_input ="";
                            val.validate_error=true;
                            val.error_text="Master Kit already have Box #!";
                            val.setFocus();
                        }
                    });
        },
        assignBoxKit(){

        },
        nextKit(){
            if(this.master_kit_from_input != null && this.box_num_from_input !=null) {

                this.master_kit  = this.master_kit_from_input;        
                this.box_num_input  = this.box_num_from_input;         
                
                var ref = this;
                axios.post('/kit-boxing/updateboxid', {
                    'batchid':this.batch_id,
                    'master_kit': this.master_kit,                    
                    'box_num_input': this.box_num_input
                }).then(function (response) {
                    // console.log('searchTransactionId:',response)
                    var data = response.data.last_insert_id;
                }).catch(function (error) {
                    ref.output = error;
                });
                this.master_kit_from_input = "";
                this.box_num_from_input = "";
                this.setFocus();
                
            }
            else{ this.setFocus(); return false;}
        },
        completeBatch() {
            this.nextKit();
            
            axios.post('/kit-boxing/completebatch', {
                    'batch_id' : this.batch_id
                }).then(function (response) {
                    // console.log('searchTransactionId:',response)
                    var data = response.data.last_insert_id;
                }).catch(function (error) {
                    ref.output = error;
                });
            this.master_kit_from_input  = "";
            this.box_num_from_input = "";
            this.batch_id  = null;
            this.batchnum_selected  = null;
            this.batchnum_select2 = "";
            this.batchnum_select  = "";            
            this.box_num_input  = "";
            this.getBatches();
            this.getcompleteBatches();
   
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
        this.getBatches();
        this.getcompleteBatches();
    }
}
</script>
