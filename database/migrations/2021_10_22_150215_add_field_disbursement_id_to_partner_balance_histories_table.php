<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldDisbursementIdToPartnerBalanceHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('partner_balance_histories', function (Blueprint $table) {
            $table->foreign('disbursement_id')
                ->references('id')
                ->on('partner_balance_disbursements');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('partner_balance_histories', function (Blueprint $table) {
            $table->dropColumn('disbursement_id');
        });
    }
}
