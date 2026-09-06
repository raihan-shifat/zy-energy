{{-- Reusable B2B "Contact Us" popup button + dynamic modal.
     Options pulled from database via ContactWechat, ContactWhatsapp, ContactEmail models.
     Usage: @include('themes.xylo.partials.contact-us', ['contactProductName' => $productName]) --}}
@php
    $contactProductName = $contactProductName ?? '';
    $contactFooter = getContactFooterData();
    $wechats = $contactFooter['wechats'];
    $whatsapps = $contactFooter['whatsapps'];
    $emails = $contactFooter['emails'];
    $settings = $contactFooter['settings'];
@endphp

{{-- Contact Us button --}}
<button type="button" class="btn btn-outline-dark btn-lg" data-bs-toggle="modal" data-bs-target="#contactUsModal">
    <i class="fa-regular fa-address-book"></i> {{ __('store.contact.contact_us') }}
</button>

{{-- Contact Us Modal --}}
<div class="modal fade" id="contactUsModal" tabindex="-1" aria-labelledby="contactUsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered contact-modal-dialog">
        <div class="modal-content contact-modal-content">
            {{-- Green Header --}}
            <div class="modal-header contact-modal-header px-3 py-2">
                <h6 class="modal-title fw-bold text-white mb-0" style="font-size: 0.95rem;">{{ $settings->page_title ?? __('store.contact.contact_us') }}</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- Subtitle --}}
            <div class="contact-modal-subtitle px-3 py-1">
                <p class="text-muted mb-0" style="font-size: 0.8rem;">{{ $settings->page_subtitle ?? __('store.contact.subtitle') }}</p>
            </div>

            <div class="modal-body p-0">
                {{-- WeChat & WhatsApp --}}
                <div class="px-3 pt-2 pb-1">
                    <div class="row g-2">
                        {{-- WeChat --}}
                        @if($wechats->count() > 0)
                            <div class="col-md-6">
                                <div class="contact-section p-2 h-100">
                                    <h6 class="fw-bold text-primary mb-1" style="font-size: 0.9rem;">{{ $settings->wechat_section_title ?? __('store.contact.wechat') }}</h6>
                                    @foreach($wechats as $wechat)
                                        <div class="d-flex align-items-start gap-2 contact-card" style="padding: 4px 0; margin-bottom: 1px;">
                                            @if($wechat->qr_code)
                                                <img src="{{ asset('storage/' . $wechat->qr_code) }}" alt="{{ $wechat->name }}" class="flex-shrink-0" loading="lazy" style="width: 80px; height: 80px; object-fit: contain; border-radius: 6px;">
                                            @else
                                                <div class="bg-light rounded d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width: 80px; height: 80px;">
                                                    <i class="fab fa-weixin fa-2x text-success"></i>
                                                </div>
                                            @endif
                                            <div class="min-width-0" style="line-height: 1.2;">
                                                <h6 class="fw-bold text-primary mb-0" style="font-size: 0.95rem; line-height: 1.15;">{{ $wechat->name }}</h6>
                                                @if($wechat->wechat_id)
                                                    <p class="text-muted mb-0" style="font-size: 0.82rem; line-height: 1.15;">{{ __('store.contact.wechat') }} ID: {{ $wechat->wechat_id }}</p>
                                                @endif
                                                @if($wechat->purpose)
                                                    <p class="text-muted mb-0" style="font-size: 0.82rem; line-height: 1.15;">{{ $wechat->purpose }}</p>
                                                @endif
                                                @if($wechat->description)
                                                    <p class="text-muted mb-0" style="font-size: 0.78rem; line-height: 1.15;">{{ $wechat->description }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- WhatsApp --}}
                        @if($whatsapps->count() > 0)
                            <div class="col-md-6">
                                <div class="contact-section p-2 h-100">
                                    <h6 class="fw-bold text-primary mb-1" style="font-size: 0.9rem;">{{ $settings->whatsapp_section_title ?? __('store.contact.whatsapp') }}</h6>
                                    @foreach($whatsapps as $whatsapp)
                                        <div class="contact-card" style="padding: 4px 0; {{ !$loop->last ? 'border-bottom: 1px solid #e8e8e8; margin-bottom: 4px; padding-bottom: 5px;' : '' }}">
                                            <div class="d-flex align-items-center gap-2 mb-0">
                                                <i class="fab fa-whatsapp text-success flex-shrink-0" style="font-size: 1.35rem;"></i>
                                                <div class="min-width-0">
                                                    <h6 class="fw-bold text-primary mb-0" style="font-size: 0.95rem; line-height: 1.15;">{{ $whatsapp->name }}</h6>
                                                    @if($whatsapp->purpose)
                                                        <p class="text-muted mb-0" style="font-size: 0.75rem; line-height: 1.15;">{{ $whatsapp->purpose }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                            <p class="text-muted mb-1 mt-1" style="font-size: 0.85rem; line-height: 1.2;">{{ $whatsapp->phone }}</p>
                                            <a href="{{ $whatsapp->whatsapp_link }}" target="_blank" rel="noopener" class="btn btn-success btn-sm w-100" style="font-size: 0.8rem; padding: 0.15rem 0.5rem;">
                                                <i class="fab fa-whatsapp me-1"></i> {{ __('store.contact.chat_now') }}
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Email --}}
                @if($emails->count() > 0)
                    <div class="px-3 pb-2">
                        <div class="contact-section p-2">
                            <h6 class="fw-bold text-primary mb-1" style="font-size: 0.9rem;">{{ $settings->email_section_title ?? __('store.contact.email') }}</h6>
                            <div class="row g-2">
                                @foreach($emails as $email)
                                    <div class="col-md-6">
                                        <div class="contact-card" style="padding: 4px 0;">
                                            <div class="d-flex align-items-center gap-2 mb-0">
                                                <i class="fa-regular fa-envelope text-primary flex-shrink-0" style="font-size: 1.35rem;"></i>
                                                <div class="min-width-0">
                                                    <h6 class="fw-bold text-primary mb-0" style="font-size: 0.95rem; line-height: 1.15;">{{ $email->name }}</h6>
                                                    @if($email->purpose)
                                                        <p class="text-muted mb-0" style="font-size: 0.75rem; line-height: 1.15;">{{ $email->purpose }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                            <p class="mb-1 mt-1" style="font-size: 0.88rem; font-weight: 500; color: #333; line-height: 1.2;">{{ $email->email }}</p>
                                            <div class="d-flex gap-1">
                                                <a href="{{ $email->mailto_link }}" class="btn btn-outline-primary btn-sm flex-fill" style="font-size: 0.8rem; padding: 0.15rem 0.4rem;">
                                                    <i class="fa-regular fa-envelope me-1"></i> {{ __('store.contact.send_email') }}
                                                </a>
                                                <button type="button" class="btn btn-outline-secondary btn-sm copy-email" data-email="{{ $email->email }}" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('store.contact.copy') }}" style="font-size: 0.8rem; padding: 0.15rem 0.4rem;">
                                                    <i class="fa-regular fa-copy"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Bottom Message --}}
                @if($settings->bottom_message || $settings->response_time_text)
                    <div class="px-3 text-center border-top pt-1 mt-0">
                        @if($settings->bottom_message)
                            <p class="text-muted mb-0" style="font-size: 0.7rem; line-height: 1.3;">{{ $settings->bottom_message }}</p>
                        @endif
                        @if($settings->response_time_text)
                            <p class="fw-medium text-primary mb-0" style="font-size: 0.7rem; line-height: 1.3;">{{ $settings->response_time_text }}</p>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Footer --}}
            <div class="modal-footer contact-modal-footer py-1 px-3 mt-0">
                <div class="d-flex justify-content-center gap-2 w-100">
                    <a href="{{ route('contact.index') }}" class="btn btn-outline-primary btn-sm" style="font-size: 0.78rem; padding: 0.2rem 0.6rem;">
                        <i class="fa-solid fa-arrow-right me-1"></i> {{ __('store.contact.view_full_page') }}
                    </a>
                    <button type="button" class="btn btn-success btn-sm" data-bs-dismiss="modal" style="font-size: 0.78rem; padding: 0.2rem 0.6rem;">
                        <i class="fa-solid fa-xmark me-1"></i> {{ __('store.common.close') }}
                    </button>
                </div>
            </div>

            @if ($wechats->isEmpty() && $whatsapps->isEmpty() && $emails->isEmpty())
                <div class="p-3 text-center text-muted small">{{ __('store.contact.not_configured') }}</div>
            @endif
        </div>
    </div>
