<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->index(['status', 'category_id', 'id'], 'products_status_category_id_index');
            $table->index(['status', 'vendor_id'], 'products_status_vendor_id_index');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->index(['status', 'sort_order', 'id'], 'categories_status_sort_order_id_index');
        });

        Schema::table('banners', function (Blueprint $table) {
            $table->index(['status', 'sort_order', 'id'], 'banners_status_sort_order_id_index');
        });

        Schema::table('news', function (Blueprint $table) {
            $table->index(['status', 'id'], 'news_status_id_index');
        });

        Schema::table('menu_items', function (Blueprint $table) {
            $table->index(['menu_id', 'parent_id', 'order_number'], 'menu_items_menu_parent_order_index');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_status_category_id_index');
            $table->dropIndex('products_status_vendor_id_index');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex('categories_status_sort_order_id_index');
        });

        Schema::table('banners', function (Blueprint $table) {
            $table->dropIndex('banners_status_sort_order_id_index');
        });

        Schema::table('news', function (Blueprint $table) {
            $table->dropIndex('news_status_id_index');
        });

        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropIndex('menu_items_menu_parent_order_index');
        });
    }
};
