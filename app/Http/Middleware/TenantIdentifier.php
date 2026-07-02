<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Site;
use Illuminate\Support\Facades\Cache;

class TenantIdentifier
{
    /**
     * Identify the current tenant by domain and load into global config.
     * Tolerante a falhas: se a tabela sites não existir, usa o ID_SITE do .env
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $host = $request->getHost();

            $siteId = Cache::remember("tenant_domain_{$host}", 60, function () use ($host) {
                // 1. Busca Exata
                $site = Site::where('domain', $host)->first();

                // 2. Busca por Slug/Subdomínio (Tolerância para testes locais)
                if (!$site) {
                    $firstPart = explode('.', $host)[0];
                    $site = Site::where('domain', $firstPart)
                                ->orWhere('domain', 'like', "%{$firstPart}%")
                                ->first();
                }

                return $site ? $site->id : env('ID_SITE', 1);
            });

            config(['tenant.site_id' => $siteId]);
            app()->instance('tenant.site_id', $siteId);
        } catch (\Exception $e) {
            // Tabela sites não existe - usa fallback do .env
            $siteId = env('ID_SITE', 1);
            config(['tenant.site_id' => $siteId]);
            app()->instance('tenant.site_id', $siteId);
        }

        // Captura de Afiliado (Referência)
        if ($request->has('ref') && $request->hasSession()) {
            $request->session()->put('referral_code', $request->query('ref'));
        }

        return $next($request);
    }
}
