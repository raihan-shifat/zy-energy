@extends('themes.xylo.layouts.master')

@php
    $postTitle = localized_translation_value($post->translations, 'title') ?: readable_slug($post->slug);
    $postExcerpt = localized_translation_value($post->translations, 'excerpt');
    $postBody = localized_translation_value($post->translations, 'body', '');
    $postCover = $post->coverPath();
    // Do not repeat the excerpt when the body already starts with the same text.
    $plain = fn ($html) => trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags((string) $html), ENT_QUOTES, 'UTF-8')));
    $showExcerpt = $postExcerpt && ! str_starts_with($plain($postBody), $plain($postExcerpt));
@endphp
@section('title', page_title(\Illuminate\Support\Str::limit($postTitle, 60, '', true)))
@section('meta_description', seo_excerpt($postExcerpt ?: $postBody, 160))
@section('og_type', 'article')
@if ($postCover)
    @section('og_image', rtrim(config('seo.canonical_domain'), '/').'/storage/'.$postCover)
@endif

@section('content')
<div class="container py-5">
    <nav aria-label="{{ __('store.a11y.breadcrumb') }}">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">{{ __('store.product_detail.home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('news.index') }}">{{ __('store.news.title') }}</a></li>
            <li class="breadcrumb-item active">{{ $postTitle }}</li>
        </ol>
    </nav>

    <article class="col-md-10 mx-auto">
        <h1 class="sec-heading mb-3">{{ $postTitle }}</h1>
        <small class="text-muted d-block mb-4">{{ \format_date($post->created_at) }}</small>

        @if ($postCover)
            {{-- Fixed 16:9 box, whole image visible (same sizing approach as the product gallery). --}}
            <div class="news-cover mb-4">
                <img src="{{ asset('storage/' . $postCover) }}" alt="{{ $postTitle }}" width="1200" height="675" decoding="async" fetchpriority="high">
            </div>
        @endif

        @if ($showExcerpt)
            <p class="lead">{{ $postExcerpt }}</p>
        @endif

        <div class="news-body">
            {!! $postBody !!}
        </div>

        @php $galleryImages = $post->galleryImages(); @endphp
        @if (count($galleryImages))
            {{-- Other images of the post: 4:3 thumbnails, click opens the lightbox. --}}
            <div class="news-gallery mt-4" data-news-gallery>
                @foreach ($galleryImages as $image)
                    <button type="button" class="news-gallery-item" data-full="{{ asset('storage/'.$image->path) }}"
                            aria-label="{{ __('store.product_detail.gallery_open', ['n' => $loop->iteration]) }}">
                        <img src="{{ asset('storage/'.$image->path) }}" alt="{{ $image->alt ?: $postTitle.' – '.__('store.product_detail.gallery_image_n', ['n' => $loop->iteration, 'total' => count($galleryImages)]) }}"
                             width="400" height="300" loading="lazy" decoding="async">
                    </button>
                @endforeach
            </div>
            <div class="news-lightbox" data-news-lightbox hidden role="dialog" aria-modal="true" aria-label="{{ $postTitle }}">
                <button type="button" class="news-lb-btn news-lb-close" data-close aria-label="{{ __('store.product_detail.gallery_close') }}"><i class="fa fa-times" aria-hidden="true"></i></button>
                @if (count($galleryImages) > 1)
                    <button type="button" class="news-lb-btn news-lb-prev" data-prev aria-label="{{ __('store.product_detail.gallery_prev') }}"><i class="fa fa-angle-left" aria-hidden="true"></i></button>
                    <button type="button" class="news-lb-btn news-lb-next" data-next aria-label="{{ __('store.product_detail.gallery_next') }}"><i class="fa fa-angle-right" aria-hidden="true"></i></button>
                @endif
                <img class="news-lb-img" src="" alt="">
            </div>
        @endif

        <a href="{{ route('news.index') }}" class="btn btn-outline-primary mt-4">← {{ __('store.news.back_to_news') }}</a>
    </article>
</div>

