<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RateLimiterService
{
    private const CACHE_PREFIX = 'apifootball_requests_';
    private const DAILY_LIMIT = 95;

    /**
     * Verifica quantas requests foram feitas hoje (via Cache, sem race condition)
     */
    public static function getRequestsUsedToday(): int
    {
        $key = self::CACHE_PREFIX . now()->format('Y-m-d');
        return (int) Cache::get($key, 0);
    }

    /**
     * Incrementa o contador de requests do dia (atomic via Cache::increment)
     */
    public static function incrementRequestCount(): int
    {
        $key = self::CACHE_PREFIX . now()->format('Y-m-d');
        $count = Cache::increment($key);

        // Garante TTL de 26 horas (expira no dia seguinte)
        if ($count === 1) {
            Cache::put($key, $count, now()->addHours(26));
        }

        return $count;
    }

    /**
     * Verifica se ainda pode fazer requests
     */
    public static function canMakeRequest(): bool
    {
        return self::getRequestsUsedToday() < self::DAILY_LIMIT;
    }

    /**
     * Retorna剩余 requests
     */
    public static function getRemaining(): int
    {
        return max(0, self::DAILY_LIMIT - self::getRequestsUsedToday());
    }

    /**
     * Retorna dados completos de quota
     */
    public static function getQuotaStatus(): array
    {
        $used = self::getRequestsUsedToday();
        return [
            'requests_today' => $used,
            'requests_limit' => self::DAILY_LIMIT,
            'requests_remaining' => self::getRemaining(),
            'can_make_request' => self::canMakeRequest(),
            'date' => now()->format('Y-m-d'),
        ];
    }
}
