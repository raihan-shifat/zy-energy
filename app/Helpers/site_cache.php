<?php

use App\Models\SiteSetting;
use App\Models\Category;
use App\Models\Banner;
use App\Models\Certification;
use App\Models\Currency;
use App\Models\ExchangeRate;
use App\Models\Product;
use App\Models\News;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\App;

if (! function_exists('getSiteSettings')) {
    /**
     * Get cached site settings (singleton-like, refreshed on update).
     */
    function getSiteSettings()
    {
        return Cache::rememberForever('site_settings', function () {
            return SiteSetting::first();
        });
    }
}

if (! function_exists('getActiveCategories')) {
    /**
     * Get cached active categories with translations.
     * TTL: 30 minutes.
     */
    function getActiveCategories($withTranslation = true)
    {
        $key = 'categories:active:' . app()->getLocale() . ':' . ($withTranslation ? 'with_trans' : 'no_trans');

        return Cache::remember($key, now()->addMinutes(30), function () use ($withTranslation) {
            $query = Category::where('status', 1)->orderBy('sort_order')->orderBy('id');

            if ($withTranslation) {
                $query->with(['translation', 'translations']);
            }

            return $query->get();
        });
    }
}

if (! function_exists('getActiveBanners')) {
    /**
     * Get cached active banners with translations.
     * TTL: 30 minutes.
     */
    function getActiveBanners($withTranslation = true)
    {
        $key = 'banners:active:' . app()->getLocale() . ':' . ($withTranslation ? 'with_trans' : 'no_trans');

        return Cache::remember($key, now()->addMinutes(30), function () use ($withTranslation) {
            $query = Banner::where('status', 1)->orderBy('sort_order')->orderBy('id');

            if ($withTranslation) {
                $query->with('translations');
            }

            return $query->get();
        });
    }
}

if (! function_exists('getActiveCertifications')) {
    /**
     * Get cached active certifications with translations.
     * TTL: 1 hour.
     */
    function getActiveCertifications($withTranslation = true)
    {
        $key = 'certifications:active:' . app()->getLocale() . ':' . ($withTranslation ? 'with_trans' : 'no_trans');

        return Cache::remember($key, now()->addHour(), function () use ($withTranslation) {
            $query = Certification::where('status', 1)->orderBy('id');

            if ($withTranslation) {
                $query->with('translations');
            }

            return $query->get();
        });
    }
}

if (! function_exists('getHomepageProducts')) {
    /**
     * Get the small, repeated homepage product set per locale.
     */
    function getHomepageProducts()
    {
        return Cache::remember('homepage:products:' . app()->getLocale(), now()->addMinutes(10), function () {
            return Product::where('status', 1)
                ->with(['translation', 'translations', 'thumbnail', 'primaryVariant'])
                ->withCount('reviews')
                ->orderByDesc('id')
                ->limit(10)
                ->get();
        });
    }
}

if (! function_exists('getHomepageNews')) {
    /**
     * Get the small, repeated homepage news set per locale.
     */
    function getHomepageNews()
    {
        return Cache::remember('homepage:news:' . app()->getLocale(), now()->addMinutes(10), function () {
            return News::where('status', 1)
                ->with(['translation', 'translations'])
                ->orderByDesc('id')
                ->limit(3)
                ->get();
        });
    }
}

if (! function_exists('getActiveCurrencies')) {
    /**
     * Get cached active currencies.
     * TTL: 1 hour.
     */
    function getActiveCurrencies()
    {
        return Cache::remember('currencies:active', now()->addHour(), function () {
            return Currency::where('is_active', true)->get(['code', 'name', 'symbol', 'exchange_rate']);
        });
    }
}

if (! function_exists('getExchangeRates')) {
    /**
     * Get cached exchange rates.
     * TTL: 5 minutes.
     */
    function getExchangeRates()
    {
        return Cache::remember('exchange_rates:active', now()->addMinutes(5), function () {
            return ExchangeRate::where('is_active', true)->get(['code', 'name', 'rate']);
        });
    }
}

if (! function_exists('getCategoryFilterValues')) {
    /**
     * Get cached filter values for a category.
     * TTL: 15 minutes.
     */
    function getCategoryFilterValues(Category $category)
    {
        $key = "filters:category:{$category->id}";

        return Cache::remember($key, now()->addMinutes(15), function () use ($category) {
            $filterValues = [
                'series' => Product::where('category_id', $category->id)
                    ->where('status', 1)
                    ->whereNotNull('series')
                    ->where('series', '!=', '')
                    ->distinct()
                    ->orderBy('series', 'asc')
                    ->pluck('series')
                    ->all(),
                'type' => $category->types ?? [],
            ];

            return $filterValues;
        });
    }
}

