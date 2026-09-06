<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\BannerTranslation;
use App\Models\Language;
use App\Services\Admin\BannerService;
use App\Traits\PreventsManagerDelete;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

if (!function_exists('forgetBannersCache')) {
    function forgetBannersCache() {
        \Illuminate\Support\Facades\Cache::forget('banners:active:with_trans');
        \Illuminate\Support\Facades\Cache::forget('banners:active:no_trans');
    }
}

class BannerController extends Controller
{
    use PreventsManagerDelete;
    protected $bannerService;

    public function __construct(BannerService $bannerService)
    {
        $this->bannerService = $bannerService;
    }

    public function index(Request $request)
    {
        $banners = $this->bannerService->getAllBanners();

        return view('admin.banners.index', compact('banners'));
    }

    public function toggleStatus($id, Request $request)
    {
        $validated = $request->validate([
            'status' => 'required|in:0,1',
        ]);

        $banner = Banner::findOrFail($id);
        $banner->status = $validated['status'];
        $banner->save();

        return response()->json(['message' => 'Banner status updated successfully']);
    }

    public function getData(Request $request)
    {
        $banners = Banner::with('translations');

        return DataTables::of($banners)
            ->addColumn('image', function ($banner) {
                $imageUrl = $banner->translations->firstWhere('language_code', 'en')->image_url ?? null;

                return $imageUrl ? '<img src="'.Storage::url($imageUrl).'" width="50" />' : 'No Image';
            })
        ->addColumn('action', function ($banner) {
            $editBtn = '<a href="'.route('admin.banners.edit', $banner->id).'" class="btn btn-primary">Edit</a>';

            $deleteBtn = '<form action="'.route('admin.banners.destroy', $banner->id).'" method="POST" style="display:inline;">
                '.csrf_field().'
                '.method_field('DELETE').'
                <button type="submit" class="btn btn-danger">Delete</button>
            </form>';

            return $editBtn . ' ' . (auth()->user()->role === 'manager' ? '' : $deleteBtn);
        })
            ->rawColumns(['image', 'action'])
            ->make(true);
    }

    public function create()
    {
        $languages = Language::where('active', 1)->get();

        return view('admin.banners.create', compact('languages'));
    }

    public function store(Request $request)
    {
        $this->bannerService->store($request);

        forgetBannersCache();

        return redirect()->route('admin.banners.index')->with('success', __('cms.banners.created'));
    }

    public function show($id)
    {
        return redirect()->route('admin.banners.edit', $id);
    }

    public function edit($id)
    {
        $banner = Banner::findOrFail($id);

        $languages = Language::where('active', 1)->get();

        $translations = BannerTranslation::where('banner_id', $banner->id)
            ->get()
            ->keyBy('language_code');

        return view('admin.banners.edit', compact('banner', 'languages', 'translations'));
    }

    public function update(Request $request, $id)
    {
        $this->bannerService->update($request, $id);

        forgetBannersCache();

        return redirect()->route('admin.banners.index')->with('success', __('cms.banners.updated'));
    }

    public function destroy($id)
    {
        if ($guard = $this->rejectManagerDelete()) {
            return $guard;
        }

        try {
            $this->bannerService->delete((int) $id);

            forgetBannersCache();

            return response()->json([
                'success' => true,
                'message' => __('cms.banners.deleted'),
            ]);
        } catch (\Exception $e) {
            \Log::error("Error deleting banner with ID {$id}: ".$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the banner.',
            ]);
        }
    }

    public function updateStatus(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:banners,id',
            'status' => 'required|in:0,1',
        ]);

        try {
            $banner = Banner::findOrFail($request->id);

            $banner->status = $request->status;
            $banner->save();

            forgetBannersCache();

            return response()->json([
                'success' => true,
                'message' => __('cms.banners.status_updated'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating banner status.',
            ]);
        }
    }
}
