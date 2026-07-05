<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Log;

class ApiProviderService
{
    const PROVIDER_API_FOOTBALL = 'api-football';
    const PROVIDER_BETS_API = 'bets-api';

    public function getActiveProvider(?int $siteId = null): string
    {
        $siteId = $siteId ?? config('tenant.site_id', 1);

        try {
            $setting = SiteSetting::where('site_id', $siteId)->first();
            if ($setting && !empty($setting->api_provider)) {
                return $setting->api_provider;
            }
        } catch (\Exception $e) {
            Log::warning("ApiProviderService: Erro ao ler provider: {$e->getMessage()}");
        }

        return self::PROVIDER_BETS_API;
    }

    public function setActiveProvider(string $provider, ?int $siteId = null): bool
    {
        if (!in_array($provider, [self::PROVIDER_API_FOOTBALL, self::PROVIDER_BETS_API])) {
            return false;
        }

        $siteId = $siteId ?? config('tenant.site_id', 1);

        try {
            SiteSetting::updateOrCreate(
                ['site_id' => $siteId],
                ['api_provider' => $provider]
            );
            Log::info("ApiProviderService: Provider alterado para {$provider} no site {$siteId}");
            return true;
        } catch (\Exception $e) {
            Log::error("ApiProviderService: Erro ao alterar provider: {$e->getMessage()}");
            return false;
        }
    }

    public function isApiFootball(): bool
    {
        return $this->getActiveProvider() === self::PROVIDER_API_FOOTBALL;
    }

    public function isBetsApi(): bool
    {
        return $this->getActiveProvider() === self::PROVIDER_BETS_API;
    }

    public function getProviderLabel(): string
    {
        return match ($this->getActiveProvider()) {
            self::PROVIDER_API_FOOTBALL => 'API-Football (api-sports.io)',
            self::PROVIDER_BETS_API => 'BetsAPI (betsapi.com)',
            default => 'Desconhecido',
        };
    }
}
