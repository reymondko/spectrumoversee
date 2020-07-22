<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLogiwaFieldsToShopifyIntegrationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('shopify_integrations')) {
            Schema::table('shopify_integrations', function (Blueprint $table) {
                $table->integer('logiwa_depositor_id')->nullable();
                $table->string('logiwa_depositor_code')->nullable();
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
        Schema::table('shopify_integrations', function (Blueprint $table) {
            //
        });
    }
}
