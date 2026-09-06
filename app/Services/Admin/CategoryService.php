<?php

namespace App\Services\Admin;

use App\Models\Category;
use App\Repositories\Admin\Category\CategoryRepositoryInterface;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class CategoryService
{
    protected $categoryRepository;

    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function getCategoriesForDataTable($request)
    {
        $query = $this->categoryRepository->all()->with('translations');

        $minSort = (clone $query)->min('sort_order');
        $maxSort = (clone $query)->max('sort_order');

        return DataTables::of($query)
            ->addColumn('name', function ($category) {
                $translation = $category->translations->firstWhere('language_code', 'en');

                return $translation ? $translation->name : 'No name available';
            })
            ->addColumn('sort_order', function ($category) {
                return $category->sort_order;
            })
            ->addColumn('description', function ($category) {
                $translation = $category->translations->firstWhere('language_code', 'en');

                return $translation ? $translation->description : 'No description available';
            })
            ->addColumn('action', function ($category) use ($minSort, $maxSort) {
                $editBtn = '<a href="'.route('admin.categories.edit', $category->id).'" class="btn btn-primary btn-sm">Edit</a>';
                $deleteBtn = '<form action="'.route('admin.categories.destroy', $category->id).'" method="POST" class="d-inline" onsubmit="return confirm(\'Are you sure you want to delete this category?\');">'
                    .csrf_field().method_field('DELETE')
                    .'<button type="submit" class="btn btn-danger btn-sm">Delete</button></form>';

                $upClass = $category->sort_order <= $minSort ? 'opacity-25 pointer-events-none' : '';
                $downClass = $category->sort_order >= $maxSort ? 'opacity-25 pointer-events-none' : '';

                $upBtn = '<span class="btn btn-sm btn-outline-secondary me-1 '.$upClass.'" onclick="moveCategory('.$category->id.', \'up\')" title="Move up"><i class="bi bi-arrow-up"></i></span>';
                $downBtn = '<span class="btn btn-sm btn-outline-secondary me-1 '.$downClass.'" onclick="moveCategory('.$category->id.', \'down\')" title="Move down"><i class="bi bi-arrow-down"></i></span>';

                $isManager = auth()->user()->role === 'manager';

                return $upBtn.$downBtn.$editBtn.' '.($isManager ? '' : $deleteBtn);
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Store a newly created category.
     */
    public function store(array $translations)
    {
        $validator = Validator::make($translations, [
            'en.name' => 'required|string|max:255',
            'en.description' => 'nullable|string',
            '*.name' => 'nullable|string|max:255',
            '*.description' => 'nullable|string',
            '*.image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10000',
        ], trans('category'));

        if ($validator->fails()) {
            return $validator->errors();
        }

        return $this->categoryRepository->storeWithTranslations(clean_translations($translations));
    }

    /**
     * Uploads an image and returns the full storage URL.
     */
    private function uploadImage($image)
    {
        $fileName = time().'_'.$image->getClientOriginalName();
        $path = $image->storeAs('categories', $fileName, 'public');

        return 'storage/'.$path; // Ensure it's publicly accessible
    }

    /**
     * Update an existing category.
     */
    public function update($request, $id)
    {
        $category = Category::findOrFail($id);

        $validatedData = $request->validate([
            'translations.en.name' => 'required|string|max:255',
            'translations.*.name' => 'nullable|string|max:255',
            'translations.*.description' => 'nullable|string',
            'translations.*.image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10000',
        ]);

        return $this->categoryRepository->updateWithTranslations($category, clean_translations($request->translations));
    }

    /**
     * Delete an existing category.
     */
    public function destroy($id)
    {
        // Call the repository to delete the category
        return $this->categoryRepository->destroy($id);
    }

    /**
     * Find a category by its ID.
     */
    public function find($id)
    {
        // Call the repository to find the category by ID
        return $this->categoryRepository->find($id);
    }
}
