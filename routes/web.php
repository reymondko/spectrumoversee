<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', 'GuestController@home');
Route::post('order/save', 'GuestController@customOrderSave')->name('guest_custom_order_save');
Route::get('order/saved', 'GuestController@customOrderSaved')->name('guest_custom_order_saved');
Route::get('order/{all}', 'GuestController@customOrder');
Auth::routes();
Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');

Route::group(['prefix' => 'search',  'middleware' => 'auth'], function() {
    Route::get('/', 'SearchController@index')->name('search');
});

Route::group(['prefix' => 'dashboard',  'middleware' => 'auth'], function() {
    Route::get('/', 'DashboardController@index')->name('dashboard');
});

Route::group(['prefix' => 'reports',  'middleware' => 'auth'], function() {
    Route::get('/', 'ReportsController@index')->name('reports');
    Route::get('/return-label', 'ReportsController@returnLabelReport')->name('return_label_report');
    Route::post('/return-label/search', 'ReportsController@returnLabelReportSearch')->name('return_label_report');
    Route::post('/return-label/export', 'ReportsController@returnLabelReportExport')->name('return_label_export');
    Route::get('/shipping-cost', 'ReportsController@shippingCostReport')->name('shipping_cost_report');
    Route::post('/shipping-cost/search', 'ReportsController@shippingCostReportSearch')->name('shipping_cost_report_search');
    Route::post('/shipping-cost/export', 'ReportsController@shippingCostReportExport')->name('shipping_cost_report_export');
});

Route::group(['prefix' => 'kpi',  'middleware' => 'auth'], function() {
    Route::get('/report', 'KpiReportsController@kpiReport')->name('kpi-reports');
    Route::post('/report/search', 'KpiReportsController@searchKpiReport')->name('kpi-reports-search');
    Route::post('/report/export', 'KpiReportsController@exportKpiReport')->name('kpi-reports-export');
});

Route::group(['prefix' => 'notifications',  'middleware' => 'auth'], function() {
    Route::get('/count', 'NotificationsController@getUnseenNotificationCount')->name('count_notifications');
    Route::get('/all', 'NotificationsController@getNotifications')->name('get_notifications');
});

Route::group(['prefix' => 'inventory',  'middleware' => 'auth'], function() {
    Route::get('/', 'InventoryController@index')->name('inventory');
    Route::get('/graph', 'InventoryController@inventoryGraph')->name('inventory_graph');
    Route::get('/graph/data', 'InventoryController@inventoryGraphData')->name('inventory_graph_data');
    Route::get('/data/paginate', 'InventoryController@paginateInventoryData')->name('inventory_paginate');
    Route::get('/data/paginate/filtered', 'InventoryController@paginateInventoryDataWithFilter')->name('inventory_paginate_filtered');
    Route::get('/import', 'InventoryController@import')->name('inventory_import');
    Route::get('/import/caselabel', 'InventoryController@importCaseLabel')->name('inventory_import_caselabel');
    Route::get('/export', 'InventoryController@exportInventory')->name('inventory_export');
    Route::post('/import/map', 'InventoryController@importMap')->name('inventory_import_map');
    Route::post('/import/save', 'InventoryController@importSave')->name('inventory_import_save');
    Route::post('/import/caselabel/save', 'InventoryController@importCaseLabelSave')->name('inventory_import_caselabel_save');
    Route::get('/import/caselabel/save/recursive', 'InventoryController@caseLabelSaveRecursive')->name('caselabel_save_recursive');
    Route::get('/import/caselabel/save/recursive/scan', 'InventoryController@caseLabelSaveRecursiveScan')->name('caselabel_save_recursive_scan');
    Route::post('/fields/hide', 'InventoryController@hideInventoryFields')->name('inventory_field_hide');
    Route::post('/filter', 'InventoryController@inventoryFilter')->name('inventory_filter');
    Route::post('/scan', 'InventoryController@scanInventory')->name('inventory_scan');
    Route::post('/delete', 'InventoryController@deleteInventory')->name('inventory_delete');
    Route::get('/details', 'InventoryController@inventoryDetail')->name('inventory_detail');
    Route::post('/addNote', 'InventoryController@addNote')->name('add_inventory_note');
    Route::post('/status/update', 'InventoryController@updateStatus')->name('update_status');
});

