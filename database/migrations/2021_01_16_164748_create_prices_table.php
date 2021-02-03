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
            $table->unsignedBigInteger('origin_province_id');
            $table->unsignedBigInteger('origin_regency_id');
            $table->unsignedBigInteger('origin_district_id')->nullable();
            $table->unsignedBigInteger('origin_sub_district_id')->nullable(); // sub district
            $table->unsignedBigInteger('destination_id'); // sub district
            $table->char('zip_code', 10);
            $table->decimal('tier_1', 14, 2)->default(0);
            $table->decimal('tier_2', 14, 2)->default(0);
            $table->decimal('tier_3', 14, 2)->default(0);
            $table->decimal('tier_4', 14, 2)->default(0);
            $table->decimal('tier_5', 14, 2)->default(0);
            $table->decimal('tier_6', 14, 2)->default(0);
            $table->decimal('tier_7', 14, 2)->default(0);
            $table->decimal('tier_8', 14, 2)->default(0);
            $table->decimal('tier_9', 14, 2)->default(0);
            $table->decimal('tier_10', 14, 2)->default(0);
            $table->char('service_code', 3);
            $table->timestamps();

            $table
                ->foreign('origin_sub_district_id')
                ->references('id')
                ->on('geo_sub_districts')
                ->cascadeOnDelete();

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
                ->foreign('destination_id')
                ->references('id')
                ->on('geo_sub_districts')
                ->nullOnDelete();

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
