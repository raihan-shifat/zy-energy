<!-- Sidebar -->
<nav id="sidebar" class="d-flex flex-column p-3">
    <div class="logo-container">
        <img src="{{ asset('logo.png') }}" alt="{{ __('cms.sidebar.logo') }}">
    </div>
    <div class="search-container position-relative">
        <input type="text" class="form-control" placeholder="{{ __('cms.sidebar.search_placeholder') }}" id="searchInput" autocomplete="off">
    </div>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link {{ Route::currentRouteName() == 'admin.dashboard' ? 'active' : '' }}" href="{{ route('admin.dashboard') }}" href="#"><i class="fas fa-home me-2"></i> <span>{{ __('cms.sidebar.dashboard') }}</span></a>
        </li>
         <li class="nav-item">
            <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#productMenu" role="button" aria-expanded="false" aria-controls="productMenu">
                <span><i class="fas fa-box me-2"></i> <span>{{ __('cms.sidebar.products.title') }}</span></span>
                <i class="fas fa-chevron-down"></i>
            </a>
            <div class="collapse {{ Route::currentRouteName() == 'admin.products.create' || Route::currentRouteName() == 'admin.products.index' ? 'show' : '' }}" id="productMenu">
                <ul class="nav flex-column ms-3">
                    <li><a class="nav-link {{ Route::currentRouteName() == 'admin.products.create' ? 'active' : '' }}" href="{{ route('admin.products.create') }}">{{ __('cms.sidebar.products.add_new') }}</a></li>
                    <li><a class="nav-link {{ Route::currentRouteName() == 'admin.products.index' ? 'active' : '' }}" href="{{ route('admin.products.index') }}">{{ __('cms.sidebar.products.list') }}</a></li>
                </ul>
            </div>
        </li>
        <li class="nav-item">
            <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#categoryMenu" role="button" aria-expanded="false" aria-controls="categoryMenu">
                <span><i class="fas fa-th-large me-2"></i> <span>{{ __('cms.sidebar.categories.title') }}</span></span>
                <i class="fas fa-chevron-down"></i>
            </a>
            <div class="collapse {{ Route::currentRouteName() == 'admin.categories.create' || Route::currentRouteName() == 'admin.categories.index' ? 'show' : '' }}" id="categoryMenu">
                <ul class="nav flex-column ms-3">
                    <li><a class="nav-link {{ Route::currentRouteName() == 'admin.categories.create' ? 'active' : '' }}" href="{{ route('admin.categories.create') }}">{{ __('cms.sidebar.categories.add_new') }}</a></li>
                    <li><a class="nav-link {{ Route::currentRouteName() == 'admin.categories.index' ? 'active' : '' }}" href="{{ route('admin.categories.index') }}">{{ __('cms.sidebar.categories.list') }}</a></li>
                </ul>
            </div>
        </li>           
        <li class="nav-item">
            <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#brandMenu" role="button" aria-expanded="false" aria-controls="brandMenu">
                <span><i class="fas fa-tags me-2"></i> <span>{{ __('cms.sidebar.brands.title') }}</span></span>
                <i class="fas fa-chevron-down"></i>
            </a>
            <div class="collapse {{ Route::currentRouteName() == 'admin.brands.create' || Route::currentRouteName() == 'admin.brands.index' ? 'show' : '' }}" id="brandMenu">
                <ul class="nav flex-column ms-3">
                    <li><a class="nav-link {{ Route::currentRouteName() == 'admin.brands.create' ? 'active' : '' }}" href="{{ route('admin.brands.create') }}">{{ __('cms.sidebar.brands.add_new') }}</a></li>
                    <li><a class="nav-link {{ Route::currentRouteName() == 'admin.brands.index' ? 'active' : '' }}" href="{{ route('admin.brands.index') }}">{{ __('cms.sidebar.brands.list') }}</a></li>
                </ul>
            </div>
        </li>       
            <li class="nav-item">
                <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#attributeMenu" role="button" aria-expanded="false" aria-controls="attributeMenu">
                    <span><i class="fas fa-cogs me-2"></i> <span>{{ __('cms.sidebar.attributes.title') }}</span></span>
                    <i class="fas fa-chevron-down"></i>
                </a>
                <div class="collapse {{ Route::currentRouteName() == 'admin.attributes.create' || Route::currentRouteName() == 'admin.attributes.index' ? 'show' : '' }}" id="attributeMenu">
                    <ul class="nav flex-column ms-3">
                        <li><a class="nav-link {{ Route::currentRouteName() == 'admin.attributes.create' ? 'active' : '' }}" href="{{ route('admin.attributes.create') }}">{{ __('cms.sidebar.attributes.add_new') }}</a></li>
                        <li><a class="nav-link {{ Route::currentRouteName() == 'admin.attributes.index' ? 'active' : '' }}" href="{{ route('admin.attributes.index') }}">{{ __('cms.sidebar.attributes.list') }}</a></li>
                    </ul>
                </div>
            </li>            
            <li class="nav-item">
                <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#customerMenu" role="button" aria-expanded="false" aria-controls="customerMenu">
                    <span><i class="fas fa-users me-2"></i> <span>{{ __('cms.sidebar.customers.title') }}</span></span>
                    <i class="fas fa-chevron-down"></i>
                </a>
                <div class="collapse {{ in_array(Route::currentRouteName(), ['admin.customers.create', 'admin.customers.index']) ? 'show' : '' }}" id="customerMenu">
                    <ul class="nav flex-column ms-3">
                        <li><a class="nav-link {{ Route::currentRouteName() == 'admin.customers.index' ? 'active' : '' }}" href="{{ route('admin.customers.index') }}">{{ __('cms.sidebar.brands.list') }}</a></li>
                    </ul>
                </div>                
                    <li class="nav-item">
                <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#vendorMenu" role="button" aria-expanded="false" aria-controls="vendorMenu">
                    <span><i class="fas fa-user-tag me-2"></i> <span>{{ __('cms.sidebar.vendors.title') }}</span></span>
                    <i class="fas fa-chevron-down"></i>
                </a>
                <div class="collapse {{ in_array(Route::currentRouteName(), ['admin.vendors.create', 'admin.vendors.index']) ? 'show' : '' }}" id="vendorMenu">
                    <ul class="nav flex-column ms-3">
                        <li>
                            <a class="nav-link {{ Route::currentRouteName() == 'admin.vendors.create' ? 'active' : '' }}" href="{{ route('admin.vendors.create') }}">
                                {{ __('cms.sidebar.vendors.add_new') }}
                            </a>
                        </li>
                        <li>
                            <a class="nav-link {{ Route::currentRouteName() == 'admin.vendors.index' ? 'active' : '' }}" href="{{ route('admin.vendors.index') }}">
                                {{ __('cms.sidebar.vendors.list') }}
                            </a>
                        </li>
                    </ul>
                </div>
            </li>  
             <li class="nav-item">
            <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#ordersMenu" role="button" aria-expanded="false" aria-controls="ordersMenu">
                <span><i class="fas fa-shopping-cart me-2"></i> <span>{{ __('cms.sidebar.orders.title') }}</span></span>
                <i class="fas fa-chevron-down"></i>
            </a>
            <div class="collapse {{ Route::currentRouteName() == 'admin.orders.index' || Route::currentRouteName() == 'admin.orders.pending' || Route::currentRouteName() == 'admin.orders.completed' ? 'show' : '' }}" id="ordersMenu">
                <ul class="nav flex-column ms-3">
                    <li><a class="nav-link {{ Route::currentRouteName() == 'admin.orders.index' ? 'active' : '' }}" href="{{ route('admin.orders.index') }}">{{ __('cms.sidebar.orders.all_orders') }}</a></li>
                    <li><a class="nav-link {{ Route::currentRouteName() == 'admin.orders.pending' ? 'active' : '' }}" href="">{{ __('cms.sidebar.orders.pending_orders') }}</a></li>
                    <li><a class="nav-link {{ Route::currentRouteName() == 'admin.orders.completed' ? 'active' : '' }}" href="">{{ __('cms.sidebar.orders.completed_orders') }}</a></li>
                </ul>
            </div>
        </li>      
        <li class="nav-item">
            <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#paymentsMenu" role="button" aria-expanded="false" aria-controls="paymentsMenu">
                <span><i class="fas fa-credit-card me-2"></i> <span>{{ __('cms.sidebar.payments.title') }}</span></span>
                <i class="fas fa-chevron-down"></i>
            </a>
            <div class="collapse {{ in_array(Route::currentRouteName(), ['admin.payments.index', 'admin.payments.getData']) ? 'show' : '' }}" id="paymentsMenu">
                <ul class="nav flex-column ms-3">
                    <li>
                        <a class="nav-link {{ Route::currentRouteName() == 'admin.payments.index' ? 'active' : '' }}" href="{{ route('admin.payments.index') }}">{{ __('cms.sidebar.payments.list') }}</a>
                    </li>
                </ul>
            </div>
        </li>
        <li class="nav-item">
            <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#refundsMenu" role="button" aria-expanded="false" aria-controls="refundsMenu">
                <span><i class="fas fa-undo me-2"></i> <span>{{ __('cms.sidebar.refunds.title') }}</span></span>
                <i class="fas fa-chevron-down"></i>
            </a>
            <div class="collapse {{ in_array(Route::currentRouteName(), ['admin.refunds.index', 'admin.refunds.getData']) ? 'show' : '' }}" id="refundsMenu">
                <ul class="nav flex-column ms-3">
                    <li>
                        <a class="nav-link {{ Route::currentRouteName() == 'admin.refunds.index' ? 'active' : '' }}" href="{{ route('admin.refunds.index') }}">{{ __('cms.sidebar.refunds.list') }}</a>
                    </li>
                </ul>
            </div>
        </li>
        <li class="nav-item">
            <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#gatewaysMenu" role="button" aria-expanded="false" aria-controls="gatewaysMenu">
                <span><i class="fas fa-cogs me-2"></i> <span>{{ __('cms.sidebar.payment_gateways.title') }}</span></span>
                <i class="fas fa-chevron-down"></i>
            </a>
            <div class="collapse {{ in_array(Route::currentRouteName(), ['admin.payment-gateways.index', 'admin.payment-gateways.getData', 'admin.payment-gateways.edit']) ? 'show' : '' }}" id="gatewaysMenu">
                <ul class="nav flex-column ms-3">
                    <li>
                        <a class="nav-link {{ Route::currentRouteName() == 'admin.payment-gateways.index' ? 'active' : '' }}" href="{{ route('admin.payment-gateways.index') }}">{{ __('cms.sidebar.payment_gateways.list') }}</a>
                    </li>
                </ul>
            </div>
        </li>
         <li class="nav-item">
                <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#productReviewMenu" role="button" aria-expanded="false" aria-controls="productReviewMenu">
                    <span><i class="fas fa-star me-2"></i> <span>{{ __('cms.sidebar.product_reviews.title') }}</span></span>
                    <i class="fas fa-chevron-down"></i>
                </a>
                <div class="collapse {{ in_array(Route::currentRouteName(), ['admin.product_reviews.create', 'admin.product_reviews.index']) ? 'show' : '' }}" id="productReviewMenu">
                    <ul class="nav flex-column ms-3">
                        <li><a class="nav-link {{ Route::currentRouteName() == 'admin.product_reviews.index' ? 'active' : '' }}" href="{{ route('admin.reviews.index') }}">{{ __('cms.sidebar.product_reviews.list') }}</a></li>
                    </ul>
                </div>
            </li>                
        <li class="nav-item">
            <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#bannerMenu" role="button" aria-expanded="false" aria-controls="bannerMenu">
                <span><i class="fas fa-image me-2"></i> <span>{{ __('cms.sidebar.banners.title') }}</span></span>
                <i class="fas fa-chevron-down"></i>
            </a>
            <div class="collapse {{ Route::currentRouteName() == 'admin.banners.create' || Route::currentRouteName() == 'admin.banners.index' ? 'show' : '' }}" id="bannerMenu">
                <ul class="nav flex-column ms-3">
                    <li><a class="nav-link {{ Route::currentRouteName() == 'admin.banners.create' ? 'active' : '' }}" href="{{ route('admin.banners.create') }}">{{ __('cms.sidebar.banners.add_new') }}</a></li>
                    <li><a class="nav-link {{ Route::currentRouteName() == 'admin.banners.index' ? 'active' : '' }}" href="{{ route('admin.banners.index') }}">{{ __('cms.sidebar.banners.list') }}</a></li>
                </ul>
            </div>
        </li>
        <li class="nav-item">
            <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#menuMenu" role="button" aria-expanded="false" aria-controls="menuMenu">
                <span><i class="fas fa-bars me-2"></i> <span>{{ __('cms.sidebar.menu.title') }}</span></span>
                <i class="fas fa-chevron-down"></i>
            </a>
            <div class="collapse {{ Route::currentRouteName() == 'admin.menus.create' || Route::currentRouteName() == 'admin.menus.index' ? 'show' : '' }}" id="menuMenu">
                <ul class="nav flex-column ms-3">
                    <li><a class="nav-link {{ Route::currentRouteName() == 'admin.menus.create' ? 'active' : '' }}" href="{{ route('admin.menus.create') }}">{{ __('cms.sidebar.menu.add_new') }}</a></li>
                    <li><a class="nav-link {{ Route::currentRouteName() == 'admin.menus.index' ? 'active' : '' }}" href="{{ route('admin.menus.index') }}">{{ __('cms.sidebar.menu.list') }}</a></li>
                </ul>
            </div>
        </li>
        <li class="nav-item">
            <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#menuItemMenu" role="button" aria-expanded="false" aria-controls="menuItemMenu">
                <span><i class="fas fa-list me-2"></i> <span>{{ __('cms.sidebar.menu_items.title') }}</span></span>
                <i class="fas fa-chevron-down"></i>
            </a>
            <div class="collapse {{ Route::currentRouteName() == 'admin.menuitems.create' || Route::currentRouteName() == 'admin.menuitems.index' ? 'show' : '' }}" id="menuItemMenu">
                <ul class="nav flex-column ms-3">
                    @if(isset($menu) && $menu)
                    <li><a class="nav-link {{ Route::currentRouteName() == 'admin.menu.items.create' ? 'active' : '' }}" href="{{ route('admin.menus.items.create', $menu) }}">{{ __('cms.sidebar.menu_items.add_new') }}</a></li>
                    <li><a class="nav-link {{ Route::currentRouteName() == 'admin.menus.item.index' ? 'active' : '' }}" href="{{ route('admin.menus.item.index') }}">{{ __('cms.sidebar.menu_items.list') }}</a></li>
                    @endif
                </ul>
            </div>
        </li>                       
        <li class="nav-item">
            <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#socialMediaLinkMenu" role="button" aria-expanded="false" aria-controls="socialMediaLinkMenu">
                <span><i class="fas fa-link me-2"></i> <span>{{ __('cms.sidebar.social_media_links.title') }}</span></span>
                <i class="fas fa-chevron-down"></i>
            </a>
            <div class="collapse {{ Route::currentRouteName() == 'admin.social-media-links.create' || Route::currentRouteName() == 'admin.social-media-links.index' ? 'show' : '' }}" id="socialMediaLinkMenu">
                <ul class="nav flex-column ms-3">
                    <li><a class="nav-link {{ Route::currentRouteName() == 'admin.social-media-links.create' ? 'active' : '' }}" href="{{ route('admin.social-media-links.create') }}">{{ __('cms.sidebar.social_media_links.add_new') }}</a></li>
                    <li><a class="nav-link {{ Route::currentRouteName() == 'admin.social-media-links.index' ? 'active' : '' }}" href="{{ route('admin.social-media-links.index') }}">{{ __('cms.sidebar.social_media_links.list') }}</a></li>
                </ul>
            </div>
        </li>
         <li class="nav-item">
        <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#pageMenu" role="button" aria-expanded="false" aria-controls="pageMenu">
            <span><i class="fas fa-file-alt me-2"></i> <span>{{ __('cms.sidebar.pages.title') }}</span></span>
            <i class="fas fa-chevron-down"></i>
        </a>
        <div class="collapse {{ Route::currentRouteName() == 'admin.pages.create' || Route::currentRouteName() == 'admin.pages.index' ? 'show' : '' }}" id="pageMenu">
            <ul class="nav flex-column ms-3">
                <li>
                    <a class="nav-link {{ Route::currentRouteName() == 'admin.pages.create' ? 'active' : '' }}" href="{{ route('admin.pages.create') }}">
                    {{ __('cms.sidebar.pages.add_new') }}
                    </a>
                </li>
                <li>
                    <a class="nav-link {{ Route::currentRouteName() == 'admin.pages.index' ? 'active' : '' }}" href="{{ route('admin.pages.index') }}">
                    {{ __('cms.sidebar.pages.list') }}
                    </a>
                </li>
            </ul>
        </div>
    </li>
    <li class="nav-item">
        <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#certificationMenu" role="button" aria-expanded="false" aria-controls="certificationMenu">
            <span><i class="fas fa-certificate me-2"></i> <span>{{ __('cms.sidebar.certifications.title') }}</span></span>
            <i class="fas fa-chevron-down"></i>
        </a>
        <div class="collapse {{ in_array(Route::currentRouteName(), ['admin.certifications.create', 'admin.certifications.index', 'admin.certifications.edit']) ? 'show' : '' }}" id="certificationMenu">
            <ul class="nav flex-column ms-3">
                <li>
                    <a class="nav-link {{ Route::currentRouteName() == 'admin.certifications.create' ? 'active' : '' }}" href="{{ route('admin.certifications.create') }}">
                    {{ __('cms.sidebar.certifications.add_new') }}
                    </a>
                </li>
                <li>
                    <a class="nav-link {{ Route::currentRouteName() == 'admin.certifications.index' ? 'active' : '' }}" href="{{ route('admin.certifications.index') }}">
                    {{ __('cms.sidebar.certifications.list') }}
                    </a>
                </li>
            </ul>
        </div>
    </li>
    <li class="nav-item">
        <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#newsMenu" role="button" aria-expanded="false" aria-controls="newsMenu">
            <span><i class="fas fa-newspaper me-2"></i> <span>{{ __('cms.sidebar.news.title') }}</span></span>
            <i class="fas fa-chevron-down"></i>
        </a>
        <div class="collapse {{ in_array(Route::currentRouteName(), ['admin.news.create', 'admin.news.index', 'admin.news.edit']) ? 'show' : '' }}" id="newsMenu">
            <ul class="nav flex-column ms-3">
                <li>
                    <a class="nav-link {{ Route::currentRouteName() == 'admin.news.create' ? 'active' : '' }}" href="{{ route('admin.news.create') }}">
                    {{ __('cms.sidebar.news.add_new') }}
                    </a>
                </li>
                <li>
                    <a class="nav-link {{ Route::currentRouteName() == 'admin.news.index' ? 'active' : '' }}" href="{{ route('admin.news.index') }}">
                    {{ __('cms.sidebar.news.list') }}
                    </a>
                </li>
            </ul>
        </div>
    </li>
    <li class="nav-item">
        <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#enquiryMenu" role="button" aria-expanded="false" aria-controls="enquiryMenu">
            <span><i class="fas fa-envelope-open-text me-2"></i> <span>{{ __('cms.sidebar.enquiries.title') }}</span></span>
            <i class="fas fa-chevron-down"></i>
        </a>
        <div class="collapse {{ Route::currentRouteName() == 'admin.enquiries.index' ? 'show' : '' }}" id="enquiryMenu">
            <ul class="nav flex-column ms-3">
                <li>
                    <a class="nav-link {{ Route::currentRouteName() == 'admin.enquiries.index' ? 'active' : '' }}" href="{{ route('admin.enquiries.index') }}">
                    {{ __('cms.sidebar.enquiries.list') }}
                    </a>
                </li>
            </ul>
        </div>
    </li>
    <li class="nav-item">
        <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#quotationMenu" role="button" aria-expanded="false" aria-controls="quotationMenu">
            <span><i class="fas fa-file-invoice me-2"></i> <span>Quotations</span></span>
            <i class="fas fa-chevron-down"></i>
        </a>
        <div class="collapse {{ in_array(Route::currentRouteName(), ['admin.quotations.index', 'admin.quotations.create', 'admin.quotations.edit', 'admin.quotations.show', 'admin.exchange-rates.index', 'admin.quotation-settings.edit']) ? 'show' : '' }}" id="quotationMenu">
            <ul class="nav flex-column ms-3">
                <li><a class="nav-link {{ Route::currentRouteName() == 'admin.quotations.create' ? 'active' : '' }}" href="{{ route('admin.quotations.create') }}">Create Quotation</a></li>
                <li><a class="nav-link {{ Route::currentRouteName() == 'admin.quotations.index' ? 'active' : '' }}" href="{{ route('admin.quotations.index') }}">All Quotations</a></li>
                <li><a class="nav-link {{ Route::currentRouteName() == 'admin.exchange-rates.index' ? 'active' : '' }}" href="{{ route('admin.exchange-rates.index') }}">Exchange Rates</a></li>
                <li><a class="nav-link {{ Route::currentRouteName() == 'admin.quotation-settings.edit' ? 'active' : '' }}" href="{{ route('admin.quotation-settings.edit') }}">Quotation Settings</a></li>
            </ul>
        </div>
    </li>
    <li class="nav-item">
        <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#contactMenu" role="button" aria-expanded="false" aria-controls="contactMenu">
            <span><i class="fas fa-address-book me-2"></i> <span>Contact Us</span></span>
            <i class="fas fa-chevron-down"></i>
        </a>
        <div class="collapse {{ in_array(Route::currentRouteName(), ['admin.contact.settings', 'admin.contact.wechat.index', 'admin.contact.wechat.create', 'admin.contact.wechat.edit', 'admin.contact.whatsapp.index', 'admin.contact.whatsapp.create', 'admin.contact.whatsapp.edit', 'admin.contact.email.index', 'admin.contact.email.create', 'admin.contact.email.edit']) ? 'show' : '' }}" id="contactMenu">
            <ul class="nav flex-column ms-3">
                <li><a class="nav-link {{ Route::currentRouteName() == 'admin.contact.settings' ? 'active' : '' }}" href="{{ route('admin.contact.settings') }}">Contact Settings</a></li>
                <li><a class="nav-link {{ in_array(Route::currentRouteName(), ['admin.contact.wechat.index', 'admin.contact.wechat.create', 'admin.contact.wechat.edit']) ? 'active' : '' }}" href="{{ route('admin.contact.wechat.index') }}">WeChat Contacts</a></li>
                <li><a class="nav-link {{ in_array(Route::currentRouteName(), ['admin.contact.whatsapp.index', 'admin.contact.whatsapp.create', 'admin.contact.whatsapp.edit']) ? 'active' : '' }}" href="{{ route('admin.contact.whatsapp.index') }}">WhatsApp Contacts</a></li>
                <li><a class="nav-link {{ in_array(Route::currentRouteName(), ['admin.contact.email.index', 'admin.contact.email.create', 'admin.contact.email.edit']) ? 'active' : '' }}" href="{{ route('admin.contact.email.index') }}">Email Contacts</a></li>
            </ul>
        </div>
    </li>
    @auth
    @if(auth()->user()->isSuperAdmin() || auth()->user()->role === 'admin')
        <li class="nav-item">
            <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#userMenu" role="button" aria-expanded="false" aria-controls="userMenu">
                <span><i class="fas fa-user-shield me-2"></i> <span>{{ __('cms.sidebar.users.title') }}</span></span>
                <i class="fas fa-chevron-down"></i>
            </a>
            <div class="collapse {{ in_array(Route::currentRouteName(), ['admin.users.create', 'admin.users.index', 'admin.users.edit']) ? 'show' : '' }}" id="userMenu">
                <ul class="nav flex-column ms-3">
                    <li><a class="nav-link {{ Route::currentRouteName() == 'admin.users.create' ? 'active' : '' }}" href="{{ route('admin.users.create') }}">{{ __('cms.sidebar.users.add_new') }}</a></li>
                    <li><a class="nav-link {{ Route::currentRouteName() == 'admin.users.index' ? 'active' : '' }}" href="{{ route('admin.users.index') }}">{{ __('cms.sidebar.users.list') }}</a></li>
                </ul>
            </div>
        </li>
    @endif
    @endauth
        <li class="nav-item">
            <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#siteSettingsMenu" role="button" aria-expanded="false" aria-controls="siteSettingsMenu">
                <span><i class="fas fa-cog me-2"></i> <span>{{ __('cms.sidebar.site_settings.title') }}</span></span>
                <i class="fas fa-chevron-down"></i>
            </a>
            <div class="collapse {{ Route::currentRouteName() == 'site-settings.index' ? 'show' : '' }}" id="siteSettingsMenu">
                <ul class="nav flex-column ms-3">
                    <li><a class="nav-link {{ Route::currentRouteName() == 'site-settings.index' ? 'active' : '' }}" href="{{ route('site-settings.index') }}">{{ __('cms.sidebar.site_settings.manage') }}</a></li>
                </ul>
            </div>
        </li>           
    </ul>
</nav>