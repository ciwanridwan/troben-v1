<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldHarborIdToScheduleTransportationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('schedule_transportations', function (Blueprint $table) {
            $table->string('ship_name')->nullable();
            $table->foreignId('harbor_id')
                ->nullable()
                ->references('id')
                ->on('harbors');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('schedule_transportations', function (Blueprint $table) {
            $table->dropColumn('ship_name');
            $table->dropColumn('harbor_id');
        });
    }
}
