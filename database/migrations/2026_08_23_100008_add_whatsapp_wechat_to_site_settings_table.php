<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add B2B quick-contact fields (WhatsApp + WeChat QR) to global site settings.
     */
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('whatsapp_number')->nullable()->after('contact_phone');
            $table->string('wechat_qr_image')->nullable()->after('whatsapp_number');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['whatsapp_number', 'wechat_qr_image']);
        });
    }
};
