<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactEmail;
use App\Models\ContactSetting;
use App\Models\ContactWechat;
use App\Models\ContactWhatsapp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ContactController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /*
    |--------------------------------------------------------------------------
    | Contact Settings
    |--------------------------------------------------------------------------
    */

    public function settings()
    {
        $settings = ContactSetting::getSettings();
        return view('admin.contact.settings', compact('settings'));
    }

    public function settingsUpdate(Request $request)
    {
        $validated = $request->validate([
            'page_title' => 'required|string|max:255',
            'page_subtitle' => 'nullable|string',
            'wechat_section_title' => 'required|string|max:255',
            'wechat_section_description' => 'nullable|string',
            'whatsapp_section_title' => 'required|string|max:255',
            'whatsapp_section_description' => 'nullable|string',
            'email_section_title' => 'required|string|max:255',
            'email_section_description' => 'nullable|string',
            'bottom_message' => 'nullable|string',
            'response_time_text' => 'required|string|max:255',
        ]);

        $settings = ContactSetting::getSettings();
        $settings->update($validated);

        return redirect()->back()->with('success', 'Contact settings updated successfully!');
    }

    /*
    |--------------------------------------------------------------------------
    | WeChat Contacts
    |--------------------------------------------------------------------------
    */

    public function wechatIndex()
    {
        $wechats = ContactWechat::ordered()->get();
        return view('admin.contact.wechat.index', compact('wechats'));
    }

    public function wechatCreate()
    {
        return view('admin.contact.wechat.create');
    }

    public function wechatStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'wechat_id' => 'nullable|string|max:255',
            'qr_code' => 'nullable|file|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'description' => 'nullable|string',
            'purpose' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        if ($request->hasFile('qr_code')) {
            $validated['qr_code'] = $request->file('qr_code')->store('contact/wechat', 'public');
        }

        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_active'] = $request->boolean('is_active');

        ContactWechat::create($validated);

        return redirect()->route('admin.contact.wechat.index')->with('success', 'WeChat contact created successfully!');
    }

    public function wechatEdit(ContactWechat $wechat)
    {
        return view('admin.contact.wechat.edit', compact('wechat'));
    }

    public function wechatUpdate(Request $request, ContactWechat $wechat)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'wechat_id' => 'nullable|string|max:255',
            'qr_code' => 'nullable|file|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'description' => 'nullable|string',
            'purpose' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        if ($request->hasFile('qr_code')) {
            if ($wechat->qr_code) {
                Storage::disk('public')->delete($wechat->qr_code);
            }
            $validated['qr_code'] = $request->file('qr_code')->store('contact/wechat', 'public');
        }

        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_active'] = $request->boolean('is_active');

        $wechat->update($validated);

        return redirect()->route('admin.contact.wechat.index')->with('success', 'WeChat contact updated successfully!');
    }

    public function wechatDestroy(ContactWechat $wechat)
    {
        if ($wechat->qr_code) {
            Storage::disk('public')->delete($wechat->qr_code);
        }
        $wechat->delete();

        return redirect()->route('admin.contact.wechat.index')->with('success', 'WeChat contact deleted successfully!');
    }

    /*
    |--------------------------------------------------------------------------
    | WhatsApp Contacts
    |--------------------------------------------------------------------------
    */

    public function whatsappIndex()
    {
        $whatsapps = ContactWhatsapp::ordered()->get();
        return view('admin.contact.whatsapp.index', compact('whatsapps'));
    }

    public function whatsappCreate()
    {
        return view('admin.contact.whatsapp.create');
    }

    public function whatsappStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'whatsapp_url' => 'nullable|url|max:500',
            'description' => 'nullable|string',
            'purpose' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_active'] = $request->boolean('is_active');

        ContactWhatsapp::create($validated);

        return redirect()->route('admin.contact.whatsapp.index')->with('success', 'WhatsApp contact created successfully!');
    }

    public function whatsappEdit(ContactWhatsapp $whatsapp)
    {
        return view('admin.contact.whatsapp.edit', compact('whatsapp'));
    }

    public function whatsappUpdate(Request $request, ContactWhatsapp $whatsapp)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'whatsapp_url' => 'nullable|url|max:500',
            'description' => 'nullable|string',
            'purpose' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_active'] = $request->boolean('is_active');

        $whatsapp->update($validated);

        return redirect()->route('admin.contact.whatsapp.index')->with('success', 'WhatsApp contact updated successfully!');
    }

    public function whatsappDestroy(ContactWhatsapp $whatsapp)
    {
        $whatsapp->delete();

        return redirect()->route('admin.contact.whatsapp.index')->with('success', 'WhatsApp contact deleted successfully!');
    }

    /*
    |--------------------------------------------------------------------------
    | Email Contacts
    |--------------------------------------------------------------------------
    */

    public function emailIndex()
    {
        $emails = ContactEmail::ordered()->get();
        return view('admin.contact.email.index', compact('emails'));
    }

    public function emailCreate()
    {
        return view('admin.contact.email.create');
    }

    public function emailStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'description' => 'nullable|string',
            'purpose' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_active'] = $request->boolean('is_active');

        ContactEmail::create($validated);

        return redirect()->route('admin.contact.email.index')->with('success', 'Email contact created successfully!');
    }

    public function emailEdit(ContactEmail $email)
    {
        return view('admin.contact.email.edit', compact('email'));
    }

    public function emailUpdate(Request $request, ContactEmail $email)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'description' => 'nullable|string',
            'purpose' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_active'] = $request->boolean('is_active');

        $email->update($validated);

        return redirect()->route('admin.contact.email.index')->with('success', 'Email contact updated successfully!');
    }

    public function emailDestroy(ContactEmail $email)
    {
        $email->delete();

        return redirect()->route('admin.contact.email.index')->with('success', 'Email contact deleted successfully!');
    }
}