<?php

use App\Models\CodeLog;
use App\Models\Partners\Pivot\UserablePivot;
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
        Schema::create('code_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('code_id')->constrained('codes')->cascadeOnDelete();
            $table->morphs('code_logable');
            $table->enum('type', CodeLog::getAvailableTypes())->nullable();
            $table->string('showable')->nullable();
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
