<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdatedTableOrdersAttributes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) { 
                $table->integer('ordered_by_user_id')->nullable()->change();
                $table->integer('order_by_name')->nullable()->change(); 
                $table->integer('title')->nullable()->default(NULL)->change(); 
                $table->integer('status')->default(0)->change(); 
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
        //
    }
}
