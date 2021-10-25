<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RevampPartnerBalanceDisbursementTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('partner_balance_disbursement', function (Blueprint $table) {
            $table->dropColumn('account_bank_id');
            $table->dropColumn('last_balance');
            $table->dropColumn('admin');
            $table->foreignId('action_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('action_at')->nullable();
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
            $table->unsignedBigInteger('account_bank_id')->nullable();
            $table->foreignId('admin')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('last_balance', 16, 2)->default(0)->nullable();
            $table->dropColumn('action_at');
            $table->dropColumn('action_by');

            $table
                ->foreign('account_bank_id')
                ->references('id')
                ->on('partner_bank_account');
        });
    }
}
