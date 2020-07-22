<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCaseNumberFieldToCaselabelRequiredField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('caselabel_required_fields')) {
            Schema::table('caselabel_required_fields', function (Blueprint $table) {
                $table->integer('case_number_field')->default(0);
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
        Schema::table('caselabel_required_field', function (Blueprint $table) {
            //
        });
    }
}
