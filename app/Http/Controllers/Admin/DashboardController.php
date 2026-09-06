<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Enquiry;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\SiteVisit;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $data = [
            'totalVisitors'       => SiteVisit::count(),
            'totalEnquiries'      => Enquiry::count(),
            'newEnquiries'        => Enquiry::where('status', 'new')->count(),
            'totalQuotations'     => Quotation::count(),
            'totalProducts'       => Product::where('status', 1)->count(),
            'totalCategories'     => Category::where('status', 1)->count(),

            // Most-selected category across enquiry submissions
            'topEnquiredCategory' => Enquiry::select('category_name', DB::raw('COUNT(*) AS total'))
                ->whereNotNull('category_name')
                ->groupBy('category_name')
                ->orderByDesc('total')
                ->first(),

            'recentEnquiries'   => Enquiry::latest()->take(5)->get(),
            'recentQuotations'  => Quotation::with('items')->orderByDesc('id')->take(5)->get(),
        ];

        return view('admin.dashboard.index', compact('data'));
    }
}
