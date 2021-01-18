<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('origin_id'); // sub district
            $table->unsignedBigInteger('origin_province_id');
            $table->unsignedBigInteger('origin_city_id');
            $table->unsignedBigInteger('origin_district_id');
            $table->unsignedBigInteger('destination_id'); // sub district
            $table->char('service_code', 3);
            $table->decimal('price', 14, 2); // per kg
            $table->timestamps();

            $table
                ->foreign('origin_id')
                ->references('id')
                ->on('geo_sub_districts')
                ->cascadeOnDelete();

            $table
                ->foreign('origin_province_id')
                ->references('id')
                ->on('geo_provinces')
                ->cascadeOnDelete();

            $table
                ->foreign('origin_city_id')
                ->references('id')
                ->on('geo_cities')
                ->cascadeOnDelete();

            $table
                ->foreign('origin_district_id')
                ->references('id')
                ->on('geo_districts')
                ->cascadeOnDelete();

            $table
                ->foreign('destination_id')
                ->references('id')
                ->on('geo_sub_districts')
                ->cascadeOnDelete();

            $table
                ->foreign('service_code')
                ->references('code')
                ->on('services')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('prices');
    }
}
