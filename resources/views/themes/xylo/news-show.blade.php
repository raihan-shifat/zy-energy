@extends('themes.xylo.layouts.master')

@php
    $postTitle = localized_translation_value($post->translations, 'title') ?: readable_slug($post->slug);
    $postExcerpt = localized_translation_value($post->translations, 'excerpt');
    $postBody = localized_translation_value($post->translations, 'body', '');
@endphp
@section('title', page_title(\Illuminate\Support\Str::limit($postTitle, 60, '', true)))
@section('meta_description', seo_excerpt($postExcerpt ?: $postBody, 160))
@section('og_type', 'article')
@if ($post->image_url)
    @section('og_image', rtrim(config('seo.canonical_domain'), '/').'/storage/'.$post->image_url)
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

        @if ($post->image_url)
            {{-- Fixed 16:9 box, whole image visible (same sizing approach as the product gallery). --}}
            <div class="news-cover mb-4">
                <img src="{{ asset('storage/' . $post->image_url) }}" alt="{{ $postTitle }}" width="1200" height="675" decoding="async" fetchpriority="high">
            </div>
        @endif

        @if ($postExcerpt)
            <p class="lead">{{ $postExcerpt }}</p>
        @endif

        <div class="news-body">
            {!! $postBody !!}
        </div>

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
</style>
@endsection
