{{-- One stats/cards item row: shared value (number or icon) + texts per language.
     Uses $name, $type, $languages, $trs, $k (key; '__K__' in the template), $item. --}}
@php
    $item = ($blank ?? false) ? [] : ($items[$k] ?? []);
    $itemTr = fn ($lang, $f) => ($blank ?? false) ? '' : data_get(optional($trs->get($lang))->extra, "items.$k.$f");
@endphp
<div class="ab-ed-item border rounded p-2 mb-2 d-flex gap-2 align-items-start">
    <div style="width: 11rem">
        @if ($type === 'stats')
            <label class="form-label small mb-1">{{ __('cms.about_editor.fields.number') }}</label>
            <input type="text" class="form-control form-control-sm" maxlength="20" placeholder="15+" name="{{ $name }}[items][{{ $k }}][number]" value="{{ $item['number'] ?? '' }}">
        @else
            <label class="form-label small mb-1">{{ __('cms.about_editor.fields.icon') }}</label>
            <input type="text" class="form-control form-control-sm" maxlength="80" placeholder="fa-solid fa-industry" title="{{ __('cms.about_editor.icon_hint') }}" name="{{ $name }}[items][{{ $k }}][icon]" value="{{ $item['icon'] ?? '' }}">
        @endif
    </div>
    <div class="flex-grow-1">
        @foreach ($languages as $language)
            <div class="ab-ed-lang" data-lang="{{ $language->code }}">
                @if ($type === 'stats')
                    <label class="form-label small mb-1">{{ __('cms.about_editor.fields.label') }} ({{ $language->code }})</label>
                    <input type="text" class="form-control form-control-sm" maxlength="500" name="{{ $name }}[tr][{{ $language->code }}][items][{{ $k }}][label]" value="{{ $itemTr($language->code, 'label') }}">
                @else
                    <label class="form-label small mb-1">{{ __('cms.about_editor.fields.item_title') }} ({{ $language->code }})</label>
                    <input type="text" class="form-control form-control-sm mb-1" maxlength="500" name="{{ $name }}[tr][{{ $language->code }}][items][{{ $k }}][title]" value="{{ $itemTr($language->code, 'title') }}">
                    <label class="form-label small mb-1">{{ __('cms.about_editor.fields.item_text') }} ({{ $language->code }})</label>
                    <textarea class="form-control form-control-sm" rows="2" maxlength="500" name="{{ $name }}[tr][{{ $language->code }}][items][{{ $k }}][text]">{{ $itemTr($language->code, 'text') }}</textarea>
                @endif
            </div>
        @endforeach
    </div>
    <button type="button" class="btn btn-sm btn-outline-danger mt-4" data-ab-remove-item aria-label="{{ __('cms.about_editor.remove_item') }}" title="{{ __('cms.about_editor.remove_item') }}"><i class="fas fa-times"></i></button>
</div>
