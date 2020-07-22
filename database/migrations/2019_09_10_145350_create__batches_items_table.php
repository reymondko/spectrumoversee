<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBatchesItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('batches_items', function (Blueprint $table) {
            $table->increments('id');                       
            $table->integer('batch_id');                    
            $table->string('master_kit_id');                 
            $table->string('subkit_id');                 
            $table->string('return_tracking');        
            $table->string('box_id');                       
            $table->integer('created_by_id');
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
        Schema::dropIfExists('batches_items');
    }
}
