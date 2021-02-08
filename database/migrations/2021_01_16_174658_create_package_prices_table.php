<?php

use App\Models\Packages\Price;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePackagePricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('package_prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('package_id');
            $table->enum('type', Price::getAvailableTypes())->default(Price::TYPE_SERVICE);
            $table->decimal('amount', 14, 2);
            $table->timestamps();

            $table
                ->foreign('package_id')
                ->references('id')
                ->on('packages')
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
        Schema::dropIfExists('package_prices');
    }
}
