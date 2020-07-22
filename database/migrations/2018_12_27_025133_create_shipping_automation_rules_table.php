<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShippingAutomationRulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shipping_automation_rules', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('companies_id');
            $table->string('name');
            $table->string('shipping_carriers_domestic');
            $table->string('shipping_carriers_international');
            $table->string('automation_method');
            $table->integer('min_shipping_days');
            $table->integer('max_shipping_days');
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
        Schema::dropIfExists('shipping_automation_rules');
    }
}
