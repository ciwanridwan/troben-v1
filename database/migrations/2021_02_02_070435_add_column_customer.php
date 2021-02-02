<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnCustomer extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->timestamp('verified_at')->nullable()->after('remember_token');
            $table->string('google_id')->nullable()->after('remember_token');
            $table->string('facebook_id')->nullable()->after('remember_token');
            $table->string('fcm_token')->nullable()->after('remember_token');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('verified_at');
            $table->dropColumn('google_id');
            $table->dropColumn('facebook_id');
            $table->dropColumn('fcm_token');
        });
    }
}
