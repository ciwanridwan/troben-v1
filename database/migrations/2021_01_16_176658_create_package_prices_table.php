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
            $table->foreignId('package_id')->references('id')->on('packages')->cascadeOnDelete();
            $table->foreignId('package_item_id')->nullable()->references('id')->on('package_items')->cascadeOnDelete();
            $table->enum('type', Price::getAvailableTypes())->default(Price::TYPE_SERVICE);
            $table->string('description')->nullable();
            $table->decimal('amount', 14);
            $table->timestamps();
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
