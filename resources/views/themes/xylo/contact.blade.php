@extends('themes.xylo.layouts.master')

@section('title', __('store.contact.contact_us'))

@section('content')

{{-- Intro hero band --}}
<section class="contact-page-hero">
    <div class="container">
        <h1 class="mb-2">{{ $settings->page_title }}</h1>
        @if($settings->page_subtitle)
            <p class="lead mb-0">{{ $settings->page_subtitle }}</p>
        @endif
    </div>
</section>

{{-- Contact card grid --}}
<section class="contact-grid">
    <div class="container">

        @if($wechats->isEmpty() && $whatsapps->isEmpty() && $emails->isEmpty())
            <div class="text-center text-muted py-5">{{ __('store.contact.not_configured') }}</div>
        @else
            <div class="row g-4 justify-content-center">

                {{-- WeChat cards --}}
                @foreach($wechats as $wechat)
                    <div class="col-12 col-sm-6 col-lg-4 d-flex">
                        <div class="contact-grid-card w-100">
                            <span class="media-badge media-badge--wechat">{{ __('store.contact.wechat') }}</span>

                            <div class="contact-visual">
                                @if($wechat->qr_code)
                                    <img src="{{ asset('storage/' . $wechat->qr_code) }}" alt="{{ $wechat->name }}" loading="lazy">
                                @else
                                    <div class="visual-fallback wechat"><i class="fab fa-weixin"></i></div>
                                @endif
                            </div>

                            <h3>{{ $wechat->name }}</h3>
                            <span class="role-badge">{{ $wechat->purpose ?? __('store.contact.support') }}</span>

                            @if($wechat->wechat_id)
                                <p class="contact-id">{{ __('store.contact.wechat') }} ID: {{ $wechat->wechat_id }}</p>
                            @endif

                            @if($wechat->description)
                                <p class="contact-desc">{{ $wechat->description }}</p>
                            @else
                                <p class="contact-desc">{{ __('store.contact.scan_qr') }}</p>
                            @endif

                            <div class="card-actions">
                                <button type="button" class="btn btn-outline-success btn-sm w-100 contact-cta copy-btn"
                                        data-copy="{{ $wechat->wechat_id }}" data-original-label="{{ __('store.contact.copy') }}">
                                    <i class="fa-regular fa-copy me-1"></i><span class="btn-label">{{ __('store.contact.copy') }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach

                {{-- WhatsApp cards --}}
                @foreach($whatsapps as $whatsapp)
                    <div class="col-12 col-sm-6 col-lg-4 d-flex">
                        <div class="contact-grid-card w-100">
                            <span class="media-badge media-badge--whatsapp">{{ __('store.contact.whatsapp') }}</span>

                            <div class="contact-visual">
                                <div class="visual-fallback whatsapp"><i class="fab fa-whatsapp"></i></div>
                            </div>

                            <h3>{{ $whatsapp->name }}</h3>
                            <span class="role-badge">{{ $whatsapp->purpose ?? __('store.contact.support') }}</span>

                            @if($whatsapp->phone)
                                <p class="contact-id">{{ $whatsapp->phone }}</p>
                            @endif

                            @if($whatsapp->description)
                                <p class="contact-desc">{{ $whatsapp->description }}</p>
                            @endif

                            <div class="card-actions d-flex gap-2">
                                <a href="{{ $whatsapp->whatsapp_link }}" target="_blank" rel="noopener"
                                   class="btn btn-success btn-sm flex-fill contact-cta">
                                    <i class="fab fa-whatsapp me-1"></i>{{ __('store.contact.chat_now') }}
                                </a>
                                <button type="button" class="btn btn-outline-secondary btn-sm contact-cta copy-btn"
                                        data-copy="{{ $whatsapp->phone }}" data-original-label="{{ __('store.contact.copy') }}">
                                    <i class="fa-regular fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach

                {{-- Email cards --}}
                @foreach($emails as $email)
                    <div class="col-12 col-sm-6 col-lg-4 d-flex">
                        <div class="contact-grid-card w-100">
                            <span class="media-badge media-badge--email">{{ __('store.contact.email') }}</span>

                            <div class="contact-visual">
                                <div class="visual-fallback email"><i class="fa-regular fa-envelope"></i></div>
                            </div>

                            <h3>{{ $email->name }}</h3>
                            <span class="role-badge">{{ $email->purpose ?? __('store.contact.support') }}</span>

                            @if($email->email)
                                <p class="contact-id">{{ $email->email }}</p>
                            @endif

                            @if($email->description)
                                <p class="contact-desc">{{ $email->description }}</p>
                            @endif

                            <div class="card-actions d-flex gap-2">
                                <a href="{{ $email->mailto_link }}" class="btn btn-primary btn-sm flex-fill contact-cta">
                                    <i class="fa-regular fa-envelope me-1"></i>{{ __('store.contact.send_email') }}
                                </a>
                                <button type="button" class="btn btn-outline-secondary btn-sm contact-cta copy-btn"
                                        data-copy="{{ $email->email }}" data-original-label="{{ __('store.contact.copy') }}">
                                    <i class="fa-regular fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach

            </div>
        @endif
    </div>
</section>

{{-- Bottom reassurance band --}}
@if($settings->bottom_message || $settings->response_time_text)
    <section class="contact-bottom">
        <div class="container">
            <div class="bottom-message">
                @if($settings->bottom_message)
                    <p class="mb-1">{{ $settings->bottom_message }}</p>
                @endif
                @if($settings->response_time_text)
                    <p class="mb-0 fw-medium text-primary">{{ $settings->response_time_text }}</p>
                @endif
            </div>
        </div>
    </section>
@endif

@endsection

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.copy-btn').forEach(function (button) {
            button.addEventListener('click', function () {
                var value = this.getAttribute('data-copy');
                var label = this.querySelector('.btn-label');
                var original = label ? label.textContent : (this.getAttribute('data-original-label') || '{{ __('store.contact.copy') }}');
                if (!value) return;

                navigator.clipboard.writeText(value).then(function () {
                    if (label) {
                        label.textContent = '{{ __('store.contact.copied') }}';
                    }
                    button.classList.remove('btn-outline-secondary');
                    button.classList.add('btn-success');
                    setTimeout(function () {
                        if (label) {
                            label.textContent = original;
                        }
                        button.classList.remove('btn-success');
                        button.classList.add('btn-outline-secondary');
                    }, 2000);
                });
            });
        });
    });
</script>
@endsection
