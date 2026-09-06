<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ config('app.name', 'ZY Energy') }} — Maintenance</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @if (!App::environment('testing'))
        @vite(['resources/views/themes/xylo/css/design-system.css'])
    @endif
    <style>
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: #f4f7f6;
            color: #14201a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }
        .maint-card {
            max-width: 520px;
            width: 100%;
            background: #ffffff;
            border: 1px solid #e3e9e5;
            border-radius: 16px;
            padding: 48px 40px;
            text-align: center;
            box-shadow: 0 18px 50px rgba(20, 32, 26, 0.08);
        }
        .maint-logo img {
            max-width: 120px;
            height: auto;
        }
        .maint-icon {
            font-size: 3rem;
            color: var(--brand-700, #0e7a4f);
        }
        .maint-title {
            font-weight: 800;
            font-size: 1.75rem;
            color: var(--ink-900, #14201a);
            margin: 16px 0 8px;
        }
        .maint-sub {
            color: #55665e;
            line-height: 1.6;
            margin: 0 auto;
            max-width: 380px;
        }
        .maint-contact {
            margin-top: 24px;
            font-size: 0.85rem;
            color: #7d8d85;
        }
        .maint-contact a {
            color: var(--brand-700, #0e7a4f);
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="maint-card">
        <div class="maint-logo">
            <img src="{{ asset('logo.png') }}" alt="Logo">
        </div>
        <div class="maint-icon"><i class="fas fa-tools"></i></div>
        <h1 class="maint-title">We'll be back soon</h1>
        <p class="maint-sub">We're performing scheduled maintenance on our website. Thank you for your patience — we'll be back online shortly.</p>
        <p class="maint-contact">
            Need assistance? Contact us at
            <a href="mailto:{{ \App\Models\SiteSetting::query()->first()?->contact_email ?? '' }}">
                {{ \App\Models\SiteSetting::query()->first()?->contact_email ?? 'info@zyenergy.com' }}
            </a>
        </p>
    </div>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
</body>
</html>
