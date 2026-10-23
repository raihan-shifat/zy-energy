<?php

namespace App\Services;

use App\Models\AboutSection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Data for the public About Us page: active sections in order, with all their
 * translations and media (language is picked at render time). Cached until the
 * admin saves the page.
 */
class AboutPageService
{
    private const CACHE_KEY = 'about_page.sections.v1';

    public function sections(): Collection
    {
        try {
            return Cache::rememberForever(self::CACHE_KEY, fn () => $this->load());
        } catch (\Throwable $e) {
            // A cache problem must not take the page down.
            report($e);

            return $this->load();
        }
    }

    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private function load(): Collection
    {
        return AboutSection::with(['translations', 'media.translations'])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    /**
     * YouTube or Vimeo page/share/embed URL -> privacy-friendly embed URL, or null if not recognised.
     */
    public static function embedUrl(?string $url): ?string
    {
        $url = trim((string) $url);
        if ($url === '' || ! preg_match('#^https?://#i', $url)) {
            return null;
        }
        if (preg_match('#^https?://(?:www\.|m\.)?(?:youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/)|youtube-nocookie\.com/embed/|youtu\.be/)([A-Za-z0-9_-]{11})#i', $url, $m)) {
            return 'https://www.youtube-nocookie.com/embed/'.$m[1];
        }
        if (preg_match('#^https?://(?:www\.|player\.)?vimeo\.com/(?:video/)?(\d{6,12})#i', $url, $m)) {
            return 'https://player.vimeo.com/video/'.$m[1];
        }

        return null;
    }
}
