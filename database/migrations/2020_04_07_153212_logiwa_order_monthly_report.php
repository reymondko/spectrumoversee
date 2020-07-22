<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class LogiwaOrderMonthlyReport extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('logiwa_order_monthly_report', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('companies_id');
            $table->string('depositor_code')->nullable();
            $table->integer('depositor_id')->nullable();
            $table->integer('year')->nullable();
            $table->string('month')->nullable();
            $table->mediumText('data')->nullable();
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
        //
    }
}
