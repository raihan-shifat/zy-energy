<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Image uploads from the CKEditor "Insert image" button in admin rich-text fields.
 * Responds in the shape the editor's upload adapter expects:
 * {"url": "..."} on success, {"error": {"message": "..."}} on failure.
 */
class EditorImageController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'upload' => 'required|file|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ], [
            'upload.required' => __('cms.editor.upload_missing', ['max' => ini_get('upload_max_filesize')]),
            'upload.image' => __('cms.editor.upload_not_image'),
            'upload.mimes' => __('cms.editor.upload_not_image'),
            'upload.max' => __('cms.editor.upload_too_large', ['max' => '5 MB']),
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => ['message' => $validator->errors()->first('upload')]], 422);
        }

        $path = $request->file('upload')->store('editor/'.date('Y/m'), 'public');

        return response()->json(['url' => asset('storage/'.$path)]);
    }
}
