{{-- Reusable B2B quick-contact icon row (WhatsApp / WeChat / Email / Phone).
     Values pulled from Admin → Site Settings (never hardcoded). --}}
@php
    $siteContact = \App\Models\SiteSetting::first();
    $contactProductName = $contactProductName ?? '';
    $whatsappRaw = $siteContact->whatsapp_number ?? '';
    $whatsappDigits = preg_replace('/[^0-9]/', '', $whatsappRaw);
    $contactEmail = $siteContact->contact_email ?? '';
    $contactPhone = $siteContact->contact_phone ?? '';
    $wechatQr = $siteContact->wechat_qr_image ?? null;
    $contactQuery = $contactProductName ? 'Enquiry about ' . $contactProductName : 'Enquiry';
@endphp
<div class="quick-contact-row d-flex align-items-center gap-2 mt-3 flex-wrap">
    @if ($whatsappDigits !== '')
        <a href="https://wa.me/{{ $whatsappDigits }}?text={{ urlencode($contactQuery) }}"
           target="_blank" rel="noopener" class="btn btn-success"
            title="{{ __('store.contact.whatsapp') }}">
            <i class="fab fa-whatsapp fs-5"></i>
        </a>
    @endif

    @if ($wechatQr)
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#wechatQrModal"
                title="{{ __('store.contact.wechat') }}">
            <i class="fab fa-weixin fs-5"></i>
        </button>
    @endif

    @if ($contactEmail !== '')
        <a href="mailto:{{ $contactEmail }}?subject={{ urlencode($contactQuery) }}"
           class="btn btn-outline-dark"
           title="{{ __('store.contact.email') }}">
            <i class="fa-regular fa-envelope fs-5"></i>
        </a>
    @endif

    @if ($contactPhone !== '')
        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $contactPhone) }}"
           class="btn btn-outline-dark"
           title="{{ __('store.contact.phone') }}">
            <i class="fa-solid fa-phone fs-5"></i>
        </a>
    @endif
</div>

@if ($wechatQr)
    <div class="modal fade" id="wechatQrModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content text-center">
                <div class="modal-header">
                    <h5 class="modal-title mx-auto">{{ __('store.contact.wechat') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <img src="{{ asset('storage/' . $wechatQr) }}" alt="{{ __('store.contact.wechat') }}" class="img-fluid" loading="lazy" style="max-width:240px;">
                    <p class="text-muted mt-2 small">{{ __('store.contact.scan_qr') }}</p>
                </div>
            </div>
        </div>
    </div>
@endif
