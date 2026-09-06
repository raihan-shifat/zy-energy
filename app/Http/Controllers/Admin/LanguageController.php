<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function changeLanguage(Request $request)
    {
        $lang = $request->input('lang');

        $supportedLanguages = supported_application_locales();

        if (! in_array($lang, $supportedLanguages)) {
            $lang = config('app.fallback_locale', 'en');
        }

        session(['locale' => $lang]);
        app()->setLocale($lang);
        Cookie::queue(Cookie::make('locale', $lang, 60 * 24 * 365));

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'locale' => $lang]);
        }

        return redirect()->back();
    }
}
