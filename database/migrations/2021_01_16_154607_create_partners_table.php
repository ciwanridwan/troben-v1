<?php

use App\Models\Partners\Partner;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePartnersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->unsignedBigInteger('geo_province_id')->nullable();
            $table->unsignedBigInteger('geo_regency_id')->nullable();
            $table->unsignedBigInteger('geo_district_id')->nullable();
            $table->unsignedBigInteger('geo_sub_district_id')->nullable();
            $table->string('address')->nullable();
            $table->point('geo_location')->nullable();
            $table->enum('type', Partner::getAvailableTypes())->default(Partner::TYPE_BUSINESS);
            $table->timestamps();
            $table->softDeletes();

            $table
                ->foreign('geo_province_id')
                ->references('id')
                ->on('geo_provinces')
                ->cascadeOnDelete();

            $table
                ->foreign('geo_regency_id')
                ->references('id')
                ->on('geo_regencies')
                ->cascadeOnDelete();

            $table
                ->foreign('geo_district_id')
                ->references('id')
                ->on('geo_districts')
                ->cascadeOnDelete();
            $table
                ->foreign('geo_sub_district_id')
                ->references('id')
                ->on('geo_sub_districts')
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
        Schema::dropIfExists('partners');
    }
}
