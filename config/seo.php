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

    /*
    |--------------------------------------------------------------------------
    | Analytics (Google Tag Manager / GA4)
    |--------------------------------------------------------------------------
    |
    | Leave both empty to output no analytics code at all. If a GTM container
    | is set, GA4 should be configured inside GTM and the GA4 ID here is not
    | used. Scripts load only after the visitor accepts the cookie notice.
    | Set GTM_CONTAINER_ID="GTM-XXXXXXX" and/or GA4_MEASUREMENT_ID="G-XXXXXXXXXX".
    |
    */

    'analytics' => [
        'gtm_id' => env('GTM_CONTAINER_ID', ''),
        'ga4_id' => env('GA4_MEASUREMENT_ID', ''),
    ],

];
