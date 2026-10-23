{{-- Product image gallery (product detail page).
     Desktop: main image in a 1:1 box with prev/next arrows and a thumbnail row below.
     Mobile: swipeable carousel (CSS scroll-snap) with dots. Click/Enter opens a lightbox.
     One image: no thumbnails, arrows or dots. No images: placeholder in the same box.
     Styles are scoped to .pg; colours come from the site's design-system variables.
     Expects $product (images loaded) and $galleryName (alt text). --}}
@php
    $galleryImages = $product->images->values();
    $galleryCount = $galleryImages->count();
    $galleryName = $galleryName ?: __('store.category.product_name_not_available');
@endphp

<div class="pg" data-pg data-count="{{ $galleryCount }}" @if ($galleryCount > 1) data-multi @endif>
    <div class="pg-stage">
        <div class="pg-track" @if ($galleryCount > 1) tabindex="0" role="region" aria-roledescription="carousel" aria-label="{{ __('store.product_detail.gallery_label', ['name' => $galleryName]) }}" @endif>
            @forelse ($galleryImages as $image)
                <div class="pg-slide" @if ($galleryCount > 1) role="group" aria-roledescription="slide" aria-label="{{ __('store.product_detail.gallery_image_n', ['n' => $loop->iteration, 'total' => $galleryCount]) }}" @endif>
                    <button type="button" class="pg-open" data-pg-open="{{ $loop->index }}"
                            data-full="{{ asset('storage/'.$image->image_url) }}"
                            aria-label="{{ __('store.product_detail.gallery_open', ['n' => $loop->iteration]) }}">
                        {!! getResponsiveImageHtml($image->image_url, [
                            'alt' => $galleryCount > 1
                                ? $galleryName.' – '.__('store.product_detail.gallery_image_n', ['n' => $loop->iteration, 'total' => $galleryCount])
                                : $galleryName,
                            'class' => 'pg-img',
                            'sizes' => '(max-width: 768px) 100vw, 50vw',
                            // First image is above the fold (LCP): load it right away.
                            'loading' => $loop->first ? 'eager' : 'lazy',
                            'fetchpriority' => $loop->first ? 'high' : null,
                        ]) !!}
                    </button>
                </div>
            @empty
                <div class="pg-slide">
                    <img src="{{ asset('images/product-placeholder.svg') }}" alt="{{ $galleryName }}" class="pg-img" width="800" height="600">
                </div>
            @endforelse
        </div>

        @if ($galleryCount > 1)
            <button type="button" class="pg-nav pg-prev" data-pg-prev aria-label="{{ __('store.product_detail.gallery_prev') }}"><i class="fa fa-angle-left" aria-hidden="true"></i></button>
            <button type="button" class="pg-nav pg-next" data-pg-next aria-label="{{ __('store.product_detail.gallery_next') }}"><i class="fa fa-angle-right" aria-hidden="true"></i></button>
            <div class="pg-dots" aria-hidden="true">
                @foreach ($galleryImages as $image)
                    <span class="pg-dot {{ $loop->first ? 'is-active' : '' }}"></span>
                @endforeach
            </div>
        @endif
    </div>

    @if ($galleryCount > 1)
        <div class="pg-thumbs">
            @foreach ($galleryImages as $image)
                <button type="button" class="pg-thumb {{ $loop->first ? 'is-active' : '' }}" data-pg-go="{{ $loop->index }}"
                        aria-label="{{ __('store.product_detail.gallery_show', ['n' => $loop->iteration]) }}"
                        @if ($loop->first) aria-current="true" @endif>
                    <img src="{{ getResponsiveImageUrl($image->image_url, 'thumbnail') }}" alt="" loading="lazy" decoding="async" width="96" height="96">
                </button>
            @endforeach
        </div>
    @endif

    @if ($galleryCount > 0)
        <div class="pg-lightbox" data-pg-lightbox hidden role="dialog" aria-modal="true" aria-label="{{ __('store.product_detail.gallery_label', ['name' => $galleryName]) }}">
            <button type="button" class="pg-lb-close" data-pg-close aria-label="{{ __('store.product_detail.gallery_close') }}"><i class="fa fa-times" aria-hidden="true"></i></button>
            @if ($galleryCount > 1)
                <button type="button" class="pg-nav pg-prev" data-pg-lb-prev aria-label="{{ __('store.product_detail.gallery_prev') }}"><i class="fa fa-angle-left" aria-hidden="true"></i></button>
                <button type="button" class="pg-nav pg-next" data-pg-lb-next aria-label="{{ __('store.product_detail.gallery_next') }}"><i class="fa fa-angle-right" aria-hidden="true"></i></button>
            @endif
            <img class="pg-lb-img" src="" alt="">
            @if ($galleryCount > 1)<p class="pg-lb-count" aria-live="polite"></p>@endif
        </div>
    @endif
</div>

