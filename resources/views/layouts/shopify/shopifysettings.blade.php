@extends('adminlte::page')


@section('title', 'Shopify Integration Congifuration')



@section('content_header')
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <h1 class="header-text">Shopify Integration Congifuration</h1>
    <!--<button type="button" id="topbar_search" class="search-bar-btn btn btn-default so-btn">
        <i class="fa fa-search"></i> Search
    </button>
    <input type="text" class="search-bar col-md-2" id="global_search" aria-describedby="search" placeholder="Search">-->
@stop


@section('content')
    @if(session('status') == 'saved')
        <div class="alert alert-info alert-dismissible alert-saved">
            <button type="button" class="close alert-close-btn" data-dismiss="alert" aria-hidden="true">×</button>
            <h4><i class="icon fa fa-check"></i>Saved!</h4>
        </div>
    @endif
    <div class="box user-box">
        <div class="box-header">
           <h3 class="box-title"></h3>
           <div class="box-tools pull-right">
             <button type="button" class="btn btn-flat add-user-btn" data-toggle="modal" data-target="#addShopifyModal">
                 <i class="fa fa-plus"></i> Add Shopify Config
             </button>
           </div>
           <!-- /.box-tools -->
        </div>
        <!-- /.box-header -->
        <div class="box-body">
            <table id="users_table" class="table table-striped table-bordered" style="width:100%">
                <thead class="table_head">
                    <tr>
                        <th class="table_head_th">Company</th>
                        <th class="table_head_th">3pl Customer ID</th>
                        <th class="table_head_th">Logiwa Depositor ID</th>
                        <th class="table_head_th">Logiwa Depositor Code</th>
                        <th class="table_head_th">Shopify Url</th>
                        <th class="table_head_th">Shopify API Key</th>
                        <th class="table_head_th">Shopify Password</th>
                        <th class="table_head_th">Integration Status</th>
                        <th class="table_head_th" colspan="3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['getConfigs'] as $c)
                        <tr>
                            <td>{{$c->company_name}}</td>
                            <td>{{$c->tpl_customer_id}}</td>
                            <td>{{$c->logiwa_depositor_id}}</td>
                            <td>{{$c->logiwa_depositor_code}}</td>
                            <td>{{$c->shopify_url}}</td>
                            <td>{{$c->shopify_api_key}}</td>
                            <td>{{$c->shopify_password}}</td>
                            <td>{{$c->integration_status}}</td>
                            <td><button type="button" class="btn btn-default"
                                data-toggle="modal"
                                data-target="#editShopifyModal" onclick="editConfig('{{$c->id}}','{{$c->companies_id}}','{{$c->shopify_url}}','{{$c->shopify_api_key}}','{{$c->shopify_password}}','{{$c->integration_status}}','{{$c->tpl_customer_id}}','{{$c->logiwa_depositor_code}}','{{$c->logiwa_depositor_id}}')"><i class="fa fa-pencil"></i></button>
                            </td>
                            <td>
                                <button title="Edit Ignored Skus" type="button" class="btn btn-default"
                                data-toggle="modal"
                                data-target="#ignoredSkuShopifyModal" onclick="editIgnoredSkus('{{$c->id}}','{{$c->companies_id}}','{{$c->ignored_skus}}')"><i class="fa fa-ban"></i></button>
                            </td>
                            <td><button type="button" onclick="deleteConf({{$c->id}})" class="btn btn-default" ><i class="fa fa-trash"></i></button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

