@extends('themes.xylo.layouts.master')

@section('title', page_title(__('legal.cookies.title')))
@section('meta_description', __('legal.cookies.meta_description', ['site' => $siteName]))
{{-- Draft text: keep out of search results until it has been reviewed. Remove this line when approved. --}}
@section('robots', 'noindex, follow')

@section('content')
@php
    $privacyLink = '<a href="/privacy-policy">'.e(__('legal.common.privacy_link')).'</a>';
    $emailHtml = $contactEmail
        ? '<a href="mailto:'.e($contactEmail).'">'.e($contactEmail).'</a>'
        : e(__('legal.common.contact_email_placeholder'));
@endphp
<div class="container py-5" style="max-width: 860px;">
    <h1 class="mb-4">{{ __('legal.cookies.title') }}</h1>

    @include('themes.xylo.legal._draft-notice')

    <p>{{ __('legal.cookies.intro') }}</p>

    <h2 class="h4 mt-5">{{ __('legal.cookies.collect_heading') }}</h2>
    <p>{{ __('legal.cookies.collect_intro') }}</p>
    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead>
                <tr>
                    <th scope="col">{{ __('legal.cookies.col_cookie') }}</th>
                    <th scope="col">{{ __('legal.cookies.col_purpose') }}</th>
                    <th scope="col">{{ __('legal.cookies.col_duration') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>zy_energy_session</code></td>
                    <td>{{ __('legal.cookies.session_purpose') }}</td>
                    <td>{{ __('legal.cookies.duration_2_hours') }}</td>
                </tr>
                <tr>
                    <td><code>XSRF-TOKEN</code></td>
                    <td>{{ __('legal.cookies.xsrf_purpose') }}</td>
                    <td>{{ __('legal.cookies.duration_2_hours') }}</td>
                </tr>
                <tr>
                    <td><code>locale</code></td>
                    <td>{{ __('legal.cookies.locale_purpose') }}</td>
                    <td>{{ __('legal.cookies.duration_1_year') }}</td>
                </tr>
                @if (analytics_config()['enabled'])
                    <tr>
                        <td><code>_ga</code>, <code>_ga_*</code></td>
                        <td>{!! __('legal.cookies.ga_purpose') !!}</td>
                        <td>{{ __('legal.cookies.duration_ga') }}</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
    @if (analytics_config()['enabled'])
        <p>{!! __('legal.cookies.analytics_text', ['key' => '<code>zy_cookie_consent</code>']) !!}</p>
        <p><button type="button" class="btn btn-outline-primary btn-sm" data-cookie-settings>{{ __('store.consent.change') }}</button></p>
    @else
        <p>{{ __('legal.cookies.no_analytics_text') }}</p>
    @endif

    <h2 class="h4 mt-5">{{ __('legal.common.how_we_use_it') }}</h2>
    <p>{{ __('legal.cookies.use_text') }}</p>

    <h2 class="h4 mt-5">{{ __('legal.cookies.third_party_heading') }}</h2>
    <p>{{ __('legal.cookies.third_party_text') }}</p>

    <h2 class="h4 mt-5">{{ __('legal.common.cookies') }}</h2>
    <p>{{ __('legal.cookies.manage_text') }}</p>

    <h2 class="h4 mt-5">{{ __('legal.common.contact') }}</h2>
    <p>{!! __('legal.cookies.contact_text', ['email' => $emailHtml, 'link' => $privacyLink]) !!}</p>

    <h2 class="h4 mt-5">{{ __('legal.common.last_updated') }}</h2>
    <p>{{ __('legal.common.draft_prepared', ['date' => $draftDate]) }}</p>
</div>
@endsection
