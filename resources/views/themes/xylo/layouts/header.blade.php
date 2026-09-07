<header>
    @php
        $siteSettings = getSiteSettings();
    @endphp

    <div class="container py-3">
        <!-- Row 2: Logo Left / Search Right -->
        <div class="row align-items-center">
            <div class="col-md-4 col-6">
                <a href="{{ route('xylo.home') }}" class="navbar-brand">
                    <img src="{{ $siteSettings?->logo ? asset('storage/' . $siteSettings->logo) : asset('logo.png') }}" width="80" alt="{{ $siteSettings->site_name ?? config('app.name') }}" />
                    @if($siteSettings?->header_brand_name_image)
                        <img src="{{ asset('storage/' . $siteSettings->header_brand_name_image) }}" alt="{{ $siteSettings->site_name ?? config('app.name') }}" class="ms-2 align-middle" style="max-height: 40px; max-width: 180px; width: auto; object-fit: contain;">
                    @elseif($siteSettings?->site_name)
                        <span class="ms-2 align-middle fw-semibold">{{ $siteSettings->site_name }}</span>
                    @endif
                </a>
            </div>
            <div class="col-md-8 col-6 text-end">
                <form class="d-flex justify-content-end" action="{{ url('/search') }}" method="GET">
                    <div class="input-group search-input-width">
                        <input type="text" class="form-control" id="search-input"  name="q" placeholder="{{ __('store.header.search_placeholder') }}">
                        <button type="submit" class="btn btn-outline-secondary search-style"><i class="fa fa-search"></i></button>
                        <div id="search-suggestions" class="dropdown-menu show w-100 mt-5 d-none"></div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="container py-3">
        <!-- Row 3: Menu Left / Actions Right -->
        <div class="row align-items-center">
            <div class="col-md-8">
                <nav>
                    <ul class="nav">
                        @if ($headerMenu && $headerMenu->menuItems->count())
                            @foreach ($headerMenu->menuItems as $menuItem)
                                @php
                                    // B2B: Certification not shown in header nav (page still reachable at /certification)
                                    $skipHeaderItem = in_array($menuItem->slug, ['certification']);
                                    $menuItemTitle = localized_translation_value($menuItem->translations, 'title', $menuItem->slug);
                                @endphp
                                @if (!$skipHeaderItem && $menuItem->slug === 'products')
                                    {{-- Products mega-menu: 7 top-level categories, DB-driven (cached) --}}
                                    @php
                                        $megaCategories = getActiveCategories()->take(7);
                                    @endphp
                                    <li class="nav-item dropdown position-static">
                                        <a class="nav-link menu-text-color dropdown-toggle" href="{{ route('shop.index') }}" data-bs-toggle="dropdown">
                                            {{ $menuItemTitle }}
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-lg-start p-3 w-100">
                                            <div class="row">
                                                @foreach ($megaCategories as $megaCat)
                                                    @php
                                                        $megaCatName = localized_translation_value($megaCat->translations, 'name', $megaCat->slug);
                                                    @endphp
                                                    <div class="col-6 col-md-3">
                                                        <a class="dropdown-item fw-bold mb-1" href="{{ route('category.show', $megaCat->slug) }}">
                                                            {{ $megaCatName }}
                                                        </a>
                                                        @if (!empty($megaCat->types))
                                                            <ul class="list-unstyled ms-3 mb-2">
                                                                @foreach ($megaCat->types as $megaType)
                                                                    <li>
                                                                        <a class="dropdown-item small" href="{{ route('category.show', [$megaCat->slug, 'type' => $megaType]) }}">
                                                                            {{ $megaType }}
                                                                        </a>
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </li>
                                @elseif (!$skipHeaderItem)
                                    <li class="nav-item">
                                        <a class="nav-link menu-text-color" href="{{ url($menuItem->slug) }}">
                                            {{ $menuItemTitle }}
                                        </a>
                                    </li>
                                @endif
                            @endforeach
                        @endif
                    </ul>
                </nav>
            </div>

            <div class="col-md-4 d-flex justify-content-end align-items-center gap-3">
                <!-- Language Selector with Flags -->
                @php
                    $storeLangFlags = [
                        'en' => 'us', 'es' => 'es', 'fr' => 'fr', 'ar' => 'sa', 'de' => 'de',
                        'fa' => 'ir', 'hi' => 'in', 'id' => 'id', 'it' => 'it', 'ja' => 'jp',
                        'ko' => 'kr', 'nl' => 'nl', 'pl' => 'pl', 'pt' => 'pt', 'ru' => 'ru',
                        'th' => 'th', 'tr' => 'tr', 'vi' => 'vn', 'zh' => 'cn',
                    ];
                    $currentStoreLang = app()->getLocale();
                    $storeLanguages = collect(getStoreLanguageOptions());
                    $orderedStoreLanguages = $storeLanguages->filter(fn ($language) => $language['code'] === 'en')
                        ->concat($storeLanguages->filter(fn ($language) => $language['code'] === 'zh'))
                        ->concat($storeLanguages->reject(fn ($language) => in_array($language['code'], ['en', 'zh'], true)));
                @endphp
                <form action="{{ route('change.store.language') }}" method="POST" id="store-lang-form">
                    @csrf
                    <input type="hidden" name="lang" id="selected-lang" value="{{ $currentStoreLang }}">
                </form>
                <div class="dropdown">
                    <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle font-style d-flex align-items-center gap-1" data-bs-toggle="dropdown">
                        <img src="https://flagcdn.com/w40/{{ $storeLangFlags[$currentStoreLang] ?? strtolower($currentStoreLang) }}.png" width="20" alt="{{ strtoupper($currentStoreLang) }}">
                        {{ strtoupper($currentStoreLang) }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        @foreach ($orderedStoreLanguages as $lang)
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2 {{ $currentStoreLang == $lang['code'] ? 'active' : '' }}" href="#"
                                   onclick="document.getElementById('selected-lang').value='{{ $lang['code'] }}'; document.getElementById('store-lang-form').submit(); return false;">
                                    <img src="https://flagcdn.com/w40/{{ $storeLangFlags[$lang['code']] ?? strtolower($lang['code']) }}.png" width="20" alt="{{ $lang['translated_text'] }}">
                                    {{ $lang['translated_text'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- B2B: currency switcher hidden (quote-based pricing) --}}
                {{--<form action="{{ route('change.currency') }}" method="POST">
                    @csrf
                    <select name="currency_code" class="form-select form-select-sm font-style" onchange="this.form.submit()">
                        @foreach (\App\Models\Currency::all() as $currency)
                            <option value="{{ $currency->code }}" {{ session('currency', 'USD') == $currency->code ? 'selected' : '' }}>
                                {{ strtoupper($currency->code) }}
                            </option>
                        @endforeach
                    </select>
                </form>--}}

                {{-- B2B: wishlist icon hidden --}}
                {{--<a href="{{ auth('customer')->check() ? route('customer.wishlist.index') : route('customer.login') }}" class="text-dark position-relative homepage-icon">
                    <i class="fa-regular fa-heart"></i>

                    @if($wishlistCount > 0)
                        <span id="wishlist-count"
                              class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            {{ $wishlistCount }}
                        </span>
                    @endif
                </a>--}}

                 <!-- Account Icon -->
                <a href="#" class="text-dark dropdown-toggle homepage-icon" data-bs-toggle="dropdown">
                    @auth('customer')
                        @php
                            $customer = Auth::guard('customer')->user();
                        @endphp
                        @if($customer->profile_image)
                            <img src="{{ asset('storage/' . $customer->profile_image) }}" 
                                alt="{{ __('store.common.profile_alt') }}" 
                                class="rounded-circle" 
                                style="width:32px; height:32px; object-fit:cover;">
                        @else
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($customer->name) }}" 
                                alt="{{ __('store.common.avatar_alt') }}" 
                                class="rounded-circle" 
                                style="width:32px; height:32px; object-fit:cover;">
                        @endif
                    @else
                        <i class="fa-regular fa-user"></i>
                    @endauth
                </a>
                <ul class="dropdown-menu dropdown-menu-end p-2">
                    @guest('customer')
                        <li><a class="dropdown-item" href="{{ route('customer.login') }}">{{ __('store.login.login_btn') }}</a></li>
                        <li><a class="dropdown-item" href="{{ route('customer.register') }}">{{ __('store.login.signup') }}</a></li>
                    @else
                        <li><a class="dropdown-item" href="{{ route('customer.profile.edit') }}">{{ __('store.profile.title') }}</a></li>
                        <li>
                            <a class="dropdown-item" href="{{ route('customer.logout') }}"
                            onclick="event.preventDefault(); document.getElementById('customer-logout-form').submit();">
                            {{ __('store.common.logout') }}
                            </a>
                            <form id="customer-logout-form" action="{{ route('customer.logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </li>
                    @endguest
                </ul>

                {{-- B2B: cart icon hidden — enquiries replace cart/checkout --}}
                {{--<a href="{{ route('cart.view') }}" class="text-dark position-relative homepage-icon">
                    <i class="fa fa-shopping-bag"></i>
                    <span id="cart-count" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        {{ session('cart') ? collect(session('cart'))->sum('quantity') : 0 }}
                    </span>
                </a>--}}
            </div>
        </div>
    </div>
</header>
