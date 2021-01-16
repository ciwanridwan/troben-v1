<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Create `attachables` table
 *
 * @author      veelasky <veelasky@gmail.com>
 */
class CreateAttachablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attachable', function (Blueprint $table) {
            $table->uuid('attachment_id');
            $table->unsignedBigInteger('attachable_id');
            $table->string('attachable_type');

            $table->foreign('attachment_id')
                  ->references('id')
                  ->on('attachments')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('attachable', function (Blueprint $table) {
            $table->dropForeign('attachable_attachment_id_foreign');
        });
        Schema::dropIfExists('attachable');
    }
}
