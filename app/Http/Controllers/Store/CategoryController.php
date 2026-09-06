<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display category page with products.
     */
    public function show($slug, Request $request)
    {
        $category = Category::with(['translation', 'parent.translation'])
            ->where('slug', $slug)
            ->firstOrFail();

        $query = Product::with([
            'translations',
            'thumbnail',
            'primaryVariant',
            'reviews',
            'images',
        ])
            ->withCount(['reviews' => function ($q) {
                $q->where('is_approved', 1);
            }])
            ->withAvg(['reviews' => function ($q) {
                $q->where('is_approved', 1);
            }], 'rating')
            ->where('status', 1)
            ->where('category_id', $category->id);

        // B2B filters — series + type, config-driven so more filters can be added later
        // by adding one entry to $filterConfig (e.g. a kW filter in Phase 2).
        $filterConfig = [
            ['field' => 'series', 'label' => __('store.category.series'), 'type' => 'select'],
            ['field' => 'type', 'label' => __('store.category.type'), 'type' => 'select'],
        ];

        $filterValues = getCategoryFilterValues($category);

        foreach ($filterConfig as $filter) {
            if ($request->filled($filter['field'])) {
                $query->where($filter['field'], $request->input($filter['field']));
            }
        }

        if ($request->filled('sort')) {
            switch ($request->sort) {
                case 'newest':
                    $query->latest();
                    break;

                case 'name_asc':
                    $query->whereHas('translation', function ($q) {
                        $q->orderBy('name', 'asc');
                    });
                    break;

                case 'name_desc':
                    $query->whereHas('translation', function ($q) {
                        $q->orderBy('name', 'desc');
                    });
                    break;

                default:
                    $query->latest();
                    break;
            }
        } else {
            $query->latest();
        }

        $products = $query->paginate(12)->withQueryString();

        $breadcrumbs = [];
        $parent = $category;
        while ($parent) {
            $breadcrumbs[] = $parent;
            $parent = $parent->parent;
        }
        $breadcrumbs = array_reverse($breadcrumbs);

        return view('themes.xylo.category', compact('category', 'products', 'breadcrumbs', 'filterConfig', 'filterValues'));
    }
}
