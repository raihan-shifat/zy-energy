<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Gallery order and cover image for product_images.
 * Existing rows keep their files; they are numbered in upload (id) order and the
 * first image of each product becomes its cover. Only database columns change.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->default(0)->after('variant_id');
            $table->boolean('is_primary')->default(false)->after('sort_order');
            $table->index(['product_id', 'sort_order']);
        });

        DB::table('product_images')
            ->select('id', 'product_id')
            ->orderBy('product_id')
            ->orderBy('id')
            ->chunk(500, function ($rows) {
                static $position = [];
                foreach ($rows as $row) {
                    $index = $position[$row->product_id] ?? 0;
                    DB::table('product_images')->where('id', $row->id)->update([
                        'sort_order' => $index,
                        'is_primary' => $index === 0,
                    ]);
                    $position[$row->product_id] = $index + 1;
                }
            });
    }

    public function down(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->dropIndex(['product_id', 'sort_order']);
            $table->dropColumn(['sort_order', 'is_primary']);
        });
    }
};
