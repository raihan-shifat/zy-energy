@extends('admin.layouts.admin')
@section('content')
<div class="card mt-4">
    <div class="card-header card-header-bg text-white">
        <h6 class="d-flex align-items-center mb-0 dt-heading">{{ __('cms.certifications.edit') }}</h6>
    </div>

    <div class="card-body">
        <form action="{{ route('admin.certifications.update', $certification->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

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
                        @php
                            $translation = $certification->translations->firstWhere('language_code', $language->code);
                        @endphp
                        <div class="tab-pane fade show {{ $loop->first ? 'active' : '' }}" id="{{ $language->name }}" role="tabpanel">
                            <label class="form-label">{{ __('cms.certifications.name') }} ({{ $language->code }})</label>
                            <input type="text"
                                   name="translations[{{ $language->code }}][name]"
                                   class="form-control"
                                   value="{{ old("translations.{$language->code}.name", $translation->name ?? '') }}">
                        </div>
                    @endforeach
                </div>

                <div class="col-md-6 mt-3">
                    <div class="form-group">
                        <label for="image_file">{{ __('cms.certifications.logo') }}</label>
                        <label class="btn btn-primary" for="image_file">{{ __('cms.certifications.choose_file') }}</label>
                        <input type="file" name="image_url" accept="image/*" class="form-control d-none" id="image_file">
                        @if($certification->image_url)
                            <div class="mt-2">
                                <img src="{{ asset('storage/' . $certification->image_url) }}" class="img-thumbnail" width="100">
                            </div>
                        @endif
                    </div>
                </div>

                <div class="col-md-6 mt-3">
                    <div class="form-group">
                        <label for="document_file">{{ __('cms.certifications.document') }}</label>
                        <label class="btn btn-primary" for="document_file">{{ __('cms.certifications.choose_file') }}</label>
                        <input type="file" name="document_url" accept=".pdf,.doc,.docx" class="form-control d-none" id="document_file">
                        @if($certification->document_url)
                            <div class="mt-2">
                                <a href="{{ asset('storage/' . $certification->document_url) }}" target="_blank" class="btn btn-sm btn-outline-primary">PDF</a>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="col-md-6 mt-3">
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" name="status" id="status" {{ $certification->status ? 'checked' : '' }}>
                        <label class="form-check-label" for="status">{{ __('cms.certifications.status') }}</label>
                    </div>
                </div>
            </div>

            <button type="submit" class="mt-3 btn btn-primary">{{ __('cms.certifications.update') }}</button>
        </form>
    </div>
</div>
@endsection
