<?php

use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CertificationController;
use App\Http\Controllers\Admin\ContactController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\EnquiryController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\MenuItemController;
use App\Http\Controllers\Admin\NewsController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\PaymentGatewayController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductReviewController;
use App\Http\Controllers\Admin\ProductVariantController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\RefundController;
use App\Http\Controllers\Admin\SocialMediaLinkController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VendorController;
use App\Http\Controllers\Auth\ConfirmPasswordController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\SiteSettingsController;
use App\Http\Controllers\Store\CheckoutController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

/* require base_path('routes/store.php'); */

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

Route::get('password/confirm', [ConfirmPasswordController::class, 'showConfirmForm'])->name('password.confirm');
Route::post('password/confirm', [ConfirmPasswordController::class, 'confirm']);

Route::prefix('admin')->name('admin.')->middleware(['auth', 'staff'])->group(function () {

    /* Dashboard */
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    /* Categiries */
    Route::resource('categories', CategoryController::class);
    Route::post('/categories/data', [CategoryController::class, 'getCategories'])->name('categories.data');
    Route::post('/admin/categories/update-status', [CategoryController::class, 'updateCategoryStatus'])->name('categories.updateStatus');
    Route::post('/categories/{id}/move-up', [CategoryController::class, 'moveUp'])->name('categories.moveUp');
    Route::post('/categories/{id}/move-down', [CategoryController::class, 'moveDown'])->name('categories.moveDown');

    /* Products */
    Route::resource('products', ProductController::class);
    Route::post('products/data', [ProductController::class, 'getProducts'])->name('products.data');
    Route::post('admin/products/updateStatus', [ProductController::class, 'updateStatus'])->name('products.updateStatus');

    /* Brands */
    Route::get('/brands', [BrandController::class, 'index'])->name('brands.index');
    Route::get('admin/brands/getdata', [BrandController::class, 'getData'])->name('brands.getData');
    Route::get('brands/{id}/edit', [BrandController::class, 'edit'])->name('brands.edit');
    Route::put('brands/{brand}', [BrandController::class, 'update'])->name('brands.update');
    Route::get('brands/create', [BrandController::class, 'create'])->name('brands.create');
    Route::post('brands', [BrandController::class, 'store'])->name('brands.store');
    Route::delete('brands/{id}', [BrandController::class, 'destroy'])->name('brands.destroy');
    Route::post('brands/update-status', [BrandController::class, 'updateStatus'])->name('brands.updateStatus');

    /* change Language */
    Route::post('/change-language', [LanguageController::class, 'changeLanguage'])->name('change.language');

    /* Menus */
    Route::resource('menus', MenuController::class);
    Route::post('menus/data', [MenuController::class, 'getData'])->name('menus.data');
    Route::resource('menus.items', MenuItemController::class)->shallow();
    Route::get('menus-items', [MenuItemController::class, 'index'])->name('menus.item.index');
    Route::post('menus-items/getdata', [MenuItemController::class, 'getData'])->name('menus.item.getData');

    /* Banners */
    Route::resource('banners', BannerController::class);
    Route::post('banners/data', [BannerController::class, 'getData'])->name('banners.data');
    Route::put('/banners/toggle-status/{id}', [BannerController::class, 'toggleStatus'])->name('banners.toggleStatus');
    Route::post('/banners/update-status', [BannerController::class, 'updateStatus'])->name('banners.updateStatus');

    /* Social Media Links */
    Route::resource('social-media-links', SocialMediaLinkController::class);
    Route::post('social-media-links/data', [SocialMediaLinkController::class, 'getData'])->name('social-media-links.data');

    /* Orders */
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::delete('orders/{id}', [OrderController::class, 'destroy'])->name('orders.destroy');
    Route::post('orders/data', [OrderController::class, 'getData'])->name('orders.data');

    /* Product Variants */
    Route::resource('product_variants', ProductVariantController::class);
    Route::post('/product_variants/data', [ProductVariantController::class, 'getData'])->name('product_variants.data');

    /* Customers */
    Route::resource('customers', CustomerController::class);
    Route::get('admin/customers/data', [CustomerController::class, 'getCustomerData'])->name('customers.data');

    /* Reviews */
    Route::get('/reviews/data', [ProductReviewController::class, 'getData'])->name('reviews.data');
    Route::resource('reviews', ProductReviewController::class)->except(['create', 'store']);

    /* Attributes */
    Route::resource('attributes', AttributeController::class);
    Route::post('attributes/data', [AttributeController::class, 'getAttributesData'])->name('attributes.data');

    /* Vendors */
    Route::get('vendors', [VendorController::class, 'index'])->name('vendors.index');
    Route::get('vendors/data', [VendorController::class, 'getVendorData'])->name('vendors.data');
    Route::delete('vendors/{id}', [VendorController::class, 'destroy'])->name('vendors.destroy');
    Route::get('vendors/create', [VendorController::class, 'create'])->name('vendors.create');
    Route::post('vendors', [VendorController::class, 'store'])->name('vendors.store');

    /* Pages */
    Route::resource('pages', PageController::class);
    Route::post('pages/update-status', [PageController::class, 'updatePageStatus'])->name('pages.updateStatus');
    Route::post('pages/data', [PageController::class, 'data'])->name('pages.data');

    /* Payments */
    Route::get('payments/get-data', [PaymentController::class, 'getData'])->name('payments.getData');
    Route::resource('payments', PaymentController::class)->only(['index', 'destroy', 'show']);

    /* Enquiries (B2B RFQ leads) */
    Route::get('enquiries', [EnquiryController::class, 'index'])->name('enquiries.index');
    Route::post('enquiries/data', [EnquiryController::class, 'getData'])->name('enquiries.data');
    Route::get('enquiries/export', [EnquiryController::class, 'export'])->name('enquiries.export');
    Route::get('enquiries/{id}', [EnquiryController::class, 'show'])->name('enquiries.show');
    Route::post('enquiries/{id}/status', [EnquiryController::class, 'updateStatus'])->name('enquiries.updateStatus');
    Route::delete('enquiries/{id}', [EnquiryController::class, 'destroy'])->name('enquiries.destroy');

    /* Certifications */
    Route::resource('certifications', CertificationController::class);
    Route::post('certifications/data', [CertificationController::class, 'getData'])->name('certifications.data');
    Route::post('certifications/update-status', [CertificationController::class, 'updateStatus'])->name('certifications.updateStatus');

    /* News */
    Route::resource('news', NewsController::class);
    Route::post('news/data', [NewsController::class, 'getData'])->name('news.data');
    Route::post('news/update-status', [NewsController::class, 'updateStatus'])->name('news.updateStatus');

    /* Refunds */
    Route::get('refunds', [RefundController::class, 'index'])->name('refunds.index');
    Route::get('refunds/data', [RefundController::class, 'getData'])->name('refunds.getData');
    Route::delete('refunds/{refund}', [RefundController::class, 'destroy'])->name('refunds.destroy');
    Route::get('refunds/{refund}', [RefundController::class, 'show'])->name('refunds.show');

    /* Payment Gateways */
    Route::get('payment-gateways', [PaymentGatewayController::class, 'index'])->name('payment-gateways.index');
    Route::get('payment-gateways/data', [PaymentGatewayController::class, 'getData'])->name('payment-gateways.getData');
    Route::get('payment-gateways/{paymentGateway}/edit', [PaymentGatewayController::class, 'edit'])->name('payment-gateways.edit');
    Route::put('payment-gateways/{paymentGateway}', [PaymentGatewayController::class, 'update'])->name('payment-gateways.update');
    Route::delete('payment-gateways/{paymentGateway}', [PaymentGatewayController::class, 'destroy'])->name('payment-gateways.destroy');

    /* User Management (Super Admin + Admin) */
    Route::resource('users', UserController::class)->except(['show'])->middleware('admin');
    Route::post('users/data', [UserController::class, 'getData'])->name('users.data')->middleware('admin');

    /* Quotations */
    Route::resource('quotations', \App\Http\Controllers\Admin\QuotationController::class);
    Route::post('quotations/data', [\App\Http\Controllers\Admin\QuotationController::class, 'getData'])->name('quotations.data');
    Route::get('quotations/{quotation}/print', [\App\Http\Controllers\Admin\QuotationController::class, 'printQuotation'])->name('quotations.print');
    Route::post('quotations/{quotation}/duplicate', [\App\Http\Controllers\Admin\QuotationController::class, 'duplicate'])->name('quotations.duplicate');
    Route::get('quotation-products', [\App\Http\Controllers\Admin\QuotationController::class, 'products'])->name('quotations.products');

    /* Exchange Rates */
    Route::get('exchange-rates', [\App\Http\Controllers\Admin\ExchangeRateController::class, 'index'])->name('exchange-rates.index');
    Route::post('exchange-rates', [\App\Http\Controllers\Admin\ExchangeRateController::class, 'store'])->name('exchange-rates.store');
    Route::put('exchange-rates/{exchangeRate}', [\App\Http\Controllers\Admin\ExchangeRateController::class, 'update'])->name('exchange-rates.update');
    Route::delete('exchange-rates/{exchangeRate}', [\App\Http\Controllers\Admin\ExchangeRateController::class, 'destroy'])->name('exchange-rates.destroy');

    /* Quotation Settings */
    Route::get('quotation-settings', [\App\Http\Controllers\Admin\QuotationSettingController::class, 'edit'])->name('quotation-settings.edit');
    Route::put('quotation-settings', [\App\Http\Controllers\Admin\QuotationSettingController::class, 'update'])->name('quotation-settings.update');

    /* Profile */
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    /* Contact Us */
    Route::get('contact', [ContactController::class, 'settings'])->name('contact.settings');
    Route::put('contact', [ContactController::class, 'settingsUpdate'])->name('contact.settings.update');

    Route::get('contact/wechat', [ContactController::class, 'wechatIndex'])->name('contact.wechat.index');
    Route::get('contact/wechat/create', [ContactController::class, 'wechatCreate'])->name('contact.wechat.create');
    Route::post('contact/wechat', [ContactController::class, 'wechatStore'])->name('contact.wechat.store');
    Route::get('contact/wechat/{wechat}/edit', [ContactController::class, 'wechatEdit'])->name('contact.wechat.edit');
    Route::put('contact/wechat/{wechat}', [ContactController::class, 'wechatUpdate'])->name('contact.wechat.update');
    Route::delete('contact/wechat/{wechat}', [ContactController::class, 'wechatDestroy'])->name('contact.wechat.destroy');

    Route::get('contact/whatsapp', [ContactController::class, 'whatsappIndex'])->name('contact.whatsapp.index');
    Route::get('contact/whatsapp/create', [ContactController::class, 'whatsappCreate'])->name('contact.whatsapp.create');
    Route::post('contact/whatsapp', [ContactController::class, 'whatsappStore'])->name('contact.whatsapp.store');
    Route::get('contact/whatsapp/{whatsapp}/edit', [ContactController::class, 'whatsappEdit'])->name('contact.whatsapp.edit');
    Route::put('contact/whatsapp/{whatsapp}', [ContactController::class, 'whatsappUpdate'])->name('contact.whatsapp.update');
    Route::delete('contact/whatsapp/{whatsapp}', [ContactController::class, 'whatsappDestroy'])->name('contact.whatsapp.destroy');

    Route::get('contact/email', [ContactController::class, 'emailIndex'])->name('contact.email.index');
    Route::get('contact/email/create', [ContactController::class, 'emailCreate'])->name('contact.email.create');
    Route::post('contact/email', [ContactController::class, 'emailStore'])->name('contact.email.store');
    Route::get('contact/email/{email}/edit', [ContactController::class, 'emailEdit'])->name('contact.email.edit');
    Route::put('contact/email/{email}', [ContactController::class, 'emailUpdate'])->name('contact.email.update');
    Route::delete('contact/email/{email}', [ContactController::class, 'emailDestroy'])->name('contact.email.destroy');
});

/* Admin Logout — outside the admin. prefix group so name stays 'admin.logout' */
Route::post('/admin/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('login');
})->middleware(['auth', 'staff'])->name('admin.logout');

Route::middleware(['auth', 'staff'])->group(function () {
    Route::get('site-settings', [SiteSettingsController::class, 'index'])->name('site-settings.index');
    Route::get('site-settings/edit', [SiteSettingsController::class, 'edit'])->name('admin.site-settings.edit');
    Route::put('site-settings/update', [SiteSettingsController::class, 'update'])->name('admin.site-settings.update');
});

/* Checkout routes — also defined in routes/store.php; these are the web.php copies.
   The store.php definitions take priority since they're loaded last. */
// Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
// Route::post('/checkout/process', [CheckoutController::class, 'process'])->name('checkout.process');
// PayPal success callback
Route::get('/checkout/paypal/success', [CheckoutController::class, 'paypalSuccess'])
    ->name('paypal.success');
// PayPal cancel callback
Route::get('/checkout/paypal/cancel', [CheckoutController::class, 'paypalCancel'])
    ->name('paypal.cancel');

Route::get('/thank-you', function () {
    return view('themes.xylo.thankyou');
})->name('thankyou');
