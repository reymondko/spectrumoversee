@extends('adminlte::page')
@section('title', 'Create Third Party Orders')
@section('content_header')
<!-- CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">
<h1 class="header-text">
    @if(isset($search))
    <a href=" {{ route('thirdparty_orders') }} " >CREATE THIRD PARTY ORDER</a>
        <i class="fa fa-chevron-right"></i>
        SEARCH <i class="fa fa-chevron-right"></i> {{$search}}
    @else
    THIRD PARTY ORDERS
    @endif

</h1>
<style>
    .tab-content{
       padding-top:20px;
    }

    .form-sub-title{
        border-bottom-style: solid;
        border-width: thin;
        border-color: #d2d6de;
        margin-top:10px;
        font-size:18px;
        padding-bottom: 10px;
    }
</style>
@stop
@section('content')
@if(session('status') == 'saved')
<div class="alert alert-info alert-dismissible alert-saved">
   <button type="button" class="close alert-close-btn" data-dismiss="alert" aria-hidden="true">×</button>
   <h4><i class="icon fa fa-check"></i>Saved!</h4>
</div>
@elseif(session('status') == 'error_saving')
<div class="alert alert-info alert-dismissible alert-error">
   <button type="button" class="close alert-close-btn" data-dismiss="alert" aria-hidden="true">×</button>
   <h4><i class="icon fa fa-warning"></i>Error Saving Data!</h4>
