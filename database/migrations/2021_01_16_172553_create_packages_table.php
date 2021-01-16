<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->char('service_code', 3);
            $table->string('barcode');

            $table->string('sender_name');
            $table->string('sender_phone');
            $table->string('sender_address');

            $table->string('receiver_name');
            $table->string('receiver_phone');
            $table->string('receiver_address');

            $table->string('received_by')->nullable();

            $table->unsignedInteger('weight')->default(0);
            $table->unsignedInteger('volume')->default(0);

            $table->decimal('total_price', 14, 2)->default(0);
            $table->string('status')->default('accepted'); // accepted, in transit, delivery

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
        Schema::dropIfExists('packages');
    }
}
