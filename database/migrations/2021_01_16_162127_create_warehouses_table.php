<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWarehousesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('partner_id');
            $table->unsignedBigInteger('geo_province_id')->nullable();
            $table->unsignedBigInteger('geo_city_id')->nullable();
            $table->unsignedBigInteger('geo_district_id')->nullable();
            $table->string('code');
            $table->string('name');
            $table->string('address')->nullable();
            $table->polygon('geo_area')->nullable();
            $table->boolean('is_pool')->default(0);
            $table->boolean('is_counter')->default(0);
            $table->timestamps();

            $table
                ->foreign('partner_id')
                ->references('id')
                ->on('partners')
                ->cascadeOnDelete();

            $table
                ->foreign('geo_province_id')
                ->references('id')
                ->on('geo_provinces')
                ->cascadeOnDelete();

            $table
                ->foreign('geo_city_id')
                ->references('id')
                ->on('geo_cities')
                ->cascadeOnDelete();

            $table
                ->foreign('geo_district_id')
                ->references('id')
                ->on('geo_districts')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('warehouses');
    }
}
