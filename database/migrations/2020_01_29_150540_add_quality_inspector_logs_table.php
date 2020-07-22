<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddQualityInspectorLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quality_inspector_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('qi_id')->nullable();
            $table->string('status')->nullable();
            $table->text('notes')->nullable();
            $table->string('qi_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quality_inspector_logs');
    }
}
