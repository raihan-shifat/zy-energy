<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Certification;

class StoreController extends Controller
{
    public function index()
    {
        $banners = getActiveBanners();
        $categories = getActiveCategories()->take(7);
        $products = getHomepageProducts();
        $certifications = getActiveCertifications();
        $newsPosts = getHomepageNews();

        return view('themes.xylo.home', compact('banners', 'categories', 'products', 'certifications', 'newsPosts'));
    }
}
