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

if (! function_exists('enabled_store_locales')) {
    /**
     * Storefront languages that are switched on (config seo.store_locales) and
     * have a resources/lang folder. The default locale is always included first.
     */
    function enabled_store_locales(): array
    {
        $default = default_store_locale();
        $configured = (array) config('seo.store_locales', ['*']);
        $installed = supported_application_locales();

        // "*" = every installed language
        $enabled = in_array('*', $configured, true) ? $installed : array_intersect($configured, $installed);

        return array_values(array_unique(array_merge([$default], $enabled)));
    }
}

if (! function_exists('admin_locales')) {
    /**
     * Languages the admin panel can use (config seo.admin_locales, default en + zh)
     * that also have a resources/lang folder. The default locale is always included.
     */
    function admin_locales(): array
    {
        $default = default_store_locale();
        $enabled = array_intersect((array) config('seo.admin_locales', [$default, 'zh']), supported_application_locales());

        return array_values(array_unique(array_merge([$default], $enabled)));
    }
}

if (! function_exists('default_store_locale')) {
    function default_store_locale(): string
    {
        return (string) config('app.fallback_locale', 'en');
    }
}

if (! function_exists('localized_base_path')) {
    /**
     * Current path without a leading /{locale} segment, e.g. "zh/products" -> "products".
     */
    function localized_base_path(): string
    {
        $segments = explode('/', trim(request()->path(), '/'));

        if (in_array($segments[0] ?? '', enabled_store_locales(), true)) {
            array_shift($segments);
        }

        return implode('/', $segments);
    }
}

if (! function_exists('language_switch_url')) {
    /**
     * Link for the language switcher: same page and query string in another language.
     */
    function language_switch_url(string $locale): string
    {
        $path = localized_base_path();
        $query = request()->getQueryString();

        return '/'.$locale.($path !== '' ? '/'.$path : '').($query ? '?'.$query : '');
    }
}

if (! function_exists('alternate_url')) {
    /**
     * Absolute hreflang URL for this page in a language (no query string).
     * The default locale has no prefix; others live under /{locale}/.
     */
    function alternate_url(string $locale): string
    {
        $base = rtrim((string) config('seo.canonical_domain'), '/');
        $path = localized_base_path();
        $prefix = $locale === default_store_locale() ? '' : '/'.$locale;
        $url = $base.$prefix.($path !== '' ? '/'.$path : '');

        return $prefix === '' && $path === '' ? $url.'/' : $url;
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

if (! function_exists('log_translation_gap_once')) {
    /**
     * Log a missing translation once per day per $key, so a gap shows up in the
     * logs without flooding them on every page view. Never breaks the page.
     */
    function log_translation_gap_once(string $key, string $message, array $context): void
    {
        static $seen = [];
        if (isset($seen[$key])) {
            return;
        }
        $seen[$key] = true;

        try {
            if (\Illuminate\Support\Facades\Cache::add('translation-gap:'.$key, true, now()->addDay())) {
                \Illuminate\Support\Facades\Log::warning($message, $context);
            }
        } catch (\Throwable $e) {
            // Logging a gap must never take the storefront down.
        }
    }
}

if (! function_exists('localized_translation')) {
    /**
     * The translation row for the current locale, else the fallback locale (en),
     * else any row. A missing current-locale row is logged with its table and
     * record ID so absent content (e.g. no zh product name) can be found.
     */
    function localized_translation($translations)
    {
        $translations = collect($translations ?? []);
        $locale = app()->getLocale();

        $translation = $translations->firstWhere('language_code', $locale);
        if ($translation !== null || $translations->isEmpty()) {
            return $translation;
        }

        $sample = $translations->first();
        $table = $sample instanceof \Illuminate\Database\Eloquent\Model ? $sample->getTable() : 'unknown';
        $attributes = $sample instanceof \Illuminate\Database\Eloquent\Model ? $sample->getAttributes() : (array) $sample;
        $ownerKey = collect(array_keys($attributes))->first(fn ($k) => $k !== 'id' && str_ends_with($k, '_id'));
        $recordId = $ownerKey ? ($attributes[$ownerKey] ?? null) : null;

        log_translation_gap_once("row:{$locale}:{$table}:{$recordId}", 'Missing translation row, showing fallback', [
            'locale' => $locale,
            'table' => $table,
            'record_key' => $ownerKey,
            'record_id' => $recordId,
        ]);

        return $translations->firstWhere('language_code', config('app.fallback_locale', 'en'))
            ?? $sample;
    }
}

if (! function_exists('localized_translation_value')) {
    /**
     * Resolve a translated model attribute without forcing English first.
     */
    function localized_translation_value($translations, string $attribute, $fallback = null)
    {
        return data_get(localized_translation($translations), $attribute, $fallback);
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

            $fallbackLines = $flatten($fallback, $group);
            $currentLines = $flatten($current, $group);

            $missingKeys = array_keys(array_diff_key($fallbackLines, $currentLines));
            if ($missingKeys !== []) {
                log_translation_gap_once("keys:{$locale}:{$group}", 'Missing translation keys, showing English', [
                    'locale' => $locale,
                    'file' => "{$group}.php",
                    'count' => count($missingKeys),
                    'keys' => array_slice($missingKeys, 0, 50),
                ]);
            }

            $translator = app('translator');
            $translator->load('*', $group, $locale);
            $translator->addLines($fallbackLines, $locale);
            $translator->addLines($currentLines, $locale);
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
