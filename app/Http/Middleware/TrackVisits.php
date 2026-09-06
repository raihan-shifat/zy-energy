<?php

namespace App\Http\Middleware;

use App\Models\SiteVisit;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackVisits
{
    /**
     * Record one storefront visit per session per day (powers the
     * dashboard "Total Visitors" metric). Admin panel visits are excluded.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('admin*') || $request->is('api/admin*')) {
            return $next($request);
        }

        try {
            SiteVisit::firstOrCreate([
                'session_id' => $request->session()->getId(),
                'visit_date' => today()->toDateString(),
            ], [
                'ip'         => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 500),
            ]);
        } catch (\Throwable $e) {
            // Never let analytics break a page render
        }

        return $next($request);
    }
}
