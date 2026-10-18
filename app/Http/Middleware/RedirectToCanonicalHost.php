<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectToCanonicalHost
{
    /**
     * Send www.<canonical host> requests to the canonical host with a 301,
     * keeping the path and query string.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $canonical = config('seo.canonical_domain');
        $canonicalHost = parse_url($canonical, PHP_URL_HOST);

        if ($canonicalHost && strcasecmp($request->getHost(), 'www.'.$canonicalHost) === 0) {
            return redirect(rtrim($canonical, '/').$request->getRequestUri(), 301);
        }

        return $next($request);
    }
}
