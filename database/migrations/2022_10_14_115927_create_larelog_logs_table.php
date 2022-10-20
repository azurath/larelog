<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateLarelogLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('larelog_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamp('created_at');
            $table->timestamp('started_at')->nullable();
            $table->float('execution_time', 16, 8);
            $table->string('direction', 10)->nullable();
            $table->string('type', 20)->nullable();
            $table->string('http_method', 20)->nullable();
            $table->string('http_protocol_version', 20)->nullable();
            $table->smallInteger('http_code')->nullable();
            $table->text('url')->nullable();
            $table->text('request_headers')->nullable();
            $table->mediumText('request')->nullable();
            $table->text('response_headers')->nullable();
            $table->mediumText('response')->nullable();
            $table->string('user_model')->nullable();
            $table->string('user_id')->nullable();
            $table->index(['created_at', 'type', DB::raw('url(20)')], 'larelog_logs_created_at_type_url_index');
            $table->index(['created_at', DB::raw('url(20)')], 'larelog_logs_created_at_url_index');
            $table->index(['created_at'], 'larelog_logs_created_at_index');
        });
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
        Schema::drop('larelog_items');
    }
}
