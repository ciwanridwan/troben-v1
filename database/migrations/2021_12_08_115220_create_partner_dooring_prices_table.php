<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartnerDooringPricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partner_dooring_prices', function (Blueprint $table) {
            $table->foreignIdFor(\App\Models\Partners\Partner::class, 'partner_id')
                ->constrained()
                ->nullOnDelete();

            $table->unsignedBigInteger('origin_regency_id');
            $table->unsignedBigInteger('destination_sub_district_id');
            $table->unsignedSmallInteger('type', false, true)->comment($this->typeComment());
            $table->unsignedInteger('value');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table
                ->foreign('origin_regency_id')
                ->references('id')
                ->on('geo_regencies')
                ->cascadeOnDelete();

            $table
                ->foreign('destination_sub_district_id')
                ->references('id')
                ->on('geo_sub_districts')
                ->cascadeOnDelete();

            $table->primary(['partner_id', 'origin_regency_id', 'destination_sub_district_id','type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('partner_dooring_prices');
    }

    /**
     * @return string
     */
    private function typeComment(): string
    {
        return '
            1: SLA
            2: Flat
            3: tier 1
            4: tier 2
            5: tier 3
            6: tier 4
            7: tier 5
            8: tier 6
            9: tier 7
            10: tier 8
            11: tier 9
            12: tier 10
        ';
    }
}
