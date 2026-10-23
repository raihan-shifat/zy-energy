<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * About Us page as ordered, translatable sections with media.
 * After the tables are created the current static page is copied in
 * (AboutPageSeeder, only when the table is empty), so /about looks the same.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('about_sections', function (Blueprint $table) {
            $table->id();
            // hero, text, image_text, stats, cards, gallery, video, cta
            $table->string('type', 30);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            // Language-independent values: image position, links, stat numbers, card icons, video URL…
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('about_section_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('about_section_id')->constrained('about_sections')->cascadeOnDelete();
            $table->string('language_code', 10);
            $table->string('title')->nullable();
            $table->text('subtitle')->nullable();
            $table->longText('body')->nullable();
            $table->string('button_text')->nullable();
            // Translated extras: eyebrow, second button text, item labels/titles/texts.
            $table->json('extra')->nullable();
            $table->timestamps();
            $table->unique(['about_section_id', 'language_code']);
        });

        Schema::create('about_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('about_section_id')->constrained('about_sections')->cascadeOnDelete();
            $table->string('kind', 10); // image, video, poster
            $table->string('path')->nullable(); // public-disk path for uploads
            $table->string('url', 500)->nullable(); // YouTube/Vimeo embed URL
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['about_section_id', 'sort_order']);
        });

        Schema::create('about_media_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('about_media_id')->constrained('about_media')->cascadeOnDelete();
            $table->string('language_code', 10);
            $table->string('alt')->nullable();
            $table->string('caption', 500)->nullable();
            $table->timestamps();
            $table->unique(['about_media_id', 'language_code']);
        });

        (new \Database\Seeders\AboutPageSeeder)->run();
    }

    public function down(): void
    {
        Schema::dropIfExists('about_media_translations');
        Schema::dropIfExists('about_media');
        Schema::dropIfExists('about_section_translations');
        Schema::dropIfExists('about_sections');
    }
};
