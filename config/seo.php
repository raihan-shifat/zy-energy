<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Canonical Domain
    |--------------------------------------------------------------------------
    |
    | Used for every URL written into the sitemap. Use the final public domain
    | without a trailing slash. Override with SITE_CANONICAL_DOMAIN in .env
    | once the domain is decided.
    |
    */

    'canonical_domain' => env('SITE_CANONICAL_DOMAIN', 'https://zzyenergy.com'),

    /*
    |--------------------------------------------------------------------------
    | Public storefront languages
    |--------------------------------------------------------------------------
    |
    | Languages offered in the public language switcher, in /{locale}/ URLs
    | and in hreflang tags. Only list languages whose resources/lang files are
    | translated well enough to publish; codes without a lang folder are ignored.
    | The default (fallback) locale is served without a URL prefix.
    | Override with STORE_LOCALES="en,zh" in .env.
    |
    */

    'store_locales' => array_values(array_filter(array_map('trim', explode(',', (string) env('STORE_LOCALES', 'en,zh'))))),

];
