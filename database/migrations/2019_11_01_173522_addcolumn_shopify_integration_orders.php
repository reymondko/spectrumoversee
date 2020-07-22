<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddcolumnShopifyIntegrationOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('shopify_integration_orders')) {
            Schema::table('shopify_integration_orders', function (Blueprint $table) {
                $table->string('tpl_tracking_number', 100)->after('status_3pl')->nullable();;
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
                $table->dropColumn('tpl_tracking_number');
            });
        }
    }
}
