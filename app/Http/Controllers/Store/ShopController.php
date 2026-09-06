<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeTranslation;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShopController extends Controller
{
    /**
     * Products listing page (B2B catalogue).
     *
     * Filters are per-category configurable: add an entry to
     * self::categoryFilterConfig() to enable filters for another category.
     */
    public function index(Request $request)
    {
        $locale = app()->getLocale();

        $categoryId = $request->input('category');
        $selectedCategory = $categoryId ? Category::find($categoryId) : null;

        // ------------------------------------------------------------------
        // Per-category filter configuration.
        // Each entry maps a category slug to its extra filters.
        // 'source' => 'attribute' pulls options from the Attributes system.
        // 'source' => 'product_type' pulls from the category's types field.
        // Categories not listed here get no extra filters (Sort By only).
        // ------------------------------------------------------------------
        $categoryFilters = [
            'wind-turbine' => [
                ['field' => 'rated_power', 'label' => __('store.shop.rated_power'), 'type' => 'checkbox_multi', 'source' => 'attribute', 'attribute_name' => 'Rated Power'],
                ['field' => 'type', 'label' => __('store.category.type'), 'type' => 'checkbox_multi', 'source' => 'product_type'],
            ],
            // Add more categories here later, e.g.:
            // 'solar-panel' => [
            //     ['field' => 'wattage', 'label' => 'Wattage', 'type' => 'checkbox_multi', 'source' => 'attribute', 'attribute_name' => 'Wattage'],
            // ],
        ];

        // Resolve active filter config based on the selected category
        $activeFilters = [];
        if ($selectedCategory && isset($categoryFilters[$selectedCategory->slug])) {
            $activeFilters = $categoryFilters[$selectedCategory->slug];
        }

        // Build dynamic filter values for each active filter using cached helper
        foreach ($activeFilters as &$filter) {
            $filter['values'] = getShopFilterValueOptions($filter, $selectedCategory);
        }
        unset($filter);

        /* ------------------------------------------------------------------
         * Series filter — commented out, restore later if needed.
         *
        $seriesValues = Product::where('status', 1)
            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
            ->whereNotNull('series')
            ->where('series', '!=', '')
            ->distinct()
            ->orderBy('series', 'asc')
            ->pluck('series')
            ->all();
         * ------------------------------------------------------------------
         */

        $query = Product::with(['translation', 'translations', 'thumbnail', 'primaryVariant'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->where('status', 1);

        // Category filter
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        /* ------------------------------------------------------------------
         * Series filter query logic — commented out, restore later if needed.
         *
        if ($request->filled('series') && $request->input('series') !== 'all') {
            $query->where('series', $request->input('series'));
        }
         * ------------------------------------------------------------------
         */

        // Apply per-category attribute/type filters
        foreach ($activeFilters as $filter) {
            $values = $request->input($filter['field']);
            if (empty($values) || !is_array($values)) {
                continue;
            }

            $values = array_filter($values, fn ($v) => trim($v) !== '');

            if ($filter['source'] === 'product_type') {
                // Filter by products.type column (Horizontal / Vertical etc.)
                $query->whereIn('type', $values);
            } elseif ($filter['source'] === 'attribute') {
                // Filter products that have any of the selected attribute values
                $attributeName = $filter['attribute_name'];
                $query->whereHas('attributeValues', function ($q) use ($values, $attributeName) {
                    $q->whereHas('attribute', function ($a) use ($attributeName) {
                        $a->where('name', $attributeName);
                    })->whereIn('value', $values);
                });
            }
        }

        // Sort
        if ($request->filled('sort')) {
            switch ($request->sort) {
                case 'name_asc':
                    $query->select('products.*')
                        ->leftJoin('product_translations', function ($join) use ($locale) {
                            $join->on('product_translations.product_id', '=', 'products.id')
                                ->where('product_translations.language_code', $locale);
                        })
                        ->orderBy('product_translations.name', 'asc');
                    break;

                case 'name_desc':
                    $query->select('products.*')
                        ->leftJoin('product_translations', function ($join) use ($locale) {
                            $join->on('product_translations.product_id', '=', 'products.id')
                                ->where('product_translations.language_code', $locale);
                        })
                        ->orderBy('product_translations.name', 'desc');
                    break;

                default:
                    $query->latest();
                    break;
            }
        } else {
            $query->latest();
        }

        $products = $query->paginate(12)->withQueryString();

        $categories = Category::with('translation')
            ->withCount('products')
            ->get();

        if ($request->ajax()) {
            return view('themes.xylo.partials.product-list', compact('products'))->render();
        }

        return view('themes.xylo.shop', compact(
            'products',
            'categories',
            'activeFilters',
            'categoryFilters',
            'selectedCategory'
        ));
    }
}