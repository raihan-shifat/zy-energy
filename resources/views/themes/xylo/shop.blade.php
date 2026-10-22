@extends('themes.xylo.layouts.master')

@section('title', __('store.footer.products'))
{{-- Filter, sort and page URLs: keep out of the index, canonical points to the clean URL. --}}
@if (! empty(request()->query()))
    @section('robots', 'noindex, follow')
@endif

@section('content')
    <section class="products-home py-5 mb-5 main-shop">
    <div class="container">
        <h1 class="visually-hidden">{{ __('store.a11y.products') }}</h1>
        <div class="row">
            {{-- Filters (per-category config-driven) --}}
            <aside class="col-md-3 d-none d-lg-inline">
                <div class="sidebar" id="filterSidebar">
                    <form method="GET" id="shop-filter-form" action="{{ route('shop.index') }}">
                        <h2 class="filter-title mb-3" id="shop-filter-category">{{ __('store.shop.category') }}</h2>
                        <select name="category" class="form-select mb-3" aria-labelledby="shop-filter-category">
                            <option value="all">{{ __('store.shop.all_categories') }}</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                    {{ $category->translation->name ?? $category->slug }}
                                </option>
                            @endforeach
                        </select>

                        {{-- Per-category dynamic filters --}}
                        @foreach ($activeFilters as $filter)
                            <h2 class="filter-title mb-2 mt-3" id="shop-filter-{{ $filter['field'] }}">{{ $filter['label'] }}</h2>
                            @if ($filter['type'] === 'checkbox_multi' && !empty($filter['values']))
                                <div class="mb-3 ps-1" role="group" aria-labelledby="shop-filter-{{ $filter['field'] }}">
                                    @foreach ($filter['values'] as $value)
                                        @php
                                            $isChecked = is_array(request($filter['field'])) && in_array($value, request($filter['field']));
                                        @endphp
                                        <div class="form-check">
                                            <input class="form-check-input"
                                                   type="checkbox"
                                                   name="{{ $filter['field'] }}[]"
                                                   value="{{ $value }}"
                                                   id="flt_{{ $filter['field'] }}_{{ $loop->index }}"
                                                   {{ $isChecked ? 'checked' : '' }}>
                                            <label class="form-check-label small" for="flt_{{ $filter['field'] }}_{{ $loop->index }}">{{ $value }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @endforeach

                        {{-- Series filter — commented out, restore later if needed.
                        <h5 class="mb-3">{{ __('store.shop.series') }}</h5>
                        <select name="series" class="form-select mb-3">
                            <option value="all">{{ __('store.shop.all_series') }}</option>
                            @foreach($seriesValues as $series)
                                <option value="{{ $series }}" {{ request('series') == $series ? 'selected' : '' }}>{{ $series }}</option>
                            @endforeach
                        </select>
                        --}}

                        <h2 class="filter-title mb-3" id="shop-filter-sort">{{ __('store.shop.sort_by') }}</h2>
                        <select name="sort" class="form-select mb-3" aria-labelledby="shop-filter-sort">
                            <option value="">{{ __('store.shop.newest') }}</option>
                            <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>{{ __('store.shop.name_asc') }}</option>
                            <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>{{ __('store.shop.name_desc') }}</option>
                        </select>

                        <button type="submit" class="btn btn-primary w-100">{{ __('store.shop.filter') }}</button>

                        {{-- Reset button: shows if any filter param is active --}}
                        @php
                            $hasActiveFilters = request()->hasAny(['category', 'sort', ...collect($activeFilters)->pluck('field')->toArray()]);
                        @endphp
                        @if ($hasActiveFilters)
                            <a href="{{ route('shop.index') }}" class="btn btn-outline-secondary w-100 mt-2">{{ __('store.shop.reset') }}</a>
                        @endif
                    </form>
                </div>
            </aside>
            <div class="col-md-9">
                <h2 class="visually-hidden">{{ __('store.a11y.products') }}</h2>
                <div class="row" id="productList">
                    @forelse ($products as $product)
                        <div class="col-lg-4 col-md-6 col-sm-6 mb-4">
                            @include('themes.xylo.partials.product-card-b2b', ['product' => $product])
                        </div>
                    @empty
                        <p class="text-muted">{{ __('store.shop.no_products_found') }}</p>
                    @endforelse
                </div>
                <div class="paginations d-flex justify-content-center align-items-center mt-5">
                    {{ $products->links() }}
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
