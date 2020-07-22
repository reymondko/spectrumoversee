<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShopifyIntegrationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopify_integrations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('companies_id')->nullable();
            $table->string('shopify_url')->nullable();
            $table->string('shopify_api_key')->nullable();
            $table->string('shopify_password')->nullable();
            $table->decimal('pull_orders_delay', 10, 6)->nullable();
            $table->integer('integration_status')->nullable()->default(1);
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
        Schema::dropIfExists('shopify_integrations');
    }
}
