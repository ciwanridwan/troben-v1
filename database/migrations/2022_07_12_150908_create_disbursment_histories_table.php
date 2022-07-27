<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDisbursmentHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('disbursment_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('disbursement_id');
            $table->string('receipt')->nullable();
            $table->string('is_approved')->nullable();
            $table->timestamps();

            $table->foreign('disbursement_id')->references('id')->on('partner_balance_disbursement');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('disbursment_histories');
    }
}
