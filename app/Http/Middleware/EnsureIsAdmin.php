<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureIsAdmin
{
    /**
     * Roles e níveis que concedem acesso administrativo.
     * Mantém compatibilidade entre o sistema novo (role) e legado (nivel).
     */
    private const ADMIN_ROLES  = ['super_admin', 'admin', 'manager'];
    private const ADMIN_NIVEIS = ['adm', 'gerente'];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return $this->deny($request);
        }

        $user = Auth::user();
        $hasRole  = in_array($user->role, self::ADMIN_ROLES);
        $hasNivel = in_array($user->nivel, self::ADMIN_NIVEIS);

        if (!$hasRole && !$hasNivel) {
            return $this->deny($request);
        }

        return $next($request);
    }

    private function deny(Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Acesso negado. Apenas administradores.'], 403);
        }
        return redirect('/');
    }
}
