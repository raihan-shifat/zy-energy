<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Language;
use App\Services\Admin\CategoryService;
use App\Traits\PreventsManagerDelete;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

if (!function_exists('forgetCategoriesCache')) {
    function forgetCategoriesCache() {
        \Illuminate\Support\Facades\Cache::forget('categories:active:with_trans');
        \Illuminate\Support\Facades\Cache::forget('categories:active:no_trans');
    }
}

if (!function_exists('forgetCategoryFilterCache')) {
    function forgetCategoryFilterCache(int $categoryId) {
        \Illuminate\Support\Facades\Cache::forget("filters:category:{$categoryId}");
        \Illuminate\Support\Facades\Cache::forget("filters:shop:{$categoryId}");
    }
}

class CategoryController extends Controller
{
    use PreventsManagerDelete;

    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function index()
    {
        return view('admin.categories.index');
    }

    public function getCategories(Request $request)
    {
        if ($request->ajax()) {
            return $this->categoryService->getCategoriesForDataTable($request);
        }
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $rules = [
            'translations' => 'required|array',
        ];

        foreach ($request->input('translations', []) as $lang => $data) {
            if ($lang === 'en') {
                $rules["translations.$lang.name"] = 'required|string|max:255';
                $rules["translations.$lang.description"] = 'nullable|string';
            } else {
                // Non-English translations are optional
                $rules["translations.$lang.name"] = 'nullable|string|max:255';
                $rules["translations.$lang.description"] = 'nullable|string';
            }
            $rules["translations.$lang.image"] = 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048';
        }

        $request->validate($rules);

        $translations = $request->input('translations');
        foreach ($translations as $languageCode => $translation) {
            if ($request->hasFile("translations.$languageCode.image")) {
                $translations[$languageCode]['image'] = $request->file("translations.$languageCode.image");
            }
        }

        $result = $this->categoryService->store($translations);

        if ($result instanceof \Illuminate\Support\MessageBag) {
            return redirect()->back()->withErrors($result)->withInput();
        }

        if ($request->filled('types')) {
            $types = array_values(array_filter(array_map('trim', explode(',', $request->input('types')))));
            $result->types = $types;
            $result->save();
        }

        forgetCategoriesCache();
        forgetCategoryFilterCache($result->id);

        return redirect()->route('admin.categories.index')->with('success', __('cms.categories.created'));
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        $category = Category::with('translations')->findOrFail($id);

        $activeLanguages = Language::where('active', true)->get();

        return view('admin.categories.edit', compact('category', 'activeLanguages'));
    }

    public function update(Request $request, $id)
    {
        $this->categoryService->update($request, $id);

        if ($request->filled('types')) {
            $types = array_values(array_filter(array_map('trim', explode(',', $request->input('types')))));
            Category::where('id', $id)->update(['types' => json_encode($types)]);
        }

        forgetCategoriesCache();
        forgetCategoryFilterCache($id);

        return redirect()->route('admin.categories.index')->with('success', __('cms.categories.updated'));
    }

    public function destroy($id)
    {
        if ($guard = $this->rejectManagerDelete()) {
            return $guard;
        }

        // Block deleting a parent that still has children (FK RESTRICT would 500)
        if (Category::where('parent_category_id', $id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete: this category has sub-categories. Delete or move them first.',
            ]);
        }

        try {
            $result = $this->categoryService->destroy($id);

            if ($result) {
                forgetCategoriesCache();
                forgetCategoryFilterCache($id);

                return response()->json([
                    'success' => true,
                    'message' => __('cms.categories.deleted'),
                ]);
            }
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Error deleting category '.$id.': '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Cannot delete this category because it is linked to products.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Error deleting category.',
        ]);
    }

    public function updateCategoryStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:categories,id',
            'status' => 'required|boolean',
        ]);

        $category = Category::find($request->id);
        $category->status = $request->status;
        $category->save();

        if ($category) {
            forgetCategoriesCache();
            forgetCategoryFilterCache($category->id);

            return response()->json([
                'success' => true,
                'message' => __('cms.categories.status_updated'),
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Category status could not be updated.',
            ]);
        }
    }

    public function moveUp($id)
    {
        $category = Category::withoutGlobalScopes()->findOrFail($id);
        $siblingQuery = Category::withoutGlobalScopes()
            ->where('sort_order', '<', $category->sort_order);
        // Root categories have NULL parent — `where(parent, NULL)` matches nothing
        $siblingQuery->when(
            $category->parent_category_id === null,
            fn ($q) => $q->whereNull('parent_category_id'),
            fn ($q) => $q->where('parent_category_id', $category->parent_category_id)
        );
        $sibling = $siblingQuery->orderBy('sort_order', 'desc')->first();

        if (! $sibling) {
            return response()->json(['success' => false, 'message' => 'Already at top.']);
        }

        DB::transaction(function () use ($category, $sibling) {
            $tmp = $category->sort_order;
            $category->update(['sort_order' => $sibling->sort_order]);
            $sibling->update(['sort_order' => $tmp]);
        });

        forgetCategoriesCache();
        forgetCategoryFilterCache($category->id);
        forgetCategoryFilterCache($sibling->id);

        return response()->json(['success' => true]);
    }

    public function moveDown($id)
    {
        $category = Category::withoutGlobalScopes()->findOrFail($id);
        $siblingQuery = Category::withoutGlobalScopes()
            ->where('sort_order', '>', $category->sort_order);
        $siblingQuery->when(
            $category->parent_category_id === null,
            fn ($q) => $q->whereNull('parent_category_id'),
            fn ($q) => $q->where('parent_category_id', $category->parent_category_id)
        );
        $sibling = $siblingQuery->orderBy('sort_order', 'asc')->first();

        if (! $sibling) {
            return response()->json(['success' => false, 'message' => 'Already at bottom.']);
        }

        DB::transaction(function () use ($category, $sibling) {
            $tmp = $category->sort_order;
            $category->update(['sort_order' => $sibling->sort_order]);
            $sibling->update(['sort_order' => $tmp]);
        });

        forgetCategoriesCache();
        forgetCategoryFilterCache($category->id);
        forgetCategoryFilterCache($sibling->id);

        return response()->json(['success' => true]);
    }
}
