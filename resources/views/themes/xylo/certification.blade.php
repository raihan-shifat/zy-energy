@extends('themes.xylo.layouts.master')

@section('title', __('store.certification.title'))

@section('content')
<div class="container py-5">
    <div class="text-center mb-5">
        <h1 class="sec-heading">{{ __('store.certification.title') }}</h1>
        <p class="text-muted">{{ __('store.certification.subtitle') }}</p>
    </div>

    <div class="row justify-content-center">
        @forelse ($certifications as $certification)
            @php
                    $certName = localized_translation_value($certification->translations, 'name', __('store.certification.title'));
            @endphp
            <div class="col-6 col-md-4 col-lg-3 mb-4">
                <div class="card text-center h-100 shadow-sm">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center">
                        @if ($certification->image_url)
                            <img src="{{ asset('storage/' . $certification->image_url) }}" alt="{{ $certName }}"
                                 class="mb-3" style="max-height:100px; max-width:140px; object-fit:contain;">
                        @else
                            <i class="fa-solid fa-certificate fa-3x text-muted mb-3"></i>
                        @endif
                        <h5 class="card-title">{{ $certName }}</h5>
                        @if ($certification->document_url)
                            <a href="{{ asset('storage/' . $certification->document_url) }}" target="_blank" class="btn btn-outline-primary btn-sm mt-2">
                                <i class="fa-solid fa-file-pdf"></i> {{ __('store.certification.view_document') }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <p class="text-muted text-center">{{ __('store.certification.title') }} — {{ __('store.news.no_posts') }}</p>
        @endforelse
    </div>
</div>
@endsection
