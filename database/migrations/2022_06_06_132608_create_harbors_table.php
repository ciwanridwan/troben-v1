<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHarborsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('harbors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('origin_regency_id');
            $table->string('origin_name');
            $table->string('destination_name');
            $table->unsignedBigInteger('destination_regency_id');
            $table->timestamps();
            $table->softDeletes();
            // $table->unsignedBigInteger('origin_harbor_id');
            // $table->unsignedBigInteger('destination_harbor_id');

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
        Schema::dropIfExists('harbors');
    }
}
