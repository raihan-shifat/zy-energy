@extends('themes.xylo.layouts.master')

@section('title', page_title(__('store.news.title')))
@section('meta_description', __('store.news.meta_description', ['site' => getSiteSettings()?->site_name ?: config('app.name')]))
{{-- Paginated list pages (?page=2 ...) stay out of the index; canonical points to /news. --}}
@if (! empty(request()->query()))
    @section('robots', 'noindex, follow')
@endif

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
                <div class="card h-100 shadow-sm">
                    {{-- Cover in a fixed 16:9 box; a placeholder when the post has no image. --}}
                    @php $postCover = $post->coverPath(); @endphp
                    <div class="card-img-top" style="aspect-ratio: 16 / 9; background: var(--surface-2, #f4f7f5); overflow: hidden;">
                        <img src="{{ $postCover ? asset('storage/' . $postCover) : asset('images/product-placeholder.svg') }}" alt="{{ $postCover ? $postTitle : '' }}" loading="lazy" width="640" height="360" style="width:100%; height:100%; object-fit:{{ $postCover ? 'contain' : 'cover' }};">
                    </div>
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
