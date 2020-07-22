<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyTplOrderMonthlyReportTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tpl_order_monthly_report')) {
            Schema::table('tpl_order_monthly_report', function (Blueprint $table) {
                $table->dropColumn('orders_received');
                $table->dropColumn('orders_completed');
                $table->mediumText('data');
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
        //
    }
}
