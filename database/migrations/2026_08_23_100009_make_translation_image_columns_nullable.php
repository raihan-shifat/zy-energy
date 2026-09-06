<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Make translation image columns nullable so non-English tabs (and image-less
     * entries) can be saved without an uploaded image.
     */
    public function up(): void
    {
        Schema::table('category_translations', function (Blueprint $table) {
            $table->string('image_url')->nullable()->change();
        });

        Schema::table('banner_translations', function (Blueprint $table) {
            $table->string('image_url')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('category_translations', function (Blueprint $table) {
            $table->string('image_url')->nullable(false)->change();
        });

        Schema::table('banner_translations', function (Blueprint $table) {
            $table->string('image_url')->nullable(false)->change();
        });
    }
};
