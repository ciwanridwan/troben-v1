<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScheduleTransportationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schedule_transportations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained('partners')->nullOnDelete();
            $table->unsignedBigInteger('origin_regency_id');
            $table->unsignedBigInteger('destination_regency_id');
            $table->date('departed_at');
            $table->timestamps();
            $table->softDeletes();

            $table
                ->foreign('origin_regency_id')
                ->references('id')
                ->on('geo_regencies')
                ->onDelete('set null');

            $table
                ->foreign('destination_regency_id')
                ->references('id')
                ->on('geo_regencies')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('schedule_transportations');
    }
}
