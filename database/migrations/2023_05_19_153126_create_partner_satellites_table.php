<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartnerSatellitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partner_satellites', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('id_partner');
            $table->foreign('id_partner')->references('id')->on('partners');

            $table->unsignedBigInteger('geo_province_id');
            $table->unsignedBigInteger('geo_regency_id');
            $table->unsignedBigInteger('geo_district_id');
            $table->unsignedBigInteger('geo_sub_district_id');

            $table->foreign('geo_province_id')->references('id')->on('geo_provinces');
            $table->foreign('geo_regency_id')->references('id')->on('geo_regencies');
            $table->foreign('geo_district_id')->references('id')->on('geo_districts');
            $table->foreign('geo_sub_district_id')->references('id')->on('geo_sub_districts');

            $table->string('address');
            $table->string('display_name');
            $table->string('latitude');
            $table->string('longitude');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('partner_satellites');
    }
}