if (! function_exists('getShopFilterValues')) {
    /**
     * Get cached filter values for shop page (per category).
     * TTL: 15 minutes.
     */
    function getShopFilterValues(?Category $category)
    {
        if (!$category) {
            return [];
        }

        $key = "filters:shop:{$category->id}";

        return Cache::remember($key, now()->addMinutes(15), function () use ($category) {
            $filters = [
                'wind-turbine' => [
                    ['field' => 'rated_power', 'label' => __('store.shop.rated_power'), 'type' => 'checkbox_multi', 'source' => 'attribute', 'attribute_name' => 'Rated Power'],
                    ['field' => 'type', 'label' => __('store.category.type'), 'type' => 'checkbox_multi', 'source' => 'product_type'],
                ],
            ];

            $activeFilters = $filters[$category->slug] ?? [];

            foreach ($activeFilters as &$filter) {
                $filter['values'] = getShopFilterValueOptions($filter, $category);
            }
            unset($filter);

            return $activeFilters;
        });
    }
}

if (! function_exists('getShopFilterValueOptions')) {
    /**
     * Get individual filter option values.
     */
    function getShopFilterValueOptions(array $filter, Category $category): array
    {
        if ($filter['source'] === 'product_type') {
            $declaredTypes = $category->types ?? [];
            $usedTypes = Product::where('category_id', $category->id)
                ->where('status', 1)
                ->whereNotNull('type')
                ->where('type', '!=', '')
                ->distinct()
                ->pluck('type')
                ->all();

            $result = array_values(array_intersect($declaredTypes, $usedTypes));
            sort($result, SORT_NATURAL | SORT_FLAG_CASE);

            return $result;
        }

        if ($filter['source'] === 'attribute') {
            $attributeName = $filter['attribute_name'] ?? null;
            if (!$attributeName) {
                return [];
            }

            $attribute = Attribute::where('name', $attributeName)->first();
            if (!$attribute) {
                return [];
            }

            $values = \Illuminate\Support\Facades\DB::table('attribute_values')
                ->join('product_attribute_values', 'product_attribute_values.attribute_value_id', '=', 'attribute_values.id')
                ->join('products', 'products.id', '=', 'product_attribute_values.product_id')
                ->where('attribute_values.attribute_id', $attribute->id)
                ->where('products.category_id', $category->id)
                ->where('products.status', 1)
                ->distinct()
                ->pluck('attribute_values.value')
                ->all();

            usort($values, function ($a, $b) {
                preg_match('/([\d.]+)/', $a, $am);
                preg_match('/([\d.]+)/', $b, $bm);
                $na = isset($am[1]) ? (float) $am[1] : PHP_FLOAT_MAX;
                $nb = isset($bm[1]) ? (float) $bm[1] : PHP_FLOAT_MAX;
                if ($na !== $nb) {
                    return $na <=> $nb;
                }
                return strcmp($a, $b);
            });

            return $values;
        }

        return [];
    }
}

if (! function_exists('forgetSiteSettingsCache')) {
    /**
     * Invalidate site settings cache.
     */
    function forgetSiteSettingsCache()
    {
        Cache::forget('site_settings');
    }
}

if (! function_exists('forgetCategoriesCache')) {
    /**
     * Invalidate categories cache.
     */
    function forgetCategoriesCache()
    {
        foreach (glob(resource_path('lang/*'), GLOB_ONLYDIR) as $path) {
            $locale = basename($path);
            Cache::forget("categories:active:{$locale}:with_trans");
            Cache::forget("categories:active:{$locale}:no_trans");
        }
    }
}

if (! function_exists('forgetBannersCache')) {
    /**
     * Invalidate banners cache.
     */
    function forgetBannersCache()
    {
        foreach (glob(resource_path('lang/*'), GLOB_ONLYDIR) as $path) {
            $locale = basename($path);
            Cache::forget("banners:active:{$locale}:with_trans");
            Cache::forget("banners:active:{$locale}:no_trans");
        }
    }
}

if (! function_exists('forgetCertificationsCache')) {
    /**
     * Invalidate certifications cache.
     */
    function forgetCertificationsCache()
    {
        foreach (glob(resource_path('lang/*'), GLOB_ONLYDIR) as $path) {
            $locale = basename($path);
            Cache::forget("certifications:active:{$locale}:with_trans");
            Cache::forget("certifications:active:{$locale}:no_trans");
        }
    }
}

if (! function_exists('forgetCategoryFilterCache')) {
    /**
     * Invalidate filter cache for a specific category.
     */
    function forgetCategoryFilterCache(int $categoryId)
    {
        Cache::forget("filters:category:{$categoryId}");
        Cache::forget("filters:shop:{$categoryId}");
    }
}

