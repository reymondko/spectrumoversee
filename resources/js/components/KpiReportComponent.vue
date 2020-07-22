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
                        <div class="col-md-1 col-md-offset-1 center-element kpi-input-margin">
                            <span class="search-label">Start Date</span>
                        </div>
                        <div class="col-md-2 kpi-input kpi-input-margin">
                            <Datepicker  :format="formatDate"  :bootstrap-styling="true"  v-model="search_param.start"></Datepicker>
                        </div>
                         <div class="col-md-1 center-element kpi-input-margin">
                            <span class="search-label">To</span>
                        </div>
                        <div class="col-md-2 kpi-input kpi-input-margin">
                             <Datepicker :format="formatDate"  :bootstrap-styling="true" v-model="search_param.end"></Datepicker>
                        </div>
                        <div class="col-md-1 center-element kpi-input-margin">
                            <span class="search-label">Client</span>
                        </div>
                         <div class="col-md-2 kpi-input kpi-input-margin">
                            <select v-model="search_param.client" class="form-control">
                                <option value="">All</option>
                                <option  v-for="client in shipclients" :key="client.tpl_client_id" :value="client.tpl_client_id" >{{client.name}}</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-flat default-kpi-btn" v-on:click="searchReport()">Search</button>
                        </div>
                    </div>

                    <div class="row" id="average_values" v-if="objectHasValues(calculated)" >
                        <div class="col-md-2 col-md-offset-1 right-align-element default-font">
                            Average  Days:
                        </div>
                        <div class="col-md-1 default-font">
                            {{calculated.average_days}}
                        </div>
                        <div class="col-md-2 right-align-element default-font">
                            Reships:
                        </div>
                        <div class="col-md-1 default-font">
                            {{calculated.reships}}
                        </div>
                         <div class="col-md-2 right-align-element default-font">
                            Error Rate:
                        </div>
                        <div class="col-md-1 default-font">
                            {{calculated.error_rate}} %
                        </div>
                     </div>

                </div>
            </div>
        </div>
        <!-- TABLE -->
        <div class="col-md-9 col-md-offset-1" v-if="table_data.length > 0">
            <div class="box box-solid box-primary default-color">
                <div class="box-header with-border">
                    <h3 class="box-title">Results</h3>
                    <div class="box-tools pull-right">
                         <button class="btn btn-flat secondary-kpi-btn" v-on:click="exportReport()" >Export</button>
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
                    client:""
                },

                // Identifier if a search is being executed
                searching: false,
                exporting: false,

                // Calculated values
                calculated:{
                    average_days:null,
                    reships:null,
                    error_rate: 0
                },

                // Table Data
                table_data:[],
                table_columns: [
                    {label: 'Client', field: 'client'},
                    {label: 'Ship Date', field: 'ship_date'},
                    {label: 'Order Date', field: 'order_date'},
                    {label: 'Tran #', field: 'order_number'},
                    {label: 'Reference #', field: 'reference_number'},
                    {label: 'Carrier', field: 'carrier'},
                    {label: 'Tracking Number', field: 'tracking_number'},
                    {label: 'Fulfilled By', field: 'fulfilled_by'},
                    {label: 'Order Age', field: 'order_age'}
                ],
                table_per_page: 25,
                table_page:1,
                endpoint:{
                    get_kpi_report:'report/search',
                    export_kpi_report: 'report/export'
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
                 this.searching = true;
                 this.table_data = [];
                 this.calculated.average_days = null;
                 this.calculated.reships = null;
                 axios.post('/kpi/' + this.endpoint.get_kpi_report, this.search_param)
                 .then(response=>{
                     var res = response.data;
                     if(res.success == true){
                       this.table_data = res.data.table_data;
                       this.calculated.average_days = res.data.average_days;
                       this.calculated.reships = res.data.reships;
                       this.calculated.error_rate = res.data.error_rate;
                     }else{

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
                     url: '/kpi/' + this.endpoint.export_kpi_report,
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
