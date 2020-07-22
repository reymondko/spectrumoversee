<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Libraries\ShipCaddie\ShipCaddie;
use App\Libraries\Logiwa\LogiwaAPI;

class TestController extends Controller
{
    public function testRateShop(){
        if (Gate::allows('admin-only', auth()->user())) {
            $sc = new ShipCaddie;
            $carrierContractIds = $sc->getCarriersContractId(['USPS']);
            $shippingData = [
                'from_address1' => '2600 Executive Pkwy #160',
                'from_city' => 'Lehi',
                'from_state' => 'UT',
                'from_zip' => '84043',
                'from_phone' => '(888) 797-0929',
                'to_address1' => '2600 Executive Pkwy #160',
                'to_city' => 'Lehi',
                'to_state' => 'UT',
                'to_zip' => '84043',
                'to_phone' => '(888) 797-0929',
                'packages' => [
                    [
                        'weight' => 2.5,
                        'length' => 2,
                        'width' => 2,
                        'height' => 2,
                    ],
                    [
                        'weight' => 2.5,
                        'length' => 3,
                        'width' => 3,
                        'height' => 3,
                    ]
                ]
            ];
            $rates = $sc->getRates($shippingData,$carrierContractIds);
            var_dump($rates);
        }else{
            return redirect()->route('dashboard');
        }
    }

    public function testAddShipment(){
        if (Gate::allows('admin-only', auth()->user())) {
            $sc = new ShipCaddie;
            $shippingData = [
                'from_address1' => '5795 S RIDGE CREEK CIR',
                'from_city' => 'MURRAY',
                'from_state' => 'UT',
                'from_zip' => '84107',
                'from_phone' => '(888) 797-0929',
                'to_address1' => '5795 S RIDGE CREEK CIR',
                'to_city' => 'MURRAY',
                'to_state' => 'UT',
                'to_zip' => '84107',
                'to_phone' => '(888) 797-0929',
                'carrier_client_contract_id' => 4825,
                'carrier_service_level_id' => 1124,
                'attention_of' => 'TEST',
                'packages' => [
                    [
                        'weight' => 2.5,
                        'length' => 2,
                        'width' => 2,
                        'height' => 2,
                    ],
                    [
                        'weight' => 2.5,
                        'length' => 3,
                        'width' => 3,
                        'height' => 3,
                    ]
                ]
            ];
            $shipment = $sc->addShipCaddieShipment($shippingData);
        }else{
            return redirect()->route('dashboard');
        }
    }

    public function getShipmentInformation(){
        if (Gate::allows('admin-only', auth()->user())) {
            $sc = new ShipCaddie;
            $shipment = $sc->getShipmentInformation(5096810);
        }else{
            return redirect()->route('dashboard');
        }
    }

    public function logiwa(){
        $logiwaAPI = new LogiwaAPI;

        // Test Insert Inventory Request
        /*
        $requestBody = [
            [
                'client'=>'Spectrum Solutions',
                'upc'=>'ARBZ TEST',
                'description'=>'ARBZ TEST DESCRIPTION',
                'consumer_unit'=>'EA',
                'pack_unit'=>'EA',
                'pack_quantity'=>1,
                'item_type'=>'Inventory',
            ],
            [
                'client'=>'Spectrum Solutions',
                'upc'=>'ARBZ 222',
                'description'=>'ARBZ TEST DESCRIPTION',
                'consumer_unit'=>'EA',
                'pack_unit'=>'EA',
                'pack_quantity'=>1,
                'item_type'=>'Inventory',
            ]
        ];
        $result = $logiwaAPI->insertInventoryItem($requestBody);
         */

        //  $requestBody = [
        //     [
        //         'code'=>'TESTORDER6',
        //         'depositor'=>'Spectrum Solutions',
        //         'inventory_site'=>'Spectrum Solutions',
        //         'warehouse'=>'Spectrum Solutions',
        //         'warehouse_order_type'=>'Customer Order',
        //         'warehouse_order_status'=>'Entered',
        //         'customer'=>'Jennas Test',
        //         'customer_address'=>'1 Test Ave',
        //         'order_date'=>'01.22.2019 06:01:02',
        //         'state'=>'WA',
        //         'country'=>'US',
        //         'city'=>'Seattle',
        //         'postal_code'=>'98999',
        //         'phone'=>'800-000-0000',
        //         'adress_text'=>'1 tEST Ave',
        //         'party_adress_type'=>'Residential',
        //         'planned_ship_date'=>'04.10.2019 04:47:22',
        //         'total_sales_gross_price'=>'123',
        //         'channel'=>'Amazon',
        //         'carrier'=>'',
        //         'notes'=>'',
        //         'details' => [
        //             'inventory_item'=>'TEST1',
        //             'inventory_item_pack_type'=>'EA',
        //             'planned_pack_quantity'=>'1',
        //         ]
        //     ],
        //     [
        //         'code'=>'TESTORDER8',
        //         'depositor'=>'Spectrum Solutions',
        //         'inventory_site'=>'Spectrum Solutions',
        //         'warehouse'=>'Spectrum Solutions',
        //         'warehouse_order_type'=>'Customer Order',
        //         'warehouse_order_status'=>'Entered',
        //         'customer'=>'Jennas Test',
        //         'customer_address'=>'1 Test Ave',
        //         'order_date'=>'01.22.2019 06:01:02',
        //         'state'=>'WA',
        //         'country'=>'US',
        //         'city'=>'Seattle',
        //         'postal_code'=>'98999',
        //         'phone'=>'800-000-0000',
        //         'adress_text'=>'1 tEST Ave',
        //         'party_adress_type'=>'Residential',
        //         'planned_ship_date'=>'04.10.2019 04:47:22',
        //         'total_sales_gross_price'=>'123',
        //         'channel'=>'Amazon',
        //         'carrier'=>'',
        //         'notes'=>'',
        //         'details' => [
        //             'inventory_item'=>'TEST1',
        //             'inventory_item_pack_type'=>'EA',
        //             'planned_pack_quantity'=>'1',
        //         ]
        //     ]
        // ];

        // Test Search
        $requestBody = [
            'warehouse_id' => 275
        ];

        $result = $logiwaAPI->getWarehouseOrderSearch($requestBody);
        var_dump($result);


        
        
    }
}
