<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class LocaleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Admin panel: only the admin languages (config seo.admin_locales, default en + zh).
        // Vendor panel: every installed language. Storefront: config seo.store_locales.
        if ($request->is('admin', 'admin/*')) {
            $supportedLocales = admin_locales();
        } elseif ($request->is('vendor', 'vendor/*')) {
            $supportedLocales = supported_application_locales();
        } else {
            $supportedLocales = enabled_store_locales();
        }

        // A /{locale}/ URL prefix wins and is remembered. Remove the parameter so
        // controllers keep receiving only their own route parameters.
        $route = $request->route();
        $routeLocale = $route?->parameter('locale');
        if ($routeLocale !== null) {
            $route->forgetParameter('locale');
        }

        if ($routeLocale !== null && in_array($routeLocale, $supportedLocales, true)) {
            $locale = $routeLocale;
        } else {
            // Prefer the persisted cookie so a first request after switching does
            // not get overridden by a session initialized with the default locale.
            $locale = $request->cookie('locale', session('locale', config('app.fallback_locale', 'en')));
        }
        if (! in_array($locale, $supportedLocales, true)) {
            $locale = config('app.fallback_locale', 'en');
        }

        // The default language has one URL without a prefix: /en/products -> /products.
        // 302, not 301: browsers cache 301s, and the switcher must reach the server
        // every time so the cookie is updated.
        if ($routeLocale === default_store_locale() && $request->isMethod('GET')) {
            session(['locale' => $locale]);
            Cookie::queue(Cookie::make('locale', $locale, 60 * 24 * 365));
            $path = localized_base_path();
            $query = $request->getQueryString();

            return redirect('/'.$path.($query ? '?'.$query : ''), 302);
        }

        App::setLocale($locale);
        load_translation_fallbacks($locale);
        if (session('locale') !== $locale) {
            session(['locale' => $locale]);
        }
        Cookie::queue(Cookie::make('locale', $locale, 60 * 24 * 365));

        return $next($request);
    }
}
