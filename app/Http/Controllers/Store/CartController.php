<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\Store\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function addToCart(Request $request)
    {
        $productId = $request->product_id;
        $quantity = max(1, (int) ($request->quantity ?? 1));
        $attributeValueIds = $request->attribute_value_ids ?? [];

        $product = Product::with(['thumbnail', 'translations'])->where('status', 1)->findOrFail($productId);

        $variant = null;
        if ($product->product_type == 'simple') {
            $variant = $product->variants()->with('images')->where('is_primary', 1)->first();
        } else {
            $variant = $this->matchVariant($productId, $attributeValueIds);
        }

        if (! $variant) {
            return response()->json([
                'message' => 'Selected variant is not available.',
            ], 422);
        }

        if ($variant->stock < 1) {
            return response()->json([
                'message' => __('store.product_detail.out_of_stock') ?? 'This product is out of stock.',
            ], 422);
        }

        $attributePairs = [];
        if (!empty($attributeValueIds)) {
            $attributeValues = \App\Models\AttributeValue::with('attribute')
                ->whereIn('id', $attributeValueIds)
                ->get()
                ->keyBy('id');

            foreach ($attributeValueIds as $attributeValueId) {
                $attributeValue = $attributeValues->get($attributeValueId);
                if ($attributeValue && $attributeValue->attribute) {
                    $attributePairs[$attributeValue->attribute->id] = $attributeValue->id;
                }
            }
        }

        $cart = Session::get('cart', []);

        $attributeValueIdsSorted = collect($attributeValueIds)->sort()->values()->implode('_');
        $key = "cart_{$productId}_{$attributeValueIdsSorted}";

        $existingQty = isset($cart[$key]) ? $cart[$key]['quantity'] : 0;
        if ($existingQty + $quantity > $variant->stock) {
            return response()->json([
                'message' => 'Only '.$variant->stock.' unit(s) available in stock.',
            ], 422);
        }

        if (isset($cart[$key])) {
            $cart[$key]['quantity'] += $quantity;
        } else {
            $cart[$key] = [
                'product_id' => $product->id,
                'variant_id' => $variant->id,
                'variant_name' => $product->getTranslation('name', app()->getLocale())
                    ?? $product->translations()->where('language_code', 'en')->value('name')
                    ?? ('Product #'.$product->id),
                'price' => $variant->converted_discount_price ?? $variant->converted_price,
                'quantity' => $quantity,
                'image' => optional($variant->images->first() ?? $product->thumbnail)->image_url,
                'attributes' => $attributePairs,
            ];
        }

        Session::put('cart', $cart);
        Session::put('cart_count', array_sum(array_column($cart, 'quantity')));

        return response()->json([
            'message' => __('store.product_detail.cart_success'),
            'cart' => $cart,
            'cart_count' => Session::get('cart_count'),
        ]);
    }

    private function matchVariant($productId, array $attributeValueIds)
    {
        if (empty($attributeValueIds)) {
            return ProductVariant::where('product_id', $productId)->where('is_primary', true)->first();
        }

        $variants = ProductVariant::with('attributeValues')
            ->where('product_id', $productId)
            ->get();

        foreach ($variants as $variant) {
            $variantAttrIds = $variant->attributeValues->pluck('id')->sort()->values();
            if ($variantAttrIds->toArray() === collect($attributeValueIds)->sort()->values()->toArray()) {
                return $variant;
            }
        }

        return null;
    }

    public function updateCart(Request $request)
    {
        $cart = Session::get('cart', []);

        // Collect all variant IDs needed for stock check
        $variantIds = [];
        foreach ($request->cart as $item) {
            if (isset($cart[$item['product_id']])) {
                $variantId = $cart[$item['product_id']]['variant_id'] ?? null;
                if ($variantId) {
                    $variantIds[] = $variantId;
                } else {
                    // Need primary variant for this product
                    $variantIds[] = ProductVariant::where('product_id', $item['product_id'])
                        ->where('is_primary', true)
                        ->value('id');
                }
            }
        }

        // Batch fetch stock for all variants
        $stockMap = ProductVariant::whereIn('id', array_filter($variantIds))
            ->whereNotNull('id')
            ->pluck('stock', 'id')
            ->toArray();

        foreach ($request->cart as $item) {
            if (isset($cart[$item['product_id']])) {
                $quantity = max(1, intval($item['quantity']));

                $variantId = $cart[$item['product_id']]['variant_id'] ?? null;
                if (!$variantId) {
                    $variantId = ProductVariant::where('product_id', $item['product_id'])
                        ->where('is_primary', true)
                        ->value('id');
                }

                $stock = $stockMap[$variantId] ?? 0;

                $cart[$item['product_id']]['quantity'] = min($quantity, max(1, $stock));
            }
        }

        Session::put('cart', $cart);
        Session::put('cart_count', array_sum(array_column($cart, 'quantity')));

        return response()->json([
            'success' => true,
            'message' => 'Cart updated successfully!',
            'cart' => $cart,
        ]);
    }

    public function viewCart()
    {
        $cart = Session::get('cart', []);

        if (empty($cart)) {
            return view('themes.xylo.cart', compact('cart'));
        }

        // Collect all product_ids and variant_ids from cart
        $productIds = [];
        $variantIds = [];
        $attributeValueIds = [];

        foreach ($cart as $item) {
            $productIds[] = $item['product_id'];
            if (isset($item['variant_id'])) {
                $variantIds[] = $item['variant_id'];
            }
            if (!empty($item['attributes'])) {
                $attributeValueIds = array_merge($attributeValueIds, $item['attributes']);
            }
        }

        $productIds = array_unique($productIds);
        $variantIds = array_unique($variantIds);
        $attributeValueIds = array_unique($attributeValueIds);

        // Eager load all needed data
        $products = Product::with(['translations', 'thumbnail', 'primaryVariant'])
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        $variants = ProductVariant::with(['images', 'translations'])
            ->whereIn('id', $variantIds)
            ->get()
            ->keyBy('id');

        $attributeValues = \App\Models\AttributeValue::with(['attribute', 'translations'])
            ->whereIn('id', $attributeValueIds)
            ->get()
            ->keyBy('id');

        // Build enriched cart items for the view
        $enrichedCart = [];
        foreach ($cart as $key => $item) {
            $product = $products->get($item['product_id']);
            $variant = $variants->get($item['variant_id'] ?? null);

            // Fallback to primary variant if specific variant not found
            if (!$variant && $product) {
                $variant = $variants->get($product->primaryVariant?->id ?? null);
            }

            $enrichedCart[$key] = array_merge($item, [
                'product_model' => $product,
                'variant_model' => $variant,
                'attribute_values_map' => collect($item['attributes'] ?? [])
                    ->mapWithKeys(function ($avId) use ($attributeValues) {
                        $av = $attributeValues->get($avId);
                        return $av ? [$avId => $av] : [];
                    })
                    ->toArray(),
            ]);
        }

        return view('themes.xylo.cart', ['cart' => $enrichedCart]);
    }

    public function removeFromCart(Request $request)
    {
        $cart = Session::get('cart', []);

        if (isset($cart[$request->product_id])) {
            unset($cart[$request->product_id]);
            Session::put('cart', $cart);
        }

        if (empty($cart)) {
            session()->forget('cart_coupon');
        }

        return response()->json(['message' => __('store.cart.product_removed'), 'cart' => $cart]);
    }

    public function applyCoupon(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        $result = $this->cartService->applyCoupon($request->code);

        return response()->json($result);
    }

    public function removeCoupon()
    {
        $this->cartService->removeCoupon();

        return response()->json(['success' => true, 'message' => 'Coupon removed successfully!']);
    }
}
