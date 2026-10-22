@extends('admin.layouts.admin')
@section('content')
<div class="card mt-4">
    <div class="card-header card-header-bg text-white">
        <h6 class="d-flex align-items-center mb-0 dt-heading">{{ __('cms.news.edit') }}</h6>
    </div>

    <div class="card-body">
        <form action="{{ route('admin.news.update', $news->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Please fix the following errors:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
            @method('PUT')

            <div class="row">
                {{-- Tabs are keyed by language code (names can contain spaces, which break the tab selector). --}}
                <ul class="nav nav-tabs" id="languageTabs" role="tablist">
                    @foreach($activeLanguages as $language)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $loop->first ? 'active' : '' }}"
                                    id="lang-{{ $language->code }}-tab"
                                    data-bs-toggle="tab"
                                    data-bs-target="#lang-{{ $language->code }}"
                                    type="button"
                                    role="tab">
                                {{ ucwords($language->name) }}
                            </button>
                        </li>
                    @endforeach
                </ul>

                <div class="tab-content mt-3" id="languageTabContent">
                    @foreach($activeLanguages as $language)
                        @php
                            $translation = $news->translations->firstWhere('language_code', $language->code);
                        @endphp
                        <div class="tab-pane fade show {{ $loop->first ? 'active' : '' }}" id="lang-{{ $language->code }}" role="tabpanel">
                            <label class="form-label">{{ __('cms.news.title') }} ({{ $language->code }})</label>
                            <input type="text"
                                   name="translations[{{ $language->code }}][title]"
                                   class="form-control @error("translations.{$language->code}.title") is-invalid @enderror"
                                   maxlength="255"
                                   value="{{ old("translations.{$language->code}.title", $translation->title ?? '') }}">
                            @error("translations.{$language->code}.title")
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror

                            <label class="form-label mt-3">{{ __('cms.news.excerpt') }} ({{ $language->code }})</label>
                            <textarea name="translations[{{ $language->code }}][excerpt]" rows="2" maxlength="500" class="form-control @error("translations.{$language->code}.excerpt") is-invalid @enderror">{{ old("translations.{$language->code}.excerpt", $translation->excerpt ?? '') }}</textarea>
                            @error("translations.{$language->code}.excerpt")
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror

                            <label class="form-label mt-3">{{ __('cms.news.body') }} ({{ $language->code }})</label>
                            <textarea name="translations[{{ $language->code }}][body]" class="form-control ck-editor-multi-languages" rows="6">{{ old("translations.{$language->code}.body", $translation->body ?? '') }}</textarea>
                            @error("translations.{$language->code}.body")
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    @endforeach
                </div>

                <div class="col-md-6 mt-3">
                    @include('admin.news._image-field', ['currentImage' => $news->coverPath()])
                </div>

                <div class="col-md-6 mt-3">
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" name="status" id="status" {{ old('_token') ? (old('status') ? 'checked' : '') : ($news->status ? 'checked' : '') }}>
                        <label class="form-check-label" for="status">{{ __('cms.news.status') }}</label>
                    </div>
                </div>
            </div>

            <button type="submit" class="mt-3 btn btn-primary">{{ __('cms.news.update') }}</button>
        </form>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>
{{-- Same upload pipeline as the product description editor. --}}
@include('admin.partials.ckeditor-upload-adapter')
<script>
document.querySelectorAll('.ck-editor-multi-languages').forEach((element) => {
    ClassicEditor.create(element, { extraPlugins: [ZyEditorUploadPlugin] }).catch(error => console.error(error));
});
</script>
@include('admin.news._form-scripts')
@endsection
