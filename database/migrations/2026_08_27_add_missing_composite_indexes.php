<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // products: composite index for category listings with status filter
        if (!Schema::hasIndex('products', 'products_category_id_status_index')) {
            Schema::table('products', function (Blueprint $table) {
                $table->index(['category_id', 'status'], 'products_category_id_status_index');
            });
        }

        // products: composite index for homepage/shop latest active products
        if (!Schema::hasIndex('products', 'products_status_created_at_index')) {
            Schema::table('products', function (Blueprint $table) {
                $table->index(['status', 'created_at'], 'products_status_created_at_index');
            });
        }

        // orders: composite index for customer order history
        if (!Schema::hasIndex('orders', 'orders_customer_id_status_index')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->index(['customer_id', 'status'], 'orders_customer_id_status_index');
            });
        }

        // product_images: composite index for thumbnail lookups
        if (!Schema::hasIndex('product_images', 'product_images_product_id_type_index')) {
            Schema::table('product_images', function (Blueprint $table) {
                $table->index(['product_id', 'type'], 'product_images_product_id_type_index');
            });
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_category_id_status_index');
            $table->dropIndex('products_status_created_at_index');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_customer_id_status_index');
        });

        Schema::table('product_images', function (Blueprint $table) {
            $table->dropIndex('product_images_product_id_type_index');
        });
    }
};