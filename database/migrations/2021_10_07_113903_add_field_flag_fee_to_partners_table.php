<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldFlagFeeToPartnersTable extends Migration
{
    private array $descriptions;

    public function __construct()
    {
        $this->arrayFields();
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('partners', function (Blueprint $table) {
            foreach ($this->descriptions as $description) $table->boolean($description)->default(false);
            $table->boolean('get_charge_delivery')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->dropColumn($this->descriptions);
        });
    }

    private function arrayFields() {
        $this->descriptions = \App\Models\Partners\Balance\History::getAvailableDescription();

        foreach ($this->descriptions as $key => $description) $this->descriptions[$key] = 'get_fee_'.$description;
    }
}
