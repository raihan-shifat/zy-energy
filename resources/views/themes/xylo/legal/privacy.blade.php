@extends('themes.xylo.layouts.master')

@section('title', page_title('Privacy Policy'))
@section('meta_description', 'Privacy Policy of '.$siteName.' (draft pending legal review).')
{{-- Draft text: keep out of search results until it has been reviewed. Remove this line when approved. --}}
@section('robots', 'noindex, follow')

@section('content')
<div class="container py-5" style="max-width: 860px;">
    <h1 class="mb-4">Privacy Policy</h1>

    @include('themes.xylo.legal._draft-notice')

    <p>
        This policy explains how {{ $siteName }} ("we") handles personal data collected through this website.
        Operator: [company legal name], [registered address], [registration number].
    </p>

    <h2 class="h4 mt-5">Data we collect</h2>
    <p>Based on how this website currently works, we collect:</p>
    <ul>
        <li><strong>Enquiry form:</strong> name, company, email address, phone / WhatsApp number, country,
            the product or category you ask about, your message, and the page the form was sent from.</li>
        <li><strong>Customer account</strong> (only if you register): the details you enter in your profile.</li>
        <li><strong>Visit log:</strong> for each visit, a session identifier, the date, your IP address and your
            browser's user-agent string.</li>
        <li><strong>Cookies:</strong> see the <a href="/cookie-policy">Cookie Policy</a>.</li>
    </ul>
    <p>[Add any other data collected offline or through other channels, e.g. email, WhatsApp, WeChat.]</p>

    <h2 class="h4 mt-5">How we use it</h2>
    <ul>
        <li>To answer your enquiry and, if you ask for one, prepare a quotation.</li>
        <li>To run and secure the website (for example, the visit log).</li>
        <li>[Other purposes, if any, e.g. sending product updates - only with consent.]</li>
    </ul>
    <p>[Legal basis for each purpose, how long each type of data is kept, who it is shared with
        (e.g. hosting provider, email provider), and whether it is transferred outside your country.]</p>

    <h2 class="h4 mt-5">Cookies</h2>
    <p>
        This website uses only the cookies it needs to work (session, security and language choice).
        It does not use analytics or advertising cookies at this time.
        Details are in the <a href="/cookie-policy">Cookie Policy</a>.
    </p>

    <h2 class="h4 mt-5">Your rights</h2>
    <p>[Rights available to you under applicable law, e.g. access, correction, deletion, objection,
        and how to exercise them.]</p>

    <h2 class="h4 mt-5">Contact</h2>
    <p>
        For questions about this policy or your data, contact us
        @if ($contactEmail)
            at <a href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a>.
        @else
            at [contact email].
        @endif
        @if ($contactAddress)
            <br>Address: {{ $contactAddress }}
        @endif
    </p>

    <h2 class="h4 mt-5">Last updated</h2>
    <p>Draft prepared {{ $draftDate }}. [Replace with the date this policy is approved and published.]</p>
</div>
@endsection
