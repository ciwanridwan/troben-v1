<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGeoCountriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('geo_countries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->char('alpha2', 2);
            $table->char('alpha3', 3);
            $table->char('numeric', 3);
            $table->string('phone_prefix', 3)->nullable();
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
        Schema::dropIfExists('geo_countries');
    }
}
