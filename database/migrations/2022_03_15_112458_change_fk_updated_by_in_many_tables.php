<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeFkUpdatedByInManyTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn('created_by');
            $table->dropColumn('updated_by');
        });
        Schema::table('packages', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->references('id')->on('customers');
            $table->foreignId('updated_by')->nullable()->references('id')->on('users');
        });
        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropColumn('created_by');
            $table->dropColumn('updated_by');
        });
        Schema::table('deliveries', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->references('id')->on('users');
            $table->foreignId('updated_by')->nullable()->references('id')->on('users');
        });
        Schema::table('deliverables', function (Blueprint $table) {
            $table->dropColumn('created_by');
            $table->dropColumn('updated_by');
        });
        Schema::table('deliverables', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->references('id')->on('users');
            $table->foreignId('updated_by')->nullable()->references('id')->on('users');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('created_by');
            $table->dropColumn('updated_by');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->references('id')->on('users');
            $table->foreignId('updated_by')->nullable()->references('id')->on('users');
        });
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('created_by');
            $table->dropColumn('updated_by');
        });
        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->references('id')->on('users');
            $table->foreignId('updated_by')->nullable()->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn('created_by');
            $table->dropColumn('updated_by');
        });
        Schema::table('packages', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->references('id')->on('customers');
            $table->foreignId('updated_by')->nullable()->references('id')->on('users');
        });
        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropColumn('created_by');
            $table->dropColumn('updated_by');
        });
        Schema::table('deliveries', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->references('id')->on('users');
            $table->foreignId('updated_by')->nullable()->references('id')->on('users');
        });
        Schema::table('deliverables', function (Blueprint $table) {
            $table->dropColumn('created_by');
            $table->dropColumn('updated_by');
        });
        Schema::table('deliverables', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->references('id')->on('users');
            $table->foreignId('updated_by')->nullable()->references('id')->on('users');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('created_by');
            $table->dropColumn('updated_by');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->references('id')->on('users');
            $table->foreignId('updated_by')->nullable()->references('id')->on('users');
        });
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('created_by');
            $table->dropColumn('updated_by');
        });
        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->references('id')->on('users');
            $table->foreignId('updated_by')->nullable()->references('id')->on('users');
        });
    }
}
