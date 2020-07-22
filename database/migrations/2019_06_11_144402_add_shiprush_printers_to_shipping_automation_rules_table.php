<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddShiprushPrintersToShippingAutomationRulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('shipping_automation_rules')) {
            Schema::table('shipping_automation_rules', function (Blueprint $table) {
                $table->text('shiprush_printers')->nullable()->after('shipping_carriers_international');
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
        if (Schema::hasTable('shipping_automation_rules')) {
            Schema::table('shipping_automation_rules', function (Blueprint $table) {
                $table->dropColumn('shiprush_printers');
            });
        }
    }
}
