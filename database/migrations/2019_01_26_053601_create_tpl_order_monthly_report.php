<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTplOrderMonthlyReport extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tpl_order_monthly_report', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('companies_id');
            $table->integer('year');
            $table->string('month');
            $table->integer('orders_received');
            $table->integer('orders_completed');
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
        Schema::dropIfExists('tpl_order_monthly_report');
    }
}
