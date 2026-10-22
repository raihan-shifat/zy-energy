{{-- One gallery image row: thumbnail, order buttons, remove, alt + caption per language.
     Uses $name, $languages, $mediaTr, $j (key; '__J__' in the template), $m (AboutMedia or null). --}}
<div class="ab-ed-gitem border rounded p-2 mb-2 d-flex gap-3 align-items-start">
    <input type="hidden" name="{{ $name }}[media][{{ $j }}][id]" value="{{ $m?->id }}">
    <input type="hidden" name="{{ $name }}[media][{{ $j }}][kind]" value="image">
    <input type="hidden" class="ab-ed-path" name="{{ $name }}[media][{{ $j }}][path]" value="{{ $m?->path }}">
    <div class="text-center">
        <img class="ab-ed-thumb" src="{{ $m ? $m->publicUrl() : '' }}" alt="">
        <div class="ab-ed-progress small text-muted mt-1"></div>
        <div class="btn-group btn-group-sm mt-1">
            <button type="button" class="btn btn-outline-secondary" data-ab-gmove="-1" aria-label="{{ __('cms.about_editor.move_up') }}"><i class="fas fa-arrow-up"></i></button>
            <button type="button" class="btn btn-outline-secondary" data-ab-gmove="1" aria-label="{{ __('cms.about_editor.move_down') }}"><i class="fas fa-arrow-down"></i></button>
            <button type="button" class="btn btn-outline-danger" data-ab-gremove aria-label="{{ __('cms.about_editor.remove_media') }}"><i class="fas fa-times"></i></button>
        </div>
    </div>
    <div class="flex-grow-1">
        @foreach ($languages as $language)
            <div class="ab-ed-lang" data-lang="{{ $language->code }}">
                <input type="text" class="form-control form-control-sm mb-1" maxlength="255" placeholder="{{ __('cms.about_editor.fields.alt') }} ({{ $language->code }})" name="{{ $name }}[media][{{ $j }}][tr][{{ $language->code }}][alt]" value="{{ $mediaTr($m, $language->code, 'alt') }}">
                <input type="text" class="form-control form-control-sm" maxlength="500" placeholder="{{ __('cms.about_editor.fields.caption') }} ({{ $language->code }})" name="{{ $name }}[media][{{ $j }}][tr][{{ $language->code }}][caption]" value="{{ $mediaTr($m, $language->code, 'caption') }}">
            </div>
        @endforeach
    </div>
</div>
