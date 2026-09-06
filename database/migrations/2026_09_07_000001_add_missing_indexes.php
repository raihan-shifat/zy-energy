<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // products: brand_id has no FK and no index -> brand listings scan the table.
        if (!Schema::hasIndex('products', 'products_brand_id_status_index')) {
            Schema::table('products', function (Blueprint $table) {
                $table->index(['brand_id', 'status'], 'products_brand_id_status_index');
            });
        }

        // orders: admin/vendor DataTables order by created_at; vendor scopes by vendor+status.
        if (!Schema::hasIndex('orders', 'orders_created_at_index')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->index('created_at', 'orders_created_at_index');
            });
        }
        if (!Schema::hasIndex('orders', 'orders_vendor_id_status_index')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->index(['vendor_id', 'status'], 'orders_vendor_id_status_index');
            });
        }

        // quotations: admin lists always sort by created_at desc.
        if (!Schema::hasIndex('quotations', 'quotations_created_at_index')) {
            Schema::table('quotations', function (Blueprint $table) {
                $table->index('created_at', 'quotations_created_at_index');
            });
        }

        // product_variants: unique SKU dropped earlier; restore non-unique seek index.
        if (!Schema::hasIndex('product_variants', 'product_variants_sku_index')) {
            Schema::table('product_variants', function (Blueprint $table) {
                $table->index('SKU', 'product_variants_sku_index');
            });
        }

        // product_reviews: storefront aggregates filter product_id + is_approved.
        if (!Schema::hasIndex('product_reviews', 'product_reviews_product_approved_index')) {
            Schema::table('product_reviews', function (Blueprint $table) {
                $table->index(['product_id', 'is_approved'], 'product_reviews_product_approved_index');
            });
        }

        // news_translations: enforce one translation per locale like every other *_translations table.
        if (!Schema::hasIndex('news_translations', 'news_translations_news_language_index')) {
            Schema::table('news_translations', function (Blueprint $table) {
                $table->unique(['news_id', 'language_code'], 'news_translations_news_language_index');
            });
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_brand_id_status_index');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_created_at_index');
            $table->dropIndex('orders_vendor_id_status_index');
        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->dropIndex('quotations_created_at_index');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropIndex('product_variants_sku_index');
        });

        Schema::table('product_reviews', function (Blueprint $table) {
            $table->dropIndex('product_reviews_product_approved_index');
        });

        Schema::table('news_translations', function (Blueprint $table) {
            $table->dropUnique('news_translations_news_language_index');
        });
    }
};
