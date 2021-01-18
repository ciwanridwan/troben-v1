<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeliveryOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delivery_orders', function (Blueprint $table) {
            $table->id();
            $table->string('code');

            // origin (customer, partner, warehouse)
            $table->point('geo_origin');

            // destination (customer, partner, warehouse)
            $table->point('geo_destination')->nullable();

            $table->unsignedBigInteger('transporter_id')->nullable();

            $table->timestamp('departure_time')->nullable();
            $table->timestamp('arrival_time')->nullable();
            $table->timestamps();

            $table
                ->foreign('transporter_id')
                ->references('id')
                ->on('transporters')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('delivery_orders');
    }
}
