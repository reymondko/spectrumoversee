<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterInventoryLastScanLocationId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      if (Schema::hasTable('inventory')) {
          Schema::table('inventory', function (Blueprint $table) {
              $table->integer('last_scan_location_id')->after('last_scan_location')->nullable();
          });
          Schema::table('inventory_item_scans', function (Blueprint $table) {
              $table->integer('location_id')->after('scanned_location')->nullable();
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
