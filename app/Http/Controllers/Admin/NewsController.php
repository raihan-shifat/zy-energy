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

    /** Most images one news post can have. */
    public const GALLERY_MAX = 8;

    public function index()
    {
        return view('admin.news.index');
    }

    public function getData(Request $request)
    {
        $news = News::with(['translations', 'images']);

        return datatables()->of($news)
            ->addColumn('title', function ($item) {
                $t = $item->translations->firstWhere('language_code', 'en');
                return $t->title ?? ($item->translations->first()->title ?? '');
            })
            // Thumbnail URL, or null when there is no usable image (list shows a placeholder).
            ->addColumn('image_thumb', fn ($item) => ($path = $item->coverPath()) ? asset('storage/'.$path) : null)
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

        // Image list from the multi-image manager (files are uploaded one by one to admin.news.upload-image).
        $rules += [
            'gallery' => 'nullable|array|max:'.self::GALLERY_MAX,
            'gallery.*.id' => 'nullable|integer',
            'gallery.*.path' => ['nullable', 'string', 'max:255', 'regex:#^news/[A-Za-z0-9._-]+$#', 'not_regex:#\.\.#'],
            'gallery_primary' => 'nullable|integer|min:0',
        ];

        $request->validate($rules, [
            'gallery.max' => __('cms.news.gallery_too_many', ['max' => self::GALLERY_MAX]),
            'gallery.*.path.regex' => __('cms.products.gallery_bad_path'),
            'gallery.*.path.not_regex' => __('cms.products.gallery_bad_path'),
            // PHP refused the file (bigger than upload_max_filesize): say so instead of "failed to upload".
            'image_url.uploaded' => __('cms.news.image_too_large', ['size' => $maxLabel]),
            'image_url.max' => __('cms.news.image_too_large', ['size' => $maxLabel]),
            'image_url.image' => __('cms.news.image_bad_type'),
            'image_url.mimes' => __('cms.news.image_bad_type'),
        ], $attributes);
    }

    /**
     * Save the uploaded cover image on the public disk and return its path.
     * store() returns false when the folder is not writable; this used to be saved
     * as "0" with no message. Now the admin gets a clear error and it is logged.
     */
    private function storeImage(Request $request): string
    {
        try {
            $path = $request->file('image_url')->store('news', 'public');
        } catch (\Throwable $e) {
            report($e);
            $path = false;
        }

        if (! $path) {
            $dir = Storage::disk('public')->path('news');
            \Log::error('News image could not be written', ['dir' => $dir, 'dir_writable' => is_writable($dir)]);

            throw \Illuminate\Validation\ValidationException::withMessages([
                'image_url' => __('cms.news.image_save_failed'),
            ]);
        }

        return $path;
    }

    /**
     * Apply the submitted image list: order, cover, new uploads and removals; then keep
     * news.image_url (old single-image column) pointing at the cover for older code.
     * Without the gallery_present marker (old cached form) the list is left as it is.
     * A removed image loses its row; its file is deleted only when no other news row uses it.
     */
    private function syncImages(News $news, Request $request, ?string $legacyPath = null): void
    {
        $existing = $news->images()->get()->keyBy('id');
        $kept = [];
        $removed = collect();

        if ($request->boolean('gallery_present')) {
            $items = array_values(array_slice((array) $request->input('gallery', []), 0, self::GALLERY_MAX));
            $primary = (int) $request->input('gallery_primary', 0);
            $primaryId = null;

            foreach ($items as $position => $item) {
                $image = null;
                if (! empty($item['id']) && $existing->has((int) $item['id'])) {
                    $image = $existing->get((int) $item['id']);
                    $image->update(['sort_order' => $position, 'is_primary' => false]);
                } elseif (! empty($item['path']) && Storage::disk('public')->exists($item['path'])
                    && ! \App\Models\NewsImage::where('path', $item['path'])->exists()) {
                    $image = $news->images()->create(['path' => $item['path'], 'sort_order' => $position, 'is_primary' => false]);
                }
                if ($image) {
                    $kept[] = $image->id;
                    $primaryId = $position === $primary ? $image->id : $primaryId;
                }
            }

            $removed = $existing->except($kept);
            $removed->each->delete();
            $coverId = $primaryId ?? ($kept[0] ?? null);
            if ($coverId) {
                $news->images()->whereKey($coverId)->update(['is_primary' => true]);
            }
        }

        if ($legacyPath) {
            $hasCover = $news->images()->where('is_primary', true)->exists();
            $news->images()->create([
                'path' => $legacyPath,
                'sort_order' => (int) $news->images()->max('sort_order') + 1,
                'is_primary' => ! $hasCover,
            ]);
        }

        $news->unsetRelation('images');
        $cover = $news->images()->where('is_primary', true)->value('path') ?? $news->images()->value('path');
        if ($request->boolean('gallery_present') || $legacyPath) {
            $news->update(['image_url' => $cover]);
        }

        foreach ($removed as $image) {
            $stillUsed = \App\Models\NewsImage::where('path', $image->path)->exists()
                || News::where('image_url', $image->path)->exists();
            if (! $stillUsed) {
                Storage::disk('public')->delete($image->path);
            }
        }
    }

    public function store(Request $request)
    {
        $this->validateNews($request);

        $enTitle = $request->translations['en']['title'] ?? $request->translations[config('app.locale')]['title'] ?? 'news';
        $slug = $this->generateUniqueSlug($enTitle);

        // A single file from an older cached form: store it first, so nothing is created if the server cannot write it.
        $legacyPath = $request->hasFile('image_url') ? $this->storeImage($request) : null;

        $news = News::create([
            'slug' => $slug,
            'image_url' => null,
            'status' => $request->has('status') ? 1 : 0,
        ]);
        $this->syncImages($news, $request, $legacyPath);

        foreach (clean_translations($request->input('translations', [])) as $lang => $data) {
            $news->translations()->create([
                'language_code' => $lang,
                'title' => $data['title'] ?? null,
                'excerpt' => $data['excerpt'] ?? null,
                'body' => $data['body'] ?? null,
            ]);
        }

        forgetHomepageNewsCache();

        return redirect()->route('admin.news.index')->with('success', __('cms.news.created'));
    }

    public function show($id)
    {
        return redirect()->route('admin.news.edit', $id);
    }

    public function edit($id)
    {
        $news = News::with(['translations', 'images'])->findOrFail($id);
        $languages = Language::active()->get();
        return view('admin.news.edit', compact('news', 'languages'));
    }

    public function update(Request $request, $id)
    {
        $news = News::findOrFail($id);

        $this->validateNews($request);

        $data = ['status' => $request->has('status') ? 1 : 0];

        $legacyPath = $request->hasFile('image_url') ? $this->storeImage($request) : null;

        $news->update($data);
        $this->syncImages($news, $request, $legacyPath);

        foreach (clean_translations($request->input('translations', [])) as $lang => $data) {
            $news->translations()->updateOrCreate(
                ['language_code' => $lang],
                ['title' => $data['title'] ?? null, 'excerpt' => $data['excerpt'] ?? null, 'body' => $data['body'] ?? null]
            );
        }

        forgetHomepageNewsCache();

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
            forgetHomepageNewsCache();
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

        forgetHomepageNewsCache();

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
