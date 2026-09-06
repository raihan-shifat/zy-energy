<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('contact_settings', function (Blueprint $table) {
            $table->id();
            $table->string('page_title')->default('Contact Us');
            $table->text('page_subtitle')->nullable()->comment('Subtitle/description under the title');
            $table->string('wechat_section_title')->default('WeChat');
            $table->text('wechat_section_description')->nullable()->comment('Description under WeChat section title');
            $table->string('whatsapp_section_title')->default('WhatsApp');
            $table->text('whatsapp_section_description')->nullable()->comment('Description under WhatsApp section title');
            $table->string('email_section_title')->default('Email');
            $table->text('email_section_description')->nullable()->comment('Description under Email section title');
            $table->text('bottom_message')->nullable()->comment('Bottom message displayed at the bottom of the page');
            $table->string('response_time_text')->default('Our team typically replies within 24 hours.');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_settings');
    }
};