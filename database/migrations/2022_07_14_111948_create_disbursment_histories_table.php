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
            $table->unsignedBigInteger('disbursment_id');
            $table->string('receipt')->nullable();
            $table->decimal('amount', 14, 2)->default(0);
            $table->timestamps();

            $table->foreign('disbursment_id')->references('id')->on('partner_balance_disbursement');
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
