<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Certification;
use App\Models\News;
use App\Models\Page;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    /**
     * Public, indexable URLs only: no admin, auth, cart/checkout, search,
     * or query-string filter pages. Products and news without real content
     * are excluded through the public() scopes.
     */
    public function index()
    {
        $xml = Cache::remember('sitemap:xml', now()->addHour(), fn () => $this->buildXml());

        return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    protected function buildXml(): string
    {
        $base = rtrim((string) config('seo.canonical_domain'), '/');
        $urls = [];

        // Static pages
        $urls[] = ['loc' => '/'];
        $urls[] = ['loc' => '/products'];
        // The news list is only worth listing when it has public posts.
        if (News::public()->exists()) {
            $urls[] = ['loc' => '/news'];
        }
        // Certification is noindex while it has no records, so only list it once it has some.
        if (Certification::where('status', 1)->exists()) {
            $urls[] = ['loc' => '/certification'];
        }
        $urls[] = ['loc' => '/contact'];

        // About is a CMS page with slug "about", served at /about
        $about = Page::where('slug', 'about')->where('status', 1)->first();
        if ($about) {
            $urls[] = ['loc' => '/about', 'lastmod' => $about->updated_at];
        }

        // Active categories that show at least one public product (the category
        // page lists its own products only, so an empty one would be a thin page)
        Category::where('status', 1)
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->whereHas('products', fn ($q) => $q->public())
            ->get(['slug', 'updated_at'])
            ->each(function ($category) use (&$urls) {
                $urls[] = ['loc' => '/category/' . $category->slug, 'lastmod' => $category->updated_at];
            });

        // Public products (active, real name, not a placeholder)
        Product::public()
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->get(['slug', 'updated_at'])
            ->each(function ($product) use (&$urls) {
                $urls[] = ['loc' => '/product/' . $product->slug, 'lastmod' => $product->updated_at];
            });

        // Public news posts (active, with title and body)
        News::public()
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->get(['slug', 'updated_at'])
            ->each(function ($post) use (&$urls) {
                $urls[] = ['loc' => '/news/' . $post->slug, 'lastmod' => $post->updated_at];
            });

        // Every page exists once per enabled language: the default language without
        // a prefix, others under /{locale}. Each entry lists all versions as hreflang
        // alternates (same as the <link rel="alternate"> tags on the page).
        $locales = enabled_store_locales();
        $default = default_store_locale();
        $localized = function (string $path, string $locale) use ($base, $default) {
            if ($locale === $default) {
                return $base . $path;
            }

            return $base . '/' . $locale . ($path === '/' ? '' : $path);
        };
        $xml = fn (string $value) => htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $multilingual = count($locales) > 1;

        $lines = [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'
                . ($multilingual ? ' xmlns:xhtml="http://www.w3.org/1999/xhtml"' : '') . '>',
        ];

        foreach ($urls as $url) {
            foreach ($locales as $locale) {
                $lines[] = '  <url>';
                $lines[] = '    <loc>' . $xml($localized($url['loc'], $locale)) . '</loc>';
                if (! empty($url['lastmod'])) {
                    $lines[] = '    <lastmod>' . $url['lastmod']->toAtomString() . '</lastmod>';
                }
                if ($multilingual) {
                    foreach ($locales as $alternate) {
                        $lines[] = '    <xhtml:link rel="alternate" hreflang="' . $alternate . '" href="' . $xml($localized($url['loc'], $alternate)) . '"/>';
                    }
                    $lines[] = '    <xhtml:link rel="alternate" hreflang="x-default" href="' . $xml($localized($url['loc'], $default)) . '"/>';
                }
                $lines[] = '  </url>';
            }
        }

        $lines[] = '</urlset>';

        return implode("\n", $lines) . "\n";
    }
}
