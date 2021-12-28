<?php

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
            $table->unsignedSmallInteger('type');
            $table->unsignedBigInteger('delivery_id');
            $table->unsignedBigInteger('package_id');
            $table->unsignedBigInteger('status');
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_at');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes()->nullable();

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
