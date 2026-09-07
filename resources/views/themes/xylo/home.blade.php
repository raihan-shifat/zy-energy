@extends('themes.xylo.layouts.master')

@section('css')
    @vite(['resources/views/themes/xylo/css/slick.css'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.css">
@endsection

@section('content')
    @php $currency = activeCurrency(); @endphp
    {{-- Hero Section (B2B: clean industrial hero, admin-editable via banner title/description) --}}
    @php
        $activeBanners = $banners->filter(function ($b) { return $b->translations->isNotEmpty(); });
    @endphp
    @if ($activeBanners->count() > 0)
    <section class="banner-area py-5">
        <div class="container h-100">
            @php
                $heroProductImage = $products->first() && $products->first()->thumbnail
                    ? $products->first()->thumbnail->image_url
                    : null;

                $bannerData = $activeBanners->map(function ($banner) use ($heroProductImage) {
                    $t = $banner->translations->firstWhere('language_code', app()->getLocale())
                        ?? $banner->translations->first();
                    return [
                        'title' => $t->title ?? __('store.home_b2b.hero_title'),
                        'subtitle' => html_entity_decode(strip_tags($t->subtitle ?? $t->description ?? __('store.home_b2b.hero_subtitle')), ENT_QUOTES, 'UTF-8'),
                        'image' => $t->image_url ? Storage::url($t->image_url) : ($heroProductImage ? Storage::url($heroProductImage) : null),
                        'cta_text' => $t->cta_text ?: __('store.home_b2b.view_all_products'),
                        'cta_link' => $t->cta_link ?: route('shop.index'),
                    ];
                })->values()->toArray();

                $initialIndex = rand(0, count($bannerData) - 1);
                $hero = $bannerData[$initialIndex];
            @endphp
            <div id="hero-rotation" class="row h-100 align-items-center g-4 position-relative">
                {{-- Render all slides (hidden except active) --}}
                @foreach ($bannerData as $idx => $slide)
                <div class="hero-slide col-md-6 {{ $idx === $initialIndex ? '' : 'd-none' }}" data-index="{{ $idx }}">
                    <span class="hero-eyebrow">{{ __('store.home_b2b.hero_eyebrow') }}</span>
                    <h1 class="mt-0"><span>{!! $slide['title'] !!}</span></h1>
                    <p class="hero-subtext">{{ $slide['subtitle'] }}</p>
                    <a href="{{ $slide['cta_link'] }}" class="btn btn-primary btn-lg">{{ $slide['cta_text'] }}</a>
                </div>
                @endforeach
                <div class="col-md-6">
                    <div class="hero-media position-relative">
                        @foreach ($bannerData as $idx => $slide)
                        <div class="hero-img-slide {{ $idx === $initialIndex ? '' : 'd-none' }}" data-index="{{ $idx }}">
                            @if ($slide['image'])
                                {!! getResponsiveImageHtml($slide['image'], [
                                    'alt' => strip_tags($slide['title']),
                                    'class' => 'img-fluid',
                                    'sizes' => '(max-width: 768px) 100vw, 50vw',
                                    'loading' => 'lazy',
                                ]) !!}
                            @else
                                <div class="hero-media-fallback"><i class="fa-solid fa-bolt"></i></div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
    @if ($activeBanners->count() > 1)
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var slides = document.querySelectorAll('#hero-rotation .hero-slide');
        var imgs = document.querySelectorAll('#hero-rotation .hero-img-slide');
        var total = slides.length;
        var current = {{ $initialIndex }};
        if (total <= 1) return;
        setInterval(function() {
            var next = (current + 1) % total;
            slides[current].classList.add('d-none');
            imgs[current].classList.add('d-none');
            slides[next].classList.remove('d-none');
            imgs[next].classList.remove('d-none');
            current = next;
        }, 60000);
    });
    </script>
    @endif
    </section>
    @endif
    {{-- Hero Section End --}}

    {{-- Product Categories Grid (7 tiles, admin-managed categories) --}}
    <section class="cat-slider animate-on-scroll">
        <div class="container">
            <h2 class="text-start pb-3 sec-heading">{{ __('store.home_b2b.categories') }}</h2>
            <p class="text-muted mb-4">{{ __('store.home_b2b.categories_subtitle') }}</p>
            <div class="row">
                @foreach($categories as $category)
                    @php
                        $catName = localized_translation_value($category->translations, 'name', $category->slug);
                    @endphp
                    <div class="col-6 col-md-3 mb-4">
                        <div class="cat-card h-100">
                            <a href="{{ route('category.show', $category->slug) }}" class="text-decoration-none d-block h-100">
                                @php
                                    $catImg = (string) localized_translation_value($category->translations, 'image_url', '');
                                    $catHasImg = $catImg !== '' && $catImg !== 'default.jpg'
                                        && \Illuminate\Support\Facades\Storage::disk('public')->exists($catImg);
                                @endphp
                                <div class="catcard-img">
                                    @if ($catHasImg)
                                        <img src="{{ asset('storage/' . ltrim($catImg, '/')) }}"
                                             alt="{{ $catName }}" class="img-fluid" loading="lazy">
                                    @else
                                        <div class="catcard-img-placeholder">
                                            <i class="fa-regular fa-image"></i>
                                        </div>
                                    @endif
                                </div>
                                <h3 class="mt-3 mb-0 text-center">{{ $catName }}</h3>
                                @if (!empty($category->types))
                                    <div class="type-pills mt-1">
                                        @foreach ($category->types as $catType)
                                            <a href="{{ route('category.show', [$category->slug, 'type' => $catType]) }}" class="type-pill">{{ $catType }}</a>
                                        @endforeach
                                    </div>
                                @endif
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Certification Strip --}}
    @if($certifications->count())
    <section class="certification-strip py-5 bg-light animate-on-scroll">
        <div class="container">
            <h2 class="text-start pb-3 sec-heading">{{ __('store.home_b2b.certifications') }}</h2>
            <div class="row align-items-center">
                @foreach ($certifications as $certification)
                    @php
                        $certName = localized_translation_value($certification->translations, 'name', __('store.certification.title'));
                    @endphp
                    <div class="col-6 col-md-3 mb-3 text-center">
                        @if ($certification->document_url)
                            <a href="{{ Storage::url($certification->document_url) }}" target="_blank" class="text-decoration-none">
                        @endif
                            @if ($certification->image_url)
                                <img src="{{ Storage::url($certification->image_url) }}" alt="{{ $certName }}" loading="lazy" style="max-height:70px; max-width:120px; object-fit:contain;">
                            @else
                                <span class="text-muted">{{ $certName }}</span>
                            @endif
                        @if ($certification->document_url)
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- Trending Products (B2B cards) --}}
    <section class="trending-products animate-on-scroll">
        <div class="container position-relative">
            <h2 class="text-start pb-3 sec-heading">{{ __('store.home.trending_products') }}</h2>

            {{-- Native scroll-snap carousel (slick is broken under Vite module builds) --}}
            <div class="product-slider" id="trendingTrack">
                @foreach ($products as $product)
                    <div class="product-slide">
                        @include('themes.xylo.partials.product-card-b2b', ['product' => $product])
                    </div>
                @endforeach
            </div>

            <!-- Custom Arrows -->
            <div class="custom-arrows">
                <button type="button" class="prev" aria-label="Previous"><i class="fa-solid fa-chevron-left"></i></button>
                <button type="button" class="next" aria-label="Next"><i class="fa-solid fa-chevron-right"></i></button>
            </div>
        </div>
    </section>

    @section('js')
    <script>
        {{-- Trending carousel arrows: scroll the track natively --}}
        document.addEventListener('DOMContentLoaded', function () {
            var section = document.querySelector('.trending-products');
            if (!section) return;
            var track = section.querySelector('.product-slider');
            var prev = section.querySelector('.custom-arrows .prev');
            var next = section.querySelector('.custom-arrows .next');
            if (!track || !prev || !next) return;

            function step() {
                return Math.max(Math.round(track.clientWidth * 0.8), 240);
            }
            next.addEventListener('click', function () {
                track.scrollBy({ left: step(), behavior: 'smooth' });
            });
            prev.addEventListener('click', function () {
                track.scrollBy({ left: -step(), behavior: 'smooth' });
            });

            // Grey out arrows at the ends
            function updateArrows() {
                var maxScroll = track.scrollWidth - track.clientWidth;
                prev.disabled = track.scrollLeft <= 1;
                next.disabled = track.scrollLeft >= maxScroll - 1;
            }
            track.addEventListener('scroll', updateArrows, { passive: true });
            window.addEventListener('resize', updateArrows);
            updateArrows();
        });
    </script>
