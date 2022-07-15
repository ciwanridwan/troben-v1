<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVoucherModifyTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->string('type')->default('discount_service_percentage'); // discount_service_percentage, discount_service_nominal, free_pickup
            $table->foreignId('aevoucher_id')
                ->nullable()
                ->references('id')
                ->on('ae_vouchers');
        });

        Schema::table('voucher_claimed_customers', function (Blueprint $table) {
            $table->foreignId('voucher_id')
                ->nullable()
                ->references('id')
                ->on('vouchers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
