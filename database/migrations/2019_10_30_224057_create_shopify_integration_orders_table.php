<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShopifyIntegrationOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopify_integration_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->string('shopify_integration_id')->nullable();
            $table->string('shopify_order_id')->nullable();
            $table->text('shopify_order_data')->nullable();
            $table->string('tpl_order_id')->nullable();
            $table->string('order_date')->nullable();
            $table->string('status_shopify')->nullable();
            $table->string('status_3pl')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shopify_integration_orders');
    }
}
