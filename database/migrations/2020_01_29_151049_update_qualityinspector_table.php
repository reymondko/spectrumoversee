<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateQualityinspectorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('quality_inspector')) {
            Schema::table('quality_inspector', function (Blueprint $table) {
                $table->string('company_id')->after('transaction_id')->nullable();
                $table->string('reference_number')->after('line_number')->nullable();
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
        if (Schema::hasTable('quality_inspector')) {
            Schema::table('quality_inspector', function (Blueprint $table) {
                $$table->dropColumn('company_id');
                $$table->dropColumn('reference_number');
            });
        }
    }
}
