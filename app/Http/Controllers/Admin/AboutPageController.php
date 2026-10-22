<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AboutMedia;
use App\Models\AboutSection;
use App\Services\AboutPageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * Admin editor for the About Us page: ordered sections with translations and media.
 * The page posts the whole editor state as one form (sent with fetch, answered in JSON).
 */
class AboutPageController extends Controller
{
    public const MAX_SECTIONS = 30;

    public const MAX_GALLERY = 12;

    public const MAX_ITEMS = 12;

    public function edit()
    {
        $sections = AboutSection::with(['translations', 'media.translations'])->orderBy('sort_order')->orderBy('id')->get();

        return view('admin.about.edit', compact('sections'));
    }

    public function update(Request $request): JsonResponse
    {
        $request->validate($this->rules(), $this->messages(), $this->attributes($request));
        $sections = array_values((array) $request->input('sections', []));
        $this->validateContent($sections);

        DB::transaction(function () use ($sections) {
            $keep = [];
            foreach ($sections as $position => $data) {
                $section = ! empty($data['id']) ? AboutSection::find((int) $data['id']) : null;
                $section ??= new AboutSection(['type' => $data['type']]);
                $section->fill([
                    'type' => $data['type'],
                    'sort_order' => $position,
                    'is_active' => ! empty($data['is_active']),
                    'settings' => $this->settingsFor($data),
                ])->save();
                $keep[] = $section->id;

                $this->saveTranslations($section, (array) ($data['tr'] ?? []));
                $this->saveMedia($section, array_values((array) ($data['media'] ?? [])));
            }

            // Removed sections: database rows only, uploaded files stay on disk.
            AboutSection::whereNotIn('id', $keep ?: [0])->get()->each->delete();
        });

        AboutPageService::clearCache();

        return response()->json(['message' => __('cms.about_editor.saved')]);
    }

    private function rules(): array
    {
        $types = implode(',', AboutSection::TYPES);
        $pathRule = ['nullable', 'string', 'max:255', 'regex:#^about/[A-Za-z0-9._/-]+$#', 'not_regex:#\.\.#'];

        return [
            'sections' => 'nullable|array|max:'.self::MAX_SECTIONS,
            'sections.*.id' => 'nullable|integer',
            'sections.*.type' => 'required|in:'.$types,
            'sections.*.is_active' => 'nullable|boolean',
            'sections.*.settings.position' => 'nullable|in:left,right',
            'sections.*.settings.variant' => 'nullable|in:plain,feature',
            'sections.*.settings.align' => 'nullable|in:left,center',
            'sections.*.settings.button_link' => ['nullable', 'string', 'max:500', 'regex:#^(https?://|/)#i'],
            'sections.*.settings.button2_link' => ['nullable', 'string', 'max:500', 'regex:#^(https?://|/)#i'],
            'sections.*.items' => 'nullable|array|max:'.self::MAX_ITEMS,
            'sections.*.items.*.number' => 'nullable|string|max:20',
            'sections.*.items.*.icon' => ['nullable', 'string', 'max:80', 'regex:/^fa-(solid|regular|brands) fa-[a-z0-9-]+$/'],
            'sections.*.tr' => 'nullable|array',
            'sections.*.tr.*.title' => 'nullable|string|max:255',
            'sections.*.tr.*.subtitle' => 'nullable|string|max:2000',
            'sections.*.tr.*.body' => 'nullable|string|max:100000',
            'sections.*.tr.*.button_text' => 'nullable|string|max:100',
            'sections.*.tr.*.eyebrow' => 'nullable|string|max:100',
            'sections.*.tr.*.button2_text' => 'nullable|string|max:100',
            'sections.*.tr.*.items' => 'nullable|array|max:'.self::MAX_ITEMS,
            'sections.*.tr.*.items.*.*' => 'nullable|string|max:500',
            'sections.*.media' => 'nullable|array|max:'.(self::MAX_GALLERY + 2),
            'sections.*.media.*.id' => 'nullable|integer',
            'sections.*.media.*.kind' => 'required|in:image,video,poster',
            'sections.*.media.*.path' => $pathRule,
            'sections.*.media.*.url' => 'nullable|string|max:500',
            'sections.*.media.*.tr.*.alt' => 'nullable|string|max:255',
            'sections.*.media.*.tr.*.caption' => 'nullable|string|max:500',
        ];
    }

    private function messages(): array
    {
        return [
            'sections.*.settings.button_link.regex' => __('cms.about_editor.link_invalid'),
            'sections.*.settings.button2_link.regex' => __('cms.about_editor.link_invalid'),
            'sections.*.items.*.icon.regex' => __('cms.about_editor.icon_invalid'),
            'sections.*.media.*.path.regex' => __('cms.about_editor.media_invalid'),
            'sections.*.media.*.path.not_regex' => __('cms.about_editor.media_invalid'),
            'sections.max' => __('cms.about_editor.too_many_sections', ['max' => self::MAX_SECTIONS]),
        ];
    }

