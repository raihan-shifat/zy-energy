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

    /** Images in display order (cover first is not implied; see coverPath()). */
    public function images()
    {
        return $this->hasMany(NewsImage::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Storage path of the cover image, or null when there is no usable image:
     * the image marked as cover, else the first image, else the old single image column.
     * Older saves stored "0" in image_url when writing the file failed; that value and
     * missing files count as "no image". The column itself is not changed.
     */
    public function coverPath(): ?string
    {
        $images = $this->relationLoaded('images') ? $this->images : $this->images()->get();
        $cover = $images->firstWhere('is_primary', true) ?? $images->first();
        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        if ($cover && $disk->exists($cover->path)) {
            return $cover->path;
        }

        $path = (string) $this->image_url;
        if ($path === '' || $path === '0') {
            return null;
        }

        return $disk->exists($path) ? $path : null;
    }

    /** Usable images other than the cover, in order (for the public gallery). */
    public function galleryImages(): array
    {
        $cover = $this->coverPath();
        $disk = \Illuminate\Support\Facades\Storage::disk('public');

        return $this->images
            ->filter(fn ($image) => $image->path !== $cover && $disk->exists($image->path))
            ->values()
            ->all();
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
