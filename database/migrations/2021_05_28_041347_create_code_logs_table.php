<?php

use App\Models\CodeLogable;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCodeLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('code_logables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('code_id')->constrained('codes')->cascadeOnDelete();
            $table->morphs('code_logable');
            $table->enum('type', CodeLogable::getAvailableTypes())->nullable();
            $table->json('showable')->nullable();
            $table->string('status')->nullable();
            $table->text('description')->nullable();
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
        Schema::dropIfExists('code_logs');
    }
}
