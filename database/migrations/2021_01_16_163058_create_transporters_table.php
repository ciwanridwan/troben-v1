<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransportersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transporters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('partner_id');
            $table->string('production_year')->default(Carbon::now()->year()); // STNK, Nama Pemilik
            $table->string('registration_name')->nullable(); // STNK, Nama Pemilik
            $table->string('registration_number'); // STNK, Plat Nomor
            $table->string('registration_year')->nullable(); // STNK, Tahun Berlaku
            $table->string('type'); // BIKE, CAR, PICKUP, BOX, TRUCK
            $table->float('length')->nullable()->default(0); // capacity
            $table->float('width')->nullable()->default(0); // capacity
            $table->float('height')->nullable()->default(0); // capacity
            $table->string('weight')->default(0); // capacity
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table
                ->foreign('partner_id')
                ->references('id')
                ->on('partners')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transporters');
    }
}
