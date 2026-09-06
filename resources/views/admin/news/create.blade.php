@extends('admin.layouts.admin')
@section('content')
<div class="card mt-4">
    <div class="card-header card-header-bg text-white">
        <h6 class="d-flex align-items-center mb-0 dt-heading">{{ __('cms.news.add_new') }}</h6>
    </div>

    <div class="card-body">
        <form action="{{ route('admin.news.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- Validation errors must be visible, not silent --}}
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

            <div class="row">
                <ul class="nav nav-tabs" id="languageTabs" role="tablist">
                    @foreach($activeLanguages as $language)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $loop->first ? 'active' : '' }}"
                                    id="{{ $language->name }}-tab"
                                    data-bs-toggle="tab"
                                    data-bs-target="#{{ $language->name }}"
                                    type="button"
                                    role="tab">
                                {{ ucwords($language->name) }}
                            </button>
                        </li>
                    @endforeach
                </ul>

                <div class="tab-content mt-3" id="languageTabContent">
                    @foreach($activeLanguages as $language)
                        <div class="tab-pane fade show {{ $loop->first ? 'active' : '' }}" id="{{ $language->name }}" role="tabpanel">
                            <label class="form-label">{{ __('cms.news.title') }} ({{ $language->code }})</label>
                            <input type="text"
                                   name="translations[{{ $language->code }}][title]"
                                   class="form-control"
                                   value="{{ old("translations.{$language->code}.title") }}">

                            <label class="form-label mt-3">{{ __('cms.news.excerpt') }} ({{ $language->code }})</label>
                            <textarea name="translations[{{ $language->code }}][excerpt]" rows="2" class="form-control @error("translations.{$language->code}.excerpt") is-invalid @enderror">{{ old("translations.{$language->code}.excerpt") }}</textarea>
                            @error("translations.{$language->code}.excerpt")
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror

                            <label class="form-label mt-3">{{ __('cms.news.body') }} ({{ $language->code }})</label>
                            <textarea name="translations[{{ $language->code }}][body]" class="form-control ck-editor-multi-languages" rows="6">{{ old("translations.{$language->code}.body") }}</textarea>
                        </div>
                    @endforeach
                </div>

                <div class="col-md-6 mt-3">
                    <div class="form-group">
                        <label for="image_file">{{ __('cms.news.image') }}</label>
                        <label class="btn btn-primary" for="image_file">{{ __('cms.news.choose_file') }}</label>
                        <input type="file" name="image_url" accept="image/*" class="form-control d-none" id="image_file">
                        <div class="mt-2 d-none" id="image_preview">
                            <img id="image_preview_img" class="img-thumbnail" width="100">
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mt-3">
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" name="status" id="status" checked>
                        <label class="form-check-label" for="status">{{ __('cms.news.status') }}</label>
                    </div>
                </div>
            </div>

            <button type="submit" class="mt-3 btn btn-primary">{{ __('cms.news.create') }}</button>
        </form>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>
<script>
document.querySelectorAll('.ck-editor-multi-languages').forEach((element) => {
    ClassicEditor.create(element).catch(error => console.error(error));
});
document.getElementById('image_file').addEventListener('change', function(event) {
    var file = event.target.files[0];
    var previewElement = document.getElementById('image_preview');
    var previewImage = document.getElementById('image_preview_img');
    if (file) {
        var reader = new FileReader();
        reader.onload = function(e) {
            previewElement.classList.remove('d-none');
            previewImage.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
});
</script>
<script>
    {{-- Excerpt character counter: shows usage vs the 500-char validation limit --}}
    document.addEventListener('DOMContentLoaded', function () {
        var EXCERPT_LIMIT = 500;
        document.querySelectorAll('textarea[name*=""[excerpt]""]'.replace(/""/g, '"')).forEach(function (ta) {
            var counter = document.createElement('small');
            counter.className = 'excerpt-counter d-block mt-1 text-muted';
            ta.insertAdjacentElement('afterend', counter);

            function update() {
                var len = ta.value.length;
                counter.textContent = len + ' / ' + EXCERPT_LIMIT + ' characters';
                counter.classList.toggle('text-danger', len > EXCERPT_LIMIT);
            }
            ta.addEventListener('input', update);
            update();
        });
    });
</script>
@endsection
