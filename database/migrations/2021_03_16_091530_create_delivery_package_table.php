<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Deliveries\Delivery;
use App\Models\Packages\Package;

class CreateDeliveryPackageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delivery_package', function (Blueprint $table) {
            $table->foreignIdFor(Delivery::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Package::class)->constrained()->cascadeOnDelete();
            $table->boolean('is_onboard')->default(false);

            $table->timestamps();
            $table->primary(['delivery_id', 'package_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('delivery_package');
    }
}
