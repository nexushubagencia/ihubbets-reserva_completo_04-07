<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Site;
use Illuminate\Support\Facades\Cache;

class CheckSiteStatus
{
    /**
     * Handle an incoming request.
     * Tolerante a falhas: se a tabela sites não existir, permite acesso normal
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $siteId = config('tenant.site_id', env('ID_SITE', 'ihub'));

            $siteStatus = Cache::remember("site_{$siteId}_status", 60, function () use ($siteId) {
                $site = Site::find($siteId);
                return $site ? $site->status : 'active';
            });

            if ($siteStatus === 'suspended') {
                if ($siteId == 1 && $request->is('admin*')) {
                    return $next($request);
                }

                if ($request->wantsJson() || $request->is('api/*')) {
                    return response()->json([
                        'error' => 'maintenance',
                        'message' => 'O sistema está em manutenção temporária.'
                    ], 503);
                }

                return response(view('errors.maintenance'));
            }
        } catch (\Exception $e) {
            // Tabela sites não existe - permite acesso normalmente
        }

        return $next($request);
    }
}
