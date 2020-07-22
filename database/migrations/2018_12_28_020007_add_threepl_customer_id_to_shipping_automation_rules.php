<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddThreeplCustomerIdToShippingAutomationRules extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shipping_automation_rules', function (Blueprint $table) {
            //
        });

        if (Schema::hasTable('shipping_automation_rules')) {
            Schema::table('shipping_automation_rules', function (Blueprint $table) {
                $table->string('threepl_customer_id');
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
        Schema::table('shipping_automation_rules', function (Blueprint $table) {
            //
        });
    }
}
