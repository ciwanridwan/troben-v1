<?php

use App\Models\Partners\Partner;
use App\Models\Deliveries\Delivery;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeliveriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();

            // only used when delivery type is transit
            $table->foreignId('origin_partner_id')->nullable()->constrained('partners')->nullOnDelete();
            // partner a.k.a target partner required if delivery type is transit
            $table->foreignIdFor(Partner::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignId('userable_id')->nullable()->constrained('userables')->nullOnDelete();

            $table->enum('type', Delivery::getAvailableTypes());
            $table->enum('status', Delivery::getAvailableStatus())->default(Delivery::STATUS_PENDING);

            # geo related field
            $table->unsignedBigInteger('origin_regency_id')->nullable();
            $table->unsignedBigInteger('origin_district_id')->nullable();
            $table->unsignedBigInteger('origin_sub_district_id')->nullable();
            $table->unsignedBigInteger('destination_regency_id')->nullable();
            $table->unsignedBigInteger('destination_district_id')->nullable();
            $table->unsignedBigInteger('destination_sub_district_id')->nullable();

            $table->timestamps();

            $table
                ->foreign('origin_regency_id')
                ->references('id')
                ->on('geo_regencies')
                ->onDelete('set null');
            $table
                ->foreign('origin_district_id')
                ->references('id')
                ->on('geo_districts')
                ->onDelete('set null');
            $table
                ->foreign('origin_sub_district_id')
                ->references('id')
                ->on('geo_sub_districts')
                ->onDelete('set null');

            $table
                ->foreign('destination_regency_id')
                ->references('id')
                ->on('geo_regencies')
                ->onDelete('set null');
            $table
                ->foreign('destination_district_id')
                ->references('id')
                ->on('geo_districts')
                ->onDelete('set null');
            $table
                ->foreign('destination_sub_district_id')
                ->references('id')
                ->on('geo_sub_districts')
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
        Schema::dropIfExists('deliveries');
    }
}
