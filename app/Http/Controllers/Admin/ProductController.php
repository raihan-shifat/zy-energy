<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Language;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use App\Models\Vendor;
use App\Services\Admin\CategoryService;
use App\Services\Admin\ProductService;
use App\Traits\PreventsManagerDelete;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    use PreventsManagerDelete;
    protected $categoryService;

    protected $productService;

    public function __construct(CategoryService $categoryService, ProductService $productService)
    {
        $this->categoryService = $categoryService;
        $this->productService = $productService;
    }

    public function index()
    {
        return view('admin.products.index');
    }

    public function getProducts(Request $request)
    {
        try {
            return $this->productService->getProductsForDataTable($request);
        } catch (\Exception $e) {
            \Log::error('Error fetching product data: '.$e->getMessage());

            return response()->json(['error' => 'An error occurred while fetching product data.'], 500);
        }
    }

    public function create()
    {
        $vendors = Vendor::all();
        $categories = Category::with('translations')->get();
        $brands = Brand::with('translations')->get();
        $attributes = Attribute::with('values.translations')->get();

        return view('admin.products.create', compact('categories', 'brands', 'attributes', 'vendors'));
    }

    public function store(Request $request)
    {
        $defaultLang = config('app.locale');
        $rules = [
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'vendor_id' => 'required|exists:vendors,id',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'variants' => 'required|array|min:1',
            'variants.*.name' => 'required|string|max:255',
            'variants.*.price' => 'required|numeric|min:0',
            'variants.*.discount_price' => 'nullable|numeric|min:0|lte:variants.*.price',
            'variants.*.stock' => 'required|integer|min:0|max:2147483647',
            'variants.*.SKU' => 'nullable|string|max:255',
            'variants.*.barcode' => 'nullable|string|max:255',
            'variants.*.weight' => 'nullable|numeric|min:0',
            'variants.*.dimensions' => 'nullable|string|max:255',
            'variants.*.language_code' => 'nullable|string|size:2',
            'variants.*.attribute_values' => 'nullable|array',
            'variants.*.attribute_values.*' => 'nullable|exists:attribute_values,id',
        ];

        foreach ($request->input('translations', []) as $lang => $data) {
            if ($lang === 'en') {
                $rules["translations.$lang.name"] = 'required|string|max:255';
            } else {
                $rules["translations.$lang.name"] = 'nullable|string|max:255';
            }
            $rules["translations.$lang.description"] = 'nullable|string';
            $rules["translations.$lang.short_description"] = 'nullable|string';
            $rules["translations.$lang.tags"] = 'nullable|string';
        }

        $validated = $request->validate($rules);

        $shopId = \App\Models\Shop::where('status', 'active')->value('id') ?? 1;

        DB::transaction(function () use ($request, $defaultLang, $shopId) {
            $defaultName = $request->translations[$defaultLang]['name'] ?? 'product';
            $slug = $this->generateUniqueSlug($defaultName);
            $product = Product::create([
                'shop_id'      => $shopId,
                'vendor_id'    => $request->vendor_id,
                'slug'         => $slug,
                'category_id'  => $request->category_id,
                'brand_id'     => $request->brand_id,
                'product_type' => 'variable',
                'series'       => $request->series,
                'type'         => $request->type,
            ]);

            foreach (clean_translations($request->translations) as $lang => $data) {
                $product->translations()->create([
                    'language_code'     => $lang,
                    'name'              => $data['name'] ?? '',
                    'description'       => $data['description'] ?? null,
                    'short_description' => $data['short_description'] ?? null,
                    'tags'              => $data['tags'] ?? null,
                ]);
            }

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('products', 'public');
                    $product->images()->create([
                        'name'      => $image->getClientOriginalName(),
                        'image_url' => $path,
                        'type'      => 'thumb',
                    ]);
                }
            }

            foreach ($request->variants as $variantData) {
                $sku = ! empty($variantData['SKU']) ? $variantData['SKU'] : strtoupper(Str::random(8));
                $variant = $product->variants()->create([
                    'variant_slug'  => Str::slug($variantData['name']).'-'.uniqid(),
                    'price'         => $variantData['price'],
                    'discount_price'=> $variantData['discount_price'] ?? null,
                    'stock'         => $variantData['stock'],
                    'SKU'           => $sku,
                    'barcode'       => $variantData['barcode'] ?? null,
                    'weight'        => $variantData['weight'] ?? null,
                    'dimensions'    => $variantData['dimension'] ?? null,
                    'is_primary'    => 1,
                ]);

                $variant->translations()->create([
                    'language_code' => $variantData['language_code'] ?? 'en',
                    'name'          => $variantData['name'],
                ]);

                $attrValues = $variantData['attribute_values'] ?? [];
                foreach ($attrValues as $attrValueId) {
                    if (! empty($attrValueId)) {
                        DB::table('product_variant_attribute_values')->insert([
                            'product_id'          => $product->id,
                            'product_variant_id'  => $variant->id,
                            'attribute_value_id'  => $attrValueId,
                            'created_at'          => now(),
                            'updated_at'          => now(),
                        ]);
                        ProductAttributeValue::firstOrCreate([
                            'product_id'          => $product->id,
                            'attribute_value_id'  => $attrValueId,
                        ]);
                    }
                }
            }
        });

        return redirect()->route('admin.products.index')->with('success', __('cms.products.success_create'));
    }

    public function generateUniqueSlug($name)
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $count = 1;
        while (Product::where('slug', $slug)->exists()) {
            $slug = $originalSlug.'-'.$count;
            $count++;
        }

        return $slug;
    }

    public function edit($id)
    {
        $product = Product::with(['translations', 'variants.translations', 'variants.attributeValues', 'images'])->findOrFail($id);
        $vendors = Vendor::all();
        $categories = Category::all();
        $brands = Brand::all();
        $attributes = Attribute::with('values.translations')->get();
        foreach ($product->variants as $variant) {
            $variant->attribute_value_map = $variant->attributeValues->pluck('id', 'attribute_id')->toArray();
        }

        return view('admin.products.edit', compact('product', 'categories', 'brands', 'attributes', 'vendors'));
    }

    public function show($id)
    {
        $product = Product::with(['translations', 'variants.translations', 'variants.attributeValues', 'images'])->findOrFail($id);
        return view('admin.products.show', compact('product'));
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $defaultLang = config('app.locale');
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'vendor_id' => 'required|exists:vendors,id',
            'translations.'.$defaultLang.'.name' => 'required|string|max:255',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'variants' => 'required|array|min:1',
            'variants.*.id' => 'nullable|exists:product_variants,id',
            'variants.*.name' => 'required|string|max:255',
            'variants.*.price' => 'required|numeric|min:0',
            'variants.*.discount_price' => 'nullable|numeric|min:0|lte:variants.*.price',
            'variants.*.stock' => 'required|integer|min:0|max:2147483647',
            'variants.*.SKU' => 'nullable|string|max:255',
            'variants.*.barcode' => 'nullable|string|max:255',
            'variants.*.weight' => 'nullable|numeric|min:0',
            'variants.*.dimensions' => 'nullable|string|max:255',
            'variants.*.language_code' => 'nullable|string|size:2',
            'variants.*.attribute_values' => 'nullable|array',
            'variants.*.attribute_values.*' => 'nullable|exists:attribute_values,id',
        ]);
        DB::transaction(function () use ($request, $product, $defaultLang) {
            $product->update(['category_id' => $request->category_id, 'brand_id' => $request->brand_id, 'vendor_id' => $request->vendor_id, 'series' => $request->series, 'type' => $request->type]);
            $newAttrValueIds = collect($request->variants)->flatMap(function ($v) {
                return array_filter($v['attribute_values'] ?? []);
            })->unique()->values()->all();
            ProductAttributeValue::where('product_id', $product->id)->whereNotIn('attribute_value_id', $newAttrValueIds)->delete();
            foreach (clean_translations($request->translations) as $lang => $data) {
                $product->translations()->updateOrCreate(['language_code' => $lang], ['name' => $data['name'] ?? '', 'description' => $data['description'] ?? null, 'short_description' => $data['short_description'] ?? null, 'tags' => $data['tags'] ?? null]);
            } if ($request->has('remove_images')) {
                foreach ($request->remove_images as $imageId) {
                    $image = $product->images()->find($imageId);
                    if ($image) {
                        Storage::disk('public')->delete($image->image_url);
                        $image->delete();
                    }
                }
            } if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('products', 'public');
                    $product->images()->create(['name' => $image->getClientOriginalName(), 'image_url' => $path, 'type' => 'thumb']);
                }
            } $product->variants()->delete();
            DB::table('product_variant_attribute_values')->where('product_id', $product->id)->delete();
            foreach ($request->variants as $variantData) {
                $sku = ! empty($variantData['SKU']) ? $variantData['SKU'] : strtoupper(Str::random(8));
                $variant = $product->variants()->create(['variant_slug' => Str::slug($variantData['name']).'-'.uniqid(), 'price' => $variantData['price'], 'discount_price' => $variantData['discount_price'] ?? null, 'stock' => $variantData['stock'], 'SKU' => $sku, 'barcode' => $variantData['barcode'] ?? null, 'weight' => $variantData['weight'] ?? null, 'dimensions' => $variantData['dimension'] ?? null, 'is_primary' => 1]);
                $variant->translations()->create(['language_code' => $variantData['language_code'] ?? $defaultLang, 'name' => $variantData['name']]);
                $attrValues = $variantData['attribute_values'] ?? [];
                foreach ($attrValues as $attrValueId) {
                    if (! empty($attrValueId)) {
                        DB::table('product_variant_attribute_values')->insert(['product_id' => $product->id, 'product_variant_id' => $variant->id, 'attribute_value_id' => $attrValueId, 'created_at' => now(), 'updated_at' => now()]);
                        ProductAttributeValue::firstOrCreate(['product_id' => $product->id, 'attribute_value_id' => $attrValueId]);
                    }
                }
            }
        });

        return redirect()->route('admin.products.index')->with('success', __('cms.products.success_update'));
    }

    public function destroy($id)
    {
        if ($guard = $this->rejectManagerDelete()) {
            return $guard;
        }

        try {
            $result = $this->productService->destroy($id);
            if ($result) {
                return response()->json(['success' => true, 'message' => __('cms.products.success_delete')]);
            }

            return response()->json(['success' => false, 'message' => 'Failed to delete product!']);
        } catch (\Exception $e) {
            \Log::error("Error deleting product with ID {$id}: ".$e->getMessage());

            return response()->json(['success' => false, 'message' => 'An error occurred while deleting the product.']);
        }
    }

    public function updateStatus(Request $request)
    {
        $request->validate(['id' => 'required|exists:products,id', 'status' => 'required|boolean']);
        $product = Product::find($request->id);
        $product->status = $request->status;
        $product->save();
        if ($product) {
            return response()->json(['success' => true, 'message' => __('cms.products.status_updated')]);
        } else {
            return response()->json(['success' => false, 'message' => 'Product status could not be updated.']);
        }
    }
}
