<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Change quotations.created_by from CASCADE to SET NULL so
        //    deleting a staff user doesn't destroy their quotation records.
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
        });
        DB::statement('ALTER TABLE quotations MODIFY created_by BIGINT UNSIGNED NULL');
        Schema::table('quotations', function (Blueprint $table) {
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });

        // 2. Add unique constraint on wishlists to prevent duplicate entries
        Schema::table('wishlists', function (Blueprint $table) {
            $table->unique(['customer_id', 'product_id']);
        });

        // 3. Add index on enquiries.product_id for filtering/joins
        Schema::table('enquiries', function (Blueprint $table) {
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
        });
        DB::statement('ALTER TABLE quotations MODIFY created_by BIGINT UNSIGNED NOT NULL');
        Schema::table('quotations', function (Blueprint $table) {
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::table('wishlists', function (Blueprint $table) {
            $table->dropUnique(['customer_id', 'product_id']);
        });

        Schema::table('enquiries', function (Blueprint $table) {
            $table->dropIndex(['product_id']);
        });
    }
};