<style>
    /* Size: a 1:1 box that follows the column width. Desktop column is col-lg-5, the box is
       capped at 520px; below lg the gallery is centred at max 560px; phones use the full width. */
    .pg { --pg-gap: 12px; --pg-nav: 40px; --pg-nav-inset: 8px; width: 100%; max-width: 560px; margin-inline: auto; }
    /* With arrows, keep the photo clear of them: side padding = arrow + inset on both sides. */
    .pg[data-multi] .pg-open { padding-inline: calc(var(--pg-nav) + var(--pg-nav-inset) * 2); }
    .pg-stage { position: relative; }
    .pg-track {
        display: flex; overflow-x: auto; scroll-snap-type: x mandatory; scroll-behavior: smooth;
        scrollbar-width: none; border: 1px solid var(--line, #dfe7e2); border-radius: var(--radius-lg, 16px);
        background: var(--surface-2, #f4f7f5);
    }
    .pg-track::-webkit-scrollbar { display: none; }
    .pg-slide { flex: 0 0 100%; min-width: 0; scroll-snap-align: start; aspect-ratio: 1 / 1; display: flex; overflow: hidden; }
    @media (min-width: 992px) { .pg-slide { max-height: 520px; } }
    /* Stacked below lg: space before the title that follows. */
    @media (max-width: 991.98px) { .pg { margin-bottom: 24px; } }
    .pg-open { all: unset; box-sizing: border-box; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; padding: 16px; cursor: zoom-in; }
    .pg-slide picture, .pg-slide > img { width: 100%; height: 100%; min-height: 0; display: flex; align-items: center; justify-content: center; }
    .pg-slide > img { padding: 16px; box-sizing: border-box; }
    .pg-img { width: 100%; height: 100%; max-width: 100%; max-height: 100%; object-fit: contain; }
    .pg-track:focus-visible, .pg-open:focus-visible, .pg-thumb:focus-visible, .pg-nav:focus-visible, .pg-lb-close:focus-visible {
        outline: 3px solid var(--brand-600, #0e7a4f); outline-offset: 2px;
    }
    .pg-nav {
        position: absolute; top: 50%; transform: translateY(-50%); width: var(--pg-nav); height: var(--pg-nav); border-radius: 50%;
        border: 1px solid var(--line, #dfe7e2); background: var(--surface, #fff); color: var(--brand-700, #0e7a4f);
        box-shadow: var(--shadow-sm, 0 1px 3px rgba(0,0,0,.08)); display: flex; align-items: center; justify-content: center;
        font-size: 20px; cursor: pointer; z-index: 2; transition: background .15s, color .15s;
    }
    .pg-nav:hover { background: var(--brand-700, #0e7a4f); color: #fff; }
    .pg-stage > .pg-prev { left: var(--pg-nav-inset); }
    .pg-stage > .pg-next { right: var(--pg-nav-inset); }
    .pg-lightbox .pg-prev { left: 12px; }
    .pg-lightbox .pg-next { right: 12px; }
    .pg-lightbox .pg-nav { width: 44px; height: 44px; }
    .pg-dots { display: none; justify-content: center; gap: 6px; margin-top: 10px; }
    .pg-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--line, #dfe7e2); transition: background .2s, transform .2s; }
    .pg-dot.is-active { background: var(--brand-700, #0e7a4f); transform: scale(1.25); }
    .pg-thumbs { display: flex; gap: 10px; margin-top: var(--pg-gap); overflow-x: auto; padding: 3px 2px; }
    .pg-thumb {
        flex: 0 0 auto; width: 72px; height: 72px; padding: 0; overflow: hidden; border-radius: var(--radius, 10px);
        border: 2px solid var(--line, #dfe7e2); background: var(--surface-2, #f4f7f5); cursor: pointer; opacity: .8; transition: border-color .15s, opacity .15s;
    }
    .pg-thumb:hover { opacity: 1; }
    .pg-thumb.is-active { border-color: var(--brand-700, #0e7a4f); opacity: 1; }
    .pg-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .pg-lightbox { position: fixed; inset: 0; z-index: 2000; background: rgba(10, 20, 15, .92); display: flex; align-items: center; justify-content: center; padding: 56px 16px; }
    .pg-lightbox[hidden] { display: none; }
    .pg-lb-img { max-width: 100%; max-height: 100%; object-fit: contain; user-select: none; touch-action: pan-y; }
    .pg-lb-close { position: absolute; top: 12px; right: 12px; width: 44px; height: 44px; border-radius: 50%; border: 0; background: rgba(255,255,255,.12); color: #fff; font-size: 22px; cursor: pointer; }
    .pg-lb-count { position: absolute; bottom: 12px; left: 0; right: 0; text-align: center; color: #fff; margin: 0; font-size: var(--fs-small, 14px); }
    @media (max-width: 767.98px) {
        .pg { --pg-nav: 32px; --pg-nav-inset: 6px; max-width: none; }
        .pg-stage > .pg-nav { font-size: 16px; }
        .pg-dots { display: flex; }
        .pg-thumb { width: 56px; height: 56px; }
    }
    @media (prefers-reduced-motion: reduce) { .pg-track { scroll-behavior: auto; } }
</style>

<script>
(function () {
    document.querySelectorAll('[data-pg]').forEach(function (gallery) {
        if (gallery.dataset.pgReady) return;
        gallery.dataset.pgReady = '1';

        const track = gallery.querySelector('.pg-track');
        const slides = Array.from(gallery.querySelectorAll('.pg-slide'));
        const thumbs = Array.from(gallery.querySelectorAll('.pg-thumb'));
        const dots = Array.from(gallery.querySelectorAll('.pg-dot'));
        const lightbox = gallery.querySelector('[data-pg-lightbox]');
        const count = slides.length;
        const hasImages = Number(gallery.dataset.count) > 0;
        let current = 0;
        let lbIndex = 0;
        let lastFocus = null;

        function go(index) {
            current = (index + count) % count;
            track.scrollTo({ left: current * track.clientWidth });
            mark();
        }

        function mark() {
            thumbs.forEach((t, i) => {
                t.classList.toggle('is-active', i === current);
                if (i === current) { t.setAttribute('aria-current', 'true'); } else { t.removeAttribute('aria-current'); }
            });
            dots.forEach((d, i) => d.classList.toggle('is-active', i === current));
        }

        // Keep thumbnails/dots in sync when the user swipes or scrolls the track.
        let ticking = false;
        track.addEventListener('scroll', function () {
            if (ticking) return;
            ticking = true;
            requestAnimationFrame(function () {
                ticking = false;
                const index = Math.round(track.scrollLeft / Math.max(track.clientWidth, 1));
                if (index !== current && index >= 0 && index < count) { current = index; mark(); }
            });
        }, { passive: true });

        gallery.querySelectorAll('[data-pg-go]').forEach(b => b.addEventListener('click', () => go(Number(b.dataset.pgGo))));
        const prev = gallery.querySelector('[data-pg-prev]');
        const next = gallery.querySelector('[data-pg-next]');
        if (prev) prev.addEventListener('click', () => go(current - 1));
        if (next) next.addEventListener('click', () => go(current + 1));
        track.addEventListener('keydown', function (e) {
            if (count < 2) return;
            if (e.key === 'ArrowLeft') { e.preventDefault(); go(current - 1); }
            if (e.key === 'ArrowRight') { e.preventDefault(); go(current + 1); }
        });
        window.addEventListener('resize', () => { track.scrollLeft = current * track.clientWidth; });

        if (!hasImages || !lightbox) return;

        // ---------- Lightbox ----------
        const lbImg = lightbox.querySelector('.pg-lb-img');
        const lbCount = lightbox.querySelector('.pg-lb-count');
        const openers = Array.from(gallery.querySelectorAll('[data-pg-open]'));
        const total = openers.length;

        function showLb(index) {
            lbIndex = (index + total) % total;
            const opener = openers[lbIndex];
            lbImg.src = opener.dataset.full;
            lbImg.alt = opener.querySelector('img') ? opener.querySelector('img').alt : '';
            if (lbCount) lbCount.textContent = (lbIndex + 1) + ' / ' + total;
        }
        function openLb(index) {
            lastFocus = document.activeElement;
            showLb(index);
            lightbox.hidden = false;
            document.body.style.overflow = 'hidden';
            lightbox.querySelector('[data-pg-close]').focus();
        }
        function closeLb() {
            lightbox.hidden = true;
            document.body.style.overflow = '';
            go(lbIndex);
            if (lastFocus) lastFocus.focus();
        }

        openers.forEach(b => b.addEventListener('click', () => openLb(Number(b.dataset.pgOpen))));
        lightbox.querySelector('[data-pg-close]').addEventListener('click', closeLb);
        const lbPrev = lightbox.querySelector('[data-pg-lb-prev]');
        const lbNext = lightbox.querySelector('[data-pg-lb-next]');
        if (lbPrev) lbPrev.addEventListener('click', () => showLb(lbIndex - 1));
        if (lbNext) lbNext.addEventListener('click', () => showLb(lbIndex + 1));
        lightbox.addEventListener('click', e => { if (e.target === lightbox) closeLb(); });
        lightbox.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') { e.preventDefault(); closeLb(); }
            if (e.key === 'ArrowLeft' && total > 1) { e.preventDefault(); showLb(lbIndex - 1); }
            if (e.key === 'ArrowRight' && total > 1) { e.preventDefault(); showLb(lbIndex + 1); }
            if (e.key === 'Tab') {
                // Keep focus inside the dialog.
                const focusables = Array.from(lightbox.querySelectorAll('button'));
                const i = focusables.indexOf(document.activeElement);
                e.preventDefault();
                focusables[(i + (e.shiftKey ? -1 : 1) + focusables.length) % focusables.length].focus();
            }
        });
        // Swipe in the lightbox (touch).
        let startX = null;
        lightbox.addEventListener('touchstart', e => { startX = e.touches[0].clientX; }, { passive: true });
        lightbox.addEventListener('touchend', e => {
            if (startX === null || total < 2) return;
            const dx = e.changedTouches[0].clientX - startX;
            startX = null;
            if (Math.abs(dx) > 40) showLb(lbIndex + (dx < 0 ? 1 : -1));
        });
    });
})();
</script>
