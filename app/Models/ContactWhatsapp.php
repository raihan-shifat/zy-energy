<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactWhatsapp extends Model
{
    use HasFactory;

    protected $table = 'contact_whatsapps';

    protected $fillable = [
        'name',
        'phone',
        'whatsapp_url',
        'description',
        'purpose',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getFormattedPhoneAttribute(): string
    {
        return preg_replace('/[^0-9+]/', '', $this->phone);
    }

    public function getWhatsAppLinkAttribute(): string
    {
        if ($this->whatsapp_url) {
            return $this->whatsapp_url;
        }
        $digits = preg_replace('/[^0-9]/', '', $this->phone);
        return "https://wa.me/{$digits}";
    }

    protected static function booted(): void
    {
        static::saved(function () {
            if (function_exists('forgetContactFooterCache')) {
                forgetContactFooterCache();
            }
        });

        static::deleted(function () {
            if (function_exists('forgetContactFooterCache')) {
                forgetContactFooterCache();
            }
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}