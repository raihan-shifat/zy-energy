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

    public function store(Request $request)
    {
        $rules = [
            'image_url' => 'nullable|file|image|mimes:jpeg,png,jpg,gif,svg,webp|max:10000',
            'translations' => 'required|array',
        ];

        foreach ($request->input('translations', []) as $lang => $data) {
            $rules["translations.$lang.title"] = 'nullable|string|max:255';
            // Catch oversized excerpts as a clear validation error instead of a raw SQL truncation crash
            $rules["translations.$lang.excerpt"] = 'nullable|string|max:500';
        }

        $request->validate($rules);

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

        return redirect()->route('admin.news.index')->with('success', 'News created successfully.');
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

        $rules = [
            'image_url' => 'nullable|file|image|mimes:jpeg,png,jpg,gif,svg,webp|max:10000',
            'translations' => 'required|array',
        ];

        foreach ($request->input('translations', []) as $lang => $data) {
            $rules["translations.$lang.title"] = 'nullable|string|max:255';
            // Catch oversized excerpts as a clear validation error instead of a raw SQL truncation crash
            $rules["translations.$lang.excerpt"] = 'nullable|string|max:500';
        }

        $request->validate($rules);

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

        return redirect()->route('admin.news.index')->with('success', 'News updated successfully.');
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
