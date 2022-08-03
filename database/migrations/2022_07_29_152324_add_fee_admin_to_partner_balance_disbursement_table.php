<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFeeAdminToPartnerBalanceDisbursementTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('partner_balance_disbursement', function (Blueprint $table) {
            $table->boolean('charge_admin')->nullable();
            $table->decimal('fee_charge_admin', 14, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('partner_balance_disbursement', function (Blueprint $table) {
            //
        });
    }
}
