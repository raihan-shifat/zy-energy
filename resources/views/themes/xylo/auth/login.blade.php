@extends('themes.xylo.layouts.auth')

@section('content')
@php
    // Admin-editable heading: Admin panel → Site Settings → "Customer Login Heading".
    // Falls back to a plain default when the setting is empty.
    $loginHeading = optional(\App\Models\SiteSetting::first())->customer_login_heading ?: 'Sign In';
@endphp
<div class="auth-page min-vh-100 d-flex align-items-center justify-content-center py-5">
    <div class="card border shadow-sm" style="max-width: 420px; width: 100%; border-radius: var(--radius-lg, 12px);">
        <div class="card-body p-4 p-md-5">

            {{-- Logo --}}
            <div class="text-center mb-4">
                <a href="{{ url('/') }}" class="d-inline-block">
                    <img src="{{ asset('logo.png') }}" alt="ZY Energy" style="height: 64px; width: auto;">
                </a>
            </div>

            {{-- Heading (admin-editable) --}}
            <h1 class="h5 fw-semibold text-center mb-1">{{ $loginHeading }}</h1>
            <p class="text-muted small text-center mb-4">{{ __('store.login.form_subtitle') }}</p>

            <form method="POST" action="{{ route('customer.login') }}">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label small fw-semibold">{{ __('store.login.email') }}</label>
                    <input type="email" name="email" id="email"
                           value="{{ old('email') }}"
                           class="form-control @error('email') is-invalid @enderror"
                           placeholder="name@company.com"
                           required autofocus>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-baseline">
                        <label for="password" class="form-label small fw-semibold">{{ __('store.login.password') }}</label>
                        <a href="{{ route('customer.password.request') }}"
                           class="small text-decoration-none"
                           style="color: var(--accent-600);">{{ __('store.login.forgot_password') }}</a>
                    </div>
                    <input type="password" name="password" id="password"
                           class="form-control @error('password') is-invalid @enderror"
                           required>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary w-100 mt-2">
                    {{ __('store.login.login_btn') }}
                </button>
            </form>

            <p class="text-center small mb-0 mt-4">
                {{ __('store.login.dont_have_account') }}
                <a href="{{ route('customer.register') }}"
                   class="fw-semibold text-decoration-none"
                   style="color: var(--accent-600);">{{ __('store.login.signup') }}</a>
            </p>

        </div>
    </div>
</div>
@endsection
