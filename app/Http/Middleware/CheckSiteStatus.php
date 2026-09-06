<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSiteStatus
{
    /**
     * Enforce the global site-status setting on the public storefront.
     *
     * - active / published: normal site
     * - inactive / unpublished: maintenance / coming-soon page (HTTP 503)
     * - unlisted: site stays live but tells crawlers not to index it
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Cached forever by getSiteSettings(); invalidated on site-settings save.
        $status = optional(getSiteSettings())->site_status ?? 'active';

        if (in_array($status, ['inactive', 'unpublished'], true)) {
            return response()
                ->view('themes.xylo.maintenance', ['siteStatus' => $status], 503)
                ->header('Cache-Control', 'no-store, max-age=0');
        }

        if ($status === 'unlisted') {
            view()->share('siteStatus', 'unlisted');
        }

        return $next($request);
    }
}
