<?php

use App\Models\Customers\Customer;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Customer::class, 'customer_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('barcode');
            $table->string('sender_name');
            $table->string('sender_phone');

            $table->decimal('total_payment', 14, 2)->default(0);

            $table->string('payment_channel')->nullable();
            $table->string('payment_ref_id')->nullable();
            $table->string('payment_status')->default('unpaid');

            $table->string('status');
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
        Schema::dropIfExists('orders');
    }
}
