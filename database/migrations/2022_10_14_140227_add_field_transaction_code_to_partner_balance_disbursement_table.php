<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldTransactionCodeToPartnerBalanceDisbursementTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('partner_balance_disbursement', function (Blueprint $table) {
            $table->string('transaction_code')->nullable();
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
            $table->dropColumn('transaction_code');
        });
    }
}
