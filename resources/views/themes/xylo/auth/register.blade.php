@extends('themes.xylo.layouts.auth')

@section('content')
<div class="auth-page min-vh-100 d-flex align-items-center justify-content-center py-5">
    {{-- Same centered single-card layout as the customer login page --}}
    <div class="card border shadow-sm" style="max-width: 420px; width: 100%; border-radius: var(--radius-lg, 12px);">
        <div class="card-body p-4 p-md-5">

            {{-- Logo --}}
            <div class="text-center mb-4">
                <a href="{{ url('/') }}" class="d-inline-block">
                    <img src="{{ asset('logo.png') }}" alt="ZY Energy" style="height: 64px; width: auto;">
                </a>
            </div>

            {{-- Heading --}}
            <h1 class="h5 fw-semibold text-center mb-1">{{ __('store.register.signup_now') }}</h1>
            <p class="text-muted small text-center mb-4">{{ __('store.register.form_subtitle') }}</p>

            <form method="POST" action="{{ route('customer.register') }}">
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label small fw-semibold">{{ __('store.register.name') }}</label>
                    <input type="text" name="name" id="name"
                           value="{{ old('name') }}"
                           class="form-control @error('name') is-invalid @enderror"
                           placeholder="John Doe"
                           required autofocus>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label small fw-semibold">{{ __('store.register.email') }}</label>
                    <input type="email" name="email" id="email"
                           value="{{ old('email') }}"
                           class="form-control @error('email') is-invalid @enderror"
                           placeholder="name@company.com"
                           required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label small fw-semibold">{{ __('store.register.password') }}</label>
                    <input type="password" name="password" id="password"
                           class="form-control @error('password') is-invalid @enderror"
                           required>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password_confirmation" class="form-label small fw-semibold">{{ __('store.register.confirm_password') }}</label>
                    <input type="password" name="password_confirmation" id="password_confirmation"
                           class="form-control"
                           required>
                </div>

                <button type="submit" class="btn btn-primary w-100 mt-2">
                    {{ __('store.register.signup_btn') }}
                </button>
            </form>

            <p class="text-center small mb-0 mt-4">
                {{ __('store.register.already_account') }}
                <a href="{{ route('customer.login') }}"
                   class="fw-semibold text-decoration-none"
                   style="color: var(--accent-600);">{{ __('store.register.login_here') }}</a>
            </p>

        </div>
    </div>
</div>
@endsection
