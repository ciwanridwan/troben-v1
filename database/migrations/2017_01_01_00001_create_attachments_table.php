<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Create `attachments` table
 *
 * @author      veelasky <veelasky@gmail.com>
 */

class CreateAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attachments', function(Blueprint $table) {
            $table->uuid('id');
            $table->string('title');
            $table->string('mime');
            $table->string('disk')->nullable();
            $table->string('path');
            $table->string('type')->default('attachment');
            $table->text('description')->nullable();
            $table->text('options')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->primary('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attachments');
    }
}
