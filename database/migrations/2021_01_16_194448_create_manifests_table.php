<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateManifestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('manifests', function (Blueprint $table) {
            $table->id();
            $table->string('code');

            $table->unsignedBigInteger('delivery_order_id');
            $table->string('type')->default('transit'); // transit / delivery (to customer)
            $table->string('status')->default('created'); // created, on going
            $table->timestamp('loaded_at')->nullable();
            $table->timestamp('unloaded_at')->nullable();
            $table->timestamps();

            $table
                ->foreign('delivery_order_id')
                ->references('id')
                ->on('delivery_orders')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('manifests');
    }
}
