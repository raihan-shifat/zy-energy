<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;

class SearchController extends Controller
{
    public function suggestions(Request $request)
    {
        $query = $request->input('q');
        $locale = $request->input('locale', App::getLocale());

        // Escape LIKE wildcards so %/_ in user input don't skew results
        $safeQuery = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $query);

        $products = Product::where('status', 1)
            ->whereHas('translations', function ($q) use ($safeQuery, $locale) {
                $q->where('name', 'like', "%{$safeQuery}%")->where('language_code', $locale);
            })
            ->with([
                'translations' => function ($q) use ($locale) {
                    $q->where('language_code', $locale)->select('product_id', 'name');
                },
                'thumbnail',
            ])
            ->limit(10)
            ->get(['id', 'slug']);

        $products = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'slug' => $product->slug,
                'thumbnail' => $product->thumbnail
                    ? Storage::url($product->thumbnail->image_url)
                    : asset('default-thumbnail.jpg'),
                'name' => $product->translations->first()->name ?? null,
            ];
        });

        return response()->json($products);
    }

    public function searchResults(Request $request)
    {
        $query = $request->input('q');
        $locale = $request->input('locale', App::getLocale());
        $safeQuery = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $query);

        $products = Product::where('status', 1)
            ->whereHas('translations', function ($q) use ($safeQuery, $locale) {
                $q->where('name', 'like', "%{$safeQuery}%")->where('language_code', $locale);
            })
            ->with([
                'translations' => function ($q) use ($locale) {
                    $q->where('language_code', $locale)->select('product_id', 'name', 'description');
                },
                'thumbnail',
            ])
            ->paginate(10);

        return view('search-results', compact('products', 'query'));
    }
}
