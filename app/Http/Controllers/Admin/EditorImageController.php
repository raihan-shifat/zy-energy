<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/**
 * Image uploads from the CKEditor "Insert image" button in admin rich-text fields.
 * Responds in the shape the editor's upload adapter expects:
 * {"url": "..."} on success, {"error": {"message": "..."}} on failure.
 */
class EditorImageController extends Controller
{
    /** Our own limit; the effective one is lowered to PHP's upload limits. */
    private const MAX_BYTES = 5 * 1024 * 1024;

    public function store(Request $request): JsonResponse
    {
        return $this->handle($request, 'editor/'.date('Y/m'), 'jpeg,png,jpg,gif,webp');
    }

    /**
     * Product gallery images (admin create/edit): same pipeline, stored next to the
     * existing product images. Also returns the storage path the form submits.
     */
    public function storeProductImage(Request $request): JsonResponse
    {
        return $this->handle($request, 'products', 'jpeg,png,jpg,webp');
    }

    private function handle(Request $request, string $folder, string $mimes): JsonResponse
    {
        $file = $request->file('upload');

        // PHP rejected the file (e.g. bigger than upload_max_filesize): it is present but not valid.
        if ($file && ! $file->isValid()) {
            $tooLarge = in_array($file->getError(), [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true);
            Log::warning('Editor image upload rejected by PHP', ['error' => $file->getErrorMessage()]);

            return $this->error($tooLarge
                ? __('cms.editor.upload_too_large', ['max' => self::humanLimit()])
                : __('cms.editor.upload_failed'), $tooLarge ? 413 : 422);
        }

        $validator = Validator::make($request->all(), [
            'upload' => 'required|file|image|mimes:'.$mimes.'|max:'.intdiv(self::maxBytes(), 1024),
        ], [
            'upload.required' => __('cms.editor.upload_missing', ['max' => self::humanLimit()]),
            'upload.image' => __(str_contains($mimes, 'gif') ? 'cms.editor.upload_not_image' : 'cms.products.gallery_bad_type'),
            'upload.mimes' => __(str_contains($mimes, 'gif') ? 'cms.editor.upload_not_image' : 'cms.products.gallery_bad_type'),
            'upload.max' => __('cms.editor.upload_too_large', ['max' => self::humanLimit()]),
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first('upload'), 422);
        }

        try {
            $path = $file->store($folder, 'public');
        } catch (\Throwable $e) {
            $path = false;
            Log::error('Editor image upload could not be written', ['exception' => $e]);
        }

        if (! $path) {
            $root = Storage::disk('public')->path('');
            Log::error('Editor image upload failed: storage not writable?', [
                'disk_root' => $root,
                'disk_root_writable' => is_writable($root),
            ]);

            return $this->error(__('cms.editor.upload_storage_failed'), 500);
        }

        return response()->json(['url' => asset('storage/'.$path), 'path' => $path]);
    }

    /** Smallest of our limit and PHP's upload_max_filesize / post_max_size, in bytes. */
    public static function maxBytes(): int
    {
        $limits = [self::MAX_BYTES];
        foreach (['upload_max_filesize', 'post_max_size'] as $key) {
            $bytes = self::iniBytes((string) ini_get($key));
            if ($bytes > 0) {
                $limits[] = $bytes;
            }
        }

        return min($limits);
    }

    public static function humanLimit(): string
    {
        return rtrim(rtrim(number_format(self::maxBytes() / 1048576, 1), '0'), '.').' MB';
    }

    private static function iniBytes(string $value): int
    {
        $value = trim($value);
        $number = (int) $value;

        return match (strtolower(substr($value, -1))) {
            'g' => $number * 1024 ** 3,
            'm' => $number * 1024 ** 2,
            'k' => $number * 1024,
            default => $number,
        };
    }

    private function error(string $message, int $status): JsonResponse
    {
        return response()->json(['error' => ['message' => $message]], $status);
    }
}
