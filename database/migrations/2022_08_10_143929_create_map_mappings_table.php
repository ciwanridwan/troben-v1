<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('map_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('level');
            $table->string('google_name');
            $table->string('google_placeid');
            $table->string('name');
            $table->unsignedInteger('regional_id');
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
        Schema::dropIfExists('map_mappings');
    }
};
