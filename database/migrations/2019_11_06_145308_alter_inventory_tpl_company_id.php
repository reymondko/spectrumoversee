<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterInventoryTplCompanyId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('inventory', function($table) {
         $table->renameColumn('tpl_company_id', 'tpl_customer_id');
      });

      Schema::table('locations', function($table) {
         $table->integer('tpl_customer_id')->after('companies_id')->nullable();
         $table->dropColumn('companies_id');
      });
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
