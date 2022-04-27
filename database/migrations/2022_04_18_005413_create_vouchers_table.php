<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVouchersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->nullable()
                ->references('id')
                ->on('users');
            $table->string('title');
            $table->foreignId('partner_id')
                ->nullable()
                ->references('id')
                ->on('partners');
            $table->double('discount')->nullable();
            $table->string('code');
            $table->boolean('is_approved')->default(false);
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('voucher_claimed_customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->nullable()
                ->references('id')
                ->on('users');
            $table->foreignId('partner_id')
                ->nullable()
                ->references('id')
                ->on('partners');
            $table->foreignId('customer_id')
                ->nullable()
                ->references('id')
                ->on('customers');
            $table->foreignId('package_id')
                ->nullable()
                ->references('id')
                ->on('packages');
            $table->double('discount')->nullable();
            $table->string('code');
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
        Schema::dropIfExists('vouchers');
        Schema::dropIfExists('voucher_claimed_customers');
    }
}
