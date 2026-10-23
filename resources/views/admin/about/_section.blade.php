{{-- One About Us section in the admin editor.
     $section: AboutSection or null (new); $type; $index: array key ('__I__' in templates); $languages.
     Translated fields sit in .ab-ed-lang blocks (one per language); the editor shows only the selected language. --}}
@php
    $settings = $section?->settings ?? [];
    $trs = $section ? $section->translations->keyBy('language_code') : collect();
    $items = $settings['items'] ?? [];
    $media = $section ? $section->media->values() : collect();
    $name = "sections[$index]";
    $isActive = $section ? $section->is_active : true;
    $preview = optional($trs->get('en'))->title ?: optional($trs->first())->title;
    $rich = in_array($type, ['text', 'image_text'], true);
    $fields = [
        'hero' => ['eyebrow', 'title', 'subtitle'],
        'text' => ['title', 'body'],
        'image_text' => ['title', 'body'],
        'stats' => ['title', 'subtitle'],
        'cards' => ['title', 'subtitle'],
        'gallery' => ['title', 'subtitle'],
        'video' => ['title', 'subtitle'],
        'cta' => ['title', 'subtitle', 'button_text', 'button2_text'],
    ][$type];
    $imageHint = __('cms.about_editor.image_hint', ['size' => \App\Http\Controllers\Admin\EditorImageController::humanLimit(\App\Http\Controllers\Admin\EditorImageController::ABOUT_IMAGE_MAX_BYTES)]);
    $mediaTr = fn ($m, $lang, $f) => optional($m?->translations->firstWhere('language_code', $lang))->{$f};
