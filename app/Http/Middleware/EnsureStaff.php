<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStaff
{
    /**
     * Allow only staff accounts (super_admin / admin / manager) into the
     * admin panel. Preserves pre-existing behavior (existing users default
     * to the 'admin' role).
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->isStaff()) {
            abort(403, 'Unauthorized access to the admin panel.');
        }

        return $next($request);
    }
}
