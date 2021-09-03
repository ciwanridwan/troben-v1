<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartnerPricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partner_prices', function (Blueprint $table) {
            $table->foreignIdFor(\App\Models\Partners\Partner::class,'partner_id')
                ->constrained()
                ->nullOnDelete();

            $table->unsignedBigInteger('origin_regency_id');
            $table->unsignedBigInteger('destination_id'); // sub district
            $table->char('zip_code', 10);
            $table->decimal('tier_1', 14, 2)->default(0);
            $table->decimal('tier_2', 14, 2)->default(0);
            $table->decimal('tier_3', 14, 2)->default(0);
            $table->decimal('tier_4', 14, 2)->default(0);
            $table->decimal('tier_5', 14, 2)->default(0);
            $table->decimal('tier_6', 14, 2)->default(0);
            $table->decimal('tier_7', 14, 2)->default(0);
            $table->decimal('tier_8', 14, 2)->default(0);
            $table->decimal('tier_9', 14, 2)->default(0);
            $table->decimal('tier_10', 14, 2)->default(0);
            $table->char('service_code', 3)->nullable();
            $table->timestamps();

            $table
                ->foreign('origin_regency_id')
                ->references('id')
                ->on('geo_regencies')
                ->cascadeOnDelete();

            $table
                ->foreign('destination_id')
                ->references('id')
                ->on('geo_sub_districts')
                ->cascadeOnDelete();

            $table->primary(['partner_id', 'origin_regency_id', 'destination_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('partner_prices');
    }
}
