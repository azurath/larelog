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
        Schema::create('larelog_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamp('created_at');
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
            $table->float('execution_time', 16, 8);
            $table->index([ 'created_at', 'type', DB::raw('url(20)') ]);
            $table->index([ 'created_at', DB::raw('url(20)') ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('larelog_logs');
    }
}
