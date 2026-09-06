<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Language;
use App\Services\Admin\BrandService;
use App\Traits\PreventsManagerDelete;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    use PreventsManagerDelete;

    protected $brandService;

    public function __construct(BrandService $brandService)
    {
        $this->brandService = $brandService;
    }

    public function index()
    {
        return view('admin.brands.index');
    }

    public function getData(Request $request)
    {
        $brands = $this->brandService->getAllBrands();

        return datatables()->of($brands)
            ->addColumn('name', function ($brand) {
                $translation = $brand->translations->firstWhere('locale', 'en');
                return $translation ? $translation->name : $brand->slug;
            })
            ->addColumn('action', function ($brand) {
                $editBtn = '<a href="'.route('admin.brands.edit', $brand->id).'" class="btn btn-sm btn-primary">Edit</a>';
                $deleteBtn = '<form action="'.route('admin.brands.destroy', $brand->id).'" method="POST" style="display:inline-block;" class="delete-brand-form">
                    '.csrf_field().method_field('DELETE').'
                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                </form>';

                return auth()->user()->role === 'manager' ? $editBtn : $editBtn.' '.$deleteBtn;
            })
            ->make(true);
    }

    public function create()
    {
        return view('admin.brands.create');
    }

    public function store(Request $request)
    {
        $rules = [
            'logo_url' => 'nullable|file|image|mimes:jpeg,png,jpg,gif,svg,webp|max:10000',
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
        }

        $validated = $request->validate($rules);

        $result = $this->brandService->store($request->all());

        if ($result instanceof \Illuminate\Support\MessageBag) {
            return redirect()->back()->withErrors($result)->withInput();
        }

        return redirect()->route('admin.brands.index')->with('success', __('cms.brands.created'));
    }

    public function edit($id)
    {
        $brand = Brand::with('translations')->findOrFail($id);

        $languages = Language::active()->get();

        return view('admin.brands.edit', compact('brand', 'languages'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'logo_url' => 'nullable|file|image|mimes:jpeg,png,jpg,gif,svg|max:10000',
            'translations' => 'required|array',
            'translations.en.name' => 'required|string|max:255',
            'translations.*.name' => 'nullable|string|max:255',
            'translations.*.description' => 'nullable|string',
        ]);

        $result = $this->brandService->updateBrand($id, $request->all());

        if ($result instanceof \Illuminate\Support\MessageBag) {
            return redirect()->back()->withErrors($result)->withInput();
        }

        return redirect()->route('admin.brands.index')->with('success', __('cms.brands.updated'));
    }

    public function destroy($id)
    {
        if ($guard = $this->rejectManagerDelete()) {
            return $guard;
        }

        $result = $this->brandService->deleteBrand($id);

        if ($result) {
            return response()->json([
                'success' => true,
                'message' => __('cms.brands.deleted'),
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting brand.',
            ]);
        }
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:brands,id',
            'status' => 'required|boolean',
        ]);

        $brand = Brand::find($request->id);

        // status column is an ENUM('active','inactive','discontinued') — map the posted boolean
        $brand->status = $request->boolean('status') ? 'active' : 'inactive';
        $brand->save();

        if ($brand) {
            return response()->json([
                'success' => true,
                'message' => __('cms.brands.status_updated'),
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Brand status could not be updated.',
            ]);
        }
    }
}
