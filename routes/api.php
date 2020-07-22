<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});*/


Route::group(['prefix' => 'v1'], function() {
    Route::post('/inventory/all', 'ApiV1Controller@getAllInventory');
    Route::post('/inventory/search/barcode', 'ApiV1Controller@searchByBarcode');
    Route::post('/inventory/search/custom', 'ApiV1Controller@searchByCustom');
    Route::post('/inventory/detail', 'ApiV1Controller@getInventoryDetail');
    Route::post('/orders/all', 'ApiV1Controller@getOrders');
    Route::post('/inventory/location/total', 'ApiV1Controller@getInventoryTotalByLocation');
    Route::post('/location/list', 'ApiV1Controller@getAllLocation');
    Route::post('/inventory/location/all', 'ApiV1Controller@getAllLocationWithTotalInventory');

    Route::post('/ship/trackingstatus', 'ApiV1Controller@getTrackStatus');

    Route::post('/kitboxing/subkits/all', 'ApiV1Controller@getSubKits');
    Route::post('/kitboxing/logiwa/subkits/all', 'ApiV1Controller@getLogiwaSubKits');

});

Route::group(['prefix' => '3pl'], function() {
    Route::post('/orders', 'ApiProxyController@createOrder');
    Route::get('/orders/{id}', 'ApiProxyController@retrieveOrder');
    Route::get('/orders', 'ApiProxyController@retrieveOrders');
    Route::get('/orders/{id}/items', 'ApiProxyController@retrieveOrderItems');
    Route::post('/orders/{id}/confirmer', 'ApiProxyController@confirmOrder');
});

Route::group(['prefix' => 'logiwa','middleware'=>['spectrum_api']], function() {
    Route::post('/create/inventory', 'LogiwaApiProxyController@insertInventoryItem');
    Route::post('/create/order', 'LogiwaApiProxyController@insertShipmentOrder');
    Route::post('/get/report/inventory', 'LogiwaApiProxyController@getListInventoryReport');
    Route::post('/get/report/consolidated-inventory', 'LogiwaApiProxyController@getConsolidatedInventoryReport');
    Route::post('/search/shipment-info', 'LogiwaApiProxyController@getShipmentInfoSearch');
    Route::post('/search/all/receipt', 'LogiwaApiProxyController@getReceiptAllSearch');
    Route::post('/search/purchase-order', 'LogiwaApiProxyController@getPurchaseOrderSearch');
    Route::post('/search/warehouse-order', 'LogiwaApiProxyController@getWarehouseOrderSearch');
    Route::post('/search/shipment-order/serial', 'LogiwaApiProxyController@getShipmentInfoSerialSearch');
    Route::post('/get/report/shipment', 'LogiwaApiProxyController@getShipmentReportAllSearch');
    Route::post('/cancel/order', 'LogiwaApiProxyController@cancelOrder');
});


Route::group(['prefix' => 'logiwa-3pl','middleware'=>['spectrum_api']], function() {
    Route::post('/create/order', 'Logiwa3plBridgeController@createOrder');
    Route::get('/orders', 'Logiwa3plBridgeController@getOrders');
    Route::get('/orders/{order_id}', 'Logiwa3plBridgeController@getOrderById');
    Route::get('/orders/{order_id}/items', 'Logiwa3plBridgeController@getOrderItemsByOrderId');
    Route::get('/orders/{order_id}/canceler', 'Logiwa3plBridgeController@cancelOrderByOrderId');
});