</div>

<style>
    .contact-modal-dialog {
        max-width: 794px;
    }

    .contact-modal-content {
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.18);
    }

    .contact-modal-header {
        background: linear-gradient(135deg, #0d6e3e 0%, #0a5c36 100%);
        border-bottom: none;
    }

    .contact-modal-header .btn-close-white {
        filter: invert(1);
        opacity: 1;
    }

    .contact-modal-subtitle {
        background: linear-gradient(135deg, #f0faf4 0%, #e8f5ee 100%);
        border-bottom: 1px solid #e0e0e0;
    }

    .contact-section {
        background: white;
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        border: 1px solid #eee;
    }

    .contact-card {
        border-bottom: 1px solid #f0f0f0;
        transition: background 0.15s ease;
    }

    .contact-card:last-child {
        border-bottom: none;
    }

    .contact-card:hover {
        background: #f8f9fa;
    }

    .min-width-0 {
        min-width: 0;
    }

    .min-width-0 p {
        margin-bottom: 0 !important;
        line-height: 1.15 !important;
    }

    .min-width-0 h6 {
        line-height: 1.15 !important;
    }

    .contact-modal-footer {
        border-top: 1px solid #eee;
        background: white;
    }

    @media (max-width: 767.98px) {
        .contact-modal-dialog {
            max-width: 95%;
        }
        .d-flex.align-items-start img {
            width: 60px !important;
            height: 60px !important;
        }
    }

    .modal.fade .contact-modal-content {
        transform: scale(0.95);
        opacity: 0;
        transition: transform 0.2s ease-out, opacity 0.2s ease-out;
    }

    .modal.show .contact-modal-content {
        transform: scale(1);
        opacity: 1;
    }

    .tooltip-inner {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        document.querySelectorAll('.copy-email').forEach(function(button) {
            button.addEventListener('click', function() {
                var email = this.getAttribute('data-email');
                var btn = this;
                navigator.clipboard.writeText(email).then(function() {
                    var originalHTML = btn.innerHTML;
                    btn.innerHTML = '<i class="fa-solid fa-check"></i>';
                    btn.classList.remove('btn-outline-secondary');
                    btn.classList.add('btn-success');
                    setTimeout(function() {
                        btn.innerHTML = originalHTML;
                        btn.classList.remove('btn-success');
                        btn.classList.add('btn-outline-secondary');
                    }, 2000);
                });
            });
        });
    });
</script>