Route::group(['prefix' => 'settings',  'middleware' => 'auth'], function() {
    Route::get('/', 'SettingsController@index')->name('settings');
    Route::get('/account', 'SettingsController@index')->name('account_settings');
    Route::get('/inventoryfields', 'SettingsController@inventoryFields')->name('inventory_fields');
    Route::get('/locations', 'SettingsController@locations')->name('locations');
    Route::get('/customorders', 'SettingsController@customOrders')->name('custom_orders_settings');
    Route::post('/updateusersettings', 'SettingsController@updateUser')->name('update_user_settings');
    Route::post('/inventoryfields/save', 'SettingsController@saveInventoryFields')->name('inventory_fields_save');
    Route::post('/locations/save', 'SettingsController@saveLocations')->name('locations_save');
    Route::post('/locations/update', 'SettingsController@updateLocations')->name('locations_update');
    Route::post('/locations/delete', 'SettingsController@deleteLocations')->name('locations_delete');
    Route::post('/customorders/save', 'SettingsController@SaveCustomOrders')->name('custom_orders_settings_save');
    Route::get('/customorders/deleted', 'SettingsController@deleteCustomOrders')->name('custom_orders_settings_delete');
    Route::post('/customorders/update', 'SettingsController@UpdateCustomOrders')->name('custom_orders_settings_update');
    Route::post('/customorders/details', 'SettingsController@getCustomOrderDetails')->name('custom_orders_details');
    Route::get('/notifications', 'SettingsController@notificationSettings')->name('notification_settings');
    Route::post('/notifications/save', 'SettingsController@saveNotificationSettings')->name('notification_settings_save');
    Route::post('/notifications/order/save', 'SettingsController@saveOrderNotificationSettings')->name('notification_order_settings_save');
    Route::get('/skus', 'SettingsController@skus')->name('skus');
    Route::post('/skus/save', 'SettingsController@saveSkus')->name('skus_save');
    Route::post('/skus/toggle', 'SettingsController@toggleSku')->name('skus_toggle');
    Route::post('/skus/update', 'SettingsController@updateSku')->name('skus_update');
    Route::get('/import/caselabels/requiredFields', 'SettingsController@caseLabelRequiredFields')->name('case_label_required');
    Route::post('/import/caselabels/requiredFields/save', 'SettingsController@saveCaseLabelRequiredFields')->name('case_label_required_save');
});

Route::group(['prefix' => 'companies',  'middleware' => 'auth'], function() {
    Route::get('/', 'CompaniesController@index')->name('companies');
    Route::post('/add', 'CompaniesController@addCompany')->name('add_company');
    Route::get('/delete', 'CompaniesController@deleteCompany')->name('delete_company');
    Route::get('/locations', 'CompaniesController@companyLocations')->name('company_locations');
    Route::post('/locations/save', 'CompaniesController@companySaveLocations')->name('company_locations_save');
    Route::post('/locations/update', 'CompaniesController@companyUpdateLocations')->name('company_locations_update');
    Route::post('/locations/delete', 'CompaniesController@companyDeleteLocations')->name('company_locations_delete');
    Route::get('/apikeys', 'CompaniesController@companyManageApiKeys')->name('manage_apikeys');
    Route::get('/apikeys/generate', 'CompaniesController@generateKey')->name('generate_apikeys');
    Route::get('/apikeys/toggle', 'CompaniesController@toggleApiKey')->name('toggle_apikeys');
    Route::post('/fulfillment/save', 'CompaniesController@saveFulfillmentIds')->name('save_fulfillment_ids');
    Route::post('/fulfillment/details', 'CompaniesController@getCompanyFulfillmentDetails')->name('get_fulfillment_ids');
    Route::post('/customer-code/details', 'CompaniesController@getCompanyCustomerCodes')->name('get_depositor_ids');
    Route::post('/customer-code/save', 'CompaniesController@saveCustomerCodes')->name('save_depositor_ids');
    Route::post('/permissions/save', 'CompaniesController@saveCompanyPermissions')->name('save_company_permissions');
    Route::get('/toggle-tracking/{company_id}', 'CompaniesController@toggleEndToEndTracking')->name('toggle_tracking');
});

