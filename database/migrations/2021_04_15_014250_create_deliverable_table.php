<?php

use App\Models\Deliveries\Deliverable as DeliverableModel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeliverableTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deliverables', function (Blueprint $table) {
            $table->foreignId('delivery_id')->constrained('deliveries')->cascadeOnDelete();
            $table->morphs('deliverable');
            $table->boolean('is_onboard')->default(false);
            $table->enum('status', DeliverableModel::getStatuses())->nullable();
            $table->timestamps();

            $table->primary(['delivery_id', 'deliverable_id', 'deliverable_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('deliverables');
    }
}