<style>
    .news-cover { aspect-ratio: 16 / 9; max-height: 480px; width: 100%; display: flex; align-items: center; justify-content: center;
        background: var(--surface-2, #f4f7f5); border: 1px solid var(--line, #dfe7e2); border-radius: var(--radius-lg, 16px); overflow: hidden; }
    .news-cover img { width: 100%; height: 100%; object-fit: contain; }
    /* Images inserted in the body editor: never wider than the article. */
    .news-body img { max-width: 100%; height: auto; }
    .news-body figure.image { margin: 1.5rem auto; max-width: 100%; text-align: center; }
    .news-gallery { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .75rem; }
    .news-gallery-item { all: unset; cursor: zoom-in; display: block; aspect-ratio: 4 / 3; overflow: hidden; border-radius: var(--radius, 10px); background: var(--surface-2, #f4f7f5); }
    .news-gallery-item img { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform .3s ease; }
    .news-gallery-item:hover img { transform: scale(1.04); }
    .news-gallery-item:focus-visible, .news-lb-btn:focus-visible { outline: 3px solid var(--brand-600, #12894f); outline-offset: 3px; }
    .news-lightbox { position: fixed; inset: 0; z-index: 2000; background: rgba(10, 20, 15, .93); display: flex; align-items: center; justify-content: center; padding: 56px 16px; }
    .news-lightbox[hidden] { display: none; }
    .news-lb-img { max-width: 100%; max-height: 100%; object-fit: contain; }
    .news-lb-btn { position: absolute; width: 44px; height: 44px; border-radius: 50%; border: 0; background: rgba(255, 255, 255, .14); color: #fff; font-size: 22px; cursor: pointer; }
    .news-lb-close { top: 12px; right: 12px; }
    .news-lb-prev { left: 12px; top: 50%; transform: translateY(-50%); }
    .news-lb-next { right: 12px; top: 50%; transform: translateY(-50%); }
    @media (max-width: 991.98px) { .news-gallery { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (prefers-reduced-motion: reduce) { .news-gallery-item img { transition: none; } }
</style>

<script>
(function () {
    const gallery = document.querySelector('[data-news-gallery]');
    const box = document.querySelector('[data-news-lightbox]');
    if (!gallery || !box) return;
    const items = [...gallery.querySelectorAll('.news-gallery-item')];
    const img = box.querySelector('.news-lb-img');
    let index = 0, lastFocus = null, startX = null;
    const show = i => { index = (i + items.length) % items.length; img.src = items[index].dataset.full; img.alt = items[index].querySelector('img').alt; };
    const close = () => { box.hidden = true; document.body.style.overflow = ''; if (lastFocus) lastFocus.focus(); };
    items.forEach((item, i) => item.addEventListener('click', () => { lastFocus = item; show(i); box.hidden = false; document.body.style.overflow = 'hidden'; box.querySelector('[data-close]').focus(); }));
    box.querySelector('[data-close]').addEventListener('click', close);
    box.querySelector('[data-prev]')?.addEventListener('click', () => show(index - 1));
    box.querySelector('[data-next]')?.addEventListener('click', () => show(index + 1));
    box.addEventListener('click', e => { if (e.target === box) close(); });
    box.addEventListener('keydown', e => {
        if (e.key === 'Escape') { e.preventDefault(); close(); }
        else if (e.key === 'ArrowLeft' && items.length > 1) { e.preventDefault(); show(index - 1); }
        else if (e.key === 'ArrowRight' && items.length > 1) { e.preventDefault(); show(index + 1); }
        else if (e.key === 'Tab') { const f = [...box.querySelectorAll('button')]; e.preventDefault(); f[(f.indexOf(document.activeElement) + (e.shiftKey ? -1 : 1) + f.length) % f.length].focus(); }
    });
    box.addEventListener('touchstart', e => { startX = e.touches[0].clientX; }, { passive: true });
    box.addEventListener('touchend', e => { if (startX === null || items.length < 2) return; const dx = e.changedTouches[0].clientX - startX; startX = null; if (Math.abs(dx) > 40) show(index + (dx < 0 ? 1 : -1)); });
})();
</script>
@endsection
