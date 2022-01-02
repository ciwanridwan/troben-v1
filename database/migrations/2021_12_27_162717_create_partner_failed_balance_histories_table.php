<?php

use App\Models\User;
use App\Models\Partners\Balance\FailedHistory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartnerFailedBalanceHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partner_failed_balance_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('partner_id');
            $table->unsignedSmallInteger('type')->default(FailedHistory::TYPE_TRANSIT)->comment('1: transit  2: dooring');
            $table->unsignedBigInteger('delivery_id');
            $table->unsignedBigInteger('package_id')->default(\App\Models\Packages\Package::PACKAGE_SYSTEM_ID);
            $table->unsignedBigInteger('status')->default(FailedHistory::STATUS_WAITING)->comment('1: waiting  2: completed');
            $table->unsignedBigInteger('created_by')->default(User::USER_SYSTEM_ID);
            $table->timestamp('created_at');
            $table->unsignedBigInteger('updated_by')->default(User::USER_SYSTEM_ID);
            $table->timestamp('updated_at');
            $table->unsignedBigInteger('deleted_by')->default(User::USER_SYSTEM_ID);
            $table->softDeletes();

            $table->foreign('partner_id')
                ->references('id')
                ->on('partners');

            $table->foreign('delivery_id')
                ->references('id')
                ->on('deliveries');

            $table->foreign('package_id')
                ->references('id')
                ->on('packages');

            $table->foreign('created_by')
                ->references('id')
                ->on('users');

            $table->foreign('updated_by')
                ->references('id')
                ->on('users');

            $table->foreign('deleted_by')
                ->references('id')
                ->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('partner_failed_balance_histories');
    }
}
