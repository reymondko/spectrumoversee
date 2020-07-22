<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterShipPackSubmissions extends Migration
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
              $table->string('shipto_name', 200)->nullable();
              $table->string('shipto_address', 200)->nullable();
              $table->string('shipto_zip', 200)->nullable();
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
