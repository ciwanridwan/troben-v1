<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePickupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pickups', function (Blueprint $table) {
            $table->id();

            $table->boolean('is_return')->default(0);
            $table->unsignedBigInteger('warehouse_id');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('customer_address_id');
            $table->string('name'); // nama barang
            $table->unsignedInteger('estimated_weight');
            $table->unsignedInteger('estimated_volume');
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->unsignedBigInteger('transporter_id')->nullable();

            // created = pertama dibuat oleh customer(on queue),
            // accepted = diaccept oleh customer service
            // rejected = tidak diterima customer service
            // ongoing = diterima oleh transporter
            // done = sudah ditimbang dan sudan create package
            // cancelled = dibatalkan oleh customer
            $table->string('status')->default('created');

            $table->decimal('cancellation_fee', 14, 2)->default(0);

            $table->timestamps();

            $table
                ->foreign('warehouse_id')
                ->references('id')
                ->on('warehouses');

            $table
                ->foreign('customer_id')
                ->references('id')
                ->on('customers');

            $table
                ->foreign('customer_address_id')
                ->references('id')
                ->on('customer_addresses');

            $table
                ->foreign('assigned_by')
                ->references('id')
                ->on('users');

            $table
                ->foreign('transporter_id')
                ->references('id')
                ->on('transporters');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pickups');
    }
}
