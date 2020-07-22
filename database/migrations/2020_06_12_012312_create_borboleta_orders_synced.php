<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBorboletaOrdersSynced extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('borboleta_orders_synced', function (Blueprint $table) {
            $table->increments('id');
            $table->string('order_id', 100);
            $table->timestamps();
            $table->index(['order_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('borboleta_orders_synced');
    }
}
