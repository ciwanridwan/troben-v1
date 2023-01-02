<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeliveryRoutesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delivery_routes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('package_id');
            $table->unsignedBigInteger('regency_origin_id');

            $table->unsignedBigInteger('regency_destination_1');
            $table->timestamp('reach_destination_1_at')->nullable();

            $table->unsignedBigInteger('regency_destination_2')->nullable();
            $table->timestamp('reach_destination_2_at')->nullable();

            $table->unsignedBigInteger('regency_destination_3')->nullable();
            $table->timestamp('reach_destination_3_at')->nullable();

            $table->unsignedBigInteger('regency_dooring_id');
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
        Schema::dropIfExists('delivery_routes');
    }
}
