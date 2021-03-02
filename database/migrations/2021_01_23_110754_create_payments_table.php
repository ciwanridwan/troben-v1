<?php

use App\Models\Payments\Gateway;
use App\Models\Payments\Payment;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Gateway::class, 'gateway_id')->nullable()->constrained()->nullOnDelete();
            $table->string('payable_type');
            $table->unsignedBigInteger('payable_id');

            $table->string('service_type')->default('pay'); // pay, withdrawal, reversal.
            $table->decimal('payment_amount', 14, 2);
            $table->decimal('payment_admin_charges', 14, 2)->default(0);
            $table->decimal('total_payment', 14, 2);

            /** Only for bank transfer */
            $table->string('sender_bank')->nullable(); // acc sender bank
            $table->string('sender_name')->nullable(); // acc sender name
            $table->string('sender_account')->nullable(); // acc sender account

            $table->string('payment_ref_id')->nullable();

            $table->string('status')->default(Payment::STATUS_PENDING);

            $table->unsignedBigInteger('confirmed_by')->nullable(); // null => auto approve by system.
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('confirmed_by')
                ->references('id')
                ->on('users')
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
        Schema::dropIfExists('payments');
    }
}
