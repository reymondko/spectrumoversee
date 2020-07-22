<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShopifyIntegrationOrderItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopify_integration_order_items', function (Blueprint $table) {
            $table->increments('id');
            $table->string('shopify_integration_orders_id')->nullable();
            $table->string('shopify_itemorder_id')->nullable();
            $table->string('variant_id')->nullable();
            $table->string('sku')->nullable();
            $table->string('quantity')->nullable();
            $table->string('fulfillment_status')->nullable();
            $table->string('tracking_number')->nullable();
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
        Schema::dropIfExists('shopify_integration_order_items');
    }
}
