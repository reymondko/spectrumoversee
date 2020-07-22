<template>
	<section class="ship-pack-input-section" >

		<!-- SEARCH TRANSACTION LOADER-->
		<center v-if="this.saving">
			<div class="loader"></div>
			Saving
		</center>

		<!-- SEARCH TRANSACTION LOADER-->
		<center v-if="this.zpl_printing">
			<div class="loader"></div>
			Printing
		</center>

		<div v-if="showAlertSuccess" class="alert alert-success alert-dismissible alert-saved">
			<button type="button" class="close alert-close-btn" data-dismiss="alert" aria-hidden="true">×</button>
			<h4><i class="icon fa fa-check"></i>Saved!</h4>
		</div>

		<center v-if="(!this.zpl_printing) && (this.show_zpl_print_success)">
			<div class="box box-solid box-primary ship-pack-input-box"  v-if="!saving">
				<div class="box-header ship-pack-box-header">
					
				</div>
				<div class="box-body">
					<div class="form-group">
						<h3>Printed Successfully</h3>
					</div>
					<div class="form-group settings-btn-container">
						<button type="button" class="btn btn-flat so-btn" @click="zplPrintAgain()" >Print Again</button>
						<button type="button" class="btn btn-flat so-btn" @click="continueCreateShipment()">Continue</button>
					</div>
				</div>
			</div>
		</center>
		
		<div class="order-body" v-if="(!this.saving) && (!this.zpl_printing) && (!this.show_zpl_print_success)">
			<div class="box inventory-box">
				<div class="box-body">
					
					<!--				
					<div class="row" style="margin-bottom:20px">
						<div class="col-sm-6">
							<label class="scan-input-qty-lbl">Shipping Label Printer:</label>
							<select class="form-control sp-pack-sel sp-printer-sel" v-model="form.printer_id">
								<option value="-1">select printer</option>
								<option  v-for="(printer,index) in printers" :key="index" :value="printer.id" >{{printer.name}}</option>
							</select>
						</div>
						<div class="col-sm-6"></div>
					</div>
					-->
					
					<div class="row">
						<div class="col-sm-6">
							<ul class="list-group list-group-flush">
								<li class="list-group-item active">TO:</li>
								<li class="list-group-item">
									<div class="form-group">
										<small id="emailHelp" class="form-text text-muted">First Name</small>
										<input v-model="form.to_addresses.first_name" type="text" class="form-control" placeholder="First Name">
									</div>
									<div class="form-group">
										<small id="emailHelp" class="form-text text-muted">Last Name</small>
										<input v-model="form.to_addresses.last_name" type="text" class="form-control" placeholder="Last Name">
									</div>
									<div class="form-group">
										<small id="emailHelp" class="form-text text-muted">Email</small>
										<input v-model="form.to_addresses.email" type="email" class="form-control" placeholder="Email">
									</div>
									<div class="form-group">
										<small id="emailHelp" class="form-text text-muted">Phone</small>
										<input v-model="form.to_addresses.phone" type="text" class="form-control" placeholder="Phone">
									</div>
									<div class="form-group">
										<small id="emailHelp" class="form-text text-muted">Address 1</small>
										<input v-model="form.to_addresses.address1" type="text" class="form-control" placeholder="Address 1">
									</div>
									<div class="form-group">
										<small id="emailHelp" class="form-text text-muted">City</small>
										<input v-model="form.to_addresses.city" type="text" class="form-control" placeholder="City">
									</div>
									<div class="form-group">
										<small id="emailHelp" class="form-text text-muted">State</small>
										<input v-model="form.to_addresses.state" type="text" class="form-control" placeholder="State">
									</div>
									<div class="form-group">
										<small id="emailHelp" class="form-text text-muted">Postalcode</small>
										<input v-model="form.to_addresses.postalcode" type="number" class="form-control" placeholder="Postalcode">
									</div>
									<div class="form-group">
										<small id="emailHelp" class="form-text text-muted">Country</small>
										<input v-model="form.to_addresses.country" type="text" class="form-control" placeholder="Country">
									</div>
								</li>
							</ul>
						</div>

						
						<div class="col-sm-6">
							<ul class="list-group list-group-flush">
								<li class="list-group-item active">FROM:</li>
								<li class="list-group-item">
									<div class="form-group">
										<small id="emailHelp" class="form-text text-muted">First Name</small>
										<input v-model="form.from_addresses.first_name" type="text" class="form-control" placeholder="First Name">
									</div>
									<div class="form-group">
										<small id="emailHelp" class="form-text text-muted">Last Name</small>
										<input v-model="form.from_addresses.last_name" type="text" class="form-control" placeholder="Last Name">
									</div>
									<div class="form-group">
										<small id="emailHelp" class="form-text text-muted">Email</small>
										<input v-model="form.from_addresses.email" type="email" class="form-control" placeholder="Email">
									</div>
									<div class="form-group">
										<small id="emailHelp" class="form-text text-muted">Phone</small>
										<input v-model="form.from_addresses.phone" type="text" class="form-control" placeholder="Phone">
									</div>
									<div class="form-group">
										<small id="emailHelp" class="form-text text-muted">Address 1</small>
										<input v-model="form.from_addresses.address1" type="text" class="form-control" placeholder="Address 1">
									</div>
									<div class="form-group">
										<small id="emailHelp" class="form-text text-muted">City</small>
										<input v-model="form.from_addresses.city" type="text" class="form-control" placeholder="City">
									</div>
									<div class="form-group">
										<small id="emailHelp" class="form-text text-muted">State</small>
										<input v-model="form.from_addresses.state" type="text" class="form-control" placeholder="State">
									</div>
									<div class="form-group">
										<small id="emailHelp" class="form-text text-muted">Postalcode</small>
										<input v-model="form.from_addresses.postalcode" type="number" class="form-control" placeholder="Postalcode">
									</div>
									<div class="form-group">
										<small id="emailHelp" class="form-text text-muted">Country</small>
										<input v-model="form.from_addresses.country" type="text" class="form-control" placeholder="Country">
									</div>
								</li>
							</ul>
						</div>
					</div>


					<div class="ship-pack-content">
						<hr class="divider">
					</div>

					<table class="table table-striped table-bordered">
						<thead class="thead-dark">
							<tr>
							<th scope="col" colspan="4" class="sp-table-th th-mini">Package</th>
							</tr>
						</thead>
						<thead class="thead-dark">
							<tr>
								<th scope="col" class="sp-table-th th-mini">Length</th>
								<th scope="col" class="sp-table-th th-mini">Width</th>
								<th scope="col" class="sp-table-th th-mini">Height</th>
								<th scope="col" class="sp-table-th th-mini">Weight</th>
							</tr>
						</thead>
						<tbody>
							<tr class="sp-table-tr">
								<td class="sp-table-td th-mini">
									<input v-model="form.package.length" type="text" class="form-control" placeholder="Length">
								</td>
								<td class="sp-table-td th-mini">
									<input v-model="form.package.width" type="text" class="form-control" placeholder="Width">
								</td>
								<td class="sp-table-td th-mini">
									<input v-model="form.package.height" type="text" class="form-control" placeholder="Height">
								</td>
								<td class="sp-table-td th-mini">
									<input v-model="form.package.weight" type="text" class="form-control" placeholder="Weight">
								</td>
							</tr>
						</tbody>
					</table>


					<div class="ship-pack-content">
						<hr class="divider">
					</div>

					 <!-- GET SHIPPING RATES -->
					<div class="ship-pack-content w-border" style="padding-top:20px">
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
							<!--<button :disabled="!shipping_rate_btn_enabled" @click="getShiprushRates()"
							type="button"
							class="btn btn-flat so-btn override-btn weight-fields">
								Get Shipping Rates <i v-if="busyGetShippingRates" class="fa fa-spinner fa-spin"></i>
							</button>-->
							<button  @click="getShiprushRates()"
							type="button"
							class="btn btn-flat so-btn override-btn weight-fields">
								Get Shipping Rates <i v-if="busyGetShippingRates" class="fa fa-spinner fa-spin"></i>
							</button>
						</div>
					</div>

				</div>
			</div>
		</div>
		<div style="display:none;border:none" v-if="this.has_zpl == true">
        	<iframe :src="zpl_print_url" @load="zplPrintFrameHasLoaded()"></iframe>
    	</div>
	</section>
