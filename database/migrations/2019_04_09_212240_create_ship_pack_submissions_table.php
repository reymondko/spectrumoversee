<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShipPackSubmissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ship_pack_submissions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('companies_id');
            $table->text('ship_package_data');
            $table->string('carrier')->nullable();
            $table->string('carrier_service')->nullable();
            $table->string('carrier_service_id')->nullable();
            $table->integer('tpl_order_id');
            $table->integer('tpl_customer_id');
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
        Schema::dropIfExists('ship_pack_submissions');
    }
}
