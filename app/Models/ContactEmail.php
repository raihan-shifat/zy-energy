<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactEmail extends Model
{
    use HasFactory;

    protected $table = 'contact_emails';

    protected $fillable = [
        'name',
        'email',
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

    public function getMailtoLinkAttribute(): string
    {
        return "mailto:{$this->email}";
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