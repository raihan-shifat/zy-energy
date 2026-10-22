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
        // All active categories in admin order; optional cap via HOME_CATEGORIES_LIMIT (was a fixed 7).
        $categories = getActiveCategories();
        if (($limit = config('storefront.home_categories_limit')) > 0) {
            $categories = $categories->take($limit);
        }
        $products = getHomepageProducts();
        $certifications = getActiveCertifications();
        $newsPosts = getHomepageNews();

        return view('themes.xylo.home', compact('banners', 'categories', 'products', 'certifications', 'newsPosts'));
    }
}
