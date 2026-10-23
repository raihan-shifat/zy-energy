@extends('themes.xylo.layouts.master')

@section('title', page_title($translation->title ?? __('store.footer.about')))
@section('meta_description', seo_excerpt($translation->content ?? '', 160))

@section('content')
@php
    // Sections come from admin → Pages → About Us (AboutPageService, cached until saved).
    $tr = fn ($model) => localized_translation($model->translations);
    $mediaTr = fn ($m) => $m ? localized_translation($m->translations) : null;
    $link = fn (?string $url) => $url ? (str_starts_with($url, '/') ? url($url) : $url) : null;
    $lines = fn (?string $text) => array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $text)), 'strlen'));
    $imageTextCount = 0;
@endphp

@foreach ($sections as $section)
    @php
        $t = $tr($section);
        $media = $section->media;
        $image = $media->firstWhere('kind', 'image');
        $imageTr = $mediaTr($image);
        $items = collect($section->setting('items', []))->map(fn ($item, $k) => $item + (array) data_get($t?->extra, "items.$k", []));
        if (! $t && $media->isEmpty()) {
            continue; // nothing to show
        }
    @endphp

    @switch($section->type)
        @case('hero')
            <section class="ab-hero {{ $image ? 'ab-hero--image' : '' }}">
                @if ($image)
                    <img class="ab-hero-bg" src="{{ $image->publicUrl() }}" alt="{{ $imageTr?->alt ?? '' }}" {!! image_size_attrs($image->path) !!} fetchpriority="high" decoding="async">
                @endif
                <div class="container position-relative">
                    <div class="row justify-content-center text-center">
                        <div class="col-lg-9">
                            @if ($eyebrow = data_get($t?->extra, 'eyebrow'))
                                <span class="ab-eyebrow">{{ $eyebrow }}</span>
                            @endif
                            <h1 class="ab-hero-title">{{ $t?->title ?: ($translation->title ?? __('store.footer.about')) }}</h1>
                            @if ($t?->subtitle)
                                <p class="ab-hero-lead">{{ $t->subtitle }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </section>
            @break

        @case('text')
            <section class="ab-narrative">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-9 {{ $section->setting('align', 'center') === 'center' ? 'text-center' : '' }}">
                            @if ($t?->title)<h2 class="sec-heading mb-4">{{ $t->title }}</h2>@endif
                            <div class="ab-narrative-text ab-rich">{!! safe_html($t?->body) !!}</div>
                        </div>
                    </div>
                </div>
            </section>
            @break

        @case('image_text')
            @php $imageRight = $section->setting('position', 'left') === 'right'; $imageTextCount++; @endphp
            <section class="ab-section ab-imgtext {{ $imageTextCount % 2 === 0 ? 'ab-alt-bg' : '' }}">
                <div class="container">
                    <div class="row align-items-center g-4 g-lg-5">
                        @if ($image)
                            <div class="col-lg-6 {{ $imageRight ? 'order-lg-2' : '' }}">
                                <div class="ab-media-box ab-media-box--4x3">
                                    <img src="{{ $image->publicUrl() }}" alt="{{ $imageTr?->alt ?: ($t?->title ?? '') }}" {!! image_size_attrs($image->path) !!} loading="lazy" decoding="async">
                                </div>
                            </div>
                        @endif
                        <div class="{{ $image ? 'col-lg-6' : 'col-lg-9 mx-auto' }}">
                            @if ($t?->title)<h2 class="sec-heading mb-3">{{ $t->title }}</h2>@endif
                            <div class="ab-rich">{!! safe_html($t?->body) !!}</div>
                        </div>
                    </div>
                </div>
            </section>
            @break

        @case('stats')
            <section class="ab-stats">
                <div class="container">
                    @if ($t?->title)
                        <div class="text-center mb-4">
                            <h2 class="sec-heading">{{ $t->title }}</h2>
                            @if ($t->subtitle)<p class="text-muted mx-auto ab-subheading">{{ $t->subtitle }}</p>@endif
                        </div>
                    @endif
                    <div class="row g-4 justify-content-center text-center">
                        @foreach ($items as $item)
                            <div class="col-6 col-md-4 col-lg-3">
                                <div class="ab-stat">
                                    <div class="ab-stat-number" data-ab-count="{{ $item['number'] ?? '' }}">{{ $item['number'] ?? '' }}</div>
                                    <div class="ab-stat-label">{{ $item['label'] ?? '' }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
            @break

        @case('cards')
            @if ($section->setting('variant', 'feature') === 'plain')
                <section class="ab-section">
                    <div class="container">
                        @if ($t?->title)
                            <div class="text-center mb-5">
                                <h2 class="sec-heading">{{ $t->title }}</h2>
                                @if ($t->subtitle)<p class="text-muted mx-auto ab-subheading">{{ $t->subtitle }}</p>@endif
                            </div>
                        @endif
                        <div class="row g-4 justify-content-center">
                            @foreach ($items as $item)
                                @php $textLines = $lines($item['text'] ?? ''); @endphp
                                <div class="col-12 col-md-6 col-lg-4">
                                    <div class="ab-card h-100">
                                        @if (! empty($item['icon']))<div class="ab-card-icon"><i class="{{ $item['icon'] }}" aria-hidden="true"></i></div>@endif
                                        <h3 class="ab-card-title">{{ $item['title'] ?? '' }}</h3>
                                        @if (count($textLines) > 1)
                                            <ul class="ab-values-list">
                                                @foreach ($textLines as $line)
                                                    <li><i class="fa-solid fa-check me-2" aria-hidden="true"></i>{{ $line }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <p>{{ $textLines[0] ?? '' }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>
            @else
                <section class="ab-strengths">
                    <div class="container">
                        @if ($t?->title)
                            <div class="text-center mb-5">
                                <h2 class="sec-heading">{{ $t->title }}</h2>
                                @if ($t->subtitle)<p class="text-muted mx-auto ab-subheading">{{ $t->subtitle }}</p>@endif
                            </div>
                        @endif
                        <div class="row g-4">
                            @foreach ($items as $item)
                                <div class="col-12 col-md-6 col-lg-4">
                                    <div class="ab-strength-card h-100">
                                        @if (! empty($item['icon']))<div class="ab-strength-icon"><i class="{{ $item['icon'] }}" aria-hidden="true"></i></div>@endif
                                        <h3 class="ab-strength-title">{{ $item['title'] ?? '' }}</h3>
                                        <p class="mb-0">{{ $item['text'] ?? '' }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif
            @break

        @case('gallery')
            @php $images = $media->where('kind', 'image')->values(); @endphp
            @if ($images->isNotEmpty())
                <section class="ab-section ab-gallery" data-ab-gallery>
                    <div class="container">
                        @if ($t?->title)
                            <div class="text-center mb-4">
                                <h2 class="sec-heading">{{ $t->title }}</h2>
                                @if ($t->subtitle)<p class="text-muted mx-auto ab-subheading">{{ $t->subtitle }}</p>@endif
                            </div>
                        @endif
                        <div class="ab-gallery-grid">
                            @foreach ($images as $img)
                                @php $it = $mediaTr($img); @endphp
                                <figure class="ab-gallery-item">
                                    <button type="button" class="ab-gallery-open" data-ab-open="{{ $loop->index }}" data-full="{{ $img->publicUrl() }}" data-caption="{{ $it?->caption }}"
                                            aria-label="{{ __('store.product_detail.gallery_open', ['n' => $loop->iteration]) }}">
                                        <span class="ab-media-box ab-media-box--4x3">
                                            <img src="{{ getResponsiveImageUrl($img->path, 'small') }}" alt="{{ $it?->alt ?: ($it?->caption ?: ($t?->title ?? '')) }}" width="480" height="360" loading="lazy" decoding="async">
                                        </span>
                                    </button>
                                    @if ($it?->caption)<figcaption>{{ $it->caption }}</figcaption>@endif
                                </figure>
                            @endforeach
                        </div>
                    </div>
                    <div class="ab-lightbox" data-ab-lightbox hidden role="dialog" aria-modal="true" aria-label="{{ $t?->title ?: __('store.footer.about') }}">
                        <button type="button" class="ab-lb-btn ab-lb-close" data-ab-close aria-label="{{ __('store.product_detail.gallery_close') }}"><i class="fa fa-times" aria-hidden="true"></i></button>
                        @if ($images->count() > 1)
                            <button type="button" class="ab-lb-btn ab-lb-prev" data-ab-prev aria-label="{{ __('store.product_detail.gallery_prev') }}"><i class="fa fa-angle-left" aria-hidden="true"></i></button>
                            <button type="button" class="ab-lb-btn ab-lb-next" data-ab-next aria-label="{{ __('store.product_detail.gallery_next') }}"><i class="fa fa-angle-right" aria-hidden="true"></i></button>
                        @endif
                        <figure class="ab-lb-figure">
                            <img class="ab-lb-img" src="" alt="">
                            <figcaption class="ab-lb-caption"></figcaption>
                        </figure>
                    </div>
                </section>
            @endif
            @break

        @case('video')
            @php $video = $media->firstWhere('kind', 'video'); $poster = $media->firstWhere('kind', 'poster'); @endphp
            @if ($video)
                <section class="ab-section ab-video">
                    <div class="container">
                        @if ($t?->title)<h2 class="sec-heading text-center mb-4">{{ $t->title }}</h2>@endif
                        <div class="ab-video-box mx-auto">
                            @if ($video->path)
                                <video controls preload="metadata" playsinline @if ($poster) poster="{{ $poster->publicUrl() }}" @endif aria-label="{{ $t?->title ?: __('store.footer.about') }}">
                                    <source src="{{ $video->publicUrl() }}" type="{{ str_ends_with(strtolower($video->path), '.webm') ? 'video/webm' : 'video/mp4' }}">
                                </video>
                            @elseif ($video->url)
                                <iframe src="{{ $video->url }}" title="{{ $t?->title ?: __('store.footer.about') }}" loading="lazy"
                                        allow="accelerometer; encrypted-media; gyroscope; picture-in-picture; fullscreen" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe>
                            @endif
                        </div>
                        @if ($t?->subtitle)<p class="text-muted text-center mt-3 mb-0">{{ $t->subtitle }}</p>@endif
                    </div>
                </section>
            @endif
            @break

        @case('cta')
            <section class="ab-cta">
                <div class="container text-center">
                    @if ($t?->title)<h2 class="ab-cta-title">{{ $t->title }}</h2>@endif
                    @if ($t?->subtitle)<p class="ab-cta-text">{{ $t->subtitle }}</p>@endif
                    <div class="d-flex flex-wrap justify-content-center gap-3">
                        @if ($t?->button_text && $section->setting('button_link'))
                            <a href="{{ $link($section->setting('button_link')) }}" class="btn btn-light btn-lg">
                                <i class="fa-regular fa-envelope me-2" aria-hidden="true"></i>{{ $t->button_text }}
                            </a>
                        @endif
                        @if (data_get($t?->extra, 'button2_text') && $section->setting('button2_link'))
                            <a href="{{ $link($section->setting('button2_link')) }}" class="btn btn-outline-light btn-lg">
                                <i class="fa-solid fa-bolt me-2" aria-hidden="true"></i>{{ data_get($t->extra, 'button2_text') }}
                            </a>
                        @endif
                    </div>
                </div>
            </section>
            @break
    @endswitch
@endforeach

<style>
    /* About page blocks added with the section editor (the original blocks keep their design-system styles). */
    .ab-hero--image { position: relative; overflow: hidden; }
    .ab-hero--image .ab-hero-bg { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    .ab-hero--image::after { content: ''; position: absolute; inset: 0; background: linear-gradient(135deg, rgba(6, 52, 43, .88), rgba(11, 91, 61, .72)); }
    .ab-hero--image .container { z-index: 1; }
    .ab-rich img { max-width: 100%; height: auto; border-radius: var(--radius, 10px); }
    .ab-rich figure.image { margin: 1.5rem auto; text-align: center; }
    .ab-rich > :last-child { margin-bottom: 0; }
    .ab-alt-bg { background: var(--surface-2); }
    .ab-media-box { display: block; overflow: hidden; border-radius: var(--radius-lg, 16px); background: var(--surface-2); }
    .ab-media-box--4x3 { aspect-ratio: 4 / 3; }
    .ab-media-box img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .ab-stats { padding: 4rem 0; background: #ffffff; }
    .ab-stat { padding: 1.5rem 1rem; border-radius: var(--radius-lg, 16px); background: var(--surface-2); height: 100%; }
    .ab-stat-number { font-size: clamp(2rem, 4vw, 2.75rem); font-weight: 800; color: var(--brand-700); line-height: 1.1; font-variant-numeric: tabular-nums; }
    .ab-stat-label { margin-top: .5rem; color: var(--ink-600); font-weight: 500; }
    .ab-gallery-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(min(100%, 240px), 1fr)); gap: 1rem; }
    .ab-gallery-item { margin: 0; }
    .ab-gallery-item figcaption { font-size: var(--fs-small, .9rem); color: var(--ink-600); margin-top: .5rem; text-align: center; }
    .ab-gallery-open { all: unset; display: block; width: 100%; cursor: zoom-in; border-radius: var(--radius-lg, 16px); }
    .ab-gallery-open img { transition: transform .3s ease; }
    .ab-gallery-open:hover img { transform: scale(1.04); }
    .ab-gallery-open:focus-visible, .ab-lb-btn:focus-visible, .ab-video-box :focus-visible { outline: 3px solid var(--brand-600, #12894f); outline-offset: 3px; }
    .ab-lightbox { position: fixed; inset: 0; z-index: 2000; background: rgba(10, 20, 15, .93); display: flex; align-items: center; justify-content: center; padding: 56px 16px; }
    .ab-lightbox[hidden] { display: none; }
    .ab-lb-figure { margin: 0; max-width: 100%; max-height: 100%; display: flex; flex-direction: column; align-items: center; }
    .ab-lb-img { max-width: 100%; max-height: calc(100vh - 150px); object-fit: contain; }
    .ab-lb-caption { color: #fff; margin-top: .75rem; text-align: center; }
    .ab-lb-btn { position: absolute; width: 44px; height: 44px; border-radius: 50%; border: 0; background: rgba(255, 255, 255, .14); color: #fff; font-size: 22px; cursor: pointer; }
    .ab-lb-close { top: 12px; right: 12px; }
    .ab-lb-prev { left: 12px; top: 50%; transform: translateY(-50%); }
    .ab-lb-next { right: 12px; top: 50%; transform: translateY(-50%); }
    .ab-video-box { max-width: 960px; aspect-ratio: 16 / 9; border-radius: var(--radius-lg, 16px); overflow: hidden; background: #000; box-shadow: var(--shadow-md); }
    .ab-video-box video, .ab-video-box iframe { width: 100%; height: 100%; border: 0; display: block; }
    @media (max-width: 767.98px) {
        .ab-section, .ab-stats { padding: 3rem 0; }
        .ab-lb-btn { width: 38px; height: 38px; font-size: 18px; }
    }
    @media (max-width: 575.98px) {
        /* Two columns on phones so a full gallery stays short. */
        .ab-gallery-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .5rem; }
        .ab-gallery-item figcaption { font-size: .8rem; }
    }
    @media (prefers-reduced-motion: reduce) { .ab-gallery-open img { transition: none; } }
</style>

<script>
(function () {
    // Stats: count up once when visible (skipped when reduced motion is preferred).
    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const counters = document.querySelectorAll('[data-ab-count]');
    if (!reduce && 'IntersectionObserver' in window) {
        const io = new IntersectionObserver(entries => entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            io.unobserve(entry.target);
            const el = entry.target;
            const m = /^(\D*)(\d[\d,]*)(.*)$/.exec(el.dataset.abCount || '');
            if (!m) return;
            const target = parseInt(m[2].replace(/,/g, ''), 10);
            const start = performance.now();
            const step = now => {
                const p = Math.min(1, (now - start) / 1200);
                el.textContent = m[1] + Math.round(target * (1 - Math.pow(1 - p, 3))).toLocaleString() + m[3];
                if (p < 1) requestAnimationFrame(step);
            };
            requestAnimationFrame(step);
        }), { threshold: 0.4 });
        counters.forEach(c => io.observe(c));
    }

    // Gallery lightbox: buttons, keyboard (arrows, Esc, focus kept inside) and swipe.
    document.querySelectorAll('[data-ab-gallery]').forEach(function (gallery) {
        const openers = [...gallery.querySelectorAll('[data-ab-open]')];
        const box = gallery.querySelector('[data-ab-lightbox]');
        const img = box.querySelector('.ab-lb-img');
        const caption = box.querySelector('.ab-lb-caption');
        let index = 0, lastFocus = null, startX = null;

        function show(i) {
            index = (i + openers.length) % openers.length;
            const o = openers[index];
            img.src = o.dataset.full;
            img.alt = o.querySelector('img').alt;
            caption.textContent = o.dataset.caption || '';
            caption.hidden = !o.dataset.caption;
        }
        function open(i) { lastFocus = document.activeElement; show(i); box.hidden = false; document.body.style.overflow = 'hidden'; box.querySelector('[data-ab-close]').focus(); }
        function close() { box.hidden = true; document.body.style.overflow = ''; if (lastFocus) lastFocus.focus(); }

        openers.forEach((o, i) => o.addEventListener('click', () => open(i)));
        box.querySelector('[data-ab-close]').addEventListener('click', close);
        const prev = box.querySelector('[data-ab-prev]'), next = box.querySelector('[data-ab-next]');
        if (prev) prev.addEventListener('click', () => show(index - 1));
        if (next) next.addEventListener('click', () => show(index + 1));
        box.addEventListener('click', e => { if (e.target === box) close(); });
        box.addEventListener('keydown', e => {
            if (e.key === 'Escape') { e.preventDefault(); close(); }
            else if (e.key === 'ArrowLeft' && openers.length > 1) { e.preventDefault(); show(index - 1); }
            else if (e.key === 'ArrowRight' && openers.length > 1) { e.preventDefault(); show(index + 1); }
            else if (e.key === 'Tab') {
                const f = [...box.querySelectorAll('button')];
                e.preventDefault();
                f[(f.indexOf(document.activeElement) + (e.shiftKey ? -1 : 1) + f.length) % f.length].focus();
            }
        });
        box.addEventListener('touchstart', e => { startX = e.touches[0].clientX; }, { passive: true });
        box.addEventListener('touchend', e => {
            if (startX === null || openers.length < 2) return;
            const dx = e.changedTouches[0].clientX - startX; startX = null;
            if (Math.abs(dx) > 40) show(index + (dx < 0 ? 1 : -1));
        });
    });
})();
</script>
@endsection
