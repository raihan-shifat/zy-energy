<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Optional hero video per banner: a YouTube/Vimeo/MP4 link, or the storage
     * path of an uploaded video (e.g. "banner_videos/abc.mp4"). Nullable, so
     * existing banners are unchanged.
     */
    public function up(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->string('video_url', 500)->nullable()->after('sort_order');
        });
    }

    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn('video_url');
        });
    }
};
