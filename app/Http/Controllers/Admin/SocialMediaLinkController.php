<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\SocialMediaLink;
use App\Services\Admin\SocialMediaLinkService;
use App\Traits\PreventsManagerDelete;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class SocialMediaLinkController extends Controller
{
    use PreventsManagerDelete;

    protected $socialMediaLinkService;

    public function __construct(SocialMediaLinkService $socialMediaLinkService)
    {
        $this->socialMediaLinkService = $socialMediaLinkService;
    }

    public function index()
    {
        $socialMediaLinks = $this->socialMediaLinkService->getAllSocialMediaLinks();

        return view('admin.social-media-links.index', compact('socialMediaLinks'));
    }

    public function getData(Request $request)
    {
        $socialMediaLinks = SocialMediaLink::query();

        return DataTables::of($socialMediaLinks)
        ->addColumn('action', function ($socialMediaLink) {
            $editBtn = '<a href="'.route('admin.social-media-links.edit', $socialMediaLink->id).'" class="btn btn-sm btn-primary">Edit</a>';

            $deleteBtn = '<a href="'.route('admin.social-media-links.destroy', $socialMediaLink->id).'" class="btn btn-sm btn-danger" 
                onclick="event.preventDefault(); document.getElementById(\'delete-form-'.$socialMediaLink->id.'\').submit();">Delete</a>
                <form id="delete-form-'.$socialMediaLink->id.'" action="'.route('admin.social-media-links.destroy', $socialMediaLink->id).'" method="POST" style="display: none;">
                    '.csrf_field().'
                    '.method_field('DELETE').'
                </form>';

            return $editBtn . ' ' . (auth()->user()->role === 'manager' ? '' : $deleteBtn);
        })
            ->rawColumns(['action'])
            ->make(true);
    }

    // Other controller methods...

    public function create()
    {
        $languages = Language::where('active', 1)->get();

        return view('admin.social-media-links.create', compact('languages'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:facebook,instagram,tiktok,youtube,x,wechat',
            'platform' => 'required|string|max:255',
            'languages.en.name' => 'required|string|max:255',
            'languages.*.name' => 'nullable|string|max:255',
        ]);

        $type = $request->input('type');
        if ($type === 'wechat') {
            $request->validate([
                'wechat_qr_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
        } else {
            $request->validate([
                'link' => 'required|url',
            ]);
        }

        $data = $request->all();
        $this->socialMediaLinkService->createSocialMediaLink($data);

        return redirect()->route('admin.social-media-links.index')->with('success', __('cms.social_media_links.created'));
    }

    public function show($id)
    {
        $socialMediaLink = SocialMediaLink::find($id);

        if (! $socialMediaLink) {
            abort(404);
        }

        return redirect()->route('admin.social-media-links.edit', $id);
    }

    public function edit($id)
    {
        $socialMediaLink = $this->socialMediaLinkService->getAllSocialMediaLinks()->find($id);
        $languages = Language::where('active', 1)->get();
        $translations = $socialMediaLink->translations->keyBy('language_code');

        return view('admin.social-media-links.edit', compact('socialMediaLink', 'languages', 'translations'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'type' => 'required|in:facebook,instagram,tiktok,youtube,x,wechat',
            'platform' => 'required|string|max:255',
            'languages.en.name' => 'required|string|max:255',
            'languages.*.name' => 'nullable|string|max:255',
        ]);

        $type = $request->input('type');
        if ($type === 'wechat') {
            $request->validate([
                'wechat_qr_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
        } else {
            $request->validate([
                'link' => 'required|url',
            ]);
        }

        $this->socialMediaLinkService->updateSocialMediaLink($id, $request->all());

        return redirect()->route('admin.social-media-links.index')->with('success', __('cms.social_media_links.updated'));
    }

    public function destroy($id)
    {
        if ($guard = $this->rejectManagerDelete()) {
            return $guard;
        }

        try {
            $socialMediaLink = \App\Models\SocialMediaLink::findOrFail($id);
            $socialMediaLink->delete();

            return response()->json([
                'success' => true,
                'message' => __('cms.social_media_links.deleted'),
            ]);
        } catch (\Exception $e) {
            \Log::error("Error deleting social media link with ID {$id}: ".$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the social media link.',
            ]);
        }
    }
}
