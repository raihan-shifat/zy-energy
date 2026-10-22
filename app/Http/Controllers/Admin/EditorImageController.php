<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ImageOptimizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/**
 * Admin file uploads (one file per request): the CKEditor "Insert image" button,
 * product gallery images and About Us page images/videos.
 * Responds {"url": "...", "path": "..."} on success, {"error": {"message": "..."}} on failure.
 */
class EditorImageController extends Controller
{
    /** Default image limit; the effective one is lowered to PHP's upload limits. */
    private const MAX_BYTES = 5 * 1024 * 1024;

    public const ABOUT_IMAGE_MAX_BYTES = 3 * 1024 * 1024;

    public const ABOUT_VIDEO_MAX_BYTES = 50 * 1024 * 1024;

    public function store(Request $request): JsonResponse
    {
        return $this->handle($request, 'editor/'.date('Y/m'), 'image|mimes:jpeg,png,jpg,gif,webp', self::MAX_BYTES, __('cms.editor.upload_not_image'));
    }

    /**
     * Product gallery images (admin create/edit): same pipeline, stored next to the
     * existing product images. Also returns the storage path the form submits.
     */
    public function storeProductImage(Request $request): JsonResponse
    {
        return $this->handle($request, 'products', 'image|mimes:jpeg,png,jpg,webp', self::MAX_BYTES, __('cms.products.gallery_bad_type'));
    }

    /** About Us images: resized to max 1920px wide, plus the site's usual WebP variants. */
    public function storeAboutImage(Request $request, ImageOptimizationService $images): JsonResponse
    {
        return $this->handle($request, 'about', 'image|mimes:jpeg,png,jpg,webp', self::ABOUT_IMAGE_MAX_BYTES,
            __('cms.about_editor.image_bad_type'), function (string $path) use ($images) {
                $images->limitWidth($path, 1920);
                $images->generateResponsiveImages($path);
            });
    }

    /** About Us video files (MP4/WebM); the content type is checked on the server. */
    public function storeAboutVideo(Request $request): JsonResponse
    {
        return $this->handle($request, 'about/videos', 'mimetypes:video/mp4,application/mp4,video/webm|mimes:mp4,webm', self::ABOUT_VIDEO_MAX_BYTES,
            __('cms.about_editor.video_bad_type'));
    }

    private function handle(Request $request, string $folder, string $typeRules, int $cap, string $badType, ?callable $after = null): JsonResponse
    {
        $file = $request->file('upload');
        $limit = self::humanLimit($cap);

        // PHP rejected the file (e.g. bigger than upload_max_filesize): it is present but not valid.
        if ($file && ! $file->isValid()) {
            $tooLarge = in_array($file->getError(), [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true);
            Log::warning('Admin upload rejected by PHP', ['error' => $file->getErrorMessage()]);

            return $this->error($tooLarge
                ? __('cms.editor.upload_too_large', ['max' => $limit])
                : __('cms.editor.upload_failed'), $tooLarge ? 413 : 422);
        }

        $validator = Validator::make($request->all(), [
            'upload' => 'required|file|'.$typeRules.'|max:'.intdiv(self::maxBytes($cap), 1024),
        ], [
            'upload.required' => __('cms.editor.upload_missing', ['max' => $limit]),
            'upload.image' => $badType,
            'upload.mimes' => $badType,
            'upload.mimetypes' => $badType,
            'upload.max' => __('cms.editor.upload_too_large', ['max' => $limit]),
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first('upload'), 422);
        }

        try {
            $path = $file->store($folder, 'public');
        } catch (\Throwable $e) {
            $path = false;
            Log::error('Admin upload could not be written', ['exception' => $e]);
        }

        if (! $path) {
            $root = Storage::disk('public')->path('');
            Log::error('Admin upload failed: storage not writable?', [
                'disk_root' => $root,
                'disk_root_writable' => is_writable($root),
            ]);

            return $this->error(__('cms.editor.upload_storage_failed'), 500);
        }

        if ($after) {
            $after($path);
        }

        return response()->json(['url' => asset('storage/'.$path), 'path' => $path]);
    }

    /** Smallest of $cap and PHP's upload_max_filesize / post_max_size, in bytes. */
    public static function maxBytes(int $cap = self::MAX_BYTES): int
    {
        $limits = [$cap];
        foreach (['upload_max_filesize', 'post_max_size'] as $key) {
            $bytes = self::iniBytes((string) ini_get($key));
            if ($bytes > 0) {
                $limits[] = $bytes;
            }
        }

        return min($limits);
    }

    public static function humanLimit(int $cap = self::MAX_BYTES): string
    {
        return rtrim(rtrim(number_format(self::maxBytes($cap) / 1048576, 1), '0'), '.').' MB';
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
