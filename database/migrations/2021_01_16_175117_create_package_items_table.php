<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePackageItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('package_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('package_id');
            $table->string('barcode')->nullable();
            $table->unsignedInteger('qty')->default(1);
            $table->string('name')->nullable();
            $table->decimal('price', 14)->default(0);
            $table->text('desc')->nullable();

            $table->unsignedInteger('weight')->default(0);
            $table->unsignedInteger('height')->default(0);
            $table->unsignedInteger('length')->default(0);
            $table->unsignedInteger('width')->default(0);

            $table->boolean('in_estimation')->default(1);
            $table->boolean('is_insured')->default(0);
            $table->json('handling')->nullable();

            $table->timestamps();

            $table
                ->foreign('package_id')
                ->references('id')
                ->on('packages')
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
        Schema::dropIfExists('package_items');
    }
}
