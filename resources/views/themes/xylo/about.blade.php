@extends('themes.xylo.layouts.master')

@section('title', $translation->title ?? __('store.footer.about'))

@section('content')

{{-- Hero band --}}
<section class="ab-hero">
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-lg-9">
                <span class="ab-eyebrow">{{ __('store.about_page.eyebrow') }}</span>
                <h1 class="ab-hero-title">
                    {{ $translation->title ?? __('store.about_page.title') }}
                </h1>
                <p class="ab-hero-lead">
                    {{ __('store.about_page.narrative_lead') }}
                </p>
            </div>
        </div>
    </div>
</section>

{{-- Company narrative --}}
<section class="ab-narrative">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9 text-center">
                <h2 class="sec-heading mb-4">{{ __('store.about_page.title') }}</h2>
                <p class="ab-narrative-text">{{ __('store.about_page.narrative') }}</p>
            </div>
        </div>
    </div>
</section>

{{-- Mission / Vision / Values --}}
<section class="ab-section">
    <div class="container">
        <div class="row g-4">
            <div class="col-12 col-md-6 col-lg-4">
                <div class="ab-card h-100">
                    <div class="ab-card-icon"><i class="fa-solid fa-bullseye"></i></div>
                    <h3 class="ab-card-title">{{ __('store.about_page.mission_title') }}</h3>
                    <p>{{ __('store.about_page.mission_text') }}</p>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="ab-card h-100">
                    <div class="ab-card-icon"><i class="fa-solid fa-eye"></i></div>
                    <h3 class="ab-card-title">{{ __('store.about_page.vision_title') }}</h3>
                    <p>{{ __('store.about_page.vision_text') }}</p>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="ab-card h-100">
                    <div class="ab-card-icon"><i class="fa-solid fa-heart"></i></div>
                    <h3 class="ab-card-title">{{ __('store.about_page.values_title') }}</h3>
                    <ul class="ab-values-list">
                        @foreach (explode('|', __('store.about_page.values_list')) as $value)
                            <li><i class="fa-solid fa-check me-2"></i>{{ $value }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Strengths grid --}}
<section class="ab-strengths">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="sec-heading">{{ __('store.about_page.strengths_heading') }}</h2>
            <p class="text-muted mx-auto ab-subheading">{{ __('store.about_page.strengths_subheading') }}</p>
        </div>
        <div class="row g-4">
            @for ($i = 1; $i <= 6; $i++)
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="ab-strength-card h-100">
                        <div class="ab-strength-icon"><i class="{{ __('store.about_page.strength_' . $i . '_icon') }}"></i></div>
                        <h3 class="ab-strength-title">{{ __('store.about_page.strength_' . $i . '_title') }}</h3>
                        <p class="mb-0">{{ __('store.about_page.strength_' . $i . '_text') }}</p>
                    </div>
                </div>
            @endfor
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="ab-cta">
    <div class="container text-center">
        <h2 class="ab-cta-title">{{ __('store.about_page.cta_title') }}</h2>
        <p class="ab-cta-text">{{ __('store.about_page.cta_text') }}</p>
        <div class="d-flex flex-wrap justify-content-center gap-3">
            <a href="{{ route('contact.index') }}" class="btn btn-light btn-lg">
                <i class="fa-regular fa-envelope me-2"></i>{{ __('store.about_page.cta_contact') }}
            </a>
            <a href="{{ route('shop.index') }}" class="btn btn-outline-light btn-lg">
                <i class="fa-solid fa-bolt me-2"></i>{{ __('store.about_page.cta_quote') }}
            </a>
        </div>
    </div>
</section>

@endsection
