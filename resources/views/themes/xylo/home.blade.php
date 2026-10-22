@extends('themes.xylo.layouts.master')

{{-- No slick CSS here: the homepage carousel is native scroll-snap, slick is only used on product detail. --}}

@section('css')
<style>
    /* Banner video playing inside the hero image box (.hero-media is 4:3 with overflow hidden) */
    .hero-video-layer {
        position: absolute;
        inset: 0;
        z-index: 1;
        overflow: hidden;
        pointer-events: none;      /* no player UI, links or clicks reachable */
        opacity: 0;
        transition: opacity 0.4s ease;
        background: transparent;
    }
    .hero-video-layer.is-shown { opacity: 1; background: #000; }
    .hero-video-layer video {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    /* 16:9 iframe covering the 4:3 box (133.33% wide), scaled up a little so the
       player's own title bar and logo at the edges are cropped away */
    .hero-video-layer iframe {
        position: absolute;
        top: 50%;
        left: 50%;
        width: 133.34%;
        height: 100%;
        border: 0;
        transform: translate(-50%, -50%) scale(1.08);
    }
    /* Soft shade in the bottom-right corner so the round button stays readable on bright video */
    .hero-video-layer.is-shown::after {
        content: "";
        position: absolute;
        inset: auto 0 0 auto;
        width: 45%;
        height: 40%;
        background: radial-gradient(circle at 100% 100%, rgba(0, 0, 0, 0.35), rgba(0, 0, 0, 0) 70%);
        pointer-events: none;
    }

    /* Pause / play button (bottom-right of the banner image), shown only when the banner has a video */
    .hero-video-btn {
        position: absolute;
        right: 14px;
        bottom: 14px;
        z-index: 2;
        width: 56px;
        height: 56px;
        border: 0;
        border-radius: 50%;
        background: rgba(17, 28, 25, 0.75);
        color: #ffffff;
        font-size: 1.15rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding-left: 4px; /* optically centre the play triangle */
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.25);
        cursor: pointer;
        transition: transform 0.2s ease, background 0.2s ease;
    }
    .hero-video-btn:hover,
    .hero-video-btn:focus-visible {
        background: var(--brand-700, #0e7a4f);
        transform: scale(1.06);
    }
    /* Latest News cards: the whole card opens the post (stretched title link). */
    .news-card { transition: box-shadow .2s ease; cursor: pointer; }
    .news-card:hover { box-shadow: 0 .5rem 1.25rem rgba(0, 0, 0, .12) !important; }
    .news-card:hover .card-title, .news-card-link:hover { color: var(--brand-700, #0b6b3c); }
    .news-card-link { color: inherit; text-decoration: none; }
    .news-card-link:focus-visible { outline: none; }
    .news-card:has(.news-card-link:focus-visible) { outline: 3px solid var(--brand-600, #12894f); outline-offset: 3px; }
    .news-card:hover .news-card-more { text-decoration: underline; }
    @media (prefers-reduced-motion: reduce) { .news-card { transition: none; } }
</style>
@endsection

@section('content')
    @php $currency = activeCurrency(); @endphp
    {{-- Hero Section (B2B: clean industrial hero, admin-editable via banner title/description) --}}
    @php
        $activeBanners = $banners->filter(function ($b) { return $b->translations->isNotEmpty(); });
    @endphp
    @if ($activeBanners->count() > 0)
    <section class="banner-area py-5" aria-label="{{ __('store.a11y.hero_carousel') }}" @if ($activeBanners->count() > 1) aria-roledescription="carousel" @endif>
        <div class="container h-100">
            @php
                $heroProductImage = $products->first() && $products->first()->thumbnail
                    ? $products->first()->thumbnail->image_url
                    : null;

                $bannerData = $activeBanners->map(function ($banner) use ($heroProductImage) {
                    $t = localized_translation($banner->translations);
                    return [
                        'title' => $t->title ?? __('store.home_b2b.hero_title'),
                        'subtitle' => html_entity_decode(strip_tags($t->subtitle ?? $t->description ?? __('store.home_b2b.hero_subtitle')), ENT_QUOTES, 'UTF-8'),
                        'image' => $t->image_url ?: ($heroProductImage ?: null),
                        'cta_text' => $t->cta_text ?: __('store.home_b2b.view_all_products'),
                        'cta_link' => $t->cta_link ?: route('shop.index'),
                        // Optional video set in Admin → Banners (YouTube / Vimeo / MP4); null if none
                        'video' => $banner->video_embed,
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
                    {{-- One H1 per page: the slide shown on load. Hidden slides are plain text styled the same,
                         so they do not appear as headings before the H1. --}}
                    @if ($idx === $initialIndex)
                        <h1 class="mt-0 hero-title"><span>{!! $slide['title'] !!}</span></h1>
                    @else
                        <p class="mt-0 hero-title"><span style="color: var(--brand-700);">{!! $slide['title'] !!}</span></p>
                    @endif
                    <p class="hero-subtext">{{ $slide['subtitle'] }}</p>
                    <a href="{{ $slide['cta_link'] }}" class="btn btn-primary btn-lg">{{ $slide['cta_text'] }}</a>
                </div>
                @endforeach
                <div class="col-md-6">
                    <div class="hero-media position-relative">
                        @foreach ($bannerData as $idx => $slide)
                        <div class="hero-img-slide {{ $idx === $initialIndex ? '' : 'd-none' }}" data-index="{{ $idx }}">
                            @if ($slide['image'])
                                {{-- The slide shown on load is the LCP image: load it eagerly with high priority.
                                     Hidden slides stay lazy and only load when the rotation shows them. --}}
                                {!! getResponsiveImageHtml($slide['image'], [
                                    'alt' => strip_tags($slide['title']),
                                    'class' => 'img-fluid',
                                    'sizes' => '(max-width: 768px) 100vw, 50vw',
                                    'loading' => $idx === $initialIndex ? 'eager' : 'lazy',
                                    'fetchpriority' => $idx === $initialIndex ? 'high' : null,
                                ]) !!}
                            @else
                                <div class="hero-media-fallback"><i class="fa-solid fa-bolt"></i></div>
                            @endif
                            @if (! empty($slide['video']))
                                {{-- Banner video (Admin → Banners) plays inside this box over the image,
                                     muted and looping. The player element is created by the script below
                                     only when the slide is shown, so hidden slides load nothing. --}}
                                <div class="hero-video-layer"
                                     data-video-type="{{ $slide['video']['type'] }}"
                                     data-video-src="{{ $slide['video']['src'] }}"
                                     data-video-id="{{ $slide['video']['id'] ?? '' }}"
                                     aria-hidden="true"></div>
                                <button type="button" class="hero-video-btn"
                                        aria-pressed="false"
                                        aria-label="{{ __('store.home_b2b.pause_video') }}"
                                        data-label-play="{{ __('store.home_b2b.play_video') }}"
                                        data-label-pause="{{ __('store.home_b2b.pause_video') }}">
                                    <i class="fa-solid fa-pause" aria-hidden="true"></i>
                                </button>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
    @if (collect($bannerData)->contains(fn ($s) => ! empty($s['video'])))
    {{-- Inline hero video: autoplays (muted, looping) inside the image box of the visible slide.
         The round button pauses / resumes it. No popup, no player controls, title or subtitles.
         YouTube uses the official IFrame Player API so captions can be switched off, and the
         banner image stays visible while YouTube would show its own title or pause screen. --}}
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var layers = document.querySelectorAll('#hero-rotation .hero-video-layer');
        if (!layers.length) return;

        // People who ask their system for less motion start paused (button shows "play").
        var userPaused = !!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);

        function buttonFor(layer) { return layer.parentElement.querySelector('.hero-video-btn'); }
        function isVisible(layer) { return !layer.closest('.hero-img-slide').classList.contains('d-none'); }
        function typeOf(layer) { return layer.getAttribute('data-video-type'); }
        function wantsPlay(layer) { return layer.getAttribute('data-playing') === '1'; }

        // ---- YouTube IFrame Player API (loaded once, only if a YouTube banner exists) ----
        var ytWaiting = [];
        function withYouTubeApi(cb) {
            if (window.YT && window.YT.Player) { cb(); return; }
            ytWaiting.push(cb);
            if (document.getElementById('yt-iframe-api')) return;
            var previous = window.onYouTubeIframeAPIReady;
            window.onYouTubeIframeAPIReady = function () {
                if (typeof previous === 'function') previous();
                ytWaiting.splice(0).forEach(function (fn) { fn(); });
            };
            var s = document.createElement('script');
            s.id = 'yt-iframe-api';
            s.src = 'https://www.youtube.com/iframe_api';
            s.async = true;
            document.head.appendChild(s);
        }
        function noCaptions(player) {
            // YouTube turns subtitles on for muted autoplay; remove the captions module.
            try { player.unloadModule('captions'); } catch (e) {}
            try { player.unloadModule('cc'); } catch (e) {}
            try { player.setOption('captions', 'track', {}); } catch (e) {}
        }
        function showLayer(layer, delay) {
            clearTimeout(layer._showTimer);
            layer._showTimer = setTimeout(function () {
                if (wantsPlay(layer)) layer.classList.add('is-shown');
            }, delay);
        }
        function hideLayer(layer) {
            clearTimeout(layer._showTimer);
            layer.classList.remove('is-shown');
        }

        // Create the player only when needed, so hidden slides download nothing.
        function mount(layer) {
            if (layer._mounted) return false;
            layer._mounted = true;
            var type = typeOf(layer);
            var src = layer.getAttribute('data-video-src');

            if (type === 'video') {
                var v = document.createElement('video');
                v.muted = true; v.loop = true; v.playsInline = true; v.autoplay = true;
                v.setAttribute('muted', ''); v.setAttribute('playsinline', '');
                v.preload = 'auto';
                v.src = src;
                layer.appendChild(v);
                layer.classList.add('is-shown');
            } else if (type === 'vimeo') {
                var f = document.createElement('iframe');
                f.src = src; // background mode: autoplay, muted, loop, no player UI
                f.setAttribute('allow', 'autoplay; encrypted-media; picture-in-picture');
                f.setAttribute('tabindex', '-1');
                f.setAttribute('title', '');
                f.setAttribute('frameborder', '0');
                layer.appendChild(f);
                layer.classList.add('is-shown');
            } else if (type === 'youtube') {
                var id = layer.getAttribute('data-video-id');
                var holder = document.createElement('div');
                layer.appendChild(holder);
                withYouTubeApi(function () {
                    layer._yt = new YT.Player(holder, {
                        host: 'https://www.youtube-nocookie.com',
                        videoId: id,
                        playerVars: {
                            autoplay: 1, mute: 1, loop: 1, playlist: id, controls: 0, playsinline: 1,
                            rel: 0, modestbranding: 1, iv_load_policy: 3, disablekb: 1, fs: 0,
                            cc_load_policy: 0, enablejsapi: 1, origin: window.location.origin
                        },
                        events: {
                            onReady: function (e) {
                                var frame = e.target.getIframe();
                                frame.setAttribute('tabindex', '-1');
                                frame.setAttribute('title', '');
                                e.target.mute();
                                noCaptions(e.target);
                                if (wantsPlay(layer)) { e.target.playVideo(); } else { e.target.pauseVideo(); }
                            },
                            onApiChange: function (e) { noCaptions(e.target); },
                            onStateChange: function (e) {
                                if (e.data === YT.PlayerState.PLAYING) {
                                    noCaptions(e.target);
                                    // First start: wait until the YouTube title overlay is gone.
                                    showLayer(layer, layer._started ? 400 : 2200);
                                    layer._started = true;
                                } else if (e.data === YT.PlayerState.PAUSED || e.data === YT.PlayerState.ENDED) {
                                    hideLayer(layer);
                                }
                            }
                        }
                    });
                });
            }
            return true;
        }

        function command(layer, play) {
            var type = typeOf(layer);
            if (type === 'video') {
                var v = layer.querySelector('video');
                if (!v) return;
                if (play) { var p = v.play(); if (p && p.catch) p.catch(function () {}); } else { v.pause(); }
            } else if (type === 'youtube') {
                if (!play) hideLayer(layer);
                if (!layer._yt || typeof layer._yt.playVideo !== 'function') return; // not ready yet: onReady applies it
                if (play) { layer._yt.playVideo(); } else { layer._yt.pauseVideo(); }
            } else if (type === 'vimeo') {
                var f = layer.querySelector('iframe');
                if (f && f.contentWindow) f.contentWindow.postMessage(JSON.stringify({ method: play ? 'play' : 'pause' }), '*');
            }
        }

        function setState(layer, playing) {
            layer.setAttribute('data-playing', playing ? '1' : '0');
            var btn = buttonFor(layer);
            if (!btn) return;
            btn.setAttribute('aria-pressed', playing ? 'false' : 'true');
            btn.setAttribute('aria-label', playing ? btn.getAttribute('data-label-pause') : btn.getAttribute('data-label-play'));
            btn.querySelector('i').className = playing ? 'fa-solid fa-pause' : 'fa-solid fa-play';
        }

        function play(layer) {
            setState(layer, true);
            if (!mount(layer)) command(layer, true); // a new player starts by itself
        }
        function pause(layer) {
            setState(layer, false);
            command(layer, false);
        }

        layers.forEach(function (layer) {
            if (isVisible(layer) && !userPaused) { play(layer); } else { setState(layer, false); }

            var btn = buttonFor(layer);
            if (btn) {
                btn.addEventListener('click', function () {
                    if (wantsPlay(layer)) {
                        userPaused = true;
                        pause(layer);
                    } else {
                        userPaused = false;
                        play(layer);
                    }
                });
            }
        });

        // When the slideshow changes slide: stop the old video, start the new one (unless paused).
        document.addEventListener('hero:slide', function () {
            layers.forEach(function (layer) {
                if (isVisible(layer)) {
                    if (userPaused) { setState(layer, false); } else { play(layer); }
                } else if (wantsPlay(layer)) {
                    pause(layer);
                }
            });
        });
    });
    </script>
    @endif
    @if ($activeBanners->count() > 1)
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var slides = document.querySelectorAll('#hero-rotation .hero-slide');
        var imgs = document.querySelectorAll('#hero-rotation .hero-img-slide');
        var total = slides.length;
        var current = {{ $initialIndex }};
        if (total <= 1) return;
        var timer = null;
        function advance() {
            // Keep a slide whose video is playing on screen; move on once it is paused.
            if (imgs[current].querySelector('.hero-video-layer[data-playing="1"]')) return;
            var next = (current + 1) % total;
            slides[current].classList.add('d-none');
            imgs[current].classList.add('d-none');
            slides[next].classList.remove('d-none');
            imgs[next].classList.remove('d-none');
            current = next;
            // Tell the hero video script so it can start / stop videos.
            document.dispatchEvent(new CustomEvent('hero:slide', { detail: { index: next } }));
        }
        function start() { timer = setInterval(advance, 60000); }
        function stop() { clearInterval(timer); timer = null; }

        // Respect users who ask for less motion: start paused.
        var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        var pauseBtn = document.getElementById('hero-pause');
        function setPaused(paused) {
            if (paused) { stop(); } else if (!timer) { start(); }
            if (!pauseBtn) return;
            pauseBtn.setAttribute('aria-pressed', paused ? 'true' : 'false');
            pauseBtn.setAttribute('aria-label', paused ? pauseBtn.dataset.labelPlay : pauseBtn.dataset.labelPause);
            pauseBtn.querySelector('i').className = paused ? 'fa-solid fa-play' : 'fa-solid fa-pause';
        }
        if (pauseBtn) {
            pauseBtn.addEventListener('click', function () {
                setPaused(timer !== null);
            });
        }
        setPaused(reduceMotion);
    });
    </script>
    @endif
    </section>
    @endif
    {{-- Hero Section End --}}

    {{-- Product Categories Grid (all active categories, admin-managed): 4 per row on desktop,
         2 on tablet and phone; a partial last row is centred so no card is left alone at the edge. --}}
    <section class="cat-slider animate-on-scroll">
        <div class="container">
            <h2 class="text-start pb-3 sec-heading">{{ __('store.home_b2b.categories') }}</h2>
            <p class="text-muted mb-4">{{ __('store.home_b2b.categories_subtitle') }}</p>
            <div class="row justify-content-center">
                @foreach($categories as $category)
                    @php
                        $catName = localized_translation_value($category->translations, 'name', $category->slug);
                    @endphp
                    <div class="col-6 col-lg-3 mb-4">
                        <div class="cat-card h-100">
                            <a href="{{ route('category.show', $category->slug) }}" class="text-decoration-none d-block h-100">
                                @php
                                    $catRaw = (string) localized_translation_value($category->translations, 'image_url', '');
                                    $catRel = '';
                                    $catImgUrl = '';

                                    if ($catRaw !== '' && $catRaw !== 'default.jpg') {
                                        if (filter_var($catRaw, FILTER_VALIDATE_URL)) {
                                            // Seeder fallback stored a full remote URL.
                                            $catImgUrl = $catRaw;
                                        } else {
                                            $disk = \Illuminate\Support\Facades\Storage::disk('public');
                                            $candidate = ltrim($catRaw, '/');

                                            if ($disk->exists($candidate)) {
                                                $catRel = $candidate;
                                            } elseif ($disk->exists('categories/' . basename($candidate))) {
                                                // Tolerate rows that only stored the bare file name.
                                                $catRel = 'categories/' . basename($candidate);
                                            }
                                        }
                                    }

                                    if ($catImgUrl === '' && $catRel !== '') {
                                        // asset() honors the forced app.url root, so it
                                        // renders https://<domain>/storage/... not the IP.
                                        $catImgUrl = asset('storage/' . $catRel);
                                    }
                                @endphp
                                <div class="catcard-img">
                                    @if ($catImgUrl !== '')
                                        <img src="{{ $catImgUrl }}"
                                             alt="{{ $catName }}" class="img-fluid" loading="lazy" decoding="async"{!! image_size_attrs($catRel) !!}>
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
                            <a href="{{ asset('storage/' . $certification->document_url) }}" target="_blank" rel="noopener" class="text-decoration-none" data-track="file_download" data-track-location="home_certifications">
                        @endif
                            @if ($certification->image_url)
                                <img src="{{ asset('storage/' . $certification->image_url) }}" alt="{{ $certName }}" loading="lazy" decoding="async"{!! image_size_attrs($certification->image_url) !!} style="max-height:70px; max-width:120px; width:auto; height:auto; object-fit:contain;">
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
            <div class="product-slider" id="trendingTrack" role="region" aria-roledescription="carousel" aria-label="{{ __('store.a11y.products_carousel') }}" tabindex="0">
                @foreach ($products as $product)
                    <div class="product-slide">
                        @include('themes.xylo.partials.product-card-b2b', ['product' => $product])
                    </div>
                @endforeach
            </div>

            <!-- Custom Arrows -->
            <div class="custom-arrows">
                <button type="button" class="prev" aria-label="{{ __('store.a11y.previous') }}" aria-controls="trendingTrack"><i class="fa-solid fa-chevron-left" aria-hidden="true"></i></button>
                <button type="button" class="next" aria-label="{{ __('store.a11y.next') }}" aria-controls="trendingTrack"><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></button>
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
                        $newsTitle = localized_translation_value($newsPost->translations, 'title') ?: readable_slug($newsPost->slug);
                        $newsExcerpt = localized_translation_value($newsPost->translations, 'excerpt', '');
                    @endphp
                    <div class="col-md-4 mb-4">
                        {{-- Whole card is one link: the title link is stretched over the card (see .news-card styles above). --}}
                        <div class="card h-100 shadow-sm news-card">
                            @if ($newsCover = $newsPost->coverPath())
                                <img src="{{ asset('storage/' . $newsCover) }}" class="card-img-top" alt="" loading="lazy" decoding="async"{!! image_size_attrs($newsCover) !!} style="height:180px; width:100%; object-fit:cover;">
                            @endif
                            <div class="card-body">
                                <h3 class="card-title"><a href="{{ route('news.show', $newsPost->slug) }}" class="news-card-link stretched-link">{{ $newsTitle }}</a></h3>
                                <p class="card-text text-muted small">{{ \Illuminate\Support\Str::limit($newsExcerpt, 90) }}</p>
                                <span class="btn btn-link p-0 news-card-more" aria-hidden="true">{{ __('store.news.read_more') }}</span>
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
