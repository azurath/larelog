<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TableLarelogItemsChangeRequestAndResponseToLongblob extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE `larelog_items` MODIFY `request` LONGBLOB null");
        DB::statement("ALTER TABLE `larelog_items` MODIFY `response` LONGBLOB null");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('larelog_items', function (Blueprint $table) {
            $table->mediumText('request')->nullable()->change();
            $table->mediumText('response')->nullable()->change();
        });
    }
}
