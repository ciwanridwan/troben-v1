<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePromotionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('promotion', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('type')->nullable();
            $table->text('terms_and_conditions');
            $table->decimal('min_payment', 14, 2)->default(0);
            $table->decimal('max_payment', 14, 2)->default(0);
            $table->decimal('min_weight', 14, 2)->default(0);
            $table->decimal('max_weight', 14, 2)->default(0);
            $table->boolean('is_active')->nullable()->default(false);
            $table->dateTime('start_date');
            $table->dateTime('end_date');
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
        Schema::dropIfExists('promotion');
    }
}
