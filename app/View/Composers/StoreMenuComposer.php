<?php

namespace App\View\Composers;

use App\Models\Menu;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class StoreMenuComposer
{
    public function compose(View $view)
    {
        // Composers run for EVERY view render (layout, partials, includes),
        // so this data is cached to avoid dozens of repeated queries per page.
        $headerMenu = Cache::remember('store_header_menu_'.app()->getLocale(), now()->addMinutes(10), function () {
            if (! Cache::remember('schema_has_menus', now()->addHour(), fn () => Schema::hasTable('menus'))) {
                return null;
            }

            return Menu::where('status', 1)
                ->select(['id', 'title', 'status'])
                ->with([
                    'menuItems' => function ($query) {
                        $query->select(['id', 'menu_id', 'slug', 'order_number', 'parent_id'])
                            ->orderBy('order_number', 'asc')
                            ->with([
                                'translation' => function ($query) {
                                    $query->where('language_code', app()->getLocale());
                                },
                                'translations',
                            ]);
                    },
                ])
                ->first();
        });

        $view->with('headerMenu', $headerMenu);
    }
}
