<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePackageBikesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('package_bikes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('package_id');
            $table->unsignedBigInteger('package_item_id');
            $table->string('type');
            $table->string('merk');
            $table->string('cc');
            $table->string('years');
            $table->timestamps();

            $table->foreign('package_id')
            ->references('id')
            ->on('packages')
            ->cascadeOnDelete();

            $table->foreign('package_item_id')
            ->references('id')
            ->on('package_items')
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
        Schema::dropIfExists('package_bikes');
    }
}
