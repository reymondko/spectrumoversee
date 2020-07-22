<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableInventoryAddTplCompany extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      if (Schema::hasTable('inventory')) {
          Schema::table('inventory', function (Blueprint $table) {
              $table->integer('tpl_company_id')->after('companies_id')->nullable();
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
      if (Schema::hasTable('inventory')) {
          Schema::table('inventory', function (Blueprint $table) {
              $table->dropColumn('tpl_company_id');
          });
      }
    }
}
