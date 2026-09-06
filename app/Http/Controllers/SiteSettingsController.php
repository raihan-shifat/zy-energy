<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

if (!function_exists('forgetSiteSettingsCache')) {
    function forgetSiteSettingsCache() {
        \Illuminate\Support\Facades\Cache::forget('site_settings');
    }
}

class SiteSettingsController extends Controller
{
    public function index()
    {
        return view('admin.site-settings.index');
    }

    public function edit()
    {
        $settings = SiteSetting::firstOrCreate([], [
            'site_name' => 'ZY Energy',
            'tagline' => '',
            'meta_title' => '',
            'meta_description' => '',
            'meta_keywords' => '',
            'contact_email' => '',
            'contact_phone' => '',
            'address' => '',
            'footer_text' => '',
            'footer_description' => '',
            'whatsapp_number' => '',
            'wechat_qr_image' => null,
        ]);

        return view('admin.site-settings.edit', compact('settings'))
            ->with('isSuperAdmin', (bool) optional(auth()->user())->isSuperAdmin());
    }

    public function update(Request $request)
    {
        $request->validate([
            'site_name' => 'required|string|max:255',
            'tagline' => 'nullable|string|max:255',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'footer_text' => 'nullable|string',
            'footer_description' => 'nullable|string',
            'whatsapp_number' => 'nullable|string|max:20',
            'customer_login_heading' => 'nullable|string|max:255',
            'wechat_qr_image' => 'nullable|file|image|mimes:jpeg,png,jpg,gif,webp|max:5000',
            'logo' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,svg|max:5000',
            'header_brand_name_image' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,svg|max:5000',
            'footer_logo' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,svg|max:5000',
            'site_status' => 'nullable|in:active,inactive,published,unpublished,unlisted',
        ]);

        $settings = SiteSetting::firstOrCreate([], [
            'site_name' => 'ZY Energy',
            'tagline' => '',
            'meta_title' => '',
            'meta_description' => '',
            'meta_keywords' => '',
            'contact_email' => '',
            'contact_phone' => '',
            'address' => '',
            'footer_text' => '',
            'footer_description' => '',
            'whatsapp_number' => '',
            'wechat_qr_image' => null,
        ]);

        $data = [
            // Coerce empty strings to '' for the NOT NULL varchar columns
            // (ConvertEmptyStringsToNull turns them into NULL otherwise).
            'site_name' => $request->input('site_name', '') ?: '',
            'tagline' => $request->input('tagline', '') ?: '',
            'meta_title' => $request->input('meta_title', '') ?: '',
            'meta_description' => $request->input('meta_description', '') ?: '',
            'meta_keywords' => $request->input('meta_keywords') ?: null,
            'contact_email' => $request->input('contact_email') ?: null,
            'contact_phone' => $request->input('contact_phone') ?: null,
            'address' => $request->input('address') ?: null,
            'footer_text' => $request->input('footer_text') ?: null,
            'footer_description' => $request->input('footer_description') ?: null,
            'whatsapp_number' => $request->input('whatsapp_number') ?: null,
            'customer_login_heading' => $request->input('customer_login_heading') ?: null,
        ];

        // The global site-status switch is Super-Admin-only. Any other staff
        // member sending it is rejected server-side (never trust the UI alone).
        if ($request->filled('site_status')) {
            if (! optional(auth()->user())->isSuperAdmin()) {
                abort(403, 'Only the Super Admin can change the site status.');
            }
            $data['site_status'] = $request->site_status;
        }

        if ($request->hasFile('wechat_qr_image')) {
            if ($settings->wechat_qr_image) {
                Storage::disk('public')->delete($settings->wechat_qr_image);
            }
            $data['wechat_qr_image'] = $request->file('wechat_qr_image')->store('site', 'public');
        }

        foreach (['logo', 'header_brand_name_image', 'footer_logo'] as $imageField) {
            if ($request->hasFile($imageField)) {
                if ($settings->{$imageField}) {
                    Storage::disk('public')->delete($settings->{$imageField});
                }
                $data[$imageField] = $request->file($imageField)->store('site', 'public');
            }
        }

        $settings->update($data);

        forgetSiteSettingsCache();

        return redirect()->back()->with('success', 'Site settings updated successfully!');
    }
}
