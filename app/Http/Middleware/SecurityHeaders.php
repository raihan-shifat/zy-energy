<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Security headers for every response Laravel produces.
 * X-Frame-Options and X-Content-Type-Options are already sent by Nginx, so they
 * are not repeated here. Static files served directly by Nginx (/build, /storage)
 * do not pass through Laravel and are not affected.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $headers = $response->headers;

        // HTTPS only: tell browsers to always use HTTPS for this host (1 year).
        // No includeSubDomains, so other subdomains are not affected.
        if ($request->isSecure() && ! $headers->has('Strict-Transport-Security')) {
            $headers->set('Strict-Transport-Security', 'max-age=31536000');
        }

        if (! $headers->has('Referrer-Policy')) {
            $headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        }

        if (! $headers->has('Permissions-Policy')) {
            $headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=()');
        }

        // Report-only first: the browser logs violations in the console but blocks nothing.
        // Lists every host the storefront and admin load from. When the console shows no
        // violations, this can be switched to the enforcing Content-Security-Policy header.
        if (! $headers->has('Content-Security-Policy') && ! $headers->has('Content-Security-Policy-Report-Only')) {
            $headers->set('Content-Security-Policy-Report-Only', implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' https://code.jquery.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://cdn.datatables.net https://cdn.ckeditor.com https://www.googletagmanager.com",
                "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://fonts.bunny.net https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://cdn.datatables.net",
                "font-src 'self' data: https://fonts.gstatic.com https://fonts.bunny.net https://cdnjs.cloudflare.com",
                "img-src 'self' data: blob: https://flagcdn.com https://ui-avatars.com https://cdn.jsdelivr.net https://www.googletagmanager.com https://*.google-analytics.com",
                "connect-src 'self' https://cdn.jsdelivr.net https://www.googletagmanager.com https://*.google-analytics.com https://*.analytics.google.com",
                // Google Maps, GTM, and hero videos (YouTube privacy-enhanced mode, Vimeo)
                'frame-src https://www.google.com https://www.googletagmanager.com https://www.youtube-nocookie.com https://www.youtube.com https://player.vimeo.com',
                // Hero videos: uploaded files are same-origin; direct .mp4/.webm links can be on any https host
                "media-src 'self' https:",
                "frame-ancestors 'self'",
                "base-uri 'self'",
                "form-action 'self'",
                "object-src 'none'",
            ]));
        }

        return $response;
    }
}
