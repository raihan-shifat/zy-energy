<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One block of the About Us page (hero, text, image_text, stats, cards, gallery, video, cta).
 */
class AboutSection extends Model
{
    public const TYPES = ['hero', 'text', 'image_text', 'stats', 'cards', 'gallery', 'video', 'cta'];

    protected $fillable = ['type', 'sort_order', 'is_active', 'settings'];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function translations()
    {
        return $this->hasMany(AboutSectionTranslation::class);
    }

    public function media()
    {
        return $this->hasMany(AboutMedia::class)->orderBy('sort_order')->orderBy('id');
    }

    /** Setting value with a default. */
    public function setting(string $key, $default = null)
    {
        return data_get($this->settings ?? [], $key, $default);
    }
}
