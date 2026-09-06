<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Traits\PreventsManagerDelete;
use Yajra\DataTables\Facades\DataTables;

class ProductVariantController extends Controller
{
    use PreventsManagerDelete;
    public function index()
    {
        /* $productVariants = ProductVariant::with('product.translations', 'translations')
         ->paginate(10);

         $languages = Language::active()->get();

         return view('admin.product_variants.index', compact('productVariants', 'languages')); */

        $languages = Language::active()->get(); // Get active languages

        return view('admin.product_variants.index', compact('languages'));
    }

    public function getData(Request $request)
    {
        $productVariants = ProductVariant::with(['product.translations', 'translations'])
            ->select('product_variants.*'); // Use select to avoid eager loading too much data

        return DataTables::of($productVariants)
            ->addColumn('id', function ($productVariant) {
                return $productVariant->id;  // Add the ID here
            })
            ->addColumn('product', function ($productVariant) {
                return $productVariant->product->translations->first()->name ?? 'Unknown Product';
            })
            ->addColumn('variant_name', function ($productVariant) {
                return $productVariant->translations->first()->name ?? 'N/A';
            })
        ->addColumn('action', function ($productVariant) {
            $editBtn = '<a href="'.route('admin.product_variants.edit', $productVariant->id).'" class="btn btn-warning btn-sm">Edit</a>';

            $deleteBtn = '<form action="'.route('admin.product_variants.destroy', $productVariant->id).'" method="POST" style="display:inline;" onsubmit="return confirm(\'Are you sure you want to delete this variant?\');">
                '.csrf_field().'
                '.method_field('DELETE').'
                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
            </form>';

            return $editBtn . ' ' . (auth()->user()->role === 'manager' ? '' : $deleteBtn);
        })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create()
    {
        $products = Product::where('status', 1)
            ->with(['translations' => function ($query) {
                $query->where('language_code', app()->getLocale());
            }])
            ->get();

        $languages = Language::active()->get();

        return view('admin.product_variants.create', compact('products', 'languages'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'translations' => 'required|array',
            'translations.en.name' => 'required|string|max:255',
            'translations.*.name' => 'nullable|string|max:255',
            'translations.*.value' => 'nullable|string|max:255',
        ]);

        $translations = clean_translations($request->input('translations'));
        $productVariantData = [
            'product_id' => $request->input('product_id'),
            'price' => $request->input('price'),
            'discount_price' => $request->input('discount_price'),
            'stock' => $request->input('stock'),
            'SKU' => $request->input('SKU'),
            'weight' => $request->input('weight'),
            'dimensions' => $request->input('dimensions'),
        ];
        $productVariantData['variant_slug'] = Str::slug($request->input('name'));

        $productVariant = ProductVariant::create($productVariantData);

        foreach ($translations as $locale => $translation) {
            $productVariant->translations()->updateOrCreate(
                ['language_code' => $locale],
                [
                    'name' => $translation['name'] ?? '',
                ]
            );
        }

        return redirect()->route('admin.product_variants.index')->with('success', 'Product Variant created successfully.');
    }

    public function show($id)
    {
        return redirect()->route('admin.product_variants.edit', $id);
    }

    public function edit($id)
    {
        $productVariant = ProductVariant::with('translations')->findOrFail($id);

        $products = Product::where('status', 1)
            ->with(['translations' => function ($query) {
                $query->where('language_code', app()->getLocale());
            }])
            ->get();

        $languages = Language::active()->get();

        return view('admin.product_variants.edit', compact('productVariant', 'products', 'languages'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'variant_slug' => 'required|unique:product_variants,variant_slug,'.$id,
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'translations' => 'required|array',
            'translations.en.name' => 'required|string|max:255',
            'translations.*.name' => 'nullable|string|max:255',
            'translations.*.value' => 'nullable|string|max:255',
        ]);

        $translations = clean_translations($request->input('translations'));
        $productVariantData = [
            'product_id' => $request->input('product_id'),
            'price' => $request->input('price'),
            'discount_price' => $request->input('discount_price'),
            'stock' => $request->input('stock'),
            'SKU' => $request->input('SKU'),
            'weight' => $request->input('weight'),
            'dimensions' => $request->input('dimensions'),
        ];
        $productVariantData['variant_slug'] = Str::slug($request->input('name'));

        $productVariant = ProductVariant::findOrFail($id);
        $productVariant->update($productVariantData);

        foreach ($translations as $locale => $translation) {
            $productVariant->translations()->updateOrCreate(
                ['language_code' => $locale],
                [
                    'name' => $translation['name'] ?? '',
                ]
            );
        }

        return redirect()->route('admin.product_variants.index')->with('success', 'Product Variant updated successfully.');
    }

    public function destroy($id)
    {
        if ($guard = $this->rejectManagerDelete()) {
            return $guard;
        }

        try {
            $productVariant = \App\Models\ProductVariant::findOrFail($id);
            $productVariant->translations()->delete();
            $productVariant->delete();

            return response()->json([
                'success' => true,
                'message' => 'Product Variant deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the product variant.',
            ]);
        }
    }
}
