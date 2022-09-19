<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFileUploadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('file_uploads', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('package_id')->nullable();
            $table->foreign('package_id')->references('id')->on('packages');

            $table->unsignedBigInteger('created_by_id')->nullable();
            $table->string('created_by_type')->nullable();

            $table->string('file');
            $table->string('file_type');
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
        Schema::dropIfExists('file_uploads');
    }
}
