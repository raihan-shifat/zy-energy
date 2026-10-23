@extends('themes.xylo.layouts.master')

@section('title', page_title(__('store.news.title')))
@section('meta_description', __('store.news.meta_description', ['site' => getSiteSettings()?->site_name ?: config('app.name')]))
{{-- Paginated list pages (?page=2 ...) stay out of the index; canonical points to /news. --}}
@if (! empty(request()->query()))
    @section('robots', 'noindex, follow')
@endif

@section('css')
<style>
    /* News list cards: the whole card opens the post (stretched title link). */
    .news-card { transition: box-shadow .2s ease, transform .2s ease; }
    .news-card:hover { box-shadow: 0 .5rem 1.25rem rgba(0, 0, 0, .12) !important; }
    /* Title keeps its normal heading look (no default link blue/underline). */
    .news-card .news-card-link { color: inherit; text-decoration: none; }
    .news-card:hover .news-card-link { color: var(--brand-700, #0f6b45); }
    .news-card, .news-card-link { cursor: pointer; }
    .news-card-link:focus-visible { outline: 2px solid var(--brand-700, #0f6b45); outline-offset: 2px; }
    @media (prefers-reduced-motion: reduce) { .news-card { transition: none; } }
</style>
@endsection

@section('content')
<div class="container py-5">
    <div class="text-center mb-5">
        <h1 class="sec-heading">{{ __('store.news.title') }}</h1>
    </div>

    <div class="row">
        @forelse ($posts as $post)
            @php
                    $postTitle = localized_translation_value($post->translations, 'title') ?: readable_slug($post->slug);
                    $postExcerpt = localized_translation_value($post->translations, 'excerpt', '');
            @endphp
            <div class="col-md-4 mb-4">
                {{-- Whole card is one link: the title link is stretched over the card (see .news-card styles below). --}}
                <div class="card h-100 shadow-sm news-card">
                    {{-- Cover in a fixed 16:9 box; a placeholder when the post has no image. --}}
                    @php $postCover = $post->coverPath(); @endphp
                    <div class="card-img-top" style="aspect-ratio: 16 / 9; background: var(--surface-2, #f4f7f5); overflow: hidden;">
                        <img src="{{ $postCover ? asset('storage/' . $postCover) : asset('images/product-placeholder.svg') }}" alt="{{ $postCover ? $postTitle : '' }}" loading="lazy" width="640" height="360" style="width:100%; height:100%; object-fit:{{ $postCover ? 'contain' : 'cover' }};">
                    </div>
                    <div class="card-body">
                        <small class="text-muted">{{ \format_date($post->created_at) }}</small>
                        <h5 class="card-title mt-1"><a href="{{ route('news.show', $post->slug) }}" class="news-card-link stretched-link">{{ $postTitle }}</a></h5>
                        <p class="card-text text-muted">{{ \Illuminate\Support\Str::limit($postExcerpt, 100) }}</p>
                        <span class="btn btn-link p-0 news-card-more" aria-hidden="true">{{ __('store.news.read_more') }}</span>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-muted">{{ __('store.news.no_posts') }}</p>
        @endforelse
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $posts->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
