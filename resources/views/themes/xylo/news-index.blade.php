@extends('themes.xylo.layouts.master')

@section('title', __('store.news.title'))

@section('content')
<div class="container py-5">
    <div class="text-center mb-5">
        <h1 class="sec-heading">{{ __('store.news.title') }}</h1>
    </div>

    <div class="row">
        @forelse ($posts as $post)
            @php
                    $postTitle = localized_translation_value($post->translations, 'title', $post->slug);
                    $postExcerpt = localized_translation_value($post->translations, 'excerpt', '');
            @endphp
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    @if ($post->image_url)
                        <img src="{{ Storage::url($post->image_url) }}" class="card-img-top" alt="{{ $postTitle }}" loading="lazy" style="height:190px; object-fit:cover;">
                    @endif
                    <div class="card-body">
                        <small class="text-muted">{{ \format_date($post->created_at) }}</small>
                        <h5 class="card-title mt-1">{{ $postTitle }}</h5>
                        <p class="card-text text-muted">{{ \Illuminate\Support\Str::limit($postExcerpt, 100) }}</p>
                        <a href="{{ route('news.show', $post->slug) }}" class="btn btn-link p-0">{{ __('store.news.read_more') }}</a>
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