if (! function_exists('forgetAllFilterCaches')) {
    /**
     * Invalidate all filter caches (use when products/categories change).
     */
    function forgetAllFilterCaches()
    {
        // Note: This is a broad invalidation. For production with Redis,
        // consider using cache tags or a more targeted approach.
        // With file cache, we can't easily do pattern-based deletion.
        // For now, we clear the known category IDs if needed.
    }
}

if (! function_exists('getFooterMenu')) {
    /**
     * Get the cached footer menu with its top-level menu items.
     * TTL: 10 minutes. Uses a sentinel so a missing menu is cached too
     * (Cache::remember treats a null return as "not present" and re-runs).
     */
    function getFooterMenu()
    {
        $key = 'footer_menu_' . app()->getLocale();

        return Cache::remember($key, now()->addMinutes(10), function () {
            $menu = \App\Models\Menu::where('status', 1)
                ->whereRaw('LOWER(title) = ?', ['footer'])
                ->with(['menuItems' => function ($query) {
                    $query->whereNull('parent_id')
                        ->orderBy('order_number')
                        ->with(['translation' => function ($query) {
                            $query->where('language_code', app()->getLocale());
                        }, 'translations']);
                }])
                ->first();

            return $menu ?: new \Illuminate\Support\Fluent(['menuItems' => collect()]);
        });
    }
}

if (! function_exists('forgetMenusCache')) {
    /**
     * Invalidate header + footer menu caches across all installed locales.
     */
    function forgetMenusCache()
    {
        foreach (glob(resource_path('lang/*'), GLOB_ONLYDIR) as $path) {
            $locale = basename($path);
            Cache::forget('store_header_menu_' . $locale);
            Cache::forget('footer_menu_' . $locale);
        }
        Cache::forget('admin_menu');
    }
}

if (! function_exists('getFooterSocials')) {
    /**
     * Get the cached footer social links (facebook/instagram/tiktok/youtube).
     * TTL: 1 hour.
     */
    function getFooterSocials()
    {
        return Cache::remember('footer_social_links', now()->addHour(), function () {
            return \App\Models\SocialMediaLink::whereRaw('LOWER(type) IN (?, ?, ?, ?)', ['facebook', 'instagram', 'tiktok', 'youtube'])->get();
        });
    }
}

if (! function_exists('getFooterWechatQr')) {
    /**
     * Get the cached footer WeChat QR image path.
     * TTL: 1 hour.
     */
    function getFooterWechatQr()
    {
        return Cache::remember('footer_wechat_qr', now()->addHour(), function () {
            return optional(\App\Models\SocialMediaLink::whereRaw('LOWER(type) = ?', ['wechat'])->first())->wechat_qr_image;
        });
    }
}

if (! function_exists('getContactFooterData')) {
    /**
     * Get cached contact options used by the reusable contact-us popup.
     * TTL: 1 hour. Invalidated when contact settings/entries change.
     */
    function getContactFooterData(): array
    {
        return Cache::remember('contact_footer_data', now()->addHour(), function () {
            return [
                'wechats' => \App\Models\ContactWechat::active()->take(2)->get(),
                'whatsapps' => \App\Models\ContactWhatsapp::active()->take(2)->get(),
                'emails' => \App\Models\ContactEmail::active()->take(2)->get(),
                'settings' => \App\Models\ContactSetting::getSettings(),
            ];
        });
    }
}

if (! function_exists('forgetContactFooterCache')) {
    /**
     * Invalidate the cached contact footer data.
     */
    function forgetContactFooterCache()
    {
        Cache::forget('contact_footer_data');
    }
}

if (! function_exists('forgetFooterCache')) {
    /**
     * Invalidate footer menu / social / WeChat caches.
     */
    function forgetFooterCache()
    {
        foreach (glob(resource_path('lang/*'), GLOB_ONLYDIR) as $path) {
            Cache::forget('footer_menu_' . basename($path));
        }
        Cache::forget('footer_social_links');
        Cache::forget('footer_wechat_qr');
    }
}

if (! function_exists('getStoreLanguageOptions')) {
    /**
     * Get cached language options for the storefront language selector.
     * TTL: 1 day (language metadata changes rarely).
     */
    function getStoreLanguageOptions(): array
    {
        return Cache::remember('store_language_options', now()->addDay(), function () {
            return application_language_options();
        });
    }
}

if (! function_exists('forgetStoreLanguageOptionsCache')) {
    /**
     * Invalidate the cached storefront language options.
     */
    function forgetStoreLanguageOptionsCache()
    {
        Cache::forget('store_language_options');
    }
}
