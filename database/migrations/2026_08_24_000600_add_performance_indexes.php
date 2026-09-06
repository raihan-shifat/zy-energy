<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->index('category_id');
            $table->index('status');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->index('is_primary');
            $table->index(['product_id', 'is_primary']);
        });

        Schema::table('enquiries', function (Blueprint $table) {
            $table->index('email');
            $table->index('status');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->index('sort_order');
            $table->index('status');
        });

        Schema::table('banners', function (Blueprint $table) {
            $table->index('status');
        });

        Schema::table('news', function (Blueprint $table) {
            $table->index('status');
        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['category_id']);
            $table->dropIndex(['status']);
        });
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropIndex(['is_primary']);
            $table->dropIndex(['product_id', 'is_primary']);
        });
        Schema::table('enquiries', function (Blueprint $table) {
            $table->dropIndex(['email']);
            $table->dropIndex(['status']);
        });
        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['sort_order']);
            $table->dropIndex(['status']);
        });
        Schema::table('banners', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });
        Schema::table('news', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropIndex(['date']);
        });
    }
};
