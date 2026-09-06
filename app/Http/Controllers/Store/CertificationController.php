<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Certification;

class CertificationController extends Controller
{
    /**
     * Certification page — renders the admin-managed certification list.
     */
    public function index()
    {
        $certifications = Certification::with('translations')
            ->where('status', 1)
            ->orderBy('id', 'asc')
            ->get();

        return view('themes.xylo.certification', compact('certifications'));
    }
}
