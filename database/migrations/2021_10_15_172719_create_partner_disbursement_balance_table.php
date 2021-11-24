<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartnerDisbursementBalanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partner_balance_disbursement', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained('partners')->nullOnDelete();
            $table->unsignedBigInteger('account_bank_id')->nullable();
            $table->decimal('first_balance', 16, 2)->default(0);
            $table->decimal('amount', 16, 2)->default(0);
            $table->decimal('last_balance', 16, 2)->default(0)->nullable();
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->string('account_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('status')->nullable();
            $table->string('notes')->nullable();
            $table->foreignId('admin')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table
                ->foreign('account_bank_id')
                ->references('id')
                ->on('partner_bank_account');

            $table
                ->foreign('bank_id')
                ->references('id')
                ->on('bank');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('partner_balance_disbursement');
    }
}
