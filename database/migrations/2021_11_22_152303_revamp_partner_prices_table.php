<?php

include_once '2021_08_31_153431_create_partner_prices_table.php';

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RevampPartnerPricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('partner_prices');
        Schema::create('partner_transit_prices', function (Blueprint $table) {
            // $table->foreignIdFor(\App\Models\Partners\Partner::class, 'partner_id')
            //     ->constrained()
            //     ->nullOnDelete();
            // $table->unsignedSmallInteger('type', false, true)->comment($this->typeComment());
            // $table->unsignedInteger('value');
            // $table->unsignedSmallInteger('shipment_type')->comment($this->shipmentTypeComment());
            // $table->unsignedBigInteger('created_by')->nullable();
            // $table->unsignedBigInteger('updated_by')->nullable();

            $table->unsignedBigInteger('origin_regency_id');
            $table->unsignedBigInteger('destination_regency_id');
            $table->unsignedBigInteger('destination_district_id');
            $table->unsignedSmallInteger('type')->comment($this->typeComment());
            $table->string('vendor', 15);
            $table->decimal('tier_1', 14, 2)->default(0);
            $table->decimal('tier_2', 14, 2)->default(0);
            $table->decimal('tier_3', 14, 2)->default(0);
            $table->decimal('tier_4', 14, 2)->default(0);
            $table->decimal('tier_5', 14, 2)->default(0);
            $table->decimal('tier_6', 14, 2)->default(0);
            $table->decimal('tier_7', 14, 2)->default(0);
            $table->decimal('tier_8', 14, 2)->default(0);
            $table->timestamps();

            $table
                ->foreign('origin_regency_id')
                ->references('id')
                ->on('geo_regencies')
                ->cascadeOnDelete();

            $table
                ->foreign('destination_regency_id')
                ->references('id')
                ->on('geo_regencies')
                ->cascadeOnDelete();

            $table
                ->foreign('destination_district_id')
                ->references('id')
                ->on('geo_districts')
                ->cascadeOnDelete();

            $table->primary(['origin_regency_id', 'destination_regency_id', 'destination_district_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('partner_transit_prices');
        (new CreatePartnerPricesTable())->up();
    }

    /**
     * type of partner transporter.
     * @return string
     */
    private function typeComment(): string
    {
        return '
        1: MTAK 1
        2: MTAK 2
        3: MTAK 3
        ';
    }

    /**
     * @return string
     */
    // private function typeComment(): string
    // {
    //     return '
    //         1: SLA (hours)
    //         2: Flat
    //         3: tier 1
    //         4: tier 2
    //         5: tier 3
    //         6: tier 4
    //         7: tier 5
    //         8: tier 6
    //         9: tier 7
    //         10: tier 8
    //         11: tier 9
    //         12: tier 10
    //     ';
    // }

    /**
     * @return string
     */
    // private function shipmentTypeComment(): string
    // {
    //     return '
    //         1: Land
    //         2: Sea
    //         3: Airway
    //     ';
    // }
}