Route::group(['prefix' => 'users',  'middleware' => 'auth'], function() {
    Route::get('/', 'UsersController@index')->name('users');
    Route::get('/delete', 'UsersController@deleteUser')->name('delete_user');
    Route::get('/sendReset', 'UsersController@sendResetEmail')->name('send_reset_email');
    Route::get('/loginas', 'UsersController@logInAs')->name('login_as');
    Route::post('/add', 'UsersController@addUser')->name('add_user');
    Route::post('/updated', 'UsersController@updateUser')->name('update_user');
    Route::post('/details', 'UsersController@getUserDetails')->name('get_user_details');
    Route::post('/sendHelp', 'UsersController@sendRequestHelp')->name('send_help');
    Route::get('/getLogs', 'UsersController@getUserLogs')->name('get_user_logs');
    Route::get('/attempts/clear', 'UsersController@clearLoginAttempts')->name('clear_login_attempts');
    Route::post('/reactivateuser', 'UsersController@reactivateUser')->name('reactivate_user');
});

Route::group(['prefix' => 'orders',  'middleware' => 'auth'], function() {
    Route::get('/', 'OrdersController@index')->name('orders');
    Route::post('/save', 'OrdersController@saveOrders')->name('orders_save');
    Route::post('/data', 'OrdersController@orderData')->name('orders_details');
    Route::post('/update', 'OrdersController@updateOrders')->name('orders_update');
    Route::post('/fulfill/details', 'OrdersController@getFulfillDetails')->name('fulfillment_details');
    Route::post('/fulfill/item/details', 'OrdersController@getFulfillItemDetails')->name('fulfillment_item_details');
    Route::post('/fulfill/item/scan', 'OrdersController@getFulfillItemScan')->name('fulfillment_item_scan');
    Route::post('/trackingnumber/save', 'OrdersController@updateTrackingNumber')->name('update_tracking_number');
    Route::post('/addNote/save', 'OrdersController@addNote')->name('add_order_note');
    Route::get('/details/', 'OrdersController@getOrderDetails')->name('order_details');
});

Route::group(['prefix' => 'thirdparty/orders',  'middleware' => 'auth'], function() {
    // Route::get('/', 'ThirdPartyOrdersController@index')->name('thirdparty_orders');
    Route::get('/', 'LogiwaOrdersController@orders')->name('thirdparty_orders');
    Route::post('/', 'LogiwaOrdersController@orders')->name('thirdparty_orders_filter');
    Route::get('/create', 'LogiwaOrdersController@createOrder')->name('thirdparty_orders_create');
    // Route::post('/search', 'ThirdPartyOrdersController@searchOrders')->name('thirdparty_orders_search');
    Route::post('/search', 'LogiwaOrdersController@searchOrders')->name('thirdparty_orders_search');
    Route::get('/search', 'LogiwaOrdersController@orders');
    // Route::post('/filter', 'ThirdPartyOrdersController@filterOrders')->name('thirdparty_orders_filter');
    // Route::get('/details/{id}', 'ThirdPartyOrdersController@orderDetails')->name('thirdparty_orders_details');
    Route::get('/details/{id}', 'LogiwaOrdersController@orderDetail')->name('thirdparty_orders_details');
    // Route::get('/cancel/{id}', 'ThirdPartyOrdersController@cancelOrder')->name('thirdparty_orders_cancel');
    Route::get('/manual-fulfill/{id}', 'ThirdPartyOrdersController@manualFulfillOrder')->name('thirdparty_manual_fulfill');
    // Route::get('/create', 'ThirdPartyOrdersController@createOrder')->name('thirdparty_orders_create');
    // Route::post('/create/save', 'ThirdPartyOrdersController@createOrderSave')->name('thirdparty_orders_create_save');
    Route::post('/create/save', 'LogiwaOrdersController@createOrderSave')->name('thirdparty_orders_create_save');
    Route::get('/cancel/{id}', 'LogiwaOrdersController@cancelOrder')->name('thirdparty_orders_cancel');
});

