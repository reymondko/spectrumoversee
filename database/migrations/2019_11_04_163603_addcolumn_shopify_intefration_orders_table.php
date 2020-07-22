<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddcolumnShopifyIntefrationOrdersTable extends Migration
{ 
    public function up()
    {
        if (Schema::hasTable('shopify_integration_orders')) {
            Schema::table('shopify_integration_orders', function (Blueprint $table) {
                $table->string('fulfillment_id')->after('status_shopify')->nullable();;
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
                $table->dropColumn('fulfillment_id');
            });
        }
    }
}
