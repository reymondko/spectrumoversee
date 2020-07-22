<template>
    <div class="row">
        <div class="col-md-12"></div>
        <div class="col-md-10 col-md-offset-1">
            <!-- TOP SEARCH FORM -->
            <div class="box box-solid box-primary default-color">
                <div class="box-header with-border">
                    <h3 class="box-title"></h3>
                    <div class="box-tools pull-right">
                    </div>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-2 center-element ">
                            <span class="search-label">Start Date</span>
                        </div>
                        <div class="col-md-2 ">
                            <Datepicker  :format="formatDate"  :bootstrap-styling="true"  v-model="search_param.start"></Datepicker>
                        </div>
                         <div class="col-md-1 center-element kpi-input-margin">
                            <span class="search-label">End Date</span>
                        </div>
                        <div class="col-md-2">
                             <Datepicker :format="formatDate"  :bootstrap-styling="true" v-model="search_param.end"></Datepicker>
                        </div>
                        <div class="col-md-1 center-element kpi-input-margin">
                            <span class="search-label">Company</span>
                        </div>
                        <div class="col-md-2">
                            <select v-if="companies"  class="form-control" v-model="search_param.company">
                                <option value="all">All</option>
                                <option v-for="company in companies" v-bind:key="company.id" :value="company.id">{{company.company_name}}</option>
                            </select>
                            <!-- <input type="text" class="form-control" placeholder="Customer ID" id="customer_id" v-model="search_param.customer"  > -->
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-flat default-kpi-btn" v-on:click="searchReport(true)">Search</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- TABLE -->
        <div class="col-md-10 col-md-offset-1" v-if="table_data.length > 0">
            <div class="box box-solid box-primary default-color">
                <div class="box-header with-border">
                    <h3 class="box-title">Results</h3>
                    <div class="box-tools pull-right">
                         <button class="btn btn-flat secondary-kpi-btn" v-on:click="exportReport()" >
                             <span v-if="!exporting">Export Shipping Cost Report&nbsp;&nbsp;<i class="fa fa-download" aria-hidden="true"></i></span>
                             <span v-if="exporting" class="blink_me">Exporting&nbsp;&nbsp;<i class="fa fa-download" aria-hidden="true"></i></span>
                         </button>
                    </div>
                </div>
                <div class="box-body">
                    <div class="row padding-default">
                        <datatable :columns="table_columns" :data="getData"></datatable>
                    </div>
                    <div class="row center-element">
                        <div class="col-xs-12 form-inline">
                            <datatable-pager v-model="table_page" type="abbreviated" :per-page="table_per_page"></datatable-pager>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- EMPTY RESULTS -->
        <div class="col-md-5 col-md-offset-3" v-if="show_empty">
            <div class="box box-solid box-primary default-color">
                <div class="box-header with-border">
                    <h3 class="box-title">Results</h3>
                </div>
                <div class="box-body">
                    <div class="row padding-default">
                        <center>
                            <h2>
                                No Data
                            </h2>
                        </center>
                    </div>
                </div>
            </div>
        </div>
    </div>

</template>

