<?php

namespace App\Menu\Filters;

use JeroenNoten\LaravelAdminLte\Menu\Filters\FilterInterface;
use App\Models\Site;

class ModuleFilter implements FilterInterface
{
    public function transform($item)
    {
        // Se o item não tem tag de módulo, libera sempre
        if (!isset($item['module'])) {
            return $item;
        }

        // Buscar o site do tenant
        $site = $this->getSite();

        // Se não encontrou site, libera tudo (Super Admin sem contexto)
        if (!$site) {
            return $item;
        }

        $module = $item['module'];

        // Mapear cada módulo para sua coluna no banco
        $moduleMap = [
            'affiliate'        => 'active_affiliates',
            'payments'         => 'active_payments',
            'mercado_pago'     => 'active_mercado_pago',
            'loto'             => 'active_loto',
            'marketing'        => 'active_marketing',
            'saques'           => 'active_payments',
            'banners'          => 'active_marketing',
            'bonus'            => 'active_bonus',
            'featured'         => 'active_marketing',
            'configuracoes'    => 'active_configuracoes',
            'relatorios'       => 'active_relatorios',
            'riscos'           => 'active_riscos',
            'online_users'     => 'active_online_users',
            'lancamentos'      => 'active_lancamentos',
            'extrato'          => 'active_extrato',
            'banner_generator' => 'active_banner_generator',
            'gateway_deposito' => 'active_gateway_deposito',
        ];

        // Se o módulo está no mapa, verifica se está ativo
        if (isset($moduleMap[$module])) {
            $column = $moduleMap[$module];
            $value = $site->{$column} ?? 1; // Default: ativo

            if (!$value) {
                return false; // Remove do menu
            }
        }

        return $item;
    }

    /**
     * Busca o site do tenant de todas as formas possíveis
     */
    private function getSite()
    {
        // 1. Tenta via config (definido pelo TenantMiddleware)
        $siteId = config('tenant.site_id');

        // 2. Fallback: tenta via container
        if (!$siteId) {
            try {
                $siteId = app('tenant.site_id');
            } catch (\Exception $e) {
                // Container não tem
            }
        }

        // 3. Fallback: tenta via auth
        if (!$siteId && auth()->check()) {
            $siteId = auth()->user()->site_id;
        }

        // 4. Não encontrou nenhum site_id
        if (!$siteId) {
            return null;
        }

        return Site::find($siteId);
    }
}
