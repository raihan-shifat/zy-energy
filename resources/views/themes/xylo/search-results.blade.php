@extends('themes.xylo.layouts.master')

@section('content')
    <section class="banner-area inner-banner pt-5 animate__animated animate__fadeIn productinnerbanner">
        <div class="container h-100">
            <div class="row">
                <div class="col-md-8">
                    <div class="breadcrumbs">
                        <a href="{{ url('/') }}">{{ __('store.home_b2b.view_all_products') }}</a> <i class="fa fa-angle-right"></i> {{ __('store.checkout.breadcrumb_checkout') }}
                    </div>
                    <h2 class="mt-3">{{ __('store.checkout.breadcrumb_checkout') }} "{{ $query }}"</h2>
                </div>
            </div>
        </div>
    </section>

    <div class="container py-5">
        @if($products->count() > 0)
            <div class="row">
                @foreach($products as $product)
                    <div class="col-md-3 mb-4">
                        @include('themes.xylo.partials.product-card-b2b', ['product' => $product])
                    </div>
                @endforeach
            </div>

            {{ $products->links() }}
        @else
            <div class="text-center py-5">
                <i class="fa-solid fa-search fa-3x text-muted mb-3"></i>
                <p class="text-muted">{{ __('store.search.no_products_found') }}</p>
                <a href="{{ route('shop.index') }}" class="btn btn-primary mt-2">{{ __('store.home_b2b.view_all_products') }}</a>
            </div>
        @endif
    </div>
@endsection
