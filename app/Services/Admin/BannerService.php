<?php

namespace App\Services\Admin;

use App\Models\BannerTranslation;
use App\Models\Language;
use App\Repositories\Admin\Banner\BannerRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerService
{
    protected $bannerRepository;

    public function __construct(BannerRepositoryInterface $bannerRepository)
    {
        $this->bannerRepository = $bannerRepository;
    }

    public function getAllBanners()
    {
        return $this->bannerRepository->getAllBanners();
    }

    public function store(Request $request)
    {
        $activeLanguages = Language::where('active', 1)->pluck('code')->toArray();
        $defaultLang = 'en';

        $rules = [
            'type' => 'required|in:promotion,sale,seasonal,featured,announcement',
        ];

        foreach ($activeLanguages as $code) {
            if ($code === $defaultLang) {
                $rules["languages.$code.title"] = 'required|string|max:255';
            } else {
                $rules["languages.$code.title"] = 'nullable|string|max:255';
            }

            $rules["languages.$code.image"] = 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:10000';
            $rules["languages.$code.description"] = 'nullable|string';
            $rules["languages.$code.subtitle"] = 'nullable|string';
            $rules["languages.$code.cta_text"] = 'nullable|string|max:255';
            $rules["languages.$code.cta_link"] = 'nullable|string|max:255';
        }

        $validated = $request->validate($rules);

        $banner = $this->bannerRepository->createBanner($request->only('type'));

        $defaultImage = null;
        if ($request->hasFile("languages.$defaultLang.image")) {
            $defaultImage = $request->file("languages.$defaultLang.image")->store('banner_images', 'public');
        }

        foreach ($activeLanguages as $code) {
            $langInput = $request->input("languages.$code", []);

            if (! has_translation_content($langInput)) {
                continue;
            }

            $image = $request->file("languages.$code.image");
            $imageUrl = $image
                ? $image->store('banner_images', 'public')
                : $defaultImage;

            BannerTranslation::create([
                'banner_id' => $banner->id,
                'language_code' => $code,
                'title' => $langInput['title'] ?? '',
                'description' => $langInput['description'] ?? null,
                'subtitle' => $this->sanitizeText($langInput['subtitle'] ?? null),
                'cta_text' => $langInput['cta_text'] ?? null,
                'cta_link' => $langInput['cta_link'] ?? null,
                'image_url' => $imageUrl,
            ]);
        }

        return redirect()->route('admin.banners.index')->with('success', __('cms.banners.created'));
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'languages.*.title' => 'nullable|string|max:255',
            'languages.*.image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:10000',
            'type' => 'required|in:promotion,sale,seasonal,featured,announcement',
        ]);

        // The edit form posts numeric-indexed rows with a language_code field,
        // so `languages.en.title` can never match — enforce the EN title manually.
        $enData = collect($request->input('languages', []))->firstWhere('language_code', 'en');
        if (! $enData || trim((string) ($enData['title'] ?? '')) === '') {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'languages.en.title' => __('validation.required', ['attribute' => 'english title']),
            ]);
        }

        $banner = $this->bannerRepository->getBannerById($id);

        $this->bannerRepository->updateBanner($banner, $request->only('type'));

        foreach ($request->languages as $languageData) {
            if (empty($languageData['title'])) {
                continue;
            }

            $translation = BannerTranslation::where('banner_id', $banner->id)
                ->where('language_code', $languageData['language_code'])
                ->first();

            if ($translation) {
                $imageUrl = null;
                if (isset($languageData['image']) && $languageData['image']) {
                    if ($translation->image_url && Storage::disk('public')->exists($translation->image_url)) {
                        Storage::disk('public')->delete($translation->image_url);
                    }
                    $imageUrl = $languageData['image']->store('banner_images', 'public');
                }

                $translation->title = $languageData['title'];
                $translation->image_url = $imageUrl ?: $translation->image_url;
                $translation->description = $languageData['description'] ?? $translation->description;
                $translation->subtitle = $this->sanitizeText($languageData['subtitle'] ?? $translation->subtitle);
                $translation->cta_text = $languageData['cta_text'] ?? $translation->cta_text;
                $translation->cta_link = $languageData['cta_link'] ?? $translation->cta_link;
                $translation->save();
            } else {
                $imageUrl = null;
                if (isset($languageData['image']) && $languageData['image']) {
                    $imageUrl = $languageData['image']->store('banner_images', 'public');
                }

                BannerTranslation::create([
                    'banner_id' => $banner->id,
                    'language_code' => $languageData['language_code'],
                    'title' => $languageData['title'],
                    'description' => $languageData['description'] ?? null,
                    'subtitle' => $this->sanitizeText($languageData['subtitle'] ?? null),
                    'cta_text' => $languageData['cta_text'] ?? null,
                    'cta_link' => $languageData['cta_link'] ?? null,
                    'image_url' => $imageUrl,
                ]);
            }
        }
    }

    public function delete(int $id)
    {
        $banner = $this->bannerRepository->getBannerById($id);
        $this->bannerRepository->deleteBanner($banner);
    }

    /**
     * Sanitize plain-text fields: decode HTML entities and strip tags.
     */
    private function sanitizeText(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $decoded = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $decoded = strip_tags($decoded);

        return trim($decoded);
    }
}
