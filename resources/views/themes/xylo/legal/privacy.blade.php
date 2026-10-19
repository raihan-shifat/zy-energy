@extends('themes.xylo.layouts.master')

@section('title', page_title(__('legal.privacy.title')))
@section('meta_description', __('legal.privacy.meta_description', ['site' => $siteName]))
{{-- Draft text: keep out of search results until it has been reviewed. Remove this line when approved. --}}
@section('robots', 'noindex, follow')

@section('content')
@php
    $cookieLink = '<a href="/cookie-policy">'.e(__('legal.common.cookie_link')).'</a>';
    $emailHtml = $contactEmail
        ? '<a href="mailto:'.e($contactEmail).'">'.e($contactEmail).'</a>'
        : e(__('legal.common.contact_email_placeholder'));
@endphp
<div class="container py-5" style="max-width: 860px;">
    <h1 class="mb-4">{{ __('legal.privacy.title') }}</h1>

    @include('themes.xylo.legal._draft-notice')

    <p>{{ __('legal.privacy.intro', ['site' => $siteName]) }}</p>

    <h2 class="h4 mt-5">{{ __('legal.common.data_we_collect') }}</h2>
    <p>{{ __('legal.privacy.collect_intro') }}</p>
    <ul>
        <li>{!! __('legal.privacy.collect_enquiry') !!}</li>
        <li>{!! __('legal.privacy.collect_account') !!}</li>
        <li>{!! __('legal.privacy.collect_visits') !!}</li>
        <li>{!! __('legal.privacy.collect_cookies', ['link' => $cookieLink]) !!}</li>
    </ul>
    <p>{{ __('legal.privacy.collect_other') }}</p>

    <h2 class="h4 mt-5">{{ __('legal.common.how_we_use_it') }}</h2>
    <ul>
        <li>{{ __('legal.privacy.use_enquiry') }}</li>
        <li>{{ __('legal.privacy.use_security') }}</li>
        <li>{{ __('legal.privacy.use_other') }}</li>
    </ul>
    <p>{{ __('legal.privacy.use_basis') }}</p>

    <h2 class="h4 mt-5">{{ __('legal.common.cookies') }}</h2>
    @if (analytics_config()['enabled'])
        <p>{!! __('legal.privacy.cookies_analytics', ['link' => $cookieLink]) !!}</p>
    @else
        <p>{!! __('legal.privacy.cookies_essential', ['link' => $cookieLink]) !!}</p>
    @endif

    <h2 class="h4 mt-5">{{ __('legal.privacy.rights_heading') }}</h2>
    <p>{{ __('legal.privacy.rights_text') }}</p>

    <h2 class="h4 mt-5">{{ __('legal.common.contact') }}</h2>
    <p>
        {!! __('legal.privacy.contact_text', ['email' => $emailHtml]) !!}
        @if ($contactAddress)
            <br>{{ __('legal.common.address', ['address' => $contactAddress]) }}
        @endif
    </p>

    <h2 class="h4 mt-5">{{ __('legal.common.last_updated') }}</h2>
    <p>{{ __('legal.common.draft_prepared', ['date' => $draftDate]) }}</p>
</div>
@endsection
