<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomerAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->string('name');
            $table->string('address');
            $table->point('geo_location')->nullable();
            $table->unsignedBigInteger('geo_province_id');
            $table->unsignedBigInteger('geo_city_id');
            $table->unsignedBigInteger('geo_district_id');
            $table->boolean('is_default')->default(0);
            $table->timestamps();

            $table
                ->foreign('customer_id')
                ->references('id')
                ->on('customers')
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
        Schema::dropIfExists('customer_addresses');
    }
}
