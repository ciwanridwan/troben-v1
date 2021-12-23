<?php

use App\Models\Customers\Customer;
use App\Models\Packages\Package;
use App\Models\Promos\Promotion;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePromotionClaimedCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('promotion_claimed_customers', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Customer::class, 'customer_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->foreignIdFor(Package::class, 'package_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->foreignIdFor(Promotion::class, 'promotion_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->dateTime('claimed_at');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('promotion_claimed_customers');
    }
}
