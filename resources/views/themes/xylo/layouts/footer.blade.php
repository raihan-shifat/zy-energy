{{-- B2B Footer: 4 columns — About | Quick Links | Address | Social Connect --}}
<footer class="site-footer">
  @php
      $siteSettings = getSiteSettings();
      $footerLogo = $siteSettings?->footer_logo ?: $siteSettings?->logo;
      $footerDescription = $siteSettings?->footer_description ?: $siteSettings?->footer_text;
      $footerMenu = getFooterMenu();
  @endphp

  <div class="container">
    <div class="row g-4">

      <!-- Column 1: About / Logo + Bio -->
      <div class="col-12 col-md-3 mb-3">
        <div class="footer-about d-flex flex-column gap-3">
          <a href="{{ route('xylo.home') }}" class="d-inline-block">
            <img
              src="{{ $footerLogo ? asset('storage/' . $footerLogo) : asset('logo.png') }}"
              alt="{{ $siteSettings?->site_name ?? __('store.footer.footer_logo_alt') }}"
              class="footer-logo"
              loading="lazy"
            >
          </a>
          @if($footerDescription)
            <p class="footer-bio">{!! nl2br(e($footerDescription)) !!}</p>
          @else
            <p class="footer-bio">{{ __('store.footer.about_text') }}</p>
          @endif
        </div>
      </div>

      <!-- Column 2: Quick Links -->
      <div class="col-6 col-md-3 mb-3">
        <h5 class="footer-heading">{{ __('store.footer.quick_links') }}</h5>
        <ul class="footer-links list-unstyled">
          @if($footerMenu && $footerMenu->menuItems->isNotEmpty())
            @foreach($footerMenu->menuItems as $menuItem)
              <li><a href="{{ url($menuItem->slug) }}">{{ $menuItem->translation->title ?? $menuItem->slug }}</a></li>
            @endforeach
          @else
            <li><a href="{{ url('/') }}">{{ __('store.footer.home') }}</a></li>
            <li><a href="{{ route('shop.index') }}">{{ __('store.footer.products') }}</a></li>
            <li><a href="{{ route('certification.index') }}">{{ __('store.footer.certification') }}</a></li>
            <li><a href="{{ route('news.index') }}">{{ __('store.footer.news') }}</a></li>
            <li><a href="{{ url('/about') }}">{{ __('store.footer.about') }}</a></li>
            <li><a href="{{ url('/contact') }}">{{ __('store.footer.contact') }}</a></li>
          @endif
        </ul>
      </div>

      <!-- Column 3: Address -->
      <div class="col-6 col-md-3 mb-3">
        <h5 class="footer-heading">{{ __('store.footer.address') }}</h5>
        <div class="footer-address">
          @if($siteSettings?->address)
            <p class="footer-address-line">
              <i class="fas fa-map-marker-alt me-2 opacity-50"></i>{{ $siteSettings->address }}
            </p>
          @endif
          @if($siteSettings?->contact_phone)
            <p class="footer-address-line">
              <i class="fas fa-phone me-2 opacity-50"></i>{{ $siteSettings->contact_phone }}
            </p>
          @endif
          @if($siteSettings?->contact_email)
            <p class="footer-address-line">
              <i class="fas fa-envelope me-2 opacity-50"></i>{{ $siteSettings->contact_email }}
            </p>
          @endif
        </div>
      </div>

      <!-- Column 4: Social Connect -->
      <div class="col-12 col-md-3 mb-3">
        <h5 class="footer-heading">{{ __('store.footer.social_connect') }}</h5>
        @php
            $footerSocial = getFooterSocials();
            $socialConfig = [
                'facebook' => ['icon' => 'fab fa-facebook-f', 'label' => 'Facebook'],
                'instagram' => ['icon' => 'fab fa-instagram', 'label' => 'Instagram'],
                'tiktok' => ['icon' => 'fab fa-tiktok', 'label' => 'TikTok'],
                'youtube' => ['icon' => 'fab fa-youtube', 'label' => 'YouTube'],
            ];
            $wechatQr = getFooterWechatQr();
        @endphp
        <div class="d-flex gap-3 flex-wrap align-items-center social-connect">
            @foreach ($footerSocial as $social)
                @php $socialType = strtolower($social->type); @endphp
                @if (isset($socialConfig[$socialType]))
                    <a href="{{ $social->link }}" target="_blank" rel="noopener"
                       class="social-icon social-icon--{{ $socialType }}"
                       aria-label="{{ $socialConfig[$socialType]['label'] }}"
                       title="{{ $socialConfig[$socialType]['label'] }}">
                        <i class="{{ $socialConfig[$socialType]['icon'] }}"></i>
                    </a>
                @endif
            @endforeach

            @if ($wechatQr)
                <a href="#" class="social-icon social-icon--wechat"
                   data-bs-toggle="modal" data-bs-target="#footerWechatModal"
                   aria-label="{{ __('store.contact.wechat') }}" title="{{ __('store.contact.wechat') }}">
                    <i class="fab fa-weixin"></i>
                </a>
            @endif
        </div>

        @if ($wechatQr)
        <div class="modal fade" id="footerWechatModal" tabindex="-1" aria-labelledby="footerWechatModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title" id="footerWechatModalLabel">{{ __('store.footer.wechat_qr_title') }}</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <img src="{{ asset('storage/' . $wechatQr) }}" alt="{{ __('store.contact.wechat') }}" class="img-fluid" loading="lazy" style="max-width: 220px;">
                        <p class="text-muted small mt-2 mb-0">{{ __('store.footer.wechat_qr_hint') }}</p>
                    </div>
                </div>
            </div>
        </div>
        @endif
      </div>

    </div>
  </div>

  <!-- Footer Bottom Strip -->
  <div class="footer-bottom">
    <div class="container">
      <div class="d-flex justify-content-start align-items-center flex-wrap gap-2">
        <span class="footer-copyright">{{ __('store.footer.copyright') }} <span class="footer-divider">|</span> Developed by <a href="mailto:raihan.shifat@qq.com" class="footer-email">Raihan Shifat<span class="footer-email-arrow">↗</span></a></span>
      </div>
    </div>
  </div>
</footer>
