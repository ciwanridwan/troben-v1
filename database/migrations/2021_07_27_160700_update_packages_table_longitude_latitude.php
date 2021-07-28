<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatePackagesTableLongitudeLatitude extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->string('sender_way_point')->after('sender_address')->nullable();
            $table->string('sender_latitude')->after('sender_way_point')->nullable();
            $table->string('sender_longitude')->after('sender_latitude')->nullable();

            $table->string('receiver_way_point')->after('receiver_address')->nullable();
            $table->string('receiver_latitude')->after('receiver_way_point')->nullable();
            $table->string('receiver_longitude')->after('receiver_latitude')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn('sender_way_point');
            $table->dropColumn('sender_latitude');
            $table->dropColumn('sender_longitude');

            $table->dropColumn('receiver_way_point');
            $table->dropColumn('receiver_latitude');
            $table->dropColumn('receiver_longitude');
        });
    }
}
