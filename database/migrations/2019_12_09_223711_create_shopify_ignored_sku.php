<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShopifyIgnoredSku extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopify_ignored_sku', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('shopify_integration_id')->nullable();
            $table->integer('companies_id')->nullable();
            $table->text('skus')->nullable();
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
        Schema::dropIfExists('shopify_ignored_sku');
    }
}
