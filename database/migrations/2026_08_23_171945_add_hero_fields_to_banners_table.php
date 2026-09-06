<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->default(0)->after('type');
        });

        Schema::table('banner_translations', function (Blueprint $table) {
            $table->text('subtitle')->nullable()->after('description');
            $table->string('cta_text')->nullable()->after('subtitle');
            $table->string('cta_link')->nullable()->after('cta_text');
        });
    }

    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });

        Schema::table('banner_translations', function (Blueprint $table) {
            $table->dropColumn(['subtitle', 'cta_text', 'cta_link']);
        });
    }
};
