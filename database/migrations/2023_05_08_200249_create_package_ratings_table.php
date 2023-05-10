<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePackageRatingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('package_ratings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('package_id');
            $table->integer('rating')->nullable();
            $table->longText('review')->nullable();

            $table->foreign('package_id')->references('id')->on('packages');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('package_rating');
    }
}
