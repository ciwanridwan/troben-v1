<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCubicPricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cubic_prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('origin_province_id');
            $table->unsignedBigInteger('origin_regency_id');
            $table->unsignedBigInteger('origin_district_id')->nullable();
            $table->unsignedBigInteger('origin_sub_district_id')->nullable(); // sub district
            $table->unsignedBigInteger('destination_id'); // sub district
            $table->char('zip_code', 10);
            $table->decimal('amount', 14, 2)->default(0);
            $table->string('notes');
            $table->char('service_code', 3);
            $table->timestamps();


            $table
                ->foreign('origin_province_id')
                ->references('id')
                ->on('geo_provinces')
                ->cascadeOnDelete();

            $table
                ->foreign('origin_regency_id')
                ->references('id')
                ->on('geo_regencies')
                ->cascadeOnDelete();

            $table
                ->foreign('origin_district_id')
                ->references('id')
                ->on('geo_districts')
                ->nullOnDelete();

            $table
                ->foreign('origin_sub_district_id')
                ->references('id')
                ->on('geo_sub_districts')
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
        Schema::dropIfExists('cubic_prices');
    }
}
