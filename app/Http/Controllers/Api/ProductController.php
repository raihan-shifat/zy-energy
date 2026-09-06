<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $lang = $request->get('lang', app()->getLocale());

        $products = Product::with([
            'translations' => function ($q) use ($lang) {
                $q->where('language_code', $lang);
            },
            'category.translations' => function ($q) use ($lang) {
                $q->where('language_code', $lang);
            },
            'brand',
            'thumbnail',
            'primaryVariant',
        ])
            ->withAvg('reviews', 'rating')
            ->where('status', 1)
            ->get()
            ->map(function ($product) {
                $translation = $product->translations->first();
                $variant = $product->primaryVariant;

                return [
                    'id' => $product->id,
                    'slug' => $product->slug,
                    'name' => $translation->name ?? '',
                    'description' => $translation->description ?? '',
                    'short_description' => $translation->short_description ?? '',
                    // products table has no price column — pricing lives on variants
                    'price' => $variant
                        ? (float) ($variant->converted_discount_price ?? $variant->converted_price)
                        : null,
                    'thumbnail' => $product->thumbnail->image_url ?? null,
                    'category' => $product->category->translations->first()->name ?? '',
                    'brand' => $product->brand->name ?? null,
                    'rating' => round($product->reviews_avg_rating ?? 0, 1),
                ];
            });

        return response()->json([
            'status' => true,
            'data' => $products,
        ]);
    }
}
