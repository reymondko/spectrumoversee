<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableShipPackShipments extends Migration
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
              $table->string('tracking_number', 100)->nullable();
              $table->float('shipping_cost', 10,2)->nullable();
              $table->float('weight', 5,2)->nullable();
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
      if (Schema::hasTable('ship_pack_submissions')) {
          Schema::table('ship_pack_submissions', function (Blueprint $table) {
              $table->dropColumn('tracking_number');
              $table->dropColumn('shipping_cost');
              $table->dropColumn('weight');
          });
      }
    }
}
