<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnSkusPart3 extends Migration
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
                $table->string('multi_barcode_count')->after('box_limit')->default(0)->nullable();
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
                $table->dropColumn('multi_barcode_count');
            });
        }
    }
}
