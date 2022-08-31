<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBikePricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bike_prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('origin_province_id');
            $table->unsignedBigInteger('origin_regency_id');
            $table->unsignedBigInteger('origin_district_id')->nullable();
            $table->unsignedBigInteger('origin_sub_district_id')->nullable();
            $table->unsignedBigInteger('destination_id');
            $table->char('zip_code', 8);
            $table->decimal('lower_cc', 14, 2)->default(0);
            $table->decimal('middle_cc', 14, 2)->default(0);
            $table->decimal('high_cc', 14, 2)->default(0);
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
                ->cascadeOnDelete();

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
        Schema::dropIfExists('bike_prices');
    }
}
