@extends('themes.xylo.layouts.master')

@php
    $postTitle = localized_translation_value($post->translations, 'title', $post->slug);
    $postExcerpt = localized_translation_value($post->translations, 'excerpt');
    $postBody = localized_translation_value($post->translations, 'body', '');
@endphp
@section('title', $postTitle)

@section('content')
<div class="container py-5">
    <nav aria-label="breadcrumb">
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
            <img src="{{ asset('storage/' . $post->image_url) }}" alt="{{ __('store.news.title') }}" class="img-fluid rounded mb-4" style="max-height:400px; object-fit:cover; width:100%;">
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
@endsection
