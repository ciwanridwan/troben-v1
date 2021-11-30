<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePromotionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('type')->nullable();
            $table->text('terms_and_conditions');
            $table->string('transporter_type')->nullable();
            $table->unsignedBigInteger('destination_regency_id')->nullable();
            $table->decimal('min_payment', 14, 2)->default(0);
            $table->decimal('min_weight', 14, 2)->default(0);
            $table->decimal('max_weight', 14, 2)->default(0);
            $table->boolean('is_active')->nullable()->default(false);
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->timestamps();
            $table->softDeletes();

            $table
                ->foreign('destination_regency_id')
                ->references('id')
                ->on('geo_regencies')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('promotions');
    }
}
