<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Header menu label "News" -> "News & Blog" (the header reads menu_item_translations;
 * the footer already uses the store.footer.news language string). URL unchanged.
 * Only titles that still hold a default "News" wording are changed, so a label the
 * admin customised is kept; missing languages get a translated label.
 */
return new class extends Migration
{
    private const LABELS = [
        'en' => 'News & Blog', 'zh' => '新闻与博客', 'ar' => 'الأخبار والمدونة', 'fr' => 'Actualités & Blog',
        'de' => 'News & Blog', 'es' => 'Noticias y Blog', 'pt' => 'Notícias e Blog', 'it' => 'Notizie e Blog',
        'nl' => 'Nieuws & Blog', 'pl' => 'Aktualności i Blog', 'ru' => 'Новости и блог', 'tr' => 'Haberler ve Blog',
        'ja' => 'ニュース＆ブログ', 'ko' => '뉴스 & 블로그', 'th' => 'ข่าวและบล็อก', 'vi' => 'Tin tức & Blog',
        'id' => 'Berita & Blog', 'hi' => 'समाचार और ब्लॉग', 'fa' => 'اخبار و وبلاگ',
    ];

    /** Default "News" titles that may be replaced (a '?' stands for a character lost to an encoding problem). */
    private const DEFAULT_TITLE = '/^(news|noticias|actualit.s|nachrichten|notizie|nieuws|新闻|الأخبار|notícias|новости|haberler|ニュース|뉴스|ข่าว|tin tức|berita|समाचार|اخبار)$/iu';

    public function up(): void
    {
        $items = DB::table('menu_items')->whereIn('slug', ['news', '/news'])->pluck('id');
        $installed = array_map('basename', glob(resource_path('lang/*'), GLOB_ONLYDIR));

        foreach ($items as $itemId) {
            foreach (self::LABELS as $lang => $label) {
                if (! in_array($lang, $installed, true)) {
                    continue;
                }
                $row = DB::table('menu_item_translations')->where('menu_item_id', $itemId)->where('language_code', $lang)->first();
                if (! $row) {
                    DB::table('menu_item_translations')->insert([
                        'menu_item_id' => $itemId, 'language_code' => $lang, 'title' => $label,
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                } elseif (preg_match(self::DEFAULT_TITLE, trim((string) $row->title))) {
                    DB::table('menu_item_translations')->where('id', $row->id)->update(['title' => $label, 'updated_at' => now()]);
                }
            }
        }

        foreach ($installed as $locale) {
            Cache::forget('store_header_menu_'.$locale);
        }
    }

    public function down(): void
    {
        // Labels are content: change them back in Admin -> Menu Items if needed.
    }
};
