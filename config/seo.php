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
    | Languages offered in the public language switcher, in /{locale}/ URLs,
    | hreflang tags and the sitemap. "*" means every installed language
    | (every folder in resources/lang). Or list codes, e.g. STORE_LOCALES="en,zh".
    | Codes without a lang folder are ignored. The default (fallback) locale is
    | served without a URL prefix.
    |
    */

    'store_locales' => array_values(array_filter(array_map('trim', explode(',', (string) env('STORE_LOCALES', '*'))))),

    /*
    |--------------------------------------------------------------------------
    | Admin panel languages
    |--------------------------------------------------------------------------
    |
    | Languages the admin panel can be switched to (dropdown + middleware).
    | Anything else falls back to the default locale inside /admin.
    |
    */

    'admin_locales' => array_values(array_filter(array_map('trim', explode(',', (string) env('ADMIN_LOCALES', 'en,zh'))))),

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
