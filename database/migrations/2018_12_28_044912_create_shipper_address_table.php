<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShipperAddressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shipper_address', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('companies_id');
            $table->string('FirstName');
            $table->string('LastName');
            $table->string('Address1');
            $table->string('City');
            $table->string('State');
            $table->string('Country');
            $table->string('PostalCode');
            $table->string('PhoneNumber');
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
        Schema::dropIfExists('shipper_address');
    }
}
