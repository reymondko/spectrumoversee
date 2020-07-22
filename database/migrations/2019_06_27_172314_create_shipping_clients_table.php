<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShippingClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shipping_clients', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->bigInteger('tpl_client_id')->nullable();
            $table->float('shipping_markup')->nullable();
            $table->boolean('require_scan_serial_number')->default(false);
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
        Schema::dropIfExists('shipping_clients');
    }
}
