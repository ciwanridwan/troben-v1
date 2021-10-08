<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use App\Models\Partners\Balance\History;

class RebuildPartnerBalanceHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('partner_balance_histories');
        Schema::create('partner_balance_histories', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('partner_id');
            $table->unsignedBigInteger('package_id')->nullable();
            $table->unsignedBigInteger('disbursement_id')->nullable();

            $table->decimal('balance', 16, 2)->default(0);

            $table->enum('type', History::getAvailableType());
            $table->enum('description', History::getAvailableDescription());

            $table->timestamps();

            $table->foreign('partner_id')
                ->references('id')
                ->on('partners');

            $table->foreign('package_id')
                ->references('id')
                ->on('packages');

            //$table->foreign('disbursement_id')
            //    ->references('id')
            //    ->on('partner_balance_disbursements');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('partner_balance_histories');
    }
}
