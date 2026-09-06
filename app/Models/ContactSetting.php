<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactSetting extends Model
{
    use HasFactory;

    protected $table = 'contact_settings';

    protected $fillable = [
        'page_title',
        'page_subtitle',
        'wechat_section_title',
        'wechat_section_description',
        'whatsapp_section_title',
        'whatsapp_section_description',
        'email_section_title',
        'email_section_description',
        'bottom_message',
        'response_time_text',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public static function getSettings(): self
    {
        return self::firstOrCreate([], [
            'page_title' => 'Contact Us',
            'page_subtitle' => 'We\'re here to help. Reach out to our team through WeChat, WhatsApp or Email.',
            'wechat_section_title' => 'WeChat',
            'wechat_section_description' => 'Scan the QR code to chat with us',
            'whatsapp_section_title' => 'WhatsApp',
            'whatsapp_section_description' => 'Chat with us directly on WhatsApp',
            'email_section_title' => 'Email',
            'email_section_description' => 'Send us an email for detailed information.',
            'bottom_message' => 'Our team is ready to assist you with product information, quotations, technical questions and business inquiries.',
            'response_time_text' => 'Our team typically replies within 24 hours.',
        ]);
    }

    protected static function booted(): void
    {
        static::saved(function () {
            if (function_exists('forgetContactFooterCache')) {
                forgetContactFooterCache();
            }
        });
    }
}