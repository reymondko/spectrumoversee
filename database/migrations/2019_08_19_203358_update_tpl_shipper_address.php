<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateTplShipperAddress extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tpl_shipper_address')) {
            Schema::table('tpl_shipper_address', function (Blueprint $table) {
                $table->integer('zip')->nullable()->after('phone_number');
                $table->string('account_number')->after('zip')->nullable();
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
