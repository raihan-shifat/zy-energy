<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\ContactEmail;
use App\Models\ContactSetting;
use App\Models\ContactWechat;
use App\Models\ContactWhatsapp;

class ContactController extends Controller
{
    public function index()
    {
        $settings = ContactSetting::getSettings();
        $wechats = ContactWechat::active()->get();
        $whatsapps = ContactWhatsapp::active()->get();
        $emails = ContactEmail::active()->get();

        return view('themes.xylo.contact', compact('settings', 'wechats', 'whatsapps', 'emails'));
    }
}