    /** "Section 3 (Gallery): …" instead of "sections.2.media.0.path". */
    private function attributes(Request $request): array
    {
        $names = [];
        foreach (array_values((array) $request->input('sections', [])) as $i => $s) {
            $label = __('cms.about_editor.section_n', ['n' => $i + 1, 'type' => __('cms.about_editor.types.'.($s['type'] ?? 'text'))]);
            foreach (['button_link', 'button2_link', 'position', 'variant', 'align'] as $f) {
                $names["sections.$i.settings.$f"] = $label.' – '.__('cms.about_editor.fields.'.$f);
            }
            $names["sections.$i.type"] = $label;
            foreach (array_keys((array) ($s['tr'] ?? [])) as $lang) {
                foreach (['title', 'subtitle', 'body', 'button_text', 'eyebrow', 'button2_text'] as $f) {
                    $names["sections.$i.tr.$lang.$f"] = $label.' – '.__('cms.about_editor.fields.'.$f)." ($lang)";
                }
            }
            foreach (array_keys((array) ($s['media'] ?? [])) as $j) {
                $names["sections.$i.media.$j.path"] = $label.' – '.__('cms.about_editor.fields.media');
            }
        }

        return $names;
    }

    /** Checks that need the whole section: files exist, gallery size, one image/video per section, video URL. */
    private function validateContent(array $sections): void
    {
        $errors = [];
        foreach ($sections as $i => $s) {
            $label = __('cms.about_editor.section_n', ['n' => $i + 1, 'type' => __('cms.about_editor.types.'.$s['type'])]);
            $media = array_values((array) ($s['media'] ?? []));
            $images = array_filter($media, fn ($m) => ($m['kind'] ?? '') === 'image');

            if ($s['type'] === 'gallery' && count($images) > self::MAX_GALLERY) {
                $errors["sections.$i.media"] = $label.': '.__('cms.about_editor.gallery_too_many', ['max' => self::MAX_GALLERY]);
            }
            if (in_array($s['type'], ['hero', 'image_text'], true) && count($images) > 1) {
                $errors["sections.$i.media"] = $label.': '.__('cms.about_editor.one_image_only');
            }
            foreach ($media as $j => $m) {
                if (! empty($m['path']) && ! Storage::disk('public')->exists($m['path'])) {
                    $errors["sections.$i.media.$j.path"] = $label.': '.__('cms.about_editor.media_missing');
                }
                if (($m['kind'] ?? '') === 'video' && empty($m['path']) && trim((string) ($m['url'] ?? '')) !== '' && ! AboutPageService::embedUrl($m['url'])) {
                    $errors["sections.$i.media.$j.url"] = $label.': '.__('cms.about_editor.video_url_invalid');
                }
            }
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function settingsFor(array $data): array
    {
        $settings = array_filter((array) ($data['settings'] ?? []), fn ($v) => $v !== null && $v !== '');
        if (in_array($data['type'], ['stats', 'cards'], true)) {
            $settings['items'] = array_values(array_map(
                fn ($item) => array_filter(['number' => $item['number'] ?? null, 'icon' => $item['icon'] ?? null], fn ($v) => $v !== null && $v !== ''),
                (array) ($data['items'] ?? [])
            ));
        }

        return $settings;
    }

    private function saveTranslations(AboutSection $section, array $tr): void
    {
        foreach ($tr as $lang => $fields) {
            $fields = (array) $fields;
            $extra = array_filter([
                'eyebrow' => $fields['eyebrow'] ?? null,
                'button2_text' => $fields['button2_text'] ?? null,
                'items' => isset($fields['items']) ? array_values(array_map(fn ($i) => array_map(fn ($v) => (string) $v, (array) $i), (array) $fields['items'])) : null,
            ], fn ($v) => $v !== null && $v !== '' && $v !== []);
            $values = [
                'title' => $fields['title'] ?? null,
                'subtitle' => $fields['subtitle'] ?? null,
                'body' => $fields['body'] ?? null,
                'button_text' => $fields['button_text'] ?? null,
                'extra' => $extra ?: null,
            ];

            $hasContent = collect($values)->except('extra')->filter(fn ($v) => trim((string) $v) !== '')->isNotEmpty()
                || collect($extra['items'] ?? [])->flatten()->filter(fn ($v) => trim((string) $v) !== '')->isNotEmpty()
                || ! empty($extra['eyebrow']) || ! empty($extra['button2_text']);

            if ($hasContent) {
                $section->translations()->updateOrCreate(['language_code' => $lang], $values);
            } else {
                // Empty language: drop the row so the page falls back to English.
                $section->translations()->where('language_code', $lang)->delete();
            }
        }
    }

    private function saveMedia(AboutSection $section, array $media): void
    {
        $existing = $section->media()->get()->keyBy('id');
        $keep = [];

        foreach ($media as $position => $m) {
            $attributes = [
                'kind' => $m['kind'],
                'path' => $m['path'] ?? null,
                'url' => ($m['kind'] === 'video' && empty($m['path'])) ? AboutPageService::embedUrl($m['url'] ?? '') : null,
                'sort_order' => $position,
            ];
            if (empty($attributes['path']) && empty($attributes['url'])) {
                continue;
            }

            $item = (! empty($m['id']) && $existing->has((int) $m['id'])) ? $existing->get((int) $m['id']) : new AboutMedia(['about_section_id' => $section->id]);
            $item->fill($attributes + ['about_section_id' => $section->id])->save();
            $keep[] = $item->id;

            foreach ((array) ($m['tr'] ?? []) as $lang => $t) {
                $alt = trim((string) ($t['alt'] ?? ''));
                $caption = trim((string) ($t['caption'] ?? ''));
                if ($alt === '' && $caption === '') {
                    $item->translations()->where('language_code', $lang)->delete();
                } else {
                    $item->translations()->updateOrCreate(['language_code' => $lang], ['alt' => $alt ?: null, 'caption' => $caption ?: null]);
                }
            }
        }

        // Removed media: rows only; the uploaded files are kept.
        $existing->except($keep)->each->delete();
    }
}
