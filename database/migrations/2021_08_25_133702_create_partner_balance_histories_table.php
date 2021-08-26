<?php

use App\Models\Partners\Balance\History;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartnerBalanceHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partner_balance_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('partner_id');
            $table->unsignedBigInteger('package_id');

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

            $table->primary(['package_id','type']);
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
