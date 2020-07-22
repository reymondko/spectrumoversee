<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLogiwaDepositorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('logiwa_depositor', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('companies_id')->nullable();
            $table->string('logiwa_depositor_id')->nullable();
            $table->string('logiwa_depositor_code')->nullable();
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
