<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInventoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id');
            $table->string('barcode_id',100)->nullable();
            $table->string('custom_field1',100)->nullable();
            $table->string('custom_field2',100)->nullable();
            $table->string('custom_field3',100)->nullable();
            $table->string('custom_field4',100)->nullable();
            $table->string('custom_field5',100)->nullable();
            $table->string('custom_field6',100)->nullable();
            $table->string('custom_field7',100)->nullable();
            $table->string('custom_field8',100)->nullable();
            $table->string('custom_field9',100)->nullable();
            $table->string('custom_field10',100)->nullable();
            $table->string('custom_field11',100)->nullable();
            $table->string('custom_field12',100)->nullable();
            $table->string('custom_field13',100)->nullable();
            $table->string('custom_field14',100)->nullable();
            $table->string('custom_field15',100)->nullable();
            $table->string('custom_field16',100)->nullable();
            $table->string('custom_field17',100)->nullable();
            $table->string('custom_field18',100)->nullable();
            $table->string('custom_field19',100)->nullable();
            $table->string('custom_field20',100)->nullable();
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
        Schema::dropIfExists('inventory');
    }
}
