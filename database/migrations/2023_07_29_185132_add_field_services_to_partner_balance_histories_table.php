<?php

use App\Models\Partners\Balance\History;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldServicesToPartnerBalanceHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('partner_balance_histories', function (Blueprint $table) {
            $table->enum('services', History::getAvailableServices())->default(History::DESCRIPTION_SERVICE_REGULAR);
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
            $table->dropColumn('services');
        });
    }
}
