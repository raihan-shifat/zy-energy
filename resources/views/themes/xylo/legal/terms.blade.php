@extends('themes.xylo.layouts.master')

@section('title', page_title('Terms of Use'))
@section('meta_description', 'Terms of Use of '.$siteName.' (draft pending legal review).')
{{-- Draft text: keep out of search results until it has been reviewed. Remove this line when approved. --}}
@section('robots', 'noindex, follow')

@section('content')
<div class="container py-5" style="max-width: 860px;">
    <h1 class="mb-4">Terms of Use</h1>

    @include('themes.xylo.legal._draft-notice')

    <p>
        These terms apply to your use of this website, operated by {{ $siteName }}
        ([company legal name], [registered address]).
    </p>

    <h2 class="h4 mt-5">Use of the website</h2>
    <p>[Permitted use of the site and what is not allowed.]</p>

    <h2 class="h4 mt-5">Product information and quotations</h2>
    <p>
        Product pages are for information. Prices are given on request through a quotation.
        [State whether quotations are binding, how long they are valid, and which terms of sale apply to orders.]
    </p>

    <h2 class="h4 mt-5">Data we collect</h2>
    <p>How we handle personal data is described in the <a href="/privacy-policy">Privacy Policy</a>.</p>

    <h2 class="h4 mt-5">Cookies</h2>
    <p>See the <a href="/cookie-policy">Cookie Policy</a>.</p>

    <h2 class="h4 mt-5">Intellectual property</h2>
    <p>[Ownership of the website content, logos and product images, and permitted use.]</p>

    <h2 class="h4 mt-5">Liability</h2>
    <p>[Limitation of liability, to be written by your legal adviser.]</p>

    <h2 class="h4 mt-5">Governing law</h2>
    <p>[Applicable law and competent courts.]</p>

    <h2 class="h4 mt-5">Contact</h2>
    <p>
        @if ($contactEmail)
            <a href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a>
        @else
            [contact email]
        @endif
        @if ($contactAddress)
            <br>Address: {{ $contactAddress }}
        @endif
    </p>

    <h2 class="h4 mt-5">Last updated</h2>
    <p>Draft prepared {{ $draftDate }}. [Replace with the date these terms are approved and published.]</p>
</div>
@endsection
