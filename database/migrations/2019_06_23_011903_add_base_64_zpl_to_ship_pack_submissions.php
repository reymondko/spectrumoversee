<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBase64ZplToShipPackSubmissions extends Migration
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
                $table->text('base_64_zpl')->nullable();
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
        Schema::table('ship_pack_submissions', function (Blueprint $table) {
            //
        });
    }
}