@endphp
<div class="card mb-3 ab-ed-section" data-type="{{ $type }}">
    <div class="card-header d-flex flex-wrap align-items-center gap-2 bg-light" draggable="true">
        <span class="ab-ed-handle text-muted" title="{{ __('cms.about_editor.move_up') }} / {{ __('cms.about_editor.move_down') }}"><i class="fas fa-grip-vertical"></i></span>
        <strong>{{ __('cms.about_editor.types.'.$type) }}</strong>
        <span class="text-muted small ab-ed-preview">{{ $preview ?: __('cms.about_editor.no_title') }}</span>
        <span class="badge bg-secondary ab-ed-hidden-badge" @if ($isActive) hidden @endif>{{ __('cms.about_editor.hidden_badge') }}</span>
        <div class="ms-auto d-flex flex-wrap gap-1 align-items-center">
            <div class="form-check form-switch m-0 me-2">
                <input type="hidden" name="{{ $name }}[is_active]" value="0">
                <input class="form-check-input ab-ed-active" type="checkbox" name="{{ $name }}[is_active]" value="1" id="active-{{ $index }}" @checked($isActive)>
                <label class="form-check-label small" for="active-{{ $index }}">{{ __('cms.about_editor.active') }}</label>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-ab-move="-1" aria-label="{{ __('cms.about_editor.move_up') }}" title="{{ __('cms.about_editor.move_up') }}"><i class="fas fa-arrow-up"></i></button>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-ab-move="1" aria-label="{{ __('cms.about_editor.move_down') }}" title="{{ __('cms.about_editor.move_down') }}"><i class="fas fa-arrow-down"></i></button>
            <button type="button" class="btn btn-sm btn-outline-primary" data-ab-toggle aria-expanded="{{ $section ? 'false' : 'true' }}" aria-label="{{ __('cms.about_editor.toggle') }}" title="{{ __('cms.about_editor.toggle') }}"><i class="fas fa-pen"></i></button>
            <button type="button" class="btn btn-sm btn-outline-danger" data-ab-remove aria-label="{{ __('cms.about_editor.remove') }}" title="{{ __('cms.about_editor.remove') }}"><i class="fas fa-trash"></i></button>
        </div>
    </div>

    <div class="card-body ab-ed-body" @if ($section) hidden @endif>
        <input type="hidden" name="{{ $name }}[id]" value="{{ $section?->id }}">
        <input type="hidden" name="{{ $name }}[type]" value="{{ $type }}">

        {{-- Language-independent settings --}}
        <div class="row g-3 mb-2">
            @if ($type === 'text')
                <div class="col-md-4">
                    <label class="form-label small">{{ __('cms.about_editor.fields.align') }}</label>
                    <select class="form-select form-select-sm" name="{{ $name }}[settings][align]">
                        <option value="center" @selected(($settings['align'] ?? 'center') === 'center')>{{ __('cms.about_editor.align_center') }}</option>
                        <option value="left" @selected(($settings['align'] ?? '') === 'left')>{{ __('cms.about_editor.align_left') }}</option>
                    </select>
                </div>
            @elseif ($type === 'image_text')
                <div class="col-md-4">
                    <label class="form-label small">{{ __('cms.about_editor.fields.position') }}</label>
                    <select class="form-select form-select-sm" name="{{ $name }}[settings][position]">
                        <option value="left" @selected(($settings['position'] ?? 'left') === 'left')>{{ __('cms.about_editor.position_left') }}</option>
                        <option value="right" @selected(($settings['position'] ?? '') === 'right')>{{ __('cms.about_editor.position_right') }}</option>
                    </select>
                </div>
            @elseif ($type === 'cards')
                <div class="col-md-4">
                    <label class="form-label small">{{ __('cms.about_editor.fields.variant') }}</label>
                    <select class="form-select form-select-sm" name="{{ $name }}[settings][variant]">
                        <option value="feature" @selected(($settings['variant'] ?? 'feature') === 'feature')>{{ __('cms.about_editor.variant_feature') }}</option>
                        <option value="plain" @selected(($settings['variant'] ?? '') === 'plain')>{{ __('cms.about_editor.variant_plain') }}</option>
                    </select>
                </div>
            @elseif ($type === 'cta')
                @foreach (['button_link', 'button2_link'] as $link)
                    <div class="col-md-6">
                        <label class="form-label small">{{ __('cms.about_editor.fields.'.$link) }}</label>
                        <input type="text" class="form-control form-control-sm" name="{{ $name }}[settings][{{ $link }}]" value="{{ $settings[$link] ?? '' }}" placeholder="/contact" maxlength="500">
                        <div class="form-text">{{ __('cms.about_editor.link_hint') }}</div>
                    </div>
                @endforeach
            @endif
        </div>

        {{-- Translated texts --}}
        @foreach ($languages as $language)
            @php $t = $trs->get($language->code); @endphp
            <div class="ab-ed-lang" data-lang="{{ $language->code }}">
                @foreach ($fields as $field)
                    @php
                        $value = in_array($field, ['eyebrow', 'button2_text'], true) ? data_get($t?->extra, $field) : $t?->{$field};
                        $fieldName = "{$name}[tr][{$language->code}][{$field}]";
                    @endphp
                    <div class="mb-2">
                        <label class="form-label small mb-1">{{ __('cms.about_editor.fields.'.$field) }} ({{ $language->code }})</label>
                        @if ($field === 'body')
                            <textarea class="form-control {{ $rich ? 'ab-ed-rich' : '' }}" rows="5" name="{{ $fieldName }}">{{ $value }}</textarea>
                        @elseif ($field === 'subtitle')
                            <textarea class="form-control form-control-sm" rows="2" maxlength="2000" name="{{ $fieldName }}">{{ $value }}</textarea>
                        @else
                            <input type="text" class="form-control form-control-sm {{ $field === 'title' ? 'ab-ed-title' : '' }}" maxlength="{{ $field === 'title' ? 255 : 100 }}" name="{{ $fieldName }}" value="{{ $value }}">
                        @endif
                    </div>
                @endforeach
            </div>
        @endforeach

        {{-- Repeatable items (stats / cards): shared value + per-language texts in one row --}}
        @if (in_array($type, ['stats', 'cards'], true))
            <label class="form-label fw-semibold mt-2">{{ __('cms.about_editor.items') }}</label>
            <div class="ab-ed-items" data-max="{{ \App\Http\Controllers\Admin\AboutPageController::MAX_ITEMS }}">
                @foreach ($items as $k => $item)
                    @include('admin.about._item', ['k' => $k])
                @endforeach
            </div>
            <template class="ab-ed-item-tpl">@include('admin.about._item', ['k' => '__K__', 'item' => [], 'blank' => true])</template>
            <button type="button" class="btn btn-sm btn-outline-primary" data-ab-add-item><i class="fas fa-plus me-1"></i>{{ __('cms.about_editor.add_item') }}</button>
        @endif

        {{-- Media --}}
        @if (in_array($type, ['hero', 'image_text'], true))
            @php $img = $media->firstWhere('kind', 'image'); @endphp
            <div class="ab-ed-media-single mt-3" data-kind="image">
                <label class="form-label fw-semibold">{{ $type === 'hero' ? __('cms.about_editor.hero_image') : __('cms.about_editor.image') }}</label>
                <div class="form-text mb-1">{{ $imageHint }}</div>
                <input type="hidden" name="{{ $name }}[media][0][id]" value="{{ $img?->id }}">
                <input type="hidden" name="{{ $name }}[media][0][kind]" value="image">
                <input type="hidden" class="ab-ed-path" name="{{ $name }}[media][0][path]" value="{{ $img?->path }}">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <img class="ab-ed-thumb" src="{{ $img ? $img->publicUrl() : '' }}" alt="" @if (! $img) hidden @endif>
                    <button type="button" class="btn btn-sm btn-primary" data-ab-upload="image">{{ __('cms.about_editor.choose_image') }}</button>
                    <button type="button" class="btn btn-sm btn-outline-danger" data-ab-clear @if (! $img) hidden @endif>{{ __('cms.about_editor.remove_media') }}</button>
                    <span class="ab-ed-progress small text-muted"></span>
                </div>
                @foreach ($languages as $language)
                    <div class="ab-ed-lang mt-2" data-lang="{{ $language->code }}">
                        <input type="text" class="form-control form-control-sm" maxlength="255" placeholder="{{ __('cms.about_editor.fields.alt') }} ({{ $language->code }})" name="{{ $name }}[media][0][tr][{{ $language->code }}][alt]" value="{{ $mediaTr($img, $language->code, 'alt') }}">
                    </div>
                @endforeach
            </div>
        @elseif ($type === 'gallery')
            <label class="form-label fw-semibold mt-3 d-block">{{ __('cms.about_editor.gallery_images') }}</label>
            <div class="form-text mb-2">{{ __('cms.about_editor.gallery_hint', ['max' => \App\Http\Controllers\Admin\AboutPageController::MAX_GALLERY, 'size' => \App\Http\Controllers\Admin\EditorImageController::humanLimit(\App\Http\Controllers\Admin\EditorImageController::ABOUT_IMAGE_MAX_BYTES)]) }}</div>
            <div class="ab-ed-gallery" data-max="{{ \App\Http\Controllers\Admin\AboutPageController::MAX_GALLERY }}">
                @foreach ($media->where('kind', 'image') as $j => $m)
                    @include('admin.about._gallery-item', ['j' => 'm'.$m->id, 'm' => $m])
                @endforeach
            </div>
            <template class="ab-ed-gallery-tpl">@include('admin.about._gallery-item', ['j' => '__J__', 'm' => null])</template>
            <button type="button" class="btn btn-sm btn-primary mt-2" data-ab-upload="gallery">{{ __('cms.about_editor.choose_images') }}</button>
        @elseif ($type === 'video')
            @php $video = $media->firstWhere('kind', 'video'); $poster = $media->firstWhere('kind', 'poster'); @endphp
            <div class="mt-3">
                <label class="form-label fw-semibold">{{ __('cms.about_editor.video_file') }}</label>
                <div class="form-text mb-1">{{ __('cms.about_editor.video_hint', ['size' => \App\Http\Controllers\Admin\EditorImageController::humanLimit(\App\Http\Controllers\Admin\EditorImageController::ABOUT_VIDEO_MAX_BYTES)]) }}</div>
                <div class="ab-ed-media-single" data-kind="video">
                    <input type="hidden" name="{{ $name }}[media][0][id]" value="{{ $video?->id }}">
                    <input type="hidden" name="{{ $name }}[media][0][kind]" value="video">
                    <input type="hidden" class="ab-ed-path" name="{{ $name }}[media][0][path]" value="{{ $video?->path }}">
                    <input type="url" class="form-control form-control-sm mb-2 ab-ed-video-url" name="{{ $name }}[media][0][url]" value="{{ $video?->path ? '' : $video?->url }}" placeholder="https://www.youtube.com/watch?v=…" maxlength="500" aria-label="{{ __('cms.about_editor.fields.video_url') }}">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="ab-ed-file small text-muted">{{ $video?->path ? basename($video->path) : '' }}</span>
                        <button type="button" class="btn btn-sm btn-primary" data-ab-upload="video">{{ __('cms.about_editor.choose_video') }}</button>
                        <button type="button" class="btn btn-sm btn-outline-danger" data-ab-clear @if (! $video?->path) hidden @endif>{{ __('cms.about_editor.remove_media') }}</button>
                        <span class="ab-ed-progress small text-muted"></span>
                    </div>
                </div>
                <div class="ab-ed-media-single mt-3" data-kind="poster">
                    <label class="form-label small">{{ __('cms.about_editor.poster') }}</label>
                    <input type="hidden" name="{{ $name }}[media][1][id]" value="{{ $poster?->id }}">
                    <input type="hidden" name="{{ $name }}[media][1][kind]" value="poster">
                    <input type="hidden" class="ab-ed-path" name="{{ $name }}[media][1][path]" value="{{ $poster?->path }}">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <img class="ab-ed-thumb" src="{{ $poster ? $poster->publicUrl() : '' }}" alt="" @if (! $poster) hidden @endif>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-ab-upload="image">{{ __('cms.about_editor.choose_poster') }}</button>
                        <button type="button" class="btn btn-sm btn-outline-danger" data-ab-clear @if (! $poster) hidden @endif>{{ __('cms.about_editor.remove_media') }}</button>
                        <span class="ab-ed-progress small text-muted"></span>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
