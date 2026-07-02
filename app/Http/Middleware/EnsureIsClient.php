<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureIsClient
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();
        $isClient = ($user->role === 'client' || $user->nivel === 'cliente');
        $isAdmin = in_array($user->role, ['super_admin', 'admin', 'manager', 'seller']) 
                   || in_array($user->nivel, ['adm', 'gerente', 'cambista']);

        if ($isAdmin) {
            return redirect('/admin');
        }

        return $next($request);
    }
}
