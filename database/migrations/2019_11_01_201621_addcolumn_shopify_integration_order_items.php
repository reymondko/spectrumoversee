<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddcolumnShopifyIntegrationOrderItems extends Migration
{
    public function up()
    {
        if (Schema::hasTable('shopify_integration_order_items')) {
            Schema::table('shopify_integration_order_items', function (Blueprint $table) {
                $table->string('fulfillment_id')->after('quantity')->nullable();;
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
        if (Schema::hasTable('shopify_integration_order_items')) {
            Schema::table('shopify_integration_order_items', function (Blueprint $table) {
                $table->dropColumn('fulfillment_id');
            });
        }
    }
}
