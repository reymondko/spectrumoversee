<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterShipmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      if (Schema::hasTable('ship_pack_submissions')) {
          Schema::table('ship_pack_submissions', function (Blueprint $table) {
              $table->dateTime('order_created_date')->nullable();
              $table->string('reference_number', 50)->nullable();
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
