<template>
    <div class="row">
        <div class="col-md-12"></div>
        <div class="col-md-9 col-md-offset-1">
            <!-- TOP SEARCH FORM -->
            <div class="box box-solid box-primary default-color">
                <div class="box-header with-border">
                    <h3 class="box-title"></h3>
                    <div class="box-tools pull-right">
                    </div>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-2 col-md-offset-1 center-element ">
                            <span class="search-label">Start Date</span>
                        </div>
                        <div class="col-md-2 ">
                            <Datepicker  :format="formatDate"  :bootstrap-styling="true"  v-model="search_param.start"></Datepicker>
                        </div>
                         <div class="col-md-2 center-element kpi-input-margin">
                            <span class="search-label">To</span>
                        </div>
                        <div class="col-md-2">
                             <Datepicker :format="formatDate"  :bootstrap-styling="true" v-model="search_param.end"></Datepicker>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-flat default-kpi-btn" v-on:click="searchReport()">Search</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- TABLE -->
        <div class="col-md-5 col-md-offset-3" v-if="table_data.length > 0">
            <div class="box box-solid box-primary default-color">
                <div class="box-header with-border">
                    <h3 class="box-title">Results</h3>
                    <div class="box-tools pull-right">
                         <button class="btn btn-flat secondary-kpi-btn" v-on:click="exportReport()" >Export Return Label Report Details&nbsp;&nbsp;<i class="fa fa-download" aria-hidden="true"></i>
                         </button>
                    </div>
                </div>
                <div class="box-body">
                    <div class="row padding-default">
                        <datatable :columns="table_columns" :data="table_data"></datatable>
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
    export default {

        props: ['shipclients'],

        data() {
            return {
                // Search parameters
                search_param : {
                    start:null,
                    end:null,
                },

                // Identifier if a search is being executed
                searching: false,
                exporting: false,
                show_empty:false,
                // Table Data
                table_data:[],
                table_columns: [
                    {label: 'Customer', field: 'tpl_customer'},
                    {label: 'Return Labels Used', field: 'return_labels_used'},
                ],
                table_per_page: 25,
                table_page:1,
                endpoint:{
                    get_return_label_report:'return-label/search',
                    export_return_label_report: 'return-label/export'
                }

            }
        },
        components: {
            Datepicker
        },

        mounted() {
        },

        methods: {

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
            searchReport(){
                 this.show_empty = false;
                 this.searching = true;
                 this.table_data = [];
                 axios.post('/reports/' + this.endpoint.get_return_label_report, this.search_param)
                 .then(response=>{
                     var res = response.data;
                     console.log(res);
                     if(res.success == true){
                         console.log(res.data.table_data);
                       this.table_data = res.data.table_data;
                     }else{
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
                //  axios.post('/kpi/' + this.endpoint.export_kpi_report, this.search_param)
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
