<?php

use App\Auditor\Auditor;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAuditsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('audits', function (Blueprint $table) {
            $table->uuid('id');
            $table->enum('type', Auditor::getAuditType());

            $table->string('auditable_type')->nullable();
            $table->string('auditable_id')->nullable();

            $table->string('performer_type')->nullable();
            $table->string('performer_id')->nullable();

            $table->string('message');
            $table->text('trails')->nullable();
            $table->timestamps();

            $table->primary('id');
            $table->index(['auditable_id', 'auditable_type']);
            $table->index(['performer_type', 'performer_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('audits');
    }
}
