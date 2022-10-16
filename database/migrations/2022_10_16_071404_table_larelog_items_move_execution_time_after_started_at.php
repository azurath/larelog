<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class TableLarelogItemsMoveExecutionTimeAfterStartedAt extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE larelog_items MODIFY COLUMN execution_time FLOAT(16,8) AFTER started_at");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE larelog_items MODIFY COLUMN execution_time FLOAT(16,8) AFTER response");
    }
}
