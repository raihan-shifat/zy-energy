<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Category;
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
        $urls[] = ['loc' => '/news'];
        $urls[] = ['loc' => '/certification'];
        $urls[] = ['loc' => '/contact'];

        // About is a CMS page with slug "about", served at /about
        $about = Page::where('slug', 'about')->where('status', 1)->first();
        if ($about) {
            $urls[] = ['loc' => '/about', 'lastmod' => $about->updated_at];
        }

        // Active categories
        Category::where('status', 1)
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
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

        $lines = [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
        ];

        foreach ($urls as $url) {
            $lines[] = '  <url>';
            $lines[] = '    <loc>' . htmlspecialchars($base . $url['loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</loc>';
            if (! empty($url['lastmod'])) {
                $lines[] = '    <lastmod>' . $url['lastmod']->toAtomString() . '</lastmod>';
            }
            $lines[] = '  </url>';
        }

        $lines[] = '</urlset>';

        return implode("\n", $lines) . "\n";
    }
}