<script>
    import Datepicker from 'vuejs-datepicker';
    import DatatableFactory from 'vuejs-datatable';
    import { IDataFnParams, ITableContentParam, TColumnsDefinition } from 'vuejs-datatable';

    export default {

        props: ['companies'],

        data() {
            return {
                // Search parameters
                search_param : {
                    start:null,
                    end:null,
                    company:null,
                    sort_by:null,
                    sort_dir:null,
                    page:1
                },

                // Identifier if a search is being executed
                searching: false,
                exporting: false,
                show_empty:false,
                // Table Data
                table_data:[],
                table_columns: [
                    {label: 'Companies', field: 'company_name'},
                    {label: '3pl Customer ID', field: 'tpl_customer_id'},
                    {label: 'Transaction ID', field: 'tpl_order_id'},
                    {label: 'Tracking Number', field: 'tracking_number'},
                    {label: 'Shipping Cost', field: 'shipping_cost'},
                    {label: 'Shipping Cost With Markup', field: 'shipping_cost_with_markup'},
                    {label: 'Weight', field: 'weight'},
                    {label: 'Shipping Vendor', field: 'shipping_vendor'},
                ] ,
                table_per_page:  25,
                table_page:1,
                table_sort:null,
                table_sort_dir:null,
                table_size:null,
                initial_load:true,
                endpoint:{
                    get_shipping_cost_report:'shipping-cost/search',
                    export_return_label_report: 'shipping-cost/export'
                }

            }
        },
        components: {
            Datepicker
        },

        mounted() {
            console.log(this.companies);
        },

        methods: {

            /**
             * Data handler for table
             * 
             * @param tableParams|instance of vueDatatableTableParams
             * @param tableData|reference function to the vueDtatable data handle
             */
            getData( tableParams, tableData){
                console.log(this.table_size);
                this.table_page = tableParams.page_number;
                this.table_sort = tableParams.sort_by;
                this.table_sort_dir = tableParams.sort_dir;
                this.search_param.page = tableParams.page_number;
                this.search_param.sort_by = tableParams.sort_by;
                this.search_param.sort_dir = tableParams.sort_dir;
                this.table_per_page = tableParams.page_length;
                this.table_page = tableParams.page_number;
                if(this.initial_load == false){
                    // this.searchReport();
                    axios.post('/reports/' + this.endpoint.get_shipping_cost_report, this.search_param)
                    .then(response=>{
                        var res = response.data;
                        if(res.success == true){
                        this.table_data = res.data.table_data;
                        this.table_size = res.table_size;
                        tableData(res.data.table_data,res.table_size);
                        }else{
                            this.table_data = [];
                            this.show_empty = true;
                        }
                        this.searching = false;
                    })
                    .catch(err => {
                        this.searching = false;
                    })
                }else{
                    this.initial_load = false;
                    tableData(this.table_data,this.table_size);
                }
            },

            /**
             * Format date into Y-m-d format.
             *
             * @param date|string
             * @return string
             */
            formatDate(date){
                return moment(date).format('Y/M/D');
            },

            /**
             * Check if all object properties have values
             *
             * @param obj|object
             * @return bool
             */
            objectHasValues(obj){
                let hasValue = true;
                Object.keys(obj).map(function(key, index) {
                    if (obj[key] == null){
                        hasValue = false;
                    }
                });
               return hasValue;
            },

            /**
             * Post Search data to the API
             *
             */
           searchReport(refresh = false){
                this.show_empty = false;
                this.searching = true;
                 
                 // Clears datable table search parameters
                 if(refresh){
                    this.table_data = [];
                    this.search_param.page = null;
                    this.search_param.sort_by = null;
                    this.search_param.sort_dir = null;
                    this.table_size = null;
                    this.initial_load = true;
                 }
                
                axios.post('/reports/' + this.endpoint.get_shipping_cost_report, this.search_param)
                .then(response=>{
                     var res = response.data;
                     if(res.success == true){
                       console.log('has_loaded');
                       this.table_data = res.data.table_data;
                       this.table_size = res.table_size;
                     }else{
                         this.table_data = [];
                         this.show_empty = true;
                     }
                     this.searching = false;
                 })
                 .catch(err => {
                    this.searching = false;
                 })
            },

            /**
             * Export Search Result
             *
             */
            exportReport(){
                 this.exporting = true;
                 axios({
                     url: '/reports/' + this.endpoint.export_return_label_report,
                     method: 'POST',
                     responseType:'blob',
                     data:this.search_param
                 })
                 .then(response=>{
                        const url = window.URL.createObjectURL(new Blob([response.data]));
                        const link = document.createElement('a');
                        link.href = url;
                        link.setAttribute('download', 'export.xlsx');
                        document.body.appendChild(link);
                        link.click();
                        this.exporting = false;
                 })
                 .catch(err => {
                    this.exporting = false;
                 })
            },

        }
    }
</script>
