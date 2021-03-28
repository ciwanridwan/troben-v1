<?php

use App\Models\Packages\Package;
use App\Models\Customers\Customer;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Customer::class, 'customer_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('barcode');
            $table->char('service_code', 3);
            $table->string('transporter_type');

            $table->string('sender_name');
            $table->string('sender_phone');
            $table->string('sender_address');

            $table->string('receiver_name');
            $table->string('receiver_phone');
            $table->string('receiver_address');

            $table->enum('status', Package::getAvailableStatuses())->default(Package::STATUS_CREATED);
            $table->boolean('is_separate_item')->default(0);

            # payment related field
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->enum('payment_status', Package::getAvailablePaymentStatuses())->default(Package::PAYMENT_STATUS_DRAFT);

            # geo related field
            $table->unsignedBigInteger('origin_regency_id')->nullable();
            $table->unsignedBigInteger('origin_district_id')->nullable();
            $table->unsignedBigInteger('origin_sub_district_id')->nullable();
            $table->unsignedBigInteger('destination_regency_id')->nullable();
            $table->unsignedBigInteger('destination_district_id')->nullable();
            $table->unsignedBigInteger('destination_sub_district_id')->nullable();

            $table->string('received_by')->nullable();
            $table->timestamp('received_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

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
        Schema::dropIfExists('packages');
    }
}
