<?php

namespace Database\Seeders;

use App\Models\AboutSection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Copies the former static About page (store.about_page.* language strings and the
 * CMS "about" page title) into about_sections, so /about looks the same after the
 * first deploy. Runs only when the table is empty; safe to run again.
 */
class AboutPageSeeder extends Seeder
{
    public function run(): void
    {
        if (AboutSection::query()->exists()) {
            return;
        }

        // Every installed language that has its own about_page strings (today: en, zh).
        $strings = [];
        foreach (glob(resource_path('lang/*/store.php')) as $file) {
            $lines = require $file;
            if (! empty($lines['about_page'])) {
                $strings[basename(dirname($file))] = ['about' => $lines['about_page'], 'footer' => $lines['footer'] ?? []];
            }
        }
        if (! isset($strings['en'])) {
            return;
        }

        $pageId = DB::table('pages')->where('slug', 'about')->value('id');
        $pageTitles = $pageId
            ? DB::table('page_translations')->where('page_id', $pageId)->pluck('title', 'language_code')->all()
            : [];

        $get = fn (string $lang, string $key) => $strings[$lang]['about'][$key] ?? $strings['en']['about'][$key] ?? '';

        DB::transaction(function () use ($strings, $pageTitles, $get) {
            $position = 0;
            $make = function (string $type, array $settings, callable $translate) use (&$position, $strings) {
                $section = AboutSection::create(['type' => $type, 'sort_order' => $position++, 'is_active' => true, 'settings' => $settings]);
                foreach (array_keys($strings) as $lang) {
                    $section->translations()->create(['language_code' => $lang] + $translate($lang));
                }
            };

            // Hero: page title (as before), eyebrow and lead line.
            $make('hero', [], fn ($l) => [
                'title' => $pageTitles[$l] ?? ($l === 'en' ? null : ($strings[$l]['footer']['about'] ?? null)) ?? $pageTitles['en'] ?? $get($l, 'title'),
                'subtitle' => $get($l, 'narrative_lead'),
                'extra' => ['eyebrow' => $get($l, 'eyebrow')],
            ]);

            // Company narrative.
            $make('text', ['align' => 'center'], fn ($l) => [
                'title' => $get($l, 'title'),
                'body' => '<p>'.e($get($l, 'narrative')).'</p>',
            ]);

            // Mission / Vision / Values cards (values as one line per item).
            $make('cards', ['variant' => 'plain', 'items' => [
                ['icon' => 'fa-solid fa-bullseye'], ['icon' => 'fa-solid fa-eye'], ['icon' => 'fa-solid fa-heart'],
            ]], fn ($l) => ['extra' => ['items' => [
                ['title' => $get($l, 'mission_title'), 'text' => $get($l, 'mission_text')],
                ['title' => $get($l, 'vision_title'), 'text' => $get($l, 'vision_text')],
                ['title' => $get($l, 'values_title'), 'text' => implode("\n", explode('|', $get($l, 'values_list')))],
            ]]]);

            // "Why partner with ZY Energy" strengths.
            $make('cards', ['variant' => 'feature', 'items' => array_map(
                fn ($i) => ['icon' => $get('en', "strength_{$i}_icon")], range(1, 6)
            )], fn ($l) => [
                'title' => $get($l, 'strengths_heading'),
                'subtitle' => $get($l, 'strengths_subheading'),
                'extra' => ['items' => array_map(
                    fn ($i) => ['title' => $get($l, "strength_{$i}_title"), 'text' => $get($l, "strength_{$i}_text")], range(1, 6)
                )],
            ]);

            // Call to action with the same two buttons.
            $make('cta', ['button_link' => '/contact', 'button2_link' => '/products'], fn ($l) => [
                'title' => $get($l, 'cta_title'),
                'subtitle' => $get($l, 'cta_text'),
                'button_text' => $get($l, 'cta_contact'),
                'extra' => ['button2_text' => $get($l, 'cta_quote')],
            ]);
        });
    }
}
