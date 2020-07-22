<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTplShipperAddressColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tpl_shipper_address', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('tpl_customer_id');
            $table->text('first_name');
            $table->text('last_name');
            $table->text('address');
            $table->text('city');
            $table->text('state');
            $table->text('country');
            $table->text('postal_code');
            $table->text('phone_number');
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
        Schema::dropIfExists('tpl_shipper_address_column');
    }
}
