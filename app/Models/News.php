<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class News extends Model
{
    use HasFactory;

    protected $fillable = ['slug', 'image_url', 'status'];

    public function translations()
    {
        return $this->hasMany(NewsTranslation::class);
    }

    public function translation()
    {
        return $this->hasOne(NewsTranslation::class)
            ->where('language_code', App::getLocale());
    }

    /**
     * Test/placeholder titles that must never appear on public pages.
     */
    const PLACEHOLDER_TITLES = ['title', 'test', 'test news', 'news title', 'lorem ipsum'];

    /**
     * Only active posts that have a real (non-placeholder) title and a body.
     * Used by public news pages, the homepage and the sitemap.
     */
    public function scopePublic($query)
    {
        $placeholders = implode(',', array_fill(0, count(self::PLACEHOLDER_TITLES), '?'));

        return $query->where('status', 1)
            ->whereHas('translations', function ($q) use ($placeholders) {
                $q->whereRaw("TRIM(COALESCE(title, '')) <> ''")
                    ->whereRaw("LOWER(TRIM(title)) NOT IN ({$placeholders})", self::PLACEHOLDER_TITLES)
                    ->whereRaw("TRIM(COALESCE(body, '')) <> ''");
            });
    }
}