Route::group(['prefix' => 'thirdparty/inventory',  'middleware' => 'auth'], function() {
    Route::get('/detail', 'ThirdPartyInventoryController@index')->name('thirdparty_inventory');
    Route::get('/detail/list', 'ThirdPartyInventoryController@thirdPartyInventoryDetail')->name('thirdparty_inventory_detail_list');
    Route::get('/detail/inventory/export', 'ThirdPartyInventoryController@exportInventory')->name('thirdparty_inventory_export');

    // Route::get('/summary', 'ThirdPartyInventoryController@summary')->name('thirdparty_inventory_summary');
    // Route::get('/summary/list', 'ThirdPartyInventoryController@thirdPartyInventorySummary')->name('thirdparty_inventory_summary_list');
    Route::get('/summary', 'LogiwaInventoryController@summary')->name('thirdparty_inventory_summary');
    Route::get('/summary/list', 'LogiwaInventoryController@thirdPartyInventorySummary')->name('thirdparty_inventory_summary_list');
    Route::get('/summary/export', 'LogiwaInventoryController@exportSummary')->name('thirdparty_summary_export');
    // Route::get('/summary/export', 'ThirdPartyInventoryController@exportSummary')->name('thirdparty_summary_export');
});

Route::group(['prefix' => 'thirdparty/dashboard',  'middleware' => 'auth'], function() {
    Route::get('/', 'ThirdPartyDashboardController@index')->name('thirdparty_dashboard');
});


Route::group(['prefix' => 'thirdparty/report',  'middleware' => 'auth'], function() {
    Route::get('/', 'LogiwaReportController@lineItemReport')->name('thirdparty_report');
    Route::post('/', 'LogiwaReportController@generateLineItemReport')->name('thirdparty_report_generate_lir');
    // Route::get('/', 'ThirdPartyReportController@index')->name('thirdparty_report');
    // Route::post('/', 'ThirdPartyReportController@generateLineItemReport')->name('thirdparty_report_generate_lir');
});

Route::group(['prefix' => 'thirdparty/qualifier/import',  'middleware' => 'auth'], function() {
    Route::get('/', 'ThirdPartyQualifierImportController@index')->name('thirdparty_qualifier_import');
    Route::post('/save', 'ThirdPartyQualifierImportController@import')->name('thirdparty_qualifier_import_save');
    Route::get('/save/api', 'ThirdPartyQualifierImportController@apiImport')->name('thirdparty_qualifier_import_api');
});

Route::group(['prefix' => 'shipping',  'middleware' => 'auth'], function() {
    Route::get('/automation', 'ShippingAutomationController@automationRules')->name('shiping_automation_rules');
    Route::post('/automation/save', 'ShippingAutomationController@automationRulesSave')->name('shiping_automation_rules_save');
    Route::post('/automation/update', 'ShippingAutomationController@automationRulesUpdate')->name('shiping_automation_rules_update');
    Route::get('/automation/delete', 'ShippingAutomationController@automationRulesDelete')->name('shiping_automation_rules_delete');
    Route::get('/automation/details', 'ShippingAutomationController@automationRulesDetails')->name('shiping_automation_rules_details');

    Route::get('/printer', 'ShippingPrinterController@index');
    Route::post('/printer', 'ShippingPrinterController@store')->name('shipping_automation.printer.store');
    Route::post('/printer/patch', 'ShippingPrinterController@patch')->name('shipping_automation.printer.patch');
    Route::post('/printer/remove', 'ShippingPrinterController@remove')->name('shipping_automation.printer.remove');
    Route::get('/shipments', 'ShippingAutomationController@shipments')->name('shipments');
});


Route::group(['prefix' => 'bulk-kit-scan',  'middleware' => 'auth'], function() {
    Route::get('/', 'BulkKitScanController@index')->name('kit_return_syncz');
    Route::get('/getskus', 'BulkKitScanController@getSkus')->name('getSkusz');
    
    Route::post('/validatboxnum', 'BulkKitScanController@validatBoxnum')->name('validatBoxnumz');
    
    Route::post('/add', 'BulkKitScanController@addBatchNum')->name('add_batch_numz');
    Route::get('/openbatches', 'BulkKitScanController@getOpenBatches')->name('getOpenBatchesz');
    Route::post('/addkit', 'BulkKitScanController@addMasterKit')->name('add_master_kit2');
    Route::post('/updatereturntracking', 'BulkKitScanController@updateReturnTracking')->name('updateReturnTracking');
    
    Route::post('/validate', 'KitReturnSyncController@validateMkit')->name('validate');
    Route::post('/editkit', 'KitReturnSyncController@editMasterKit')->name('editMasterKit');
    Route::post('/closebatch', 'KitReturnSyncController@closeBatch')->name('closeBatch');
    Route::post('/validate', 'KitReturnSyncController@validateMkit')->name('validate');
    Route::post('/checkbarcode', 'KitReturnSyncController@checkBarcode')->name('checkbarcode');
    Route::post('/validatesubkit', 'KitReturnSyncController@validateSubKit')->name('validateSubKit');
    Route::post('/validatereturntracking', 'KitReturnSyncController@validaterReturnTracking')->name('validaterReturnTracking');
});

Route::group(['prefix' => 'kit-return-sync',  'middleware' => 'auth'], function() {
    Route::get('/', 'KitReturnSyncController@index')->name('kit_return_sync');
    Route::get('/getskus', 'KitReturnSyncController@getSkus')->name('getSkus');
    Route::post('/getboxnum', 'KitReturnSyncController@getBoxnum')->name('getBoxnum');
    
    Route::post('/validatboxnum', 'KitReturnSyncController@validatBoxnum')->name('validatBoxnum');
    
    Route::post('/add', 'KitReturnSyncController@addBatchNum')->name('add_batch_num');
    Route::post('/delete-batch', 'KitReturnSyncController@deleteBatch')->name('deleteBatch');
    Route::get('/delete-batch-item/{id}', 'KitReturnSyncController@deleteBatchItem')->name('deleteBatchItem');
    Route::post('/addkit', 'KitReturnSyncController@addMasterKit')->name('add_master_kit');
    Route::post('/editkit', 'KitReturnSyncController@editMasterKit')->name('editMasterKit');
    Route::post('/closebatch', 'KitReturnSyncController@closeBatch')->name('closeBatch');
    Route::post('/validate', 'KitReturnSyncController@validateMkit')->name('validate');
    Route::post('/checkbarcode', 'KitReturnSyncController@checkBarcode')->name('checkbarcode');
    Route::post('/validatesubkit', 'KitReturnSyncController@validateSubKit')->name('validateSubKit');
    Route::post('/validatereturntracking', 'KitReturnSyncController@validaterReturnTracking')->name('validaterReturnTracking');
    Route::get('/openbatches', 'KitReturnSyncController@getOpenBatches')->name('getOpenBatches');
    Route::get('/summary', 'KitReturnSyncController@summary')->name('kitReturnSummary');
    Route::get('/summary/{id}', 'KitReturnSyncController@details')->name('kitReturnDetails');
    Route::get('/summary/{id}/export', 'KitReturnSyncController@export')->name('kitReturnExport');
    Route::get('/summary/{id}/create-receiver', 'KitReturnSyncController@createReceiver')->name('kitCreateReceiver');
    Route::get('/logiwa/summary/{id}/export','KitReturnSyncController@exportKitReturnSyncLogiwa')->name('kitReturnExportLogiwa');
    Route::post('/checkexpirtaion', 'KitReturnSyncController@checkExpirtaion')->name('checkexpirtaion');

});

Route::group(['prefix' => 'kit-boxing',  'middleware' => 'auth'], function() {
    Route::get('/', 'KitBoxingController@index')->name('kit_boxing');
    Route::get('/batches', 'KitBoxingController@getBatches')->name('getBatches');
    Route::get('/batchnumber', 'KitBoxingController@getBatchNumber')->name('get_batch_number');
    Route::post('/updateboxid', 'KitBoxingController@updateBoxID')->name('update_box_id');
    Route::post('/validate', 'KitBoxingController@validateMkit')->name('validate');
    Route::post('/completebatch', 'KitBoxingController@completeBatch')->name('completeBatch');
    Route::get('/getcompletebatches', 'KitBoxingController@getCompleteBatches')->name('getCompleteBatches');
});

Route::group(['prefix' => 'ship-pack',  'middleware' => 'auth'], function() {
    Route::get('/', 'ShipPackController@index')->name('ship_pack');
    Route::post('/search', 'ShipPackController@searchByTransactionId')->name('ship_pack_search_by_transaction_id');
    Route::post('/save', 'ShipPackController@saveShipPack')->name('ship_pack_save');
    Route::get('/get-carriers', 'ShipPackController@getCarriers');
    Route::get('/get-services/{code}', 'ShipPackController@getServices');
    Route::get('/recent', 'ShipPackController@recentShipments');
    Route::get('/reprint/{id}', 'ShipPackController@reprintShipment');
    Route::post('/get-shipping-client', 'ShipPackController@getShippingClient');
    Route::post('/get-shiprush-rates', 'ShipPackController@getShipRushRates');
    // Route::get('/test', 'ShipPackController@test');
});


Route::group(['prefix' => 'thirdparty-reallocate-orders',  'middleware' => 'auth'], function() {
    Route::get('/', 'ThirdPartyReallocateController@index')->name('thirdparty_reallocate');
    Route::post('/search', 'ThirdPartyReallocateController@searchByTransactionId')->name('thirdparty_reallocatesearch_by_transaction_id');
    Route::post('/save', 'ThirdPartyReallocateController@saveReallocation')->name('thirdparty_reallocate_save');

});

Route::group(['prefix' => 'create-shipment',  'middleware' => 'auth'], function() {
    Route::get('/', 'ShipmentFinderController@index');
    Route::post('/get-shiprush-rates', 'ShipmentFinderController@getShipRushRates');
    Route::post('/store', 'ShipmentFinderController@store');
});

Route::group(['prefix' => 'shippackage',  'middleware' => 'auth'], function() {
    Route::get('/', 'ShipPackageController@index')->name('shippackage');
    Route::post('/save', 'ShipPackageController@addPackageSize')->name('shippackage_save');
    Route::post('/update', 'ShipPackageController@editPackageSize')->name('shippackage_edit');
    Route::get('/delete/{package_id}', 'ShipPackageController@deletePackageSize')->name('shippackage_delete');
});

Route::group(['prefix' => 'shipper-address',  'middleware' => 'auth'], function() {
    Route::get('/', 'ThirdPartyShipperAddressController@index')->name('shipper');
    Route::post('/save', 'ThirdPartyShipperAddressController@addShipperAddress')->name('shipper_save');
    Route::post('/update', 'ThirdPartyShipperAddressController@editShipperAddress')->name('shipper_edit');
    Route::get('/delete/{shipper_address_id}', 'ThirdPartyShipperAddressController@deleteShipperAddress')->name('shipper_delete');
});

Route::group(['prefix' => 'shipping-clients',  'middleware' => 'auth'], function() {
    Route::get('/', 'ThirdPartyShippingClientController@index')->name('shippingclients');
    Route::post('/store', 'ThirdPartyShippingClientController@store')->name('shippingclients.store');
    Route::post('/patch', 'ThirdPartyShippingClientController@patch')->name('shippingclients.patch');
    Route::post('/remove', 'ThirdPartyShippingClientController@remove')->name('shippingclients.remove');
});

Route::group(['prefix' => 'kit-sku',  'middleware' => 'auth'], function() {
    Route::get('/', 'KitSkuController@index')->name('shipper');
    Route::post('/addsku', 'KitSkuController@addKitSku')->name('add_kitsku');
    Route::post('/update', 'KitSkuController@editKitSku')->name('edit_kitsku');
    Route::get('/delete', 'KitSkuController@deleteKitSku')->name('delete_kitsku');
});

