@extends('themes.xylo.layouts.master')

@section('title', localized_translation_value($category->translations, 'name', $category->slug))

@section('content')
<div class="container py-4">

    {{-- Breadcrumbs --}}
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('xylo.home') }}">{{ __('store.category.home') }}</a></li>
            @foreach($breadcrumbs as $crumb)
                <li class="breadcrumb-item">
                    <a href="{{ route('category.show', $crumb->slug) }}">{{ $crumb->translation->name ?? $crumb->slug }}</a>
                </li>
            @endforeach
        </ol>
    </nav>

    <h2 class="mb-3">{{ $category->translation->name ?? $category->slug }}</h2>

    {{-- Filters (config-driven) --}}
    <form method="GET" class="mb-4 d-flex flex-wrap gap-2 align-items-end">
        @foreach ($filterConfig as $filter)
            @if ($filter['field'] === 'type' && empty($filterValues['type']))
                @continue
            @endif
            @if ($filter['field'] === 'series' && empty($filterValues['series']))
                @continue
            @endif
            <div>
                <label class="form-label small mb-1">{{ $filter['label'] }}</label>
                <select name="{{ $filter['field'] }}" class="form-select">
                    <option value="">
                        {{ $filter['field'] === 'type' ? __('store.category.all') : __('store.category.all_series') }}
                    </option>
                    @foreach ($filterValues[$filter['field']] as $value)
                        <option value="{{ $value }}" {{ request($filter['field']) == $value ? 'selected' : '' }}>
                            {{ $value }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endforeach

        <div>
            <label class="form-label small mb-1">{{ __('store.category.sort_by') }}</label>
            <select name="sort" class="form-select">
                <option value="">{{ __('store.category.newest') }}</option>
                <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>{{ __('store.category.name_asc') }}</option>
                <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>{{ __('store.category.name_desc') }}</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">{{ __('store.category.filter') }}</button>
        @if (request()->has('series') || request()->has('type') || request()->has('sort'))
            <a href="{{ route('category.show', $category->slug) }}" class="btn btn-outline-secondary">{{ __('store.shop.reset') }}</a>
        @endif
    </form>

    {{-- Products --}}
    <div class="row">
        @forelse ($products as $product)
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                @include('themes.xylo.partials.product-card-b2b', ['product' => $product])
            </div>
        @empty
            <p>{{ __('store.category.no_products_found') }}</p>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="d-flex justify-content-center mt-4">
        {{ $products->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
