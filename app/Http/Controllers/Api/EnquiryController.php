<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Enquiry;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EnquiryController extends Controller
{
    /**
     * POST /api/enquiries
     * Store a B2B enquiry / RFQ lead submitted from the storefront.
     */
    public function store(Request $request)
    {
        // Accept both new (whatsapp_number/country_code/category) and old (phone/country/category_id) field names
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            // Only Name + Message are mandatory; email/phone/country are optional
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'whatsapp_number' => 'nullable|string|max:50',
            // Derived client-side from the intl-tel-input flag/dial-code selection
            // (e.g. "+880" for Bangladesh) — not user-typed
            'country' => 'nullable|string|max:255',
            'country_code' => 'nullable|string|max:20',
            'product_id' => 'nullable|exists:products,id',
            'product_name' => 'nullable|string|max:255',
            // Enquiry form selects a Category (not an individual product)
            'category_id' => 'nullable|exists:categories,id',
            'category' => 'nullable|exists:categories,id',
            'message' => 'required|string|max:5000',
            'source_page' => 'nullable|string|max:255',
        ]);

        // Normalize aliases: new field names take precedence
        $phoneValue = $validated['whatsapp_number'] ?? $validated['phone'] ?? null;
        $countryCodeValue = $validated['country_code'] ?? null;
        $countryValue = $validated['country'] ?? null;
        // If country_code is a dial code (e.g. +880), resolve to country name
        if (!empty($countryCodeValue) && empty($countryValue)) {
            $countryValue = $this->resolveCountryName($countryCodeValue);
        }
        $categoryIdValue = $validated['category'] ?? $validated['category_id'] ?? null;

        $productName = $validated['product_name'] ?? null;
        if (empty($productName) && !empty($validated['product_id'])) {
            $product = Product::with('translation')->find($validated['product_id']);
            $productName = $product ? ($product->translation->name ?? 'Product #'.$product->id) : null;
        }

        // Resolve the selected category's name for storage/display
        $categoryName = null;
        if (! empty($categoryIdValue)) {
            $category = Category::with('translations')->find($categoryIdValue);
            $categoryName = $category?->translations->firstWhere('language_code', 'en')?->name
                ?? $category?->translations->first()?->name
                ?? 'Category #'.$category?->id;
        }

        $sourcePage = $validated['source_page'] ?? null;
        if (empty($sourcePage)) {
            $sourcePage = $request->headers->get('referer') ?? url()->current();
        }

        $enquiry = Enquiry::create([
            'product_id' => $validated['product_id'] ?? null,
            'name' => $validated['name'],
            'company' => $validated['company'] ?? null,
            'email' => $validated['email'] ?? null,
            'phone' => $phoneValue,
            'country' => $countryValue,
            'product_name' => $productName,
            'category_id' => $categoryIdValue,
            'category_name' => $categoryName,
            'message' => $validated['message'],
            'status' => 'new',
            'source_page' => $sourcePage,
        ]);

        try {
            Mail::raw(
                "New Enquiry received from the website:\n\n".
                "Name: {$enquiry->name}\n".
                "Company: ".($enquiry->company ?? '-')."\n".
                "Email: {$enquiry->email}\n".
                "Phone: ".($enquiry->phone ?? '-')."\n".
                "Country: ".($enquiry->country ?? '-')."\n".
                "Product: ".($enquiry->product_name ?? '-')."\n".
                "Category: ".($enquiry->category_name ?? '-')."\n".
                "Source Page: ".($enquiry->source_page ?? '-')."\n".
                "Message:\n".($enquiry->message ?? '-'),
                function ($message) use ($enquiry) {
                    $message->to(config('mail.from.address'))
                        ->subject('New Product Enquiry - '.($enquiry->product_name ?? $enquiry->name));
                }
            );
        } catch (\Throwable $e) {
            Log::warning('Enquiry email could not be sent: '.$e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => __('store.enquiry.success'),
        ], 201);
    }

    /**
     * Resolve a dial code (e.g. +880, 880, +86) or ISO code to country name.
     */
    private function resolveCountryName(string $code): ?string
    {
        $code = trim($code);
        // If it's already a country name (contains letters and no leading +), return as-is
        if (preg_match('/^[A-Za-z ]+$/', $code)) {
            return $code;
        }
        // Normalize dial code: ensure leading + and digits only
        $digits = preg_replace('/[^0-9]/', '', $code);
        $map = [
            '880' => 'Bangladesh', '86' => 'China', '91' => 'India', '1' => 'United States',
            '971' => 'United Arab Emirates', '44' => 'United Kingdom', '33' => 'France',
            '49' => 'Germany', '81' => 'Japan', '82' => 'South Korea', '7' => 'Russia',
            '39' => 'Italy', '34' => 'Spain', '55' => 'Brazil', '52' => 'Mexico',
            '61' => 'Australia', '31' => 'Netherlands', '46' => 'Sweden', '47' => 'Norway',
            '45' => 'Denmark', '41' => 'Switzerland', '43' => 'Austria', '32' => 'Belgium',
            '48' => 'Poland', '351' => 'Portugal', '90' => 'Turkey', '66' => 'Thailand',
            '84' => 'Vietnam', '62' => 'Indonesia', '60' => 'Malaysia', '65' => 'Singapore',
            '66' => 'Thailand', '27' => 'South Africa', '234' => 'Nigeria', '92' => 'Pakistan',
            '94' => 'Sri Lanka', '95' => 'Myanmar', '977' => 'Nepal', '92' => 'Pakistan',
        ];
        // Try longest prefix match (3 digits, then 2, then 1)
        for ($len = 3; $len >= 1; $len--) {
            $prefix = substr($digits, 0, $len);
            if (isset($map[$prefix])) {
                return $map[$prefix];
            }
        }
        return $code; // fallback: return original code
    }
}
