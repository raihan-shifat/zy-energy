<?php

namespace App\View\Composers;

use App\Models\Language;
use App\Models\Menu;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AdminLanguageComposer
{
    public function compose(View $view)
    {
        // Cached: composers run for every admin view render (8+ per page).
        $menu = Cache::remember('admin_menu', now()->addMinutes(10), function () {
            if (! Cache::remember('schema_has_menus', now()->addHour(), fn () => Schema::hasTable('menus'))) {
                return null;
            }

            return Menu::first();
        });
        $view->with('menu', $menu);

        $hasLanguagesTable = Cache::remember('schema_has_languages', now()->addHour(), fn () => Schema::hasTable('languages'));
        $activeLanguages = $hasLanguagesTable
            ? Language::whereIn('code', supported_application_locales())->orderBy('name')->get()
            : collect();
        $view->with('activeLanguages', $activeLanguages);
    }
}
