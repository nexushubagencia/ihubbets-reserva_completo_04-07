<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureIsSeller
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();
        $isSeller = ($user->role === 'seller' || $user->nivel === 'cambista');
        $isAdmin = in_array($user->role, ['super_admin', 'admin', 'manager']) || in_array($user->nivel, ['adm', 'gerente']);

        if (!$isSeller && !$isAdmin) {
            return redirect('/');
        }

        return $next($request);
    }
}
