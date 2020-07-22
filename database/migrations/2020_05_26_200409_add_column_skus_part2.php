<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnSkusPart2 extends Migration
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
                $table->string('box_limit')->after('requires_expiration_date')->default(0)->nullable();
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
                $table->dropColumn('box_limit');
            });
        }
    }
}
