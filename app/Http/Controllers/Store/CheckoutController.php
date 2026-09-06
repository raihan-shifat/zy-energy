<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\PaymentGateway;
use App\Models\ProductVariant;
use App\Services\Store\OrderService;
use App\Services\PaymentGateway\PaymentManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class CheckoutController extends Controller
{
    public function index()
    {
        $paymentGateways = PaymentGateway::with('configs')
            ->where('is_active', 1)
            ->get();

        $paypal = $paymentGateways->firstWhere('code', 'paypal');
        $paypalClientId = $paypal
            ? $paypal->getConfigValue('client_id', 'sandbox')
            : null;

        $stripe = $paymentGateways->firstWhere('code', 'stripe');
        $stripePublicKey = $stripe
            ? $stripe->getConfigValue('public_key')
            : null;

        $cart = Session::get('cart', []);
        $subtotal = 0;

        if (!empty($cart)) {
            // Collect all product IDs and variant IDs from cart
            $productIds = [];
            $variantIds = [];

            foreach ($cart as $item) {
                $productIds[] = $item['product_id'];
                if (isset($item['variant_id'])) {
                    $variantIds[] = $item['variant_id'];
                }
            }

            $productIds = array_unique($productIds);
            $variantIds = array_unique($variantIds);

            // Batch load products and variants
            $products = \App\Models\Product::with(['translations', 'thumbnail', 'primaryVariant'])
                ->whereIn('id', $productIds)
                ->get()
                ->keyBy('id');

            $variants = ProductVariant::with('images')
                ->whereIn('id', $variantIds)
                ->get()
                ->keyBy('id');

            // For items without variant_id, get primary variant
            $primaryVariantIds = [];
            foreach ($cart as $item) {
                if (!isset($item['variant_id'])) {
                    $primaryVariantIds[] = $item['product_id'];
                }
            }
            if (!empty($primaryVariantIds)) {
                $primaryVariants = ProductVariant::whereIn('product_id', array_unique($primaryVariantIds))
                    ->where('is_primary', true)
                    ->get()
                    ->keyBy('product_id');

                foreach ($primaryVariants as $productId => $variant) {
                    $variants->put($variant->id, $variant);
                }
            }

            foreach ($cart as $key => $item) {
                $product = $products->get($item['product_id']);

                $variant = isset($item['variant_id'])
                    ? $variants->get($item['variant_id'])
                    : ($products->get($item['product_id'])?->primaryVariant);

                // $subtotal calculation uses cart item price (already computed)
                $subtotal += $item['price'] * $item['quantity'];
            }
        }

        $shipping = null;
        $total = $subtotal + ($shipping ?? 0);

        return view('themes.xylo.checkout', compact('cart', 'subtotal', 'shipping', 'total', 'paymentGateways', 'paypalClientId', 'stripePublicKey'));
    }

    public function process(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'zipcode' => 'required|string|max:20',
            'email' => 'required|email',
            'phone' => 'required|string|max:20',
        ]);

        $cart = Session::get('cart', []);

        if (empty($cart)) {
            return response()->json([
                'success' => false,
                'message' => 'Your cart is empty.',
            ], 400);
        }

        // Calculate total from cart + coupon discount
        $subtotal = collect($cart)->sum(fn ($item) => $item['price'] * $item['quantity']);

        $discount = 0;
        $coupon = Session::get('cart_coupon');
        if ($coupon) {
            $discount = $coupon['type'] === 'percentage'
                ? $subtotal * ($coupon['discount'] / 100)
                : min($coupon['discount'], $subtotal);
        }

        $total = max(0, $subtotal - $discount);

        try {
            DB::beginTransaction();

            // Collect all variant IDs needed
            $variantIds = [];
            foreach ($cart as $item) {
                if (isset($item['variant_id'])) {
                    $variantIds[] = $item['variant_id'];
                } else {
                    $primaryVariant = ProductVariant::where('product_id', $item['product_id'])
                        ->where('is_primary', true)
                        ->value('id');
                    if ($primaryVariant) {
                        $variantIds[] = $primaryVariant;
                    }
                }
            }
            $variantIds = array_unique(array_filter($variantIds));

            // Batch load all variants with lockForUpdate
            $variants = ProductVariant::whereIn('id', $variantIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // Validate stock for every line before writing anything
            foreach ($cart as $item) {
                $variantId = $item['variant_id'] ?? null;
                if (!$variantId) {
                    $variantId = ProductVariant::where('product_id', $item['product_id'])
                        ->where('is_primary', true)
                        ->value('id');
                }

                $variant = $variants->get($variantId);

                if (! $variant) {
                    throw new \Exception('A product in your cart is no longer available.');
                }

                if ($variant->stock < $item['quantity']) {
                    throw new \Exception('Insufficient stock for "'.$item['name'].'" (only '.$variant->stock.' left).');
                }
            }

            $order = Order::create([
                'customer_id' => Auth::guard('customer')->id(),
                'guest_email' => Auth::guard('customer')->check() ? null : $request->email,
                'total_amount' => $total,
                'status' => 'pending',
            ]);

            foreach ($cart as $item) {
                OrderDetail::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);

                // Decrement stock using the already-locked variant
                $variantId = $item['variant_id'] ?? null;
                if (!$variantId) {
                    $variantId = ProductVariant::where('product_id', $item['product_id'])
                        ->where('is_primary', true)
                        ->value('id');
                }

                $variant = $variants->get($variantId);
                if ($variant) {
                    $variant->decrement('stock', $item['quantity']);
                }
            }

            // Persist the shipping address (table exists; model was missing)
            \App\Models\ShippingAddress::create([
                'order_id'    => $order->id,
                'customer_id' => Auth::guard('customer')->id(),
                'name'        => trim($request->first_name.' '.$request->last_name),
                'phone'       => $request->phone,
                'address'     => $request->address,
                'city'        => $request->city,
                'postal_code' => $request->zipcode,
                'country'     => $request->country,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order placement failed: '.$e->getMessage());

            $message = str_starts_with($e->getMessage(), 'Insufficient stock') || str_contains($e->getMessage(), 'no longer available')
                ? $e->getMessage()
                : 'Failed to place order. Please try again.';

            return response()->json([
                'success' => false,
                'message' => $message,
            ], 400);
        }

        // Clear cart & coupon
        Session::forget('cart');
        Session::forget('cart_coupon');

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully!',
                'order_id' => $order->id,
                'redirect' => route('thankyou'),
            ]);
        }

        return redirect()->route('thankyou')->with('success', 'Order placed successfully!');
    }

    /**
     * PayPal success callback
     */
    public function paypalSuccess(Request $request, OrderService $orderService)
    {
        $orderId = $request->query('token'); // PayPal returns ?token=ORDER_ID

        try {
            $paypal = PaymentManager::make('paypal', 'sandbox');
            $result = $paypal->captureOrder($orderId);

            if (($result['status'] ?? null) === 'COMPLETED') {

                $order = $orderService->createOrderFromPaypal($result);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment completed & order stored successfully.',
                    'order_id' => $order->id,
                    'details' => $result,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Payment not completed.',
                'details' => $result,
            ]);
        } catch (\Exception $e) {
            \Log::error('PayPal success error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * PayPal cancel callback
     */
    public function paypalCancel()
    {
        return response()->json([
            'success' => false,
            'message' => 'Payment was cancelled by user.',
        ]);
    }
}
