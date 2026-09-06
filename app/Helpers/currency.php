<?php

use App\Models\Currency;
use App\Models\StoreSetting;
use Illuminate\Support\Facades\Cache;

if (! function_exists('convert_price')) {
    function convert_price($amount, $currencyCode = null)
    {
        $currencyCode = $currencyCode ?: session('currency', getWebConfig('default_currency', 'USD'));

        $usdExchangeRate = Cache::remember('currency_USD', now()->addMinutes(5), function () {
            return Currency::where('code', 'USD')->value('exchange_rate') ?: 1.0;
        });

        $targetExchangeRate = Cache::remember("currency_{$currencyCode}", now()->addMinutes(5), function () use ($currencyCode) {
            return Currency::where('code', $currencyCode)->value('exchange_rate') ?: 1.0;
        });

        return round($amount * ($targetExchangeRate / $usdExchangeRate), 2);
    }
}

if (! function_exists('currency_to_usd')) {
    function currency_to_usd($amount, $fromCurrency)
    {
        $usdRate = Currency::where('code', 'USD')->value('exchange_rate') ?: 1.0;
        $fromRate = Currency::where('code', $fromCurrency)->value('exchange_rate') ?: 1.0;

        return round($amount * ($usdRate / $fromRate), 2);
    }
}

if (! function_exists('getWebConfig')) {
    function getWebConfig($key, $default = null)
    {
        return Cache::remember("store_setting_{$key}", now()->addMinutes(5), function () use ($key, $default) {
            return StoreSetting::where('key', $key)->value('value') ?? $default;
        });
    }
}

if (! function_exists('activeCurrency')) {
    function activeCurrency()
    {
        $currency = Cache::remember('active_currency_'.session('currency', 'USD'), now()->addMinutes(5), function () {
            return Currency::where('code', session('currency', 'USD'))->first();
        });

        // Fall back to the default currency if the session holds a stale/deleted code
        if (! $currency) {
            $currency = Currency::where('code', 'USD')->first();
        }

        return $currency;
    }
}
