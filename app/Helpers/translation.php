<?php

if (! function_exists('has_translation_content')) {
    /**
     * Whether a single translation array has any real (non-empty) content.
     * Uploaded files always count as content.
     */
    function has_translation_content($translation)
    {
        if (! is_array($translation)) {
            return false;
        }

        foreach ($translation as $value) {
            if ($value instanceof \Illuminate\Http\UploadedFile) {
                return true;
            }
            if (is_string($value) && trim($value) !== '') {
                return true;
            }
        }

        return false;
    }
}

if (! function_exists('supported_application_locales')) {
    /**
     * Return locales that have translation resources installed.
     * The languages table controls presentation metadata, not whether a
     * shipped locale can be selected.
     */
    function supported_application_locales(): array
    {
        return collect(glob(resource_path('lang/*'), GLOB_ONLYDIR))
            ->map(fn ($path) => basename($path))
            ->filter(fn ($locale) => preg_match('/^[a-z]{2}(?:[_-][A-Z]{2})?$/', $locale))
            ->values()
            ->all();
    }
}

if (! function_exists('application_language_options')) {
    /**
     * Return installed locales with database-provided display metadata.
     */
    function application_language_options(): array
    {
        $locales = supported_application_locales();

        if (! \Illuminate\Support\Facades\Schema::hasTable('languages')) {
            return collect($locales)->map(fn ($code) => [
                'code' => $code,
                'name' => strtoupper($code),
                'translated_text' => strtoupper($code),
            ])->all();
        }

        $languages = \App\Models\Language::whereIn('code', $locales)
            ->get(['code', 'name', 'translated_text'])
            ->keyBy('code');

        return collect($locales)->map(fn ($code) => [
            'code' => $code,
            'name' => $languages->get($code)?->name ?: strtoupper($code),
            'translated_text' => $languages->get($code)?->translated_text ?: strtoupper($code),
        ])->all();
    }
}

if (! function_exists('localized_translation_value')) {
    /**
     * Resolve a translated model attribute without forcing English first.
     */
    function localized_translation_value($translations, string $attribute, $fallback = null)
    {
        $translations = collect($translations ?? []);
        $translation = $translations->firstWhere('language_code', app()->getLocale())
            ?? $translations->firstWhere('language_code', config('app.fallback_locale', 'en'))
            ?? $translations->first();

        return data_get($translation, $attribute, $fallback);
    }
}

if (! function_exists('load_translation_fallbacks')) {
    /**
     * Fill incomplete locale files from English without overwriting translations.
     * This keeps newly added UI keys usable in every installed locale.
     */
    function load_translation_fallbacks(string $locale): void
    {
        if ($locale === config('app.fallback_locale', 'en')) {
            return;
        }

        foreach (['store', 'cms'] as $group) {
            $fallbackPath = resource_path("lang/" . config('app.fallback_locale', 'en') . "/{$group}.php");
            $localePath = resource_path("lang/{$locale}/{$group}.php");

            if (! is_file($fallbackPath) || ! is_file($localePath)) {
                continue;
            }

            $fallback = require $fallbackPath;
            $current = require $localePath;
            $flatten = function (array $lines, string $prefix = '') use (&$flatten): array {
                $result = [];
                foreach ($lines as $key => $value) {
                    $fullKey = $prefix === '' ? $key : $prefix . '.' . $key;
                    if (is_array($value)) {
                        $result += $flatten($value, $fullKey);
                    } else {
                        $result[$fullKey] = $value;
                    }
                }
                return $result;
            };

            $translator = app('translator');
            $translator->load('*', $group, $locale);
            $translator->addLines($flatten($fallback, $group), $locale);
            $translator->addLines($flatten($current, $group), $locale);
        }
    }
}

if (! function_exists('clean_translations')) {
    /**
     * Remove empty translation arrays so empty language tabs are never saved
     * (only languages the admin actually filled in get created/updated).
     */
    function clean_translations($translations)
    {
        if (! is_array($translations)) {
            return [];
        }

        return array_filter($translations, function ($translation) {
            return has_translation_content($translation);
        });
    }
}
