<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'status',
        'type',
        'sort_order',
        'video_url',
    ];

    /**
     * How to play this banner's video on the storefront, or null if there is
     * no playable video. Only YouTube, Vimeo and direct MP4/WebM files are
     * accepted, so no other site can be embedded.
     *
     * @return array{type: 'iframe'|'video', src: string}|null
     */
    public function getVideoEmbedAttribute(): ?array
    {
        $value = trim((string) $this->video_url);
        if ($value === '') {
            return null;
        }

        // Uploaded file on the public disk
        if (! preg_match('#^https?://#i', $value)) {
            return preg_match('/\.(mp4|webm)$/i', $value)
                ? ['type' => 'video', 'src' => asset('storage/'.ltrim($value, '/'))]
                : null;
        }

        // YouTube: watch?v=, youtu.be/, shorts/, embed/
        if (preg_match('#^https?://(?:www\.|m\.)?(?:youtube\.com/(?:watch\?(?:.*&)?v=|shorts/|embed/)|youtu\.be/)([A-Za-z0-9_-]{11})#i', $value, $m)) {
            return ['type' => 'iframe', 'src' => 'https://www.youtube-nocookie.com/embed/'.$m[1].'?autoplay=1&rel=0'];
        }

        // Vimeo: vimeo.com/123456 or player.vimeo.com/video/123456
        if (preg_match('#^https?://(?:www\.)?(?:player\.)?vimeo\.com/(?:video/)?(\d+)#i', $value, $m)) {
            return ['type' => 'iframe', 'src' => 'https://player.vimeo.com/video/'.$m[1].'?autoplay=1'];
        }

        // Direct video file link
        if (preg_match('#^https://[^\s"\'<>]+\.(mp4|webm)(\?[^\s"\'<>]*)?$#i', $value)) {
            return ['type' => 'video', 'src' => $value];
        }

        return null;
    }

    /**
     * Get the translations for the banner.
     */
    public function translations()
    {
        return $this->hasMany(BannerTranslation::class);
    }

    public function translation()
    {
        return $this->hasOne(BannerTranslation::class)->where('language_code', App::getLocale());
    }
}