<!--Add Shopify Modal -->
<div class="modal fade" id="addShopifyModal" tabindex="-1" role="dialog" aria-labelledby="addShopifyModallLabel">
    <div class="modal-dialog" role="document">
       <div class="modal-content">
          <div class="modal-header">
             <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
             <h4 class="modal-title" id="addShopifyModalLabel">Add Shopify</h4>
          </div>
          <form id="add_Shopify_form" class="form-horizontal" method="POST" action="{{ route('add_Shopify') }}" >
             @csrf
             <div class="modal-body">
                <fieldset>
                   <div class="form-group">
                      <label class="col-md-5 control-label" for="company_name">Company</label>
                      <div class="col-md-4">
                      <select class="form-control select-md" data-show-subtext="true" data-live-search="true" id="company" name="company">
                             <option value="0">---</option>
                             @foreach($data['companies'] as $c)
                                 <option value="{{ $c->id }}">{{$c->company_name}}</option>
                             @endforeach
                       </select>
                      </div>
                   </div>
                   <div class="form-group">
                      <label class="col-md-5 control-label" for="tpl_customer_id">3pl Company ID</label>
                      <div class="col-md-4">
                         <input id="tpl_customer_id" name="tpl_customer_id" type="text" placeholder="3pl Company ID" class="form-control input-md" required="">
                      </div>
                   </div>
                   <div class="form-group">
                      <label class="col-md-5 control-label" for="logiwa_depositor_code">Logiwa Depositor Code</label>
                      <div class="col-md-4">
                         <input id="logiwa_depositor_code" name="logiwa_depositor_code" type="text" placeholder="Logiwa Depositor ID" class="form-control input-md" required="">
                      </div>
                   </div>
                   <div class="form-group">
                      <label class="col-md-5 control-label" for="logiwa_depositor_id">Logiwa Depositor ID</label>
                      <div class="col-md-4">
                         <input id="logiwa_depositor_id" name="logiwa_depositor_id" type="text" placeholder="Logiwa Depositor ID" class="form-control input-md" required="">
                      </div>
                   </div>
                   <div class="form-group">
                      <label class="col-md-5 control-label" for="tpl_customer_id">3pl Company ID</label>
                      <div class="col-md-4">
                         <input id="tpl_customer_id" name="tpl_customer_id" type="text" placeholder="3pl Company ID" class="form-control input-md" required="">
                      </div>
                   </div>
                   <div class="form-group">
                      <label class="col-md-5 control-label" for="shopify_url">Shopify Url</label>
                      <div class="col-md-4">
                         <input id="shopify_url" name="shopify_url" type="text" placeholder="Shopify URL" class="form-control input-md" required="">
                      </div>
                   </div>
                   <div class="form-group">
                      <label class="col-md-5 control-label" for="shopify_api_key">Shopify API Key</label>
                      <div class="col-md-4">
                         <input id="shopify_api_key" name="shopify_api_key" type="text" placeholder="Shopify API Key" class="form-control input-md" required="">
                      </div>
                   </div>
                   <div class="form-group">
                      <label class="col-md-5 control-label" for="shopify_password">Shopify Password</label>
                      <div class="col-md-4">
                         <input id="shopify_password" name="shopify_password" type="text" placeholder="Shopify Password" class="form-control input-md" required="">
                      </div>
                   </div>
                   <div class="form-group">
                      <label class="col-md-5 control-label" for="integration_status">Integration?</label>
                      <div class="col-md-4">
                        <div class="radio-inline">
                            <label>
                                <input  type="radio" name="integration_status" id="button_link" value="1" >Yes
                            </label>
                        </div>
                        <div class="radio-inline">
                            <label>
                                <input type="radio" name="integration_status" id="button_link"  value="0">No
                            </label>
                        </div>
                      </div>
                   </div>
                   <div class="form-group">
                      <label class="col-md-5 control-label" for="submit"></label>
                   </div>
                </fieldset>
             </div>
             <div class="modal-footer">
                <button type="button" class="btn btn-flat close-btn" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-flat add-Shopify-btn">Submit</button>
             </div>
          </form>
       </div>
    </div>
</div>

