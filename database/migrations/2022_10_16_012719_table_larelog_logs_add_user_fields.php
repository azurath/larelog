<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TableLarelogLogsAddUserFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('larelog_logs', function (Blueprint $table) {
            $table->string('user_model')->nullable();
            $table->string('user_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('larelog_logs', function (Blueprint $table) {
            $table->dropColumn('user_model');
            $table->dropColumn('user_id');
        });
    }
}
