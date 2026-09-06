@extends('themes.xylo.layouts.master')

@section('title', __('store.checkout.place_order') . ' - ' . config('app.name'))

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 text-center">
                <div class="mb-4">
                    <i class="fa-solid fa-circle-check fa-5x" style="color: #28a745;"></i>
                </div>
                <h1 class="sec-heading mb-3">{{ __('store.checkout.breadcrumb_checkout') }}</h1>
                @if (session('success'))
                    <p class="text-muted mb-4">{{ session('success') }}</p>
                @else
                    <p class="text-muted mb-4">{{ __('store.checkout.order_success') }}</p>
                @endif
                <p class="mb-4">{{ __('store.checkout.order_success') }}</p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="{{ route('xylo.home') }}" class="btn btn-primary px-4">{{ __('store.cart.continue_shopping') }}</a>
                    <a href="{{ route('shop.index') }}" class="btn btn-outline-secondary px-4">{{ __('store.footer.products') }}</a>
                </div>
            </div>
        </div>
    </div>
@endsection
