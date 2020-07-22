<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddcolumnBatchesTablePt2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('batches')) {
            Schema::table('batches', function (Blueprint $table) {
                $table->string('expiration_date')->after('receiver_id')->nullable();
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
        if (Schema::hasTable('batches')) {
            Schema::table('batches', function (Blueprint $table) {
                $table->string('expiration_date');
            });
        }
    }
}
