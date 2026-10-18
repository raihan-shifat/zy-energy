<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;

/**
 * Static legal pages (privacy, terms, cookies).
 * The texts are DRAFTS pending legal review; the views say so on the page.
 */
class LegalPageController extends Controller
{
    public function privacy()
    {
        return view('themes.xylo.legal.privacy', $this->shared());
    }

    public function terms()
    {
        return view('themes.xylo.legal.terms', $this->shared());
    }

    public function cookies()
    {
        return view('themes.xylo.legal.cookies', $this->shared());
    }

    protected function shared(): array
    {
        $settings = getSiteSettings();

        return [
            'siteName' => $settings?->site_name ?: config('app.name'),
            'contactEmail' => $settings?->contact_email,
            'contactAddress' => $settings?->address,
            // Date the draft was written; replace with the approval date once reviewed.
            'draftDate' => '2026-10-19',
        ];
    }
}
