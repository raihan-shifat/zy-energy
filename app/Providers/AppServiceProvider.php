<?php

namespace App\Providers;

use App\Repositories\Admin\Attribute\AttributeRepository;
use App\Repositories\Admin\Attribute\AttributeRepositoryInterface;
use App\Repositories\Admin\Banner\BannerRepository;
use App\Repositories\Admin\Banner\BannerRepositoryInterface;
use App\Repositories\Admin\Brand\BrandRepository;
use App\Repositories\Admin\Brand\BrandRepositoryInterface;
use App\Repositories\Admin\Menu\MenuRepository;
use App\Repositories\Admin\Menu\MenuRepositoryInterface;
use App\Repositories\Admin\MenuItem\MenuItemRepository;
use App\Repositories\Admin\MenuItem\MenuItemRepositoryInterface;
use App\Repositories\Admin\Product\ProductRepository;
use App\Repositories\Admin\Product\ProductRepositoryInterface;
use App\Repositories\Admin\SocialMediaLink\SocialMediaLinkRepository;
use App\Repositories\Admin\SocialMediaLink\SocialMediaLinkRepositoryInterface;
use App\Services\Admin\ImageService;
use App\Services\Admin\MenuService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Repositories\Admin\Category\CategoryRepositoryInterface::class,
            \App\Repositories\Admin\Category\CategoryRepository::class
        );

        $this->app->singleton(ImageService::class, function ($app) {
            return new ImageService;
        });

        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);

        $this->app->bind(BrandRepositoryInterface::class, BrandRepository::class);

        $this->app->bind(BannerRepositoryInterface::class, BannerRepository::class);
        $this->app->bind(BannerRepositoryInterface::class, BannerRepository::class);

        $this->app->bind(MenuRepositoryInterface::class, MenuRepository::class);
        $this->app->bind(MenuService::class, MenuService::class);

        $this->app->bind(SocialMediaLinkRepositoryInterface::class, SocialMediaLinkRepository::class);

        $this->app->bind(MenuItemRepositoryInterface::class, MenuItemRepository::class);

        $this->app->bind(AttributeRepositoryInterface::class, AttributeRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->configureAppUrl();
    }

    /**
     * Keep every generated URL (asset(), secure_asset(), Storage::url())
     * aligned with config('app.url') so images/links never fall back to the
     * raw server IP or an http scheme when the site is served over https.
     *
     * Storage::url() reads the 'public' disk url from config, so that value is
     * re-derived here at runtime (config:cache safe) instead of only env().
     */
    protected function configureAppUrl(): void
    {
        $appUrl = rtrim((string) config('app.url'), '/');

        if ($appUrl === '') {
            return;
        }

        if (str_starts_with($appUrl, 'https://')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
            \Illuminate\Support\Facades\URL::forceRootUrl($appUrl);
        }

        config(['filesystems.disks.public.url' => $appUrl . '/storage']);
    }
}
