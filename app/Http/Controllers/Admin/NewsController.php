<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Traits\PreventsManagerDelete;
use Illuminate\Support\Str;

class NewsController extends Controller
{
    use PreventsManagerDelete;
    public function index()
    {
        return view('admin.news.index');
    }

    public function getData(Request $request)
    {
        $news = News::with('translations');

        return datatables()->of($news)
            ->addColumn('title', function ($item) {
                $t = $item->translations->firstWhere('language_code', 'en');
                return $t->title ?? ($item->translations->first()->title ?? '');
            })
        ->addColumn('action', function ($item) {
            $editBtn = '<span class="border border-edit dt-trash rounded-3 d-inline-block"><a href="/admin/news/' . $item->id . '/edit" class=""><i class="bi bi-pencil-fill pencil-edit-color"></i></a></span>';
            $deleteBtn = '<span class="border border-danger dt-trash rounded-3 d-inline-block" onclick="deleteNews(' . $item->id . ')"> <i class="bi bi-trash-fill text-danger"></i> </span>';

            return $editBtn . ' ' . (auth()->user()->role === 'manager' ? '' : $deleteBtn);
        })
            ->make(true);
    }

    public function create()
    {
        $languages = Language::active()->get();
        return view('admin.news.create', compact('languages'));
    }

    /**
     * Shared create/update validation. Title and body are required in the default
     * language (a post without both is never shown on the public site). The cover
     * image follows the same type/size limit as the other admin image uploads.
     */
    private function validateNews(Request $request): void
    {
        $default = config('app.fallback_locale', 'en');
        $maxKb = intdiv(\App\Http\Controllers\Admin\EditorImageController::maxBytes(), 1024);
        $maxLabel = \App\Http\Controllers\Admin\EditorImageController::humanLimit();

        $rules = [
            'image_url' => 'nullable|file|image|mimes:jpeg,png,jpg,webp|max:'.$maxKb,
            'translations' => 'required|array',
            "translations.$default.title" => 'required|string|max:255',
            "translations.$default.body" => 'required|string',
        ];
        $attributes = ['image_url' => __('cms.news.image')];

        foreach (array_unique(array_merge([$default], array_keys((array) $request->input('translations', [])))) as $lang) {
            $rules["translations.$lang.title"] ??= 'nullable|string|max:255';
            // Catch oversized excerpts as a clear validation error instead of a raw SQL truncation crash
            $rules["translations.$lang.excerpt"] = 'nullable|string|max:500';
            $rules["translations.$lang.body"] ??= 'nullable|string';
            $attributes["translations.$lang.title"] = __('cms.news.title')." ($lang)";
            $attributes["translations.$lang.excerpt"] = __('cms.news.excerpt')." ($lang)";
            $attributes["translations.$lang.body"] = __('cms.news.body')." ($lang)";
        }

        $request->validate($rules, [
            // PHP refused the file (bigger than upload_max_filesize): say so instead of "failed to upload".
            'image_url.uploaded' => __('cms.news.image_too_large', ['size' => $maxLabel]),
            'image_url.max' => __('cms.news.image_too_large', ['size' => $maxLabel]),
            'image_url.image' => __('cms.news.image_bad_type'),
            'image_url.mimes' => __('cms.news.image_bad_type'),
        ], $attributes);
    }

    public function store(Request $request)
    {
        $this->validateNews($request);

        $enTitle = $request->translations['en']['title'] ?? $request->translations[config('app.locale')]['title'] ?? 'news';
        $slug = $this->generateUniqueSlug($enTitle);

        $news = News::create([
            'slug' => $slug,
            'image_url' => $request->hasFile('image_url') ? $request->file('image_url')->store('news', 'public') : null,
            'status' => $request->has('status') ? 1 : 0,
        ]);

        foreach (clean_translations($request->input('translations', [])) as $lang => $data) {
            $news->translations()->create([
                'language_code' => $lang,
                'title' => $data['title'] ?? null,
                'excerpt' => $data['excerpt'] ?? null,
                'body' => $data['body'] ?? null,
            ]);
        }

        return redirect()->route('admin.news.index')->with('success', __('cms.news.created'));
    }

    public function show($id)
    {
        return redirect()->route('admin.news.edit', $id);
    }

    public function edit($id)
    {
        $news = News::with('translations')->findOrFail($id);
        $languages = Language::active()->get();
        return view('admin.news.edit', compact('news', 'languages'));
    }

    public function update(Request $request, $id)
    {
        $news = News::findOrFail($id);

        $this->validateNews($request);

        $data = ['status' => $request->has('status') ? 1 : 0];

        if ($request->hasFile('image_url')) {
            if ($news->image_url) {
                Storage::disk('public')->delete($news->image_url);
            }
            $data['image_url'] = $request->file('image_url')->store('news', 'public');
        }

        $news->update($data);

        foreach (clean_translations($request->input('translations', [])) as $lang => $data) {
            $news->translations()->updateOrCreate(
                ['language_code' => $lang],
                ['title' => $data['title'] ?? null, 'excerpt' => $data['excerpt'] ?? null, 'body' => $data['body'] ?? null]
            );
        }

        return redirect()->route('admin.news.index')->with('success', __('cms.news.updated'));
    }

    public function destroy($id)
    {
        if ($guard = $this->rejectManagerDelete()) {
            return $guard;
        }

        $news = News::find($id);
        if ($news) {
            if ($news->image_url) {
                Storage::disk('public')->delete($news->image_url);
            }
            $news->delete();
            return response()->json(['success' => true, 'message' => 'News deleted successfully.']);
        }

        return response()->json(['success' => false, 'message' => 'Error deleting news.']);
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:news,id',
            'status' => 'required|boolean',
        ]);

        $news = News::find($request->id);
        $news->status = $request->status;
        $news->save();

        return response()->json(['success' => true, 'message' => 'Status updated.']);
    }

    private function generateUniqueSlug($title)
    {
        $slug = Str::slug($title) ?: 'news-' . uniqid();
        $base = $slug;
        $i = 1;
        while (News::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
