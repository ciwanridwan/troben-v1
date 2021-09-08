<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldUserInHistoryrejectTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('history_reject', function (Blueprint $table) {
            $table->foreignId('userable_id')->nullable()->constrained('userables')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('history_reject', function (Blueprint $table) {
            $table->dropColumn('userable_id');
        });
    }
}
