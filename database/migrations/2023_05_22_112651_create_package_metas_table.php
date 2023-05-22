<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePackageMetasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('package_metas', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('package_id');
            $table->foreign('package_id')->references('id')->on('packages')->restrictOnDelete();

            $table->string('key');
            $table->text('meta');

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
        Schema::dropIfExists('package_metas');
    }
}
