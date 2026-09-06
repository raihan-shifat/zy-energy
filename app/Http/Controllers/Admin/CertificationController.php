<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certification;
use App\Models\Language;
use Illuminate\Http\Request;
use App\Traits\PreventsManagerDelete;
use Illuminate\Support\Facades\Storage;

if (!function_exists('forgetCertificationsCache')) {
    function forgetCertificationsCache() {
        \Illuminate\Support\Facades\Cache::forget('certifications:active:with_trans');
        \Illuminate\Support\Facades\Cache::forget('certifications:active:no_trans');
    }
}

class CertificationController extends Controller
{
    use PreventsManagerDelete;
    public function index()
    {
        return view('admin.certifications.index');
    }

    public function getData(Request $request)
    {
        $certifications = Certification::with('translations');

        return datatables()->of($certifications)
            ->addColumn('name', function ($certification) {
                $t = $certification->translations->firstWhere('language_code', 'en');
                return $t->name ?? ($certification->translations->first()->name ?? '');
            })
        ->addColumn('action', function ($certification) {
            $editBtn = '<span class="border border-edit dt-trash rounded-3 d-inline-block"><a href="/admin/certifications/' . $certification->id . '/edit" class=""><i class="bi bi-pencil-fill pencil-edit-color"></i></a></span>';
            $deleteBtn = '<span class="border border-danger dt-trash rounded-3 d-inline-block" onclick="deleteCertification(' . $certification->id . ')"> <i class="bi bi-trash-fill text-danger"></i> </span>';

            return $editBtn . ' ' . (auth()->user()->role === 'manager' ? '' : $deleteBtn);
        })
            ->make(true);
    }

    public function create()
    {
        $languages = Language::active()->get();
        return view('admin.certifications.create', compact('languages'));
    }

    public function store(Request $request)
    {
        $rules = [
            'image_url' => 'nullable|file|image|mimes:jpeg,png,jpg,gif,svg,webp|max:10000',
            'document_url' => 'nullable|file|mimes:pdf,doc,docx|max:20000',
            'translations' => 'required|array',
        ];

        foreach ($request->input('translations', []) as $lang => $data) {
            $rules["translations.$lang.name"] = 'nullable|string|max:255';
        }

        $request->validate($rules);

        $certification = Certification::create([
            'image_url' => $request->hasFile('image_url') ? $request->file('image_url')->store('certifications', 'public') : null,
            'document_url' => $request->hasFile('document_url') ? $request->file('document_url')->store('certifications', 'public') : null,
            'status' => $request->has('status') ? 1 : 0,
        ]);

        foreach (clean_translations($request->input('translations', [])) as $lang => $data) {
            $certification->translations()->create([
                'language_code' => $lang,
                'name' => $data['name'],
            ]);
        }

        forgetCertificationsCache();

        return redirect()->route('admin.certifications.index')->with('success', 'Certification created successfully.');
    }

    public function show($id)
    {
        return redirect()->route('admin.certifications.edit', $id);
    }

    public function edit($id)
    {
        $certification = Certification::with('translations')->findOrFail($id);
        $languages = Language::active()->get();
        return view('admin.certifications.edit', compact('certification', 'languages'));
    }

    public function update(Request $request, $id)
    {
        $certification = Certification::findOrFail($id);

        $rules = [
            'image_url' => 'nullable|file|image|mimes:jpeg,png,jpg,gif,svg,webp|max:10000',
            'document_url' => 'nullable|file|mimes:pdf,doc,docx|max:20000',
            'translations' => 'required|array',
        ];

        foreach ($request->input('translations', []) as $lang => $data) {
            $rules["translations.$lang.name"] = 'nullable|string|max:255';
        }

        $request->validate($rules);

        $data = [
            'status' => $request->has('status') ? 1 : 0,
        ];

        if ($request->hasFile('image_url')) {
            if ($certification->image_url) {
                Storage::disk('public')->delete($certification->image_url);
            }
            $data['image_url'] = $request->file('image_url')->store('certifications', 'public');
        }

        if ($request->hasFile('document_url')) {
            if ($certification->document_url) {
                Storage::disk('public')->delete($certification->document_url);
            }
            $data['document_url'] = $request->file('document_url')->store('certifications', 'public');
        }

        $certification->update($data);

        foreach (clean_translations($request->input('translations', [])) as $lang => $data) {
            $certification->translations()->updateOrCreate(
                ['language_code' => $lang],
                ['name' => $data['name']]
            );
        }

        forgetCertificationsCache();

        return redirect()->route('admin.certifications.index')->with('success', 'Certification updated successfully.');
    }

    public function destroy($id)
    {
        if ($guard = $this->rejectManagerDelete()) {
            return $guard;
        }

        $certification = Certification::find($id);
        if ($certification) {
            if ($certification->image_url) {
                Storage::disk('public')->delete($certification->image_url);
            }
            if ($certification->document_url) {
                Storage::disk('public')->delete($certification->document_url);
            }
            $certification->delete();

            forgetCertificationsCache();

            return response()->json(['success' => true, 'message' => 'Certification deleted successfully.']);
        }

        return response()->json(['success' => false, 'message' => 'Error deleting certification.']);
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:certifications,id',
            'status' => 'required|boolean',
        ]);

        $certification = Certification::find($request->id);
        $certification->status = $request->status;
        $certification->save();

        forgetCertificationsCache();

        return response()->json(['success' => true, 'message' => 'Status updated.']);
    }
}