Route::group(['prefix' => 'carriers',  'middleware' => 'auth'], function() {
    Route::get('/', 'CarriersController@index')->name('carriers');
    Route::post('/save', 'CarriersController@addCarrier')->name('carrier_save');
    Route::post('/update', 'CarriersController@editCarrier')->name('carrier_update');
    Route::post('/method/save', 'CarriersController@addCarrierMethod')->name('carrier_method_save');
    Route::post('/method/update', 'CarriersController@editCarrierMethod')->name('carrier_method_update');
    Route::get('/method/delete/{method_id}', 'CarriersController@deleteMethod')->name('carrier_method_delete');
    Route::get('/delete/{carrier_id}', 'CarriersController@deleteCarrier')->name('carrier_delete');
});


Route::get('/admin/import-serials', 'RayTestController@importSerials');
Route::get('/ray/test1', 'RayTestController@test1');

Route::group(['prefix' => 'shopify',  'middleware' => 'auth'], function() {

    Route::get('/', 'ShopifyController@index')->name('shopify');
    Route::post('/addshopifyconfig', 'ShopifyController@addShopifyConfig')->name('add_Shopify');
    Route::post('/editshopifyconfig', 'ShopifyController@editShopifyConfig')->name('edit_Shopify');
    Route::post('/ignored-skus/save', 'ShopifyController@saveIgnoredSkus')->name('shopifySaveIgnoredSkus');
    Route::get('/delete', 'ShopifyController@deleteShopifyConfig')->name('configDelete');
    Route::get('/integrate', 'ShopifyController@integrate')->name('shopifyFullfill');
    Route::get('/fulfill', 'ShopifyController@fullfill')->name('shopifyFullfill');
});

Route::group(['prefix' => 'test',  'middleware' => 'auth'], function() {
    Route::get('/rateshop', 'TestController@testRateShop')->name('testRateShop');
    Route::get('/ship', 'TestController@testAddShipment')->name('testAddShipment');
    Route::get('/shipment', 'TestController@getShipmentInformation')->name('getShipmentInformation');
    Route::get('/logiwa', 'TestController@logiwa')->name('logiwa');
});

Route::group(['prefix' => 'quality-inspector/',  'middleware' => 'auth'], function() {

    Route::get('/', 'LogiwaQualityInspectorController@index')->name('quality_inspector');
    Route::post('/search', 'LogiwaQualityInspectorController@searchQITransactionID')->name('search_TransactionID');
    Route::get('/quality-inspect/{transID}/{item_id}', 'LogiwaQualityInspectorController@quality_inspect')->name('qualityInspect');
    Route::get('/approve/{qi_id}', 'LogiwaQualityInspectorController@approve')->name('approve');
    Route::post('/fail', 'LogiwaQualityInspectorController@fail')->name('fail');
    Route::get('/report', 'LogiwaQualityInspectorController@qiReport')->name('qiReport');
    Route::get('/download', 'LogiwaQualityInspectorController@qiReportDownload')->name('qiReportDownload');
    Route::get('/getSubkitIds', 'LogiwaQualityInspectorController@getSubkitIds')->name('getSubkitIds');

    // Route::get('/', 'QualityInspectorController@index')->name('quality_inspector');
    // Route::post('/search', 'QualityInspectorController@searchQITransactionID')->name('search_TransactionID');
    Route::get('/qualityinspector_details', 'QualityInspectorController@qualityinspector_details')->name('search_TransactionID');
    // Route::get('/getSubkitIds', 'QualityInspectorController@getSubkitIds')->name('getSubkitIds');
    // Route::get('/quality-inspect/{transID}/{item_id}', 'QualityInspectorController@quality_inspect')->name('qualityInspect');
    // Route::get('/approve/{qi_id}', 'QualityInspectorController@approve')->name('approve');
    // Route::post('/fail', 'QualityInspectorController@fail')->name('fail');
    // Route::get('/report', 'QualityInspectorController@qiReport')->name('qiReport');
    // Route::get('/download', 'QualityInspectorController@qiReportDownload')->name('qiReportDownload');

});


// Route::group(['middleware' => ['auth','is-super-admin']], function() {
//     Route::get('/ray/test1', 'RayTestController@test1');
// });
