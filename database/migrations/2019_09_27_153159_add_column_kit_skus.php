<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnKitSkus extends Migration
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
                $table->integer('multi_barcode')->after('sku')->default('0');;
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
                $table->dropColumn('multi_barcode');
            });
        }
    }
}
