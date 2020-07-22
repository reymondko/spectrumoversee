<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnSkusPart4 extends Migration
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
                $table->string('bulk_count')->after('multi_barcode_count')->default(0)->nullable();
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
                $table->dropColumn('bulk_count');
            });
        }
    }
}
