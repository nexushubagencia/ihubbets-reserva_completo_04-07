<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class UpdateLastActivity
{
    /**
     * Update the authenticated user's last activity timestamp.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            Auth::user()->updateQuietly(['last_activity' => now()]);
        }

        return $next($request);
    }
}
