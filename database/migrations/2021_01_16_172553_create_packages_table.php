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

            $table->string('receiver_name');
            $table->string('receiver_phone');
            $table->string('receiver_address');

            $table->string('received_by')->nullable();

            $table->decimal('total_amount', 14, 2)->default(0);
            $table->enum('status', Package::getAvailableStatuses())->default(Package::STATUS_CREATED);
            $table->enum('payment_status', Package::getAvailablePaymentStatuses())->default(Package::PAYMENT_STATUS_DRAFT);
            $table->boolean('is_separate_item')->default(0);

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
        Schema::dropIfExists('packages');
    }
}
