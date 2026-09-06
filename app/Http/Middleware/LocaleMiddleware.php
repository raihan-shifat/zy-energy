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
        $supportedLocales = supported_application_locales();

        // Prefer the persisted cookie so a first request after switching does
        // not get overridden by a session initialized with the default locale.
        $locale = $request->cookie('locale', session('locale', config('app.fallback_locale', 'en')));
        if (! in_array($locale, $supportedLocales, true)) {
            $locale = config('app.fallback_locale', 'en');
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
