<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ContactWechat extends Model
{
    use HasFactory;

    protected $table = 'contact_wechats';

    protected $fillable = [
        'name',
        'wechat_id',
        'qr_code',
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

    public function getQrCodeUrlAttribute(): ?string
    {
        if ($this->qr_code) {
            return Storage::disk('public')->url($this->qr_code);
        }
        return null;
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