@endsection

    {{-- Latest News --}}
    <section class="latest-news py-5 animate-on-scroll">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="sec-heading mb-0">{{ __('store.home_b2b.latest_news') }}</h2>
                <a href="{{ route('news.index') }}" class="btn btn-outline-primary btn-sm">{{ __('store.home_b2b.view_all_news') }}</a>
            </div>
            <div class="row">
                @forelse ($newsPosts as $newsPost)
                    @php
                        $newsTitle = localized_translation_value($newsPost->translations, 'title', $newsPost->slug);
                        $newsExcerpt = localized_translation_value($newsPost->translations, 'excerpt', '');
                    @endphp
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm">
                            @if ($newsPost->image_url)
                                <img src="{{ Storage::url($newsPost->image_url) }}" class="card-img-top" alt="{{ $newsTitle }}" loading="lazy" style="height:180px; object-fit:cover;">
                            @endif
                            <div class="card-body">
                                <h5 class="card-title">{{ $newsTitle }}</h5>
                                <p class="card-text text-muted small">{{ \Illuminate\Support\Str::limit($newsExcerpt, 90) }}</p>
                                <a href="{{ route('news.show', $newsPost->slug) }}" class="btn btn-link p-0">{{ __('store.news.read_more') }}</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted">{{ __('store.news.no_posts') }}</p>
                @endforelse
            </div>
        </div>
    </section>

    {{-- Why Choose Us (admin-editable copy, keep as-is) --}}
    <section class="why-choose-us py-5 animate-on-scroll">
        <div class="container">
            <h2 class="sec-heading text-start mb-5">{{ __('store.home.why_choose_us') }}</h2>
            <div class="row">
                <div class="col-md-3">
                    <div class="feature-box text-start">
                        <div class="feature-icon">
                            <i class="fa-solid fa-gears"></i>
                        </div>
                        <h3>{{ __('store.home.fast_delivery_title') }}</h3>
                        <p>{{ __('store.home.fast_delivery_text') }}</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="feature-box text-start">
                        <div class="feature-icon">
                            <i class="fa-solid fa-headset"></i>
                        </div>
                        <h3>{{ __('store.home.customer_support_title') }}</h3>
                        <p>{{ __('store.home.customer_support_text') }}</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="feature-box text-start">
                        <div class="feature-icon">
                            <i class="fa-solid fa-earth-americas"></i>
                        </div>
                        <h3>{{ __('store.home.trusted_worldwide_title') }}</h3>
                        <p>{{ __('store.home.trusted_worldwide_text') }}</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="feature-box text-start">
                        <div class="feature-icon">
                            <i class="fa-solid fa-medal"></i>
                        </div>
                        <h3>{{ __('store.home.ten_years_services_title') }}</h3>
                        <p>{{ __('store.home.ten_years_services_text') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Closing CTA -> Enquiry --}}
    <section class="cta-block py-5 bg-primary text-white animate-on-scroll">
        <div class="container text-center">
            <h2 class="mb-2">{{ __('store.home_b2b.get_quote') }}</h2>
            <p class="mb-4">{{ __('store.home_b2b.get_quote_text') }}</p>
            <button type="button" class="btn btn-light btn-lg" onclick="openEnquiryModal(null, '')">
                <i class="fa-regular fa-envelope"></i> {{ __('store.home_b2b.send_enquiry') }}
            </button>
        </div>
    </section>
@endsection

@section('js')
{{-- B2B: old add-to-cart handler commented out (cart removed) --}}
{{--<script>
        function addToCart(productId) {

            fetch("{{ route('cart.add') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: 1
                })
            })
            .then(response => response.json())
            .then(data => {
                toastr.success(data.message || "Added to cart successfully!", "", {
                    closeButton: true,
                    progressBar: true,
                    positionClass: "toast-top-right",
                    timeOut: 5000
                });
                updateCartCount(data.cart);
            })
            .catch(error => console.error("Error:", error));
        }

        function updateCartCount(cart) {
            let totalCount = Object.values(cart).reduce((sum, item) => sum + item.quantity, 0);
            document.getElementById("cart-count").textContent = totalCount;
        }
</script>--}}
@endsection
