<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BetsApiLogoService
{
    private static array $negativeCache = [];
    /**
     * Tenta baixar e cachear o logo de um time.
     * Retorna a URL local (asset) se conseguir, ou null se nao encontrar.
     */
    public static function cacheTeamLogo(?string $imageUrl, ?string $teamName, ?string $sport = 'football'): ?string
    {
        if (empty($imageUrl) && empty($teamName)) {
            return null;
        }

        $cacheKey = md5(($imageUrl ?? '') . '|' . ($teamName ?? '') . '|' . $sport);
        if (isset(self::$negativeCache[$cacheKey])) {
            return null;
        }

        $folder = public_path('storage/logos/teams/' . strtolower($sport));

        // Se ja temos uma URL da CDN BetsAPI, tenta baixar pelo ID
        if (!empty($imageUrl)) {
            $localPath = self::downloadBetsApiLogo($imageUrl, $folder);
            if ($localPath) return $localPath;
        }

        // Fallback: TheSportsDB pelo nome (apenas para esportes com cobertura decente)
        if (!empty($teamName) && in_array(strtolower($sport), ['football', 'basketball', 'tennis', 'baseball', 'ice_hockey', 'volleyball'], true)) {
            $localPath = self::downloadFromTheSportsDbTeam($teamName, $folder);
            if ($localPath) return $localPath;
        }

        self::$negativeCache[$cacheKey] = true;
        return null;
    }

    /**
     * Tenta baixar e cachear o logo de uma liga.
     */
    public static function cacheLeagueLogo(?string $imageUrl, ?string $leagueName, ?string $sport = 'football'): ?string
    {
        if (empty($imageUrl) && empty($leagueName)) {
            return null;
        }

        $cacheKey = md5(($imageUrl ?? '') . '|' . ($leagueName ?? '') . '|' . $sport . '|league');
        if (isset(self::$negativeCache[$cacheKey])) {
            return null;
        }

        $folder = public_path('storage/logos/leagues/' . strtolower($sport));

        if (!empty($imageUrl)) {
            $localPath = self::downloadBetsApiLogo($imageUrl, $folder);
            if ($localPath) return $localPath;
        }

        if (!empty($leagueName) && strtolower($sport) === 'football') {
            $localPath = self::downloadFromTheSportsDbLeague($leagueName, $folder);
            if ($localPath) return $localPath;
        }

        self::$negativeCache[$cacheKey] = true;
        return null;
    }

    /**
     * Baixa uma imagem da CDN BetsAPI e salva localmente.
     */
    private static function downloadBetsApiLogo(string $url, string $folder): ?string
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) return null;

        $filename = md5($url) . '.png';
        $fullPath = $folder . '/' . $filename;
        $relativePath = self::relativePath($fullPath);

        if (file_exists($fullPath) && filesize($fullPath) > 0) {
            return asset($relativePath);
        }

        try {
            $response = Http::timeout(5)->get($url);
            $contentType = $response->header('Content-Type') ?? '';
            if ($response->successful() && str_contains($contentType, 'image') && strlen($response->body()) > 100) {
                if (!is_dir($folder)) mkdir($folder, 0755, true);
                file_put_contents($fullPath, $response->body());
                return asset($relativePath);
            }
        } catch (\Exception $e) {
            Log::debug("Falha ao baixar logo BetsAPI: {$url} - " . $e->getMessage());
        }

        return null;
    }

    /**
     * Busca logo do time no TheSportsDB pelo nome.
     */
    private static function downloadFromTheSportsDbTeam(string $teamName, string $folder): ?string
    {
        $filename = md5($teamName) . '.png';
        $fullPath = $folder . '/' . $filename;
        $relativePath = self::relativePath($fullPath);

        if (file_exists($fullPath) && filesize($fullPath) > 0) {
            return asset($relativePath);
        }

        try {
            $response = Http::timeout(5)->get('https://www.thesportsdb.com/api/v1/json/3/searchteams.php', [
                't' => $teamName,
            ]);

            if (!$response->successful()) return null;

            $data = $response->json();
            $teams = $data['teams'] ?? [];

            if (empty($teams)) return null;

            $logoUrl = $teams[0]['strBadge'] ?? $teams[0]['strLogo'] ?? null;
            if (!$logoUrl) return null;

            $imgResponse = Http::timeout(5)->get($logoUrl);
            $contentType = $imgResponse->header('Content-Type') ?? '';
            if ($imgResponse->successful() && str_contains($contentType, 'image') && strlen($imgResponse->body()) > 100) {
                if (!is_dir($folder)) mkdir($folder, 0755, true);
                file_put_contents($fullPath, $imgResponse->body());
                return asset($relativePath);
            }
        } catch (\Exception $e) {
            Log::debug("Falha TheSportsDB team: {$teamName} - " . $e->getMessage());
        }

        return null;
    }

    /**
     * Busca logo da liga no TheSportsDB pelo nome.
     */
    private static function downloadFromTheSportsDbLeague(string $leagueName, string $folder): ?string
    {
        $filename = md5($leagueName) . '.png';
        $fullPath = $folder . '/' . $filename;
        $relativePath = self::relativePath($fullPath);

        if (file_exists($fullPath) && filesize($fullPath) > 0) {
            return asset($relativePath);
        }

        try {
            $response = Http::timeout(5)->get('https://www.thesportsdb.com/api/v1/json/3/search_all_leagues.php', [
                's' => 'Soccer',
                'c' => '',
                'l' => $leagueName,
            ]);

            if (!$response->successful()) return null;

            $data = $response->json();
            $leagues = $data['countries'] ?? [];

            $logoUrl = null;
            foreach ($leagues as $league) {
                if (stripos($league['strLeague'] ?? '', $leagueName) !== false) {
                    $logoUrl = $league['strBadge'] ?? $league['strLogo'] ?? null;
                    break;
                }
            }

            if (!$logoUrl) return null;

            $imgResponse = Http::timeout(5)->get($logoUrl);
            $contentType = $imgResponse->header('Content-Type') ?? '';
            if ($imgResponse->successful() && str_contains($contentType, 'image') && strlen($imgResponse->body()) > 100) {
                if (!is_dir($folder)) mkdir($folder, 0755, true);
                file_put_contents($fullPath, $imgResponse->body());
                return asset($relativePath);
            }
        } catch (\Exception $e) {
            Log::debug("Falha TheSportsDB league: {$leagueName} - " . $e->getMessage());
        }

        return null;
    }

    /**
     * Converte caminho absoluto em caminho relativo ao public/ para usar no asset().
     */
    private static function relativePath(string $absolutePath): string
    {
        $publicPath = public_path();
        $relative = str_replace($publicPath, '', $absolutePath);
        return ltrim(str_replace('\\', '/', $relative), '/');
    }
}
