<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/**
 * Several images per news post. The existing single image (news.image_url) is copied
 * in as the cover when it points to a real file; "0" (a failed upload) and missing
 * files are skipped. news.image_url, the news rows and all files are left unchanged.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('news_id')->constrained('news')->cascadeOnDelete();
            $table->string('path');
            $table->string('alt')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            $table->index(['news_id', 'sort_order']);
        });

        $now = now();
        DB::table('news')->select('id', 'image_url')->orderBy('id')->chunk(200, function ($rows) use ($now) {
            foreach ($rows as $row) {
                $path = trim((string) $row->image_url);
                if ($path === '' || $path === '0' || ! Storage::disk('public')->exists($path)) {
                    continue;
                }
                DB::table('news_images')->insert([
                    'news_id' => $row->id, 'path' => $path, 'sort_order' => 0, 'is_primary' => true,
                    'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_images');
    }
};
