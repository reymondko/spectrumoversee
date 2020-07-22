<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddcolumnSkusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('skus')) {
            Schema::table('skus', function (Blueprint $table) {
                $table->string('requires_expiration_date')->after('multi_barcode')->default(0)->nullable();
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
        if (Schema::hasTable('skus')) {
            Schema::table('skus', function (Blueprint $table) {
                $table->dropColumn('requires_expiration_date');
            });
        }
    }
}
