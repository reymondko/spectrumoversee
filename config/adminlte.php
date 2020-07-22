<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Title
    |--------------------------------------------------------------------------
    |
    | The default title of your admin panel, this goes into the title tag
    | of your page. You can override it per page with the title section.
    | You can optionally also specify a title prefix and/or postfix.
    |
    */

    'title' => 'SPECTRUM OVERSEE',

    'title_prefix' => '',

    'title_postfix' => '',

    /*
    |--------------------------------------------------------------------------
    | Logo
    |--------------------------------------------------------------------------
    |
    | This logo is displayed at the upper left corner of your admin panel.
    | You can use basic HTML here if you want. The logo has also a mini
    | variant, used for the mini side bar. Make it 3 letters or so
    |
    */

    'logo' => '<span class="sidebar-title-1">SPECTRUM</span><span class="sidebar-title-2">OVERSEE</span>',

    'logo_mini' => '<b>A</b>LT',

    /*
    |--------------------------------------------------------------------------
    | Skin Color
    |--------------------------------------------------------------------------
    |
    | Choose a skin color for your admin panel. The available skin colors:
    | blue, black, purple, yellow, red, and green. Each skin also has a
    | ligth variant: blue-light, purple-light, purple-light, etc.
    |
    */

    'skin' => 'blue',

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    |
    | Choose a layout for your admin panel. The available layout options:
    | null, 'boxed', 'fixed', 'top-nav'. null is the default, top-nav
    | removes the sidebar and places your menu in the top navbar
    |
    */

    'layout' => null,

    /*
    |--------------------------------------------------------------------------
    | Collapse Sidebar
    |--------------------------------------------------------------------------
    |
    | Here we choose and option to be able to start with a collapsed side
    | bar. To adjust your sidebar layout simply set this  either true
    | this is compatible with layouts except top-nav layout option
    |
    */

    'collapse_sidebar' => false,

    /*
    |--------------------------------------------------------------------------
    | URLs
    |--------------------------------------------------------------------------
    |
    | Register here your dashboard, logout, login and register URLs. The
    | logout URL automatically sends a POST request in Laravel 5.3 or higher.
    | You can set the request to a GET or POST with logout_method.
    | Set register_url to null if you don't want a register link.
    |
    */

    'dashboard_url' => 'home',

    'logout_url' => 'logout',

    'logout_method' => null,

    'login_url' => 'login',

    'register_url' => 'register',

    /*
    |--------------------------------------------------------------------------
    | Menu Items
    |--------------------------------------------------------------------------
    |
    | Specify your menu items to display in the left sidebar. Each menu item
    | should have a text and and a URL. You can also specify an icon from
    | Font Awesome. A string instead of an array represents a header in sidebar
    | layout. The 'can' is a filter on Laravel's built in Gate functionality.
    |
    */
    'menu' => [
        [
            'header' => 'NOTIFICATIONS',
            'can' => 'company-only'
        ],
        [
            'text'        => 'NOTIFICATIONS',
            'can' => 'company-only',
            'url'         => '#showNotifications',
            'icon'        => 'bell',
            'can' => 'company-only',
            'label' => '',
            'label_color' => 'notifications_label'
        ],
        ' ',

            [
                'text' => 'Dashboard',
                'url'  => 'dashboard',
                'icon' => 'columns',
                //'can' => 'admin-only'
            ],
            /*[
                'text' => 'Reports',
                'url'  => 'reports',
                'icon' => 'clipboard',
                'can' => 'admin-only'
            ],*/
            [
                'text' => 'KPI Report',
                'url'  => 'kpi/report',
                'icon' => 'clipboard',
                'can' => 'admin-only'
            ],
            [
                'text' => 'Return Label Report',
                'url'  => 'reports/return-label',
                'icon' => 'clipboard',
                'can' => 'admin-only'
            ],
            [
                'text' => 'Companies',
                'url'  => 'companies',
                'icon' => 'building',
                'can' => 'admin-only'
            ],
            [
                'text' => 'Users',
                'url'  => 'users',
                'icon' => 'users',
                'can' => 'admin-only'
            ],
            [
                'text' => 'Settings',
                'url'  => 'settings',
                'icon' => 'cog',
                'can' => 'admin-only'
            ],
            [
                'text' => 'Kit Assembly',
                'icon' => 'truck',
                'url'  => 'thirdparty',
                'can' => 'admin-only',
                'submenu' => [
                    [
                        'text' => 'Kit SKUs',
                        'url'  => 'kit-sku',
                        'icon' => 'barcode',
                        'can' => 'admin-only'
                    ],
                    [
                        'text' => 'Kit Sync Report',
                        'url'  => 'kit-return-sync/summary',
                        'icon' => 'barcode',
                        'can' => 'admin-only'
                    ],
                    [
                        'text' => 'Kit Sync Scan',
                        'url'  => 'kit-return-sync',
                        'icon' => 'barcode',
                        'can' => 'admin-only',
                    ],
                    [
                        'text' => 'Kit Bulk Scan',
                        'url'  => 'bulk-kit-scan',
                        'icon' => 'barcode',
                        'can' => 'admin-only',
                    ],
                    /*[
                        'text' => 'Kit Boxing',
                        'url'  => 'kit-boxing',
                        'icon' => 'archive',
                        'can' => 'admin-only',
                    ],*/
                ]
            ],
            [
                'text' => 'Kit Assembly',
                'icon' => 'truck',
                'url'  => 'thirdparty',
                'can' => 'can_see_kit_return_sync',
                'submenu' => [
                    [
                        'text' => 'Kit SKUs',
                        'url'  => 'kit-sku',
                        'icon' => 'barcode',
                        'can' => 'can_see_kit_return_sync'
                    ],
                    [
                        'text' => 'Kit Sync Report',
                        'url'  => 'kit-return-sync/summary',
                        'icon' => 'barcode',
                        'can' => 'can_see_kit_return_sync'
                    ],
                    [
                        'text' => 'Kit Sync Scan',
                        'url'  => 'kit-return-sync',
                        'icon' => 'barcode',
                        'can' => 'can_see_kit_return_sync',
                    ],
                    [
                        'text' => 'Kit Bulk Scan',
                        'url'  => 'bulk-kit-scan',
                        'icon' => 'barcode',
                        'can' => 'can_see_kit_return_sync',
                    ],
                    /*[
                        'text' => 'Kit Boxing',
                        'url'  => 'kit-boxing',
                        'icon' => 'archive',
                        'can' => 'admin-only',
                    ],*/
                ]
            ],

            [
                'text' => 'Inventory',
                'url'  => 'inventory',
                'icon' => 'archive',
                'can' => 'can_see_inventory'
            ],

            [
                'text' => 'Warehouse',
                'icon' => 'truck',
                'url'  => 'thirdparty',
                'can' => 'company-with-third-party',
                'submenu' => [
                    [
                        'text' => 'Dashboard',
                        'url'  => 'thirdparty/dashboard',
                        'icon' => 'file-text',
                        'can' => 'company-with-third-party',
                    ],
                    // [
                    //     'text' => 'Inventory Detail Report',
                    //     'url'  => 'thirdparty/inventory/detail',
                    //     'icon' => 'file-text',
                    //     'can' => 'company-with-third-party',
                    // ],
                    [
                        'text' => 'Inventory Summary Report',
                        'url'  => 'thirdparty/inventory/summary',
                        'icon' => 'file-text',
                        'can' => 'company-with-third-party',
                    ],
                    [
                        'text' => 'Orders',
                        'url'  => 'thirdparty/orders',
                        'icon' => 'file-text',
                        'can' => 'company-with-third-party',
                    ],

                    [
                        'text' => 'Line Item Report',
                        'url'  => 'thirdparty/report',
                        'icon' => 'file-text',
                        'can' => 'company-with-third-party',
                    ],
                ]
            ],

            [
                'text' => 'Reports',
                'url'  => 'reports',
                'icon' => 'clipboard',
                'can' => 'company-only'
            ],
            [
                'text' => 'KPI Report',
                'url'  => 'kpi/report',
                'icon' => 'clipboard',
                'can' => 'can_see_kpi_report'
            ],
            // [
            //     'text' => 'Kit SKUs',
            //     'url'  => 'kit-sku',
            //     'icon' => 'barcode',
            //     'can' => 'can_see_kit_sync_report'
            // ],
            // [
            //     'text' => 'Kit Sync Report',
            //     'url'  => 'kit-return-sync/summary',
            //     'icon' => 'barcode',
            //     'can' => 'can_see_kit_sync_report'
            // ],
            // [
            //     'text' => 'Kit Return Sync',
            //     'url'  => 'kit-return-sync',
            //     'icon' => 'barcode',
            //     'can' => 'can_see_kit_return_sync',
            // ],
            // [
            //     'text' => 'Kit Boxing',
            //     'url'  => 'kit-boxing',
            //     'icon' => 'archive',
            //     'can' => 'can_see_kit_boxing',
            // ],
            [
                'text' => 'Users',
                'url'  => 'users',
                'icon' => 'users',
                'can' => 'company-only'
            ],

            [
                'text' => 'Settings',
                'url'  => 'settings',
                'icon' => 'cog',
                'can' => 'company-only',
                'submenu' => [
                    [
                        'text' => 'Account Settings',
                        'url'  => 'settings/account',
                        'icon' => 'wrench',
                        'can' => 'company-only'
                    ],
                    [
                        'text' => 'Inventory Fields',
                        'url'  => 'settings/inventoryfields',
                        'icon' => 'wrench',
                        'can' => 'company-only'
                    ],
                    [
                        'text' => 'Locations',
                        'url'  => 'settings/locations',
                        'icon' => 'wrench',
                        'can' => 'company-only'
                    ],
                    [
                        'text' => 'Custom Order Page',
                        'url'  => 'settings/customorders',
                        'icon' => 'wrench',
                        'can' => 'company-only'
                    ],
                    [
                        'text' => 'Notification',
                        'url'  => 'settings/notifications',
                        'icon' => 'wrench',
                        'can' => 'company-only'
                    ],
                    [
                        'text' => 'SKUs',
                        'url'  => 'settings/skus',
                        'icon' => 'wrench',
                        'can' => 'company-only'
                    ],
                    [
                        'text' => 'Import Case Required Fields',
                        'url'  => 'settings/import/caselabels/requiredFields',
                        'icon' => 'wrench',
                        'can' => 'company-only'
                    ],
                ]
            ],

        [
            'text' => 'Orders',
            'url'  => 'orders',
            'icon' => 'file',
            'can' => 'can_see_orders'
        ],
        [
            'text' => 'Quality Inspector',
            'url'  => 'quality-inspector',
            'icon' => 'archive',
            'can' => 'can_quality_inspector',
        ],

        [
            'text' => 'Reallocate Order Items',
            'url'  => 'thirdparty-reallocate-orders',
            'icon' => 'refresh',
            'can' => 'can_see_reallocate',
        ],
        [
            'text' => 'Warehouse Orders',
            'url'  => 'thirdparty/orders',
            'icon' => 'file-text',
            'can' => 'can_see_third_party_orders',
        ],
        [
            'text' => 'Reports',
            'url'  => 'reports',
            'icon' => 'clipboard',
            'can' => 'user-only'
        ],
        [
            'text' => 'Users',
            'url'  => 'users',
            'icon' => 'users',
            'can' => 'can_login_as'
        ],
        [
            'text' => 'Settings',
            'url'  => 'settings',
            'icon' => 'cog',
            'can' => 'user-only'
        ],
        /****************/


        /*[
            'text' => 'Line Item Report',
            'url'  => 'thirdparty/report',
            'icon' => 'clipboard',
            'can' => 'tpl-only',
        ],
        [
            'text' => 'Inventory Detail Report',
            'url'  => 'thirdparty/inventory/detail',
            'icon' => 'clipboard',
            'can' => 'tpl-only',
        ],
        [
            'text' => 'Inventory Summary Report',
            'url'  => 'thirdparty/inventory/summary',
            'icon' => 'clipboard',
            'can' => 'tpl-only',
        ],*/
        /*[
            'text' => 'Settings',
            'url'  => 'settings',
            'icon' => 'cog',
            'can' => 'tpl-only',
            'submenu' => [
                [
                    'text' => 'Account Settings',
                    'url'  => 'settings/account',
                    'icon' => 'wrench',
                    'can' => 'tpl-only'
                ],
            ]
        ],*/

        [
            'text' => 'Logout',
            'url'  => 'logout',
            'icon' => 'sign-out',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Menu Filters
    |--------------------------------------------------------------------------
    |
    | Choose what filters you want to include for rendering the menu.
    | You can add your own filters to this array after you've created them.
    | You can comment out the GateFilter if you don't want to use Laravel's
    | built in Gate functionality
    |
    */

    'filters' => [
        JeroenNoten\LaravelAdminLte\Menu\Filters\HrefFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ActiveFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\SubmenuFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ClassesFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\GateFilter::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugins Initialization
    |--------------------------------------------------------------------------
    |
    | Choose which JavaScript plugins should be included. At this moment,
    | only DataTables is supported as a plugin. Set the value to true
    | to include the JavaScript file from a CDN via a script tag.
    |
    */

    'plugins' => [
        'datatables' => true,
        'select2'    => true,
        'chartjs'    => true,
    ],
];
