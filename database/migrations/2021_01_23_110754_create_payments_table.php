<?php

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
            $table->string('service_type')->default('pay'); // pay, withdrawal, reversal.
            $table->string('payable_type');
            $table->unsignedBigInteger('payable_id');
            $table->string('channel')->default('bank_transfer');

            $table->decimal('payment_amount', 14, 2);
            $table->decimal('payment_admin_charges', 14, 2)->default(0);
            $table->string('payment_ref_id')->nullable();
            $table->decimal('total_payment', 14, 2);
            $table->string('status')->default(Payment::STATUS_PENDING);
            $table->unsignedBigInteger('confirmed_by')->nullable(); // null => auto approve by system.
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();

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