</template>

<script>
export default {
    props: ['package_size', 'printers'],

    data() {
		return {
			shipping_rate_btn_enabled:false,
			has_shipping_rates: false,
			show_zpl_print_success:false,
            has_zpl:false,
            zpl_printing:false,
            zpl_print_url:null, ///js/zpl/zpl.html
			saving: false,
			carriers: [],
			services: [],

			alert: {
				success: false
			},
			
			form: {
				status: 'initial',

				printer_id: "-1",

				to_addresses: {
					first_name: null,
					last_name: null,
					email:null,
					phone:null,
					address1: null,
					city: null,
					state: null,
					postalcode: null,
					country: null,
				},

				from_addresses: {
					first_name: null,
					last_name: null,
					email:null,
					phone:null,
					address1: null,
					city: null,
					state: null,
					postalcode: null,
					country: null,
				},

				package: {
					length: null,
					width: null,
					height: null,
					weight: null
				},

				carrier_code: '-1',

				service_code: '-1',
			},
			get_shipping_rates: {
                status: 'ready',
                can_submit: false,
                data: [],
                selected: null,
            }
		}
	},

	computed: {
		isFormSubmitting() {
			return this.form.status == 'submitting';
		},
		isFormSubmitted() {
			return this.form.status == 'submitted';
		},
		busyGetShippingRates() {
            return this.get_shipping_rates.status === 'busy';
        },
        getShippingRatesSuccess() {
            return this.get_shipping_rates.status === 'success';
        },
		canSubmit() {
			return (
				this.carriers.length > 0 && 
				this.services.length > 0 && 
				this.form.carrier_code != '-1' && 
				this.form.service_code != '-1'
			);
		},
		showAlertSuccess() {
			return this.alert.success;
		},
	},
    
    mounted() {
    },

    methods: {
		
		 /*
        * Ship the selected package based on the selected carrier & service type
        * @param $idx = int | index of the selected shipping service `this.get_shipping_rates.data`
        */
		shipPackage(idx) 
		{
			this.alert.success = false; 
			this.saving = true;  

			if ( !this.validateSubmit() ) {
				alert('Incomplete input data')
				return false;
			}

			var vm = this;
			
			axios.post('/create-shipment/store', {
                to_address: this.form.to_addresses,
				from_address: this.form.from_addresses,
				package: this.form.package,
				selected_service:this.get_shipping_rates.data[idx]
            })
            .then(response => {
				if (response.data.status == 'saved') {
					var zpl_data = response.data.shiprush_record.zpl;
					localStorage.setItem('zpl_datastring',zpl_data); //Temporarily store on local storage for use on zpl printing
					vm.printZPL('/js/zpl/zpl.html?zpl=1'); 
				}
			})
			.catch(err => {
				console.log('err:',err)
			});

		},

		validateSubmit()
		{	
			// Validate Delivery address for empty values
			for (var key in this.form.to_addresses) {
				if (this.form.to_addresses.hasOwnProperty(key)) {
					if(this.form.to_addresses[key] == null){
						return false;
					}
				}
			}

			// Validate Shipper address for empty values
			for (var key in this.form.from_addresses) {
				if (this.form.from_addresses.hasOwnProperty(key)) {
					if(this.form.from_addresses[key] == null){
						return false;
					}
				}
			}

			// Validate Package Details for empty values
			for (var key in this.form.package) {
				if (this.form.package.hasOwnProperty(key)) {
					if(this.form.package[key] == null){
						return false;
					}
				}
			}

			return true;
		},

		
		successMessage() {
			this.alert.success = true;
		},

		clear() {
			this.form.to_addresses.first_name = '';
			this.form.to_addresses.last_name = '';
			this.form.to_addresses.address1 = '';
			this.form.to_addresses.email = '';
			this.form.to_addresses.phone = '';
			this.form.to_addresses.city = '';
			this.form.to_addresses.state = '';
			this.form.to_addresses.postalcode = '';
			this.form.to_addresses.country = '';
			

			this.form.from_addresses.first_name = '';
			this.form.from_addresses.last_name = '';
			this.form.from_addresses.address1 = '';
			this.form.from_addresses.email = '';
			this.form.from_addresses.phone = '';
			this.form.from_addresses.city = '';
			this.form.from_addresses.state = '';
			this.form.from_addresses.postalcode = '';
			this.form.from_addresses.country = '';

			this.form.package.length = '';
			this.form.package.width = '';
			this.form.package.height = '';
			this.form.package.weight = '';
			this.get_shipping_rates.data = [];
			this.get_shipping_rates.selected = null;
			this.get_shipping_rates.can_submit = false;
			this.get_shipping_rates.status = 'ready';
		},

		getShiprushRates() {

			if ( !this.validateSubmit() ) {
				alert('Incomplete input data')
				return false;
			}

            this.get_shipping_rates.selected = null;
            this.get_shipping_rates.can_submit = false;
            this.get_shipping_rates.status = 'busy';
			
            var vm = this;

            axios.post('/create-shipment/get-shiprush-rates', {
                to_address: this.form.to_addresses,
				from_address: this.form.from_addresses,
                package: this.form.package
            })
            .then(response => {
                if (response.data.IsSuccess === "true") {
                    var shipping_rate_data = response.data.AvailableServices.AvailableService;
                    console.log("Shipping rates", shipping_rate_data);
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
                vm.get_shipping_rates.status = 'error';
                vm.get_shipping_rates.can_submit = true;
            });
        },

		
		printZPL(zplUrl){
			this.has_zpl=true;
			this.zpl_print_url=zplUrl;
			this.zpl_printing = true;
			this.saving = false;
		},
		
		continueCreateShipment(){
			this.show_zpl_print_success = false;
			localStorage.removeItem('zpl_datastring'); // Destroy Stored Value
			this.clear();
			location.reload();
			// this.successMessage();
		},

		zplPrintFrameHasLoaded(){
			this.zpl_printing = false;
			this.show_zpl_print_success = true;
		},

		zplPrintAgain(){
			this.zpl_printing = true;
			this.zpl_print_url = this.zpl_print_url+'&dt='+new Date();
		},
    
	},

}
</script>


<style scoped>
	ul li {
		text-align: left;
	}
</style>