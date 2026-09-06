<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('social_media_links', function (Blueprint $table) {
            $table->string('wechat_qr_image')->nullable()->after('link');
        });
    }

    public function down(): void
    {
        Schema::table('social_media_links', function (Blueprint $table) {
            $table->dropColumn('wechat_qr_image');
        });
    }
};
