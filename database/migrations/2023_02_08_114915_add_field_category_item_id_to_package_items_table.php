<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldCategoryItemIdToPackageItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('package_items', function (Blueprint $table) {
            $table->unsignedBigInteger('category_item_id')->nullable();
            $table->foreign('category_item_id')->references('id')->on('category_items');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('package_items', function (Blueprint $table) {
            $table->dropColumn('category_item_id');
        });
    }
}
