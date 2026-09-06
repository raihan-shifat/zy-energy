<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Page;

class PageController extends Controller
{
    public function show($slug)
    {
        $page = Page::where('slug', $slug)
            ->where('status', 1)
            ->firstOrFail();

        $locale = app()->getLocale();

        // Fall back to English when the current-locale translation is missing
        $translation = $page->translation()
            ?: $page->translations()->where('language_code', 'en')->first();

        if (! $translation) {
            abort(404);
        }

        if ($page->slug === 'about') {
            return view('themes.xylo.about', compact('page', 'translation'))
                ->with('siteSettings', getSiteSettings());
        }

        return view('themes.xylo.page', compact('page', 'translation'));
    }
}
