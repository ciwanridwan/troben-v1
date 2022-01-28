<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnUpdatedByOnDeliverablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('deliverables', function (Blueprint $table) {
            $table->foreignId('created_by')
                ->nullable()
                ->references('id')
                ->on('offices');
            $table->foreignId('updated_by')
                ->nullable()
                ->references('id')
                ->on('offices');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('deliverables', function (Blueprint $table) {
            $table->dropColumn('updated_by');
            $table->dropColumn('created_by');
        });
    }
}
