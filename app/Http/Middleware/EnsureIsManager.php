<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureIsManager
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();
        $isManager = ($user->role === 'manager' || $user->nivel === 'gerente');
        $isAdmin = in_array($user->role, ['super_admin', 'admin']) || ($user->nivel === 'adm');

        if (!$isManager && !$isAdmin) {
            return redirect('/');
        }

        return $next($request);
    }
}
