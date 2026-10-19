@extends('themes.xylo.layouts.master')

@section('title', page_title(__('legal.terms.title')))
@section('meta_description', __('legal.terms.meta_description', ['site' => $siteName]))
{{-- Draft text: keep out of search results until it has been reviewed. Remove this line when approved. --}}
@section('robots', 'noindex, follow')

@section('content')
@php
    $privacyLink = '<a href="/privacy-policy">'.e(__('legal.common.privacy_link')).'</a>';
    $cookieLink = '<a href="/cookie-policy">'.e(__('legal.common.cookie_link')).'</a>';
@endphp
<div class="container py-5" style="max-width: 860px;">
    <h1 class="mb-4">{{ __('legal.terms.title') }}</h1>

    @include('themes.xylo.legal._draft-notice')

    <p>{{ __('legal.terms.intro', ['site' => $siteName]) }}</p>

    <h2 class="h4 mt-5">{{ __('legal.terms.use_heading') }}</h2>
    <p>{{ __('legal.terms.use_text') }}</p>

    <h2 class="h4 mt-5">{{ __('legal.terms.products_heading') }}</h2>
    <p>{{ __('legal.terms.products_text') }}</p>

    <h2 class="h4 mt-5">{{ __('legal.common.data_we_collect') }}</h2>
    <p>{!! __('legal.terms.data_text', ['link' => $privacyLink]) !!}</p>

    <h2 class="h4 mt-5">{{ __('legal.common.cookies') }}</h2>
    <p>{!! __('legal.terms.cookies_text', ['link' => $cookieLink]) !!}</p>

    <h2 class="h4 mt-5">{{ __('legal.terms.ip_heading') }}</h2>
    <p>{{ __('legal.terms.ip_text') }}</p>

    <h2 class="h4 mt-5">{{ __('legal.terms.liability_heading') }}</h2>
    <p>{{ __('legal.terms.liability_text') }}</p>

    <h2 class="h4 mt-5">{{ __('legal.terms.law_heading') }}</h2>
    <p>{{ __('legal.terms.law_text') }}</p>

    <h2 class="h4 mt-5">{{ __('legal.common.contact') }}</h2>
    <p>
        @if ($contactEmail)
            <a href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a>
        @else
            {{ __('legal.common.contact_email_placeholder') }}
        @endif
        @if ($contactAddress)
            <br>{{ __('legal.common.address', ['address' => $contactAddress]) }}
        @endif
    </p>

    <h2 class="h4 mt-5">{{ __('legal.common.last_updated') }}</h2>
    <p>{{ __('legal.terms.draft_prepared', ['date' => $draftDate]) }}</p>
</div>
@endsection
