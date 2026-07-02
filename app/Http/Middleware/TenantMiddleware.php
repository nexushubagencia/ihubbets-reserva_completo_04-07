<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    /**
     * Middleware de isolamento Multi-Tenant.
     * Garante que cada banca (site) veja apenas seus próprios dados.
     * 
     * Super Admin: pode acessar qualquer banca via header ou session
     * Admin/Gerente/Cambista: isolados automaticamente por site_id
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user) {
            return $next($request);
        }

        // Determina o Site ID
        $siteId = $user->site_id;
        
        // Se for Super Admin, permite troca via Header ou Session
        // Caso contrário, respeita o identificador global (do domínio)
        if ($user->role === 'super_admin') {
            $siteId = $request->header('X-Site-Id')
                ?? session('active_site_id')
                ?? app('tenant.site_id')
                ?? $user->site_id;
        }

        // Fallback e Normalização
        if (!$siteId || $siteId === 'ihub') {
            $siteId = '1';
        }

        // Garante que seja string/id consistente
        app()->instance('tenant.site_id', $siteId);
        config(['tenant.site_id' => $siteId]);

        // Personaliza o Painel AdminLTE dinamicamente
        $site = \App\Models\Site::find($siteId);
        if ($site) {
            config([
                'adminlte.title' => $site->name . ' - Painel Administrativo',
                'adminlte.logo' => '<b>' . strtoupper($site->name) . '</b>',
                'adminlte.logo_img' => $site->logo_path ? '/' . $site->logo_path : '',
                'adminlte.logo_img_alt' => $site->name,
                
                // Módulos Ativos (SaaS Control)
                'sites.active_bonus' => $site->active_bonus,
                'sites.active_loto' => $site->active_loto,
                'sites.loto_enabled' => $site->loto_enabled,
                'sites.bonus_enabled' => $site->bonus_enabled,
            ]);
        }

        return $next($request);
    }
}
