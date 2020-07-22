<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddcolumnShopifyIntegrationOrders2Table extends Migration
{

    public function up()
    {
        if (Schema::hasTable('shopify_integration_orders')) {
            Schema::table('shopify_integration_orders', function (Blueprint $table) {
                $table->string('shopify_internal_order_id', 25)->after('shopify_order_id')->nullable();;
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('shopify_integration_orders')) {
            Schema::table('shopify_integration_orders', function (Blueprint $table) {
                $table->dropColumn('shopify_internal_order_id');
            });
        }
    }
}
