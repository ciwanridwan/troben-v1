<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGeoDistrictsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('geo_districts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->unsignedBigInteger('province_id')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->string('name');
            $table->timestamps();

            $table
                ->foreign('country_id')
                ->references('id')
                ->on('geo_countries')
                ->nullOnDelete();

            $table
                ->foreign('province_id')
                ->references('id')
                ->on('geo_provinces')
                ->nullOnDelete();

            $table
                ->foreign('city_id')
                ->references('id')
                ->on('geo_cities')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('geo_districts');
    }
}