</div>
@endif
<section class="invoice" style="background-color:#3330">
      <!-- title row -->
      <div class="row">
        <div class="col-xs-12">
          <h2 class="page-header">
            <i class="fa fa-truck"></i> Create 3PL Order
            <small class="pull-right">Date: {{date('m/d/Y')}}</small>
          </h2>
        </div>
        <!-- /.col -->
      </div>
      <!-- info row -->
      <!-- @if(count($customers) > 1)
        <div class="row" style="width: 100%;margin-bottom: 20px;">
            <label for="customer_select" style="font-size:20px;">Customer</label>
            <select  class="form-control" id="customer_select"  style="width:20%;">>
                <option value="">--Select Customer--</option>
                @foreach($customers as $key=>$value)
                <option value="{{$key}}">{{$value}}</option>
                @endforeach
            </select>
        </div>
        @endif -->
        @if(count($depositors) > 1)
            <div class="row" style="width: 100%;margin-bottom: 20px;">
                <label for="customer_select" style="font-size:20px;">Customer</label>
                <select  class="form-control" id="customer_select"  style="width:20%;">>
                    <option value="">--Select Customer--</option>
                    @foreach($depositors as $depositor)
                    <option value="{{$depositor->logiwa_depositor_id}}"
                        @if($selected_depositor == $depositor->logiwa_depositor_id)
                            selected
                        @endif
                    >
                        {{$depositor->logiwa_depositor_code}}
                    </option>
                    @endforeach
                </select>
            </div>
        @endif

         <div class="row" id="create_order_form_div" @if($selected_depositor == null) style="display:none;" @endif>
            <form method="POST" action="{{route('thirdparty_orders_create_save')}}" id="create_tpl_order_form">
                @csrf
                <div id="ship_to">
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Ship To</h3>
                            <div class="box-tools pull-right">
                            <!-- Collapse Button -->
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                            </div>
                        </div>
                        <div class="box-body">
                            <div class="form-group col-md-6">
                                <label for="">Reference #<span style="color:red">*</span></label>
                                <input type="text" class="form-control required-input" id="ref_number" name="ref_number" placeholder="Reference Number" required >
                            </div>
                            <div class="form-group col-md-6">
                                <label for="">P.O. #</label>
                                <input type="text" class="form-control unrequired-input" id="po_number" name="po_number" placeholder="P.O number" >
                            </div>
                            <div class="form-group col-md-12" >
                                <h3 class="box-title form-sub-title">Ship To</h3>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="name">Contact</label>
                                <input type="text" class="form-control unrequired-input" id="name" name="name" placeholder="Name">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="name">Company or Name <span style="color:red">*</span></label>
                                <input type="text" class="form-control required-input" id="company" name="company" placeholder="Company" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="address1">Address 1 <span style="color:red">*</span></label>
                                <input type="text" class="form-control required-input" id="address1" name="address1" placeholder="Address 1" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="address2">Address 2</label>
                                <input type="text" class="form-control unrequired-input" id="address2" name="address2" placeholder="Address 2">
                            </div>
                            <div class="form-group col-md-3">
                                <label for="city">City <span style="color:red">*</span></label>
                                <input type="text" class="form-control required-input" id="city" name="city" placeholder="city" required>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="street">St./Prov</label>
                                <input type="text" class="form-control unrequired-input" id="street" name="street" placeholder="St./Prov">
                            </div>
                            <div class="form-group col-md-3">
                                <label for="country">Country <span style="color:red">*</span></label>
                                <select  class="form-control required-input" id="country" name="country" placeholder="Country" required>
                                    <option value="">Country</option>
                                    <option value="US">United States</option>
                                    <option value="AF">Afghanistan</option>
                                    <option value="AX">Åland Islands</option>
                                    <option value="AL">Albania</option>
                                    <option value="DZ">Algeria</option>
                                    <option value="AS">American Samoa</option>
                                    <option value="AD">Andorra</option>
                                    <option value="AO">Angola</option>
                                    <option value="AI">Anguilla</option>
                                    <option value="AQ">Antarctica</option>
                                    <option value="AG">Antigua and Barbuda</option>
                                    <option value="AR">Argentina</option>
                                    <option value="AM">Armenia</option>
                                    <option value="AW">Aruba</option>
                                    <option value="AU">Australia</option>
                                    <option value="AT">Austria</option>
                                    <option value="AZ">Azerbaijan</option>
                                    <option value="BS">Bahamas</option>
                                    <option value="BH">Bahrain</option>
                                    <option value="BD">Bangladesh</option>
                                    <option value="BB">Barbados</option>
                                    <option value="BY">Belarus</option>
                                    <option value="BE">Belgium</option>
                                    <option value="BZ">Belize</option>
                                    <option value="BJ">Benin</option>
                                    <option value="BM">Bermuda</option>
                                    <option value="BT">Bhutan</option>
                                    <option value="BO">Bolivia, Plurinational State of</option>
                                    <option value="BQ">Bonaire, Sint Eustatius and Saba</option>
                                    <option value="BA">Bosnia and Herzegovina</option>
                                    <option value="BW">Botswana</option>
                                    <option value="BV">Bouvet Island</option>
                                    <option value="BR">Brazil</option>
                                    <option value="IO">British Indian Ocean Territory</option>
                                    <option value="BN">Brunei Darussalam</option>
                                    <option value="BG">Bulgaria</option>
                                    <option value="BF">Burkina Faso</option>
                                    <option value="BI">Burundi</option>
                                    <option value="KH">Cambodia</option>
                                    <option value="CM">Cameroon</option>
                                    <option value="CA">Canada</option>
                                    <option value="CV">Cape Verde</option>
                                    <option value="KY">Cayman Islands</option>
                                    <option value="CF">Central African Republic</option>
                                    <option value="TD">Chad</option>
                                    <option value="CL">Chile</option>
                                    <option value="CN">China</option>
                                    <option value="CX">Christmas Island</option>
                                    <option value="CC">Cocos (Keeling) Islands</option>
                                    <option value="CO">Colombia</option>
                                    <option value="KM">Comoros</option>
                                    <option value="CG">Congo</option>
                                    <option value="CD">Congo, the Democratic Republic of the</option>
                                    <option value="CK">Cook Islands</option>
                                    <option value="CR">Costa Rica</option>
                                    <option value="CI">Côte d'Ivoire</option>
                                    <option value="HR">Croatia</option>
                                    <option value="CU">Cuba</option>
                                    <option value="CW">Curaçao</option>
                                    <option value="CY">Cyprus</option>
                                    <option value="CZ">Czech Republic</option>
                                    <option value="DK">Denmark</option>
                                    <option value="DJ">Djibouti</option>
                                    <option value="DM">Dominica</option>
                                    <option value="DO">Dominican Republic</option>
                                    <option value="EC">Ecuador</option>
                                    <option value="EG">Egypt</option>
                                    <option value="SV">El Salvador</option>
                                    <option value="GQ">Equatorial Guinea</option>
                                    <option value="ER">Eritrea</option>
                                    <option value="EE">Estonia</option>
                                    <option value="ET">Ethiopia</option>
                                    <option value="FK">Falkland Islands (Malvinas)</option>
                                    <option value="FO">Faroe Islands</option>
                                    <option value="FJ">Fiji</option>
                                    <option value="FI">Finland</option>
                                    <option value="FR">France</option>
                                    <option value="GF">French Guiana</option>
                                    <option value="PF">French Polynesia</option>
                                    <option value="TF">French Southern Territories</option>
                                    <option value="GA">Gabon</option>
                                    <option value="GM">Gambia</option>
                                    <option value="GE">Georgia</option>
                                    <option value="DE">Germany</option>
                                    <option value="GH">Ghana</option>
                                    <option value="GI">Gibraltar</option>
                                    <option value="GR">Greece</option>
                                    <option value="GL">Greenland</option>
                                    <option value="GD">Grenada</option>
                                    <option value="GP">Guadeloupe</option>
                                    <option value="GU">Guam</option>
                                    <option value="GT">Guatemala</option>
                                    <option value="GG">Guernsey</option>
                                    <option value="GN">Guinea</option>
                                    <option value="GW">Guinea-Bissau</option>
                                    <option value="GY">Guyana</option>
                                    <option value="HT">Haiti</option>
                                    <option value="HM">Heard Island and McDonald Islands</option>
                                    <option value="VA">Holy See (Vatican City State)</option>
                                    <option value="HN">Honduras</option>
                                    <option value="HK">Hong Kong</option>
                                    <option value="HU">Hungary</option>
                                    <option value="IS">Iceland</option>
                                    <option value="IN">India</option>
                                    <option value="ID">Indonesia</option>
                                    <option value="IR">Iran, Islamic Republic of</option>
                                    <option value="IQ">Iraq</option>
                                    <option value="IE">Ireland</option>
                                    <option value="IM">Isle of Man</option>
                                    <option value="IL">Israel</option>
                                    <option value="IT">Italy</option>
                                    <option value="JM">Jamaica</option>
                                    <option value="JP">Japan</option>
                                    <option value="JE">Jersey</option>
                                    <option value="JO">Jordan</option>
                                    <option value="KZ">Kazakhstan</option>
                                    <option value="KE">Kenya</option>
                                    <option value="KI">Kiribati</option>
                                    <option value="KP">Korea, Democratic People's Republic of</option>
                                    <option value="KR">Korea, Republic of</option>
                                    <option value="KW">Kuwait</option>
                                    <option value="KG">Kyrgyzstan</option>
                                    <option value="LA">Lao People's Democratic Republic</option>
                                    <option value="LV">Latvia</option>
                                    <option value="LB">Lebanon</option>
                                    <option value="LS">Lesotho</option>
                                    <option value="LR">Liberia</option>
                                    <option value="LY">Libya</option>
                                    <option value="LI">Liechtenstein</option>
                                    <option value="LT">Lithuania</option>
                                    <option value="LU">Luxembourg</option>
                                    <option value="MO">Macao</option>
                                    <option value="MK">Macedonia, the former Yugoslav Republic of</option>
                                    <option value="MG">Madagascar</option>
                                    <option value="MW">Malawi</option>
                                    <option value="MY">Malaysia</option>
                                    <option value="MV">Maldives</option>
                                    <option value="ML">Mali</option>
                                    <option value="MT">Malta</option>
                                    <option value="MH">Marshall Islands</option>
                                    <option value="MQ">Martinique</option>
                                    <option value="MR">Mauritania</option>
                                    <option value="MU">Mauritius</option>
                                    <option value="YT">Mayotte</option>
                                    <option value="MX">Mexico</option>
                                    <option value="FM">Micronesia, Federated States of</option>
                                    <option value="MD">Moldova, Republic of</option>
                                    <option value="MC">Monaco</option>
                                    <option value="MN">Mongolia</option>
                                    <option value="ME">Montenegro</option>
                                    <option value="MS">Montserrat</option>
                                    <option value="MA">Morocco</option>
                                    <option value="MZ">Mozambique</option>
                                    <option value="MM">Myanmar</option>
                                    <option value="NA">Namibia</option>
                                    <option value="NR">Nauru</option>
                                    <option value="NP">Nepal</option>
                                    <option value="NL">Netherlands</option>
                                    <option value="NC">New Caledonia</option>
                                    <option value="NZ">New Zealand</option>
                                    <option value="NI">Nicaragua</option>
                                    <option value="NE">Niger</option>
                                    <option value="NG">Nigeria</option>
                                    <option value="NU">Niue</option>
                                    <option value="NF">Norfolk Island</option>
                                    <option value="MP">Northern Mariana Islands</option>
                                    <option value="NO">Norway</option>
                                    <option value="OM">Oman</option>
                                    <option value="PK">Pakistan</option>
                                    <option value="PW">Palau</option>
                                    <option value="PS">Palestinian Territory, Occupied</option>
                                    <option value="PA">Panama</option>
                                    <option value="PG">Papua New Guinea</option>
                                    <option value="PY">Paraguay</option>
                                    <option value="PE">Peru</option>
                                    <option value="PH">Philippines</option>
                                    <option value="PN">Pitcairn</option>
                                    <option value="PL">Poland</option>
                                    <option value="PT">Portugal</option>
                                    <option value="PR">Puerto Rico</option>
                                    <option value="QA">Qatar</option>
                                    <option value="RE">Réunion</option>
                                    <option value="RO">Romania</option>
                                    <option value="RU">Russian Federation</option>
                                    <option value="RW">Rwanda</option>
                                    <option value="BL">Saint Barthélemy</option>
                                    <option value="SH">Saint Helena, Ascension and Tristan da Cunha</option>
                                    <option value="KN">Saint Kitts and Nevis</option>
                                    <option value="LC">Saint Lucia</option>
                                    <option value="MF">Saint Martin (French part)</option>
                                    <option value="PM">Saint Pierre and Miquelon</option>
                                    <option value="VC">Saint Vincent and the Grenadines</option>
                                    <option value="WS">Samoa</option>
                                    <option value="SM">San Marino</option>
                                    <option value="ST">Sao Tome and Principe</option>
                                    <option value="SA">Saudi Arabia</option>
                                    <option value="SN">Senegal</option>
                                    <option value="RS">Serbia</option>
                                    <option value="SC">Seychelles</option>
                                    <option value="SL">Sierra Leone</option>
                                    <option value="SG">Singapore</option>
                                    <option value="SX">Sint Maarten (Dutch part)</option>
                                    <option value="SK">Slovakia</option>
                                    <option value="SI">Slovenia</option>
                                    <option value="SB">Solomon Islands</option>
                                    <option value="SO">Somalia</option>
                                    <option value="ZA">South Africa</option>
                                    <option value="GS">South Georgia and the South Sandwich Islands</option>
                                    <option value="SS">South Sudan</option>
                                    <option value="ES">Spain</option>
                                    <option value="LK">Sri Lanka</option>
                                    <option value="SD">Sudan</option>
                                    <option value="SR">Suriname</option>
                                    <option value="SJ">Svalbard and Jan Mayen</option>
                                    <option value="SZ">Swaziland</option>
                                    <option value="SE">Sweden</option>
                                    <option value="CH">Switzerland</option>
                                    <option value="SY">Syrian Arab Republic</option>
                                    <option value="TW">Taiwan, Province of China</option>
                                    <option value="TJ">Tajikistan</option>
                                    <option value="TZ">Tanzania, United Republic of</option>
                                    <option value="TH">Thailand</option>
                                    <option value="TL">Timor-Leste</option>
                                    <option value="TG">Togo</option>
                                    <option value="TK">Tokelau</option>
                                    <option value="TO">Tonga</option>
                                    <option value="TT">Trinidad and Tobago</option>
                                    <option value="TN">Tunisia</option>
                                    <option value="TR">Turkey</option>
                                    <option value="TM">Turkmenistan</option>
                                    <option value="TC">Turks and Caicos Islands</option>
                                    <option value="TV">Tuvalu</option>
                                    <option value="UG">Uganda</option>
                                    <option value="UA">Ukraine</option>
                                    <option value="AE">United Arab Emirates</option>
                                    <option value="GB">United Kingdom</option>
                                    <option value="UM">United States Minor Outlying Islands</option>
                                    <option value="UY">Uruguay</option>
                                    <option value="UZ">Uzbekistan</option>
                                    <option value="VU">Vanuatu</option>
                                    <option value="VE">Venezuela, Bolivarian Republic of</option>
                                    <option value="VN">Viet Nam</option>
                                    <option value="VG">Virgin Islands, British</option>
                                    <option value="VI">Virgin Islands, U.S.</option>
                                    <option value="WF">Wallis and Futuna</option>
                                    <option value="EH">Western Sahara</option>
                                    <option value="YE">Yemen</option>
                                    <option value="ZM">Zambia</option>
                                    <option value="ZW">Zimbabwe</option>
                                </select>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="zip">Zip <span style="color:red">*</span></label>
                                <input type="text" class="form-control required-input" id="zip" name="zip" placeholder="Zip" required>
                            </div>
                            <!-- <div class="form-group col-md-3">
                                <label for="retailer_id">Retailer ID</label>
                                <input type="text" class="form-control unrequired-input" id="retailer_id" name="retailer_id" placeholder="Retailer ID">
                            </div> -->
                            <div class="form-group col-md-3">
                                <label for="dept_no">Dept #</label>
                                <input type="text" class="form-control unrequired-input" id="dept_no" name="dept_no" placeholder="Department Number">
                            </div>
                            <div class="form-group col-md-3">
                                <label for="phone">Phone<span style="color:red">*</span></label>
                                <input type="text" class="form-control required-input" id="phone" name="phone" placeholder="Phone" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="name">Email<span style="color:red">*</span></label>
                                <input type="email" class="form-control required-input" id="email" name="ziemailp" placeholder="Email" required>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="show_quick_lookum">Show in 'Quick Lookup' dropdown list &nbsp;</label>
                                <input type="checkbox" id="show_quick_lookup" name="show_quick_lookup" value="1">
                            </div>
                        </div>
                    </div>

                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Carrier & Routing</h3>
                            <div class="box-tools pull-right">
                            <!-- Collapse Button -->
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                            </div>
                        </div>
                        <div class="box-body">
                            <div class="form-group col-md-12" >
                                <h3 class="box-title form-sub-title">Shipping Instructions and Notes</h3>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="carrier">Carrier <span style="color:red">*</span></label>
                                <!-- <input type="text" class="form-control" id="carrier" name="carrier" placeholder="Carrier" required> -->
                                <select  class="form-control required-input" id="carrier" name="carrier"  required>
                                    <option value="">Carrier</option>
                                    <option value="USPS">USPS</option>
                                    <option value="DHL">DHL</option>
                                    <option value="FedEx">FedEx</option>
                                    <option value="UPS">UPS</option>
                                    <option value="Customer Pickup">Customer Pickup</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="service">Service <span style="color:red">*</span></label>
                                <!-- <input type="text" class="form-control" id="service" name="service" placeholder="Service" required> -->
                                <select  class="form-control required-input" id="service" name="service"  required>
                                    <option value="">Service</option>
                                    <option value="Standard">Standard</option>
                                    <option value="1-Day Expedited">1-Day Expedited</option>
                                    <option value="2-Day Expedited">2-Day Expedited</option>
                                    <option value="Will Call- Draper, UT">Will Call- Draper, UT</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="billing">Billing <span style="color:red">*</span></label>
                                <!-- <input type="text" class="form-control" id="billing" name="billing" placeholder="Billing" required> -->
                                <select  class="form-control required-input" id="billing" name="billing"  required disabled="disabled">
                                    <option value="">Billing</option>
                                    <option value="3" selected="selected">Prepaid</option>
                                    <option value="4">Thirdparty</option>
                                </select>
                            </div>
                            <div style="display:none"><!--hiddden-->
                              <div class="form-group col-md-6">
                                  <label for="billing">Account</label>
                                  <input type="text" class="form-control unrequired-input" id="account" name="account" placeholder="Account">
                              </div>
                              <div class="form-group col-md-6">
                                  <label for="account_zip">Account Zip</label>
                                  <input type="text" class="form-control unrequired-input" id="account_zip" name="account_zip" placeholder="Account Zip">
                              </div>
                              <div class="form-group col-md-12">
                                  <label for="cod">COD&nbsp;</label>
                                  <input type="checkbox" id="cod" name="cod" value="1">
                                  <label for="insurance">Insurance&nbsp;</label>
                                  <input type="checkbox" id="insurance" name="insurance" value="1">
                              </div>
                            </div>
                            <div class="form-group col-md-12">
                                <label for="warehouse_instructions">Warehouse Instructions</label>
                                <textarea class="form-control unrequired-input" id="warehouse_instructions" name="warehouse_instructions" ></textarea>
                            </div>
                            <div class="form-group col-md-12">
                                <label for="carrier_instructions">Carrier Instructions</label>
                                <textarea class="form-control unrequired-input" id="carrier_instructions" name="carrier_instructions" ></textarea>
                            </div>
                            <!-- <div class="form-group col-md-12" >
                                <h3 class="box-title form-sub-title">Routing</h3>
                            </div>
                            <div class="form-group col-md-12" >
                                <h3 class="box-title form-sub-title">Totals</h3>
                            </div> -->
                        </div>
                    </div>

                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Line Items
                                <span tabindex="0" id="line_item_error"
                                      style="font-size: 14px;font-weight: bold; color: #dd4b39;margin-left: 20px;display:none;">
                                    *You need to add line items to proceed
                                </span></h3>
                            <div class="box-tools">
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addLineItemModal">
                                    Add Line Items
                                </button>
                            </div>
                        </div>
                        <div class="box-body">
                            <div class="form-group col-md-12" id="line_items_div">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-12" style="text-align:center;padding-top:10px">
                    {{--
                        <input type="hidden" class="form-control" name="facility_id" value="{{$facilityData['id']}}" readonly>
                    <input type="hidden" class="form-control" name="facility_name" value="{{$facilityData['name']}}" readonly>
                    --}}
                    <input type="hidden" class="form-control" id="customer_id" name="customer_id"  readonly>
                    <input type="hidden" class="form-control" id="customer_name" name="customer_name" readonly>
                    <button type="submit" class="btn btn-primary">Create Order</button>
                </div>
            </form>
         </div>
      <!-- /.row -->
    </section>

    <!-- Modal -->
    <div class="modal fade" id="addLineItemModal" tabindex="-1" role="dialog" aria-labelledby="addLineItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="width: 800px;">
    <form id="add_line_items" method="POST">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="addLineItemModalLabel">Line Items</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
                <div class="form-group col-md-4">
                    <label for="storage">SKU</label>
                    <select id="l_item_sku" class="form-control" required>
                        <option value="">-- Select SKU --</option>
                        @foreach($customerItemData as $c)
                            <option @if(count($customers)>1) class="sku-multi-cust sku-multi-cust-{{$customerItemDataCid[$c['sku']]['customer_id']}}"  @endif value="{{$c['id']}}">{{$c['sku']}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label for="l_item_desc">Description</label>
                    <input type="text" class="form-control" id="l_item_desc" placeholder="Description" readonly>
                </div>
                <div class="form-group col-md-4">
                    <label for="l_item_qty">Quantity</label>
                    <input type="number" class="form-control" min="1" id="l_item_qty" name="l_item_qty" placeholder="Quantity" required>
                </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary" >Add</button>
        </div>
        </div>
    </form>
    </div>
    </div>
@stop
@section('css')
<link rel="stylesheet" href="/css/admin_custom.css">
<link rel="stylesheet" href="/css/jquery-ui.css">

@stop
@section('js')
<script src="{{ asset('js/jquery-ui.js') }}" defer></script>
<script src="{{ asset('js/moment.js') }}" defer></script>
<script src="{{ asset('js/jquery/thirdpartycreateorder.jquery.js') }}?v={{preg_replace('/[^0-9]/', '', \Tremby\LaravelGitVersion\GitVersionHelper::getNameAndVersion())}}" defer></script>
<script>
    var skuItems = {!! json_encode($customerItemDataSku) !!};
    var skuCids = {!! json_encode($customerItemDataCid) !!};
    $('#l_item_sku').change(function(){
        $('#l_item_desc').val(skuItems[$("#l_item_sku option:selected").text()]);
    })

    @if(count($depositors) > 1)
        $('#customer_select').change(function(){
            if(this.value != ''){
                window.location.href = '/thirdparty/orders/create?c='+this.value;
            }else{
                window.location.href = '/thirdparty/orders/create';
            }
        });
    @endif;

    // @if(count($customers) > 1)
    // $('#customer_select').change(function(){
    //     if(this.value != ''){
    //         $('.sku-multi-cust').hide();
    //         $('.sku-multi-cust-'+this.value).show();
    //         $('.line-itms').remove();
    //         $('#create_order_form_div').show();
    //     }else{
    //         $('#create_order_form_div').hide();
    //     }
    // });
    // @endif;

</script>
@stop
