<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableShipPackSubmissions extends Migration
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
              $table->double('shipping_cost_with_markup', 6, 2)->nullable()->after('shipping_cost');
              $table->integer('shipping_vendor_id')->default(1);
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