<!--edit Shopify Modal -->
<div class="modal fade" id="editShopifyModal" tabindex="-1" role="dialog" aria-labelledby="editShopifyModallLabel">
    <div class="modal-dialog" role="document">
       <div class="modal-content">
          <div class="modal-header">
             <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
             <h4 class="modal-title" id="editShopifyModalLabel">Edit Shopify</h4>
          </div>
          <form id="edit_Shopify_form" class="form-horizontal" method="POST" action="{{ route('edit_Shopify') }}" >
             @csrf
             <input type="hidden" name="id_edit" id="id_edit" >
             <div class="modal-body">
                <fieldset>
                   <div class="form-group">
                      <label class="col-md-5 control-label" for="company_name">Company</label>
                      <div class="col-md-4">
                      <select class="form-control select-md" data-show-subtext="true" data-live-search="true" id="company" name="company">
                             <option value="0">---</option>
                             @foreach($data['companies'] as $c)
                                 <option value="{{ $c->id }}">{{$c->company_name}}</option>
                             @endforeach
                       </select>
                      </div>
                   </div>
                   <div class="form-group">
                      <label class="col-md-5 control-label" for="tpl_customer_id">3pl Customer ID</label>
                      <div class="col-md-4">
                         <input id="tpl_customer_id" name="tpl_customer_id" type="text" placeholder="3pl Company ID" class="form-control input-md" required="">
                      </div>
                   </div>
                   <div class="form-group">
                      <label class="col-md-5 control-label" for="logiwa_depositor_code">Logiwa Depositor Code</label>
                      <div class="col-md-4">
                         <input id="logiwa_depositor_code" name="logiwa_depositor_code" type="text" placeholder="Logiwa Depositor Code" class="form-control input-md" required="">
                      </div>
                   </div>
                   <div class="form-group">
                      <label class="col-md-5 control-label" for="logiwa_depositor_id">Logiwa Depositor ID</label>
                      <div class="col-md-4">
                         <input id="logiwa_depositor_id" name="logiwa_depositor_id" type="text" placeholder="Logiwa Depositor ID" class="form-control input-md" required="">
                      </div>
                   </div>
                   <div class="form-group">
                      <label class="col-md-5 control-label" for="shopify_url">Shopify Url</label>
                      <div class="col-md-4">
                         <input id="shopify_url" name="shopify_url" type="text" placeholder="Shopify URL" class="form-control input-md" required="">
                      </div>
                   </div>
                   <div class="form-group">
                      <label class="col-md-5 control-label" for="shopify_api_key">Shopify API Key</label>
                      <div class="col-md-4">
                         <input id="shopify_api_key" name="shopify_api_key" type="text" placeholder="Shopify API Key" class="form-control input-md" required="">
                      </div>
                   </div>
                   <div class="form-group">
                      <label class="col-md-5 control-label" for="shopify_password">Shopify Password</label>
                      <div class="col-md-4">
                         <input id="shopify_password" name="shopify_password" type="text" placeholder="Shopify Password" class="form-control input-md" required="">
                      </div>
                   </div>
                   <div class="form-group">
                      <label class="col-md-5 control-label" for="integration_status">Integration?</label>
                      <div class="col-md-4">
                        <div class="radio-inline">
                            <label>
                                <input  type="radio" name="integration_status" id="button_link" value="1" >Yes
                            </label>
                        </div>
                        <div class="radio-inline">
                            <label>
                                <input type="radio"  name="integration_status" id="button_link"  value="0">No
                            </label>
                        </div>
                      </div>
                   </div>
                   <div class="form-group">
                      <label class="col-md-5 control-label" for="submit"></label>
                   </div>
                </fieldset>
             </div>
             <div class="modal-footer">
                <button type="button" class="btn btn-flat close-btn" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-flat edit-Shopify-btn">Submit</button>
             </div>
          </form>
       </div>
    </div>
</div>

<!-- Ignored skus -->
<div class="modal fade" id="ignoredSkuShopifyModal" tabindex="-1" role="dialog" aria-labelledby="ignoredSkuShopifyModalLabel">
    <div class="modal-dialog" role="document">
       <div class="modal-content">
          <div class="modal-header">
             <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
             <h4 class="modal-title" id="ignoredSkuShopifyModalLabel">Edit Ignored Skus</h4>
          </div>
          <form id="ignored_sku_Shopify_form" class="form-horizontal" method="POST" action="{{ route('shopifySaveIgnoredSkus') }}" >
             @csrf
             <input type="hidden" name="id_edit_ignored_sku" id="id_edit_ignored_sku" >
             <input type="hidden" name="company_edit_ignored_sku" id="company_edit_ignored_sku" >
             <div class="modal-body">
                <div id="ignored_sku_checkboxes">
                </div>
                <div id="ignored_sku_add_input_div">
                <div class="form-group">
                    <label class="col-md-4 control-label" for="ignored_sku_add_input">SKU</label>
                    <div class="col-md-4">
                        <input id="ignored_sku_add_input" name="ignored_sku_add_input" type="text" placeholder="SKU" class="form-control input-md">
                    </div>
                    <div class="col-md-4">
                        <button type="button" title="Add" class="ignored-sku-add-btn" onClick="addSkuToElem()"> <i class="fa fa-plus"></i></button>
                    </div>
                </div>
                </div>
             </div>
             <div class="modal-footer">
                <button type="button" class="btn btn-flat close-btn" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-flat add-user-btn edit-Shopify-btn">Submit</button>
             </div>
          </form>
       </div>
    </div>
</div>
@stop
@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
    <link rel="stylesheet" href="/css/users.css">
    <link rel="stylesheet" href="/css/spectrumoversee.tables.css">
@stop

@section('js')
<script src="{{ asset('js/jquery/shopifyconfig.jquery.js') }}?{{preg_replace('/[^0-9]/', '', \Tremby\LaravelGitVersion\GitVersionHelper::getNameAndVersion())}}" defer></script>
<script type="text/javascript" src="/js/jquery/topbarsearch.jquery.js?{{preg_replace('/[^0-9]/', '', \Tremby\LaravelGitVersion\GitVersionHelper::getNameAndVersion())}}"></script>
@stop
