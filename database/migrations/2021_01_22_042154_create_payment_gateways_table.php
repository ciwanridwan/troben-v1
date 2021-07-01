<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentGatewaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('channel')->unique();
            $table->string('name');
            $table->decimal('admin_charges', 14, 2)->default(0);
            $table->boolean('is_fixed')->default(1);

            // only bank transfer
            $table->boolean('is_bank_transfer')->default(1);
            $table->string('account_bank')->nullable();
            $table->string('account_number')->nullable();
            $table->string('account_name')->nullable();

            $table->json('options');
            $table->boolean('auto_approve')->default(0);
            $table->boolean('is_active')->default(1);
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
        Schema::dropIfExists('payment_gateways');
    }
}
