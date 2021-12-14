<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Partners\Balance\DeliveryHistory;

class CreatePartnerBalanceDeliveryHistories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partner_balance_delivery_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('partner_id');
            $table->unsignedBigInteger('delivery_id');

            $table->decimal('balance', 16, 2)->default(0);

            $table->enum('type', DeliveryHistory::getAvailableType());
            $table->enum('description', DeliveryHistory::getAvailableDescription());

            $table->timestamps();

            $table->foreign('partner_id')
                ->references('id')
                ->on('partners');

            $table->foreign('delivery_id')
                ->references('id')
                ->on('deliveries');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('partner_balance_delivery_histories');
    }
}
