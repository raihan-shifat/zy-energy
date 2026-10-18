@extends('themes.xylo.layouts.master')

@section('title', page_title('Cookie Policy'))
@section('meta_description', 'Cookie Policy of '.$siteName.' (draft pending legal review).')
{{-- Draft text: keep out of search results until it has been reviewed. Remove this line when approved. --}}
@section('robots', 'noindex, follow')

@section('content')
<div class="container py-5" style="max-width: 860px;">
    <h1 class="mb-4">Cookie Policy</h1>

    @include('themes.xylo.legal._draft-notice')

    <p>This page lists the cookies this website sets and why.</p>

    <h2 class="h4 mt-5">Data we collect with cookies</h2>
    <p>The website currently sets only these cookies. All are needed for the site to work:</p>
    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead>
                <tr>
                    <th scope="col">Cookie</th>
                    <th scope="col">Purpose</th>
                    <th scope="col">Duration</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>zy_energy_session</code></td>
                    <td>Keeps your session (for example, the enquiry form and login) working between pages.</td>
                    <td>2 hours</td>
                </tr>
                <tr>
                    <td><code>XSRF-TOKEN</code></td>
                    <td>Security: protects forms against cross-site request forgery.</td>
                    <td>2 hours</td>
                </tr>
                <tr>
                    <td><code>locale</code></td>
                    <td>Remembers the language you chose.</td>
                    <td>1 year</td>
                </tr>
            </tbody>
        </table>
    </div>
    <p>We do not use analytics, advertising or social-media tracking cookies at this time.
        If that changes, this page will be updated and you will be asked for consent first.</p>

    <h2 class="h4 mt-5">How we use it</h2>
    <p>These cookies are used only to provide the website you asked for. They are not used for profiling or advertising.</p>

    <h2 class="h4 mt-5">Third-party content</h2>
    <p>
        To display the site, your browser also loads files (fonts, scripts, icons, flag images) from third-party
        services such as Google Fonts, jsDelivr, cdnjs, code.jquery.com and flagcdn.com. These services receive your
        IP address when your browser requests the files. [Confirm whether this needs to be listed under applicable law.]
    </p>

    <h2 class="h4 mt-5">Cookies</h2>
    <p>You can block or delete cookies in your browser settings. If you block the essential cookies above,
        forms and language selection may not work.</p>

    <h2 class="h4 mt-5">Contact</h2>
    <p>
        Questions about cookies:
        @if ($contactEmail)
            <a href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a>.
        @else
            [contact email].
        @endif
        See also the <a href="/privacy-policy">Privacy Policy</a>.
    </p>

    <h2 class="h4 mt-5">Last updated</h2>
    <p>Draft prepared {{ $draftDate }}. [Replace with the date this policy is approved and published.]</p>
</div>
@endsection
