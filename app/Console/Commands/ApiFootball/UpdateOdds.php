<?php

namespace App\Console\Commands\ApiFootball;

use Illuminate\Console\Command;
use App\Models\MatchModel;
use App\Models\Odd;
use App\Services\ApiProviderService;
use App\Services\RateLimiterService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class UpdateOdds extends Command
{
    protected $signature = 'apifootball:update_odds {--force : Força atualização mesmo de odds recentes}';
    protected $description = 'Atualiza odds da API-Football (api-sports.io) para o IHUB (otimizado para plano free 100 req/dia)';

    private const LOCK_FILE = 'storage/logs/apifootball_odds.lock';
    private const LOG_FILE  = 'storage/logs/apifootball_odds.log';
    private const COUNTER_FILE = 'storage/logs/apifootball_requests_today.log';
    private const MAX_DAILY_REQUESTS = 95; // Buffer abaixo do limite de 100

    private int $inserted = 0;
    private int $updated  = 0;
    private int $skipped  = 0;
    private int $requestsUsed = 0;
    private string $apiKey;
    private string $apiUrl;

    public function handle(ApiProviderService $provider): int
    {
        if (!$provider->isApiFootball()) {
            $this->warn("Provedor ativo: {$provider->getProviderLabel()}. Use 'api:provider api-football' para trocar.");
            $this->warn("Use comandos BetsAPI diretamente (command:InsertOddBasketBall, command:InsertOddVolleyBall, etc).");
            return self::SUCCESS;
        }

        if (!$this->acquireLock()) {
            $this->error("Outro processo esta em execucao. Abortando.");
            return self::FAILURE;
        }

        $this->rotateLog();

        $this->apiKey = config('services.apifootball.api_key');
        $this->apiUrl = rtrim(config('services.apifootball.api_url'), '/');

        if (empty($this->apiKey)) {
            $this->error("API key nao configurada em services.apifootball.api_key");
            $this->releaseLock();
            return self::FAILURE;
        }

        // Verifica quota diária via Cache (sem race condition)
        $this->requestsUsed = RateLimiterService::getRequestsUsedToday();
        $this->info("Requisicoes usadas hoje: {$this->requestsUsed}/" . self::MAX_DAILY_REQUESTS);

        if (!RateLimiterService::canMakeRequest()) {
            $this->warn("Limite diario atingido. Usando fallback Jogadinha Scraper...");
            $this->tryScraperFallback();
            $this->releaseLock();
            return self::SUCCESS;
        }

        $siteId = config('tenant.site_id', 1);
        $force = $this->option('force');

        $this->info("Buscando odds para partidas de hoje e amanha...");

        // OTIMIZAÇÃO: Ordena por updated_at ASC para retomar de onde parou
        // Se o script parar no meio, na proxima execução pega os próximos
        $query = MatchModel::where('site_id', $siteId)
            ->where('date', '>=', Carbon::today()->subDay())
            ->where('date', '<=', Carbon::tomorrow()->endOfDay())
            ->where('time_status', '!=', '-1')
            ->orderBy('updated_at', 'asc');

        // Pula partidas cujas odds foram atualizadas nas últimas 12 horas (a menos que --force)
        if (!$force) {
            $query->where(function ($q) {
                $q->whereNull('updated_at')
                  ->orWhere('updated_at', '<', Carbon::now()->subHours(12));
            });
        }

        $matches = $query->get();

        if ($matches->isEmpty()) {
            $this->warn("Nenhuma partida pendente. Todas as odds estao atualizadas.");
            $this->releaseLock();
            return self::SUCCESS;
        }

        $eventIds = $matches->pluck('event_id')->toArray();
        $this->info("Partidas pendentes: " . count($eventIds));
        $this->info("Ligas: " . $matches->pluck('league')->unique()->implode(', '));

        $this->fetchAndProcessOdds($eventIds);

        $this->saveRequestsUsedToday();
        $this->releaseLock();

        $this->info("Concluido! Inseridas: {$this->inserted} | Atualizadas: {$this->updated} | Ignoradas: {$this->skipped} | Requisicoes: {$this->requestsUsed}");
        Log::info("apifootball:update_odds finalizado. Inseridas: {$this->inserted}, Atualizadas: {$this->updated}, Ignoradas: {$this->skipped}, Requests: {$this->requestsUsed}");

        return self::SUCCESS;
    }

    private function fetchAndProcessOdds(array $eventIds): void
    {
        $total = count($eventIds);
        $current = 0;

        foreach ($eventIds as $fixtureId) {
            $current++;

            // Verifica quota antes de cada request (via Cache)
            if (!RateLimiterService::canMakeRequest()) {
                $this->warn("Limite diario atingido na request {$current}/{$total}. Fallback Jogadinha...");
                $this->tryScraperFallback();
                break;
            }

            $this->info("  [{$current}/{$total}] Fixture #{$fixtureId}...");

            $response = $this->makeRequest($this->apiUrl . '/odds', [
                'fixture' => $fixtureId,
            ]);

            if ($response === null) {
                // Se retornou null por rate limit, para gracefully
                $this->warn("  Falha ao buscar odds. Possivel rate limit. Parando...");
                break;
            }

            $body     = $response->json();
            $oddsData = $body['response'] ?? [];

            if (empty($oddsData)) {
                $this->skipped++;
                continue;
            }

            $this->processOddsEntry($oddsData[0], $fixtureId);

            // Rate limit: Plano free = 10 req/min, usamos 6s entre requests
            sleep(7);
        }
    }

    private const MARKET_MAP = [
        'Match Winner'                       => 'Vencedor do Encontro',
        'Goals Over/Under'                   => 'Gols Acima/Abaixo',
        'Total Goals'                        => 'Gols Acima/Abaixo',
        'Both Teams Score'                   => 'Ambas Marcam',
        'Both Teams To Score'                => 'Ambas Marcam',
        'Double Chance'                      => 'Dupla Chance',
        'Asian Handicap'                     => 'Handicap Asiático',
        'Handicap'                           => 'Handicap Asiático',
        'Correct Score'                      => 'Resultado Exato',
        'Draw No Bet'                        => 'Empate Anula a Aposta',
        'Odd/Even'                           => 'Gols Impar/Par',
        'First Half Winner'                  => 'Vencedor do Encontro (1T)',
        'Second Half Winner'                 => 'Vencedor do Encontro (2T)',
        'Goals Over/Under First Half'        => 'Gols Acima/Abaixo (1T)',
        'Goals Over/Under - Second Half'     => 'Gols Acima/Abaixo (2T)',
        'HT/FT Double'                       => 'Intervalo/Final',
        'Highest Scoring Half'               => 'Tempo com Mais Gols',
        'Total - Home'                       => 'Total de Gols - Casa',
        'Total - Away'                       => 'Total de Gols - Fora',
        'Corners Over Under'                 => 'Escanteios Acima/Abaixo',
        'Corners 1x2'                        => 'Escanteios 1x2',
        'Cards Over/Under'                   => 'Cartões Acima/Abaixo',
        'Exact Score'                        => 'Resultado Exato',
        'Correct Score - First Half'         => 'Resultado Exato (1T)',
        'Correct Score - Second Half'        => 'Resultado Exato (2T)',
        'Exact Goals Number'                 => 'Número Exato de Gols',
        'Home/Away'                          => 'Dupla Chance',
        'Asian Handicap First Half'          => 'Handicap Asiático (1T)',
        'Asian Handicap (2nd Half)'          => 'Handicap Asiático (2T)',
        'Double Chance - First Half'         => 'Dupla Chance (1T)',
        'Double Chance - Second Half'        => 'Dupla Chance (2T)',
        'Both Teams Score - First Half'      => 'Ambas Marcam (1T)',
        'Both Teams To Score - Second Half'  => 'Ambas Marcam (2T)',
        'Home Team Total Goals(1st Half)'    => 'Total Gols Casa (1T)',
        'Away Team Total Goals(1st Half)'    => 'Total Gols Fora (1T)',
        'Home Team Total Goals(2nd Half)'    => 'Total Gols Casa (2T)',
        'Away Team Total Goals(2nd Half)'    => 'Total Gols Fora (2T)',
        'Draw No Bet (1st Half)'             => 'Empate Anula (1T)',
        'Draw No Bet (2nd Half)'             => 'Empate Anula (2T)',
        'Home win both halves'               => 'Casa Vence Ambos os Times',
        'Away win both halves'               => 'Fora Vence Ambos os Times',
        'Results/Both Teams Score'           => 'Resultado e Ambas Marcam',
        'Total Corners (1st Half)'           => 'Escanteios Acima/Abaixo (1T)',
        'Total Corners (2nd Half)'           => 'Escanteios Acima/Abaixo (2T)',
        'Home Corners Over/Under'            => 'Escanteios Casa Acima/Abaixo',
        'Away Corners Over/Under'            => 'Escanteios Fora Acima/Abaixo',
        'Win to Nil - Home'                  => 'Vencer sem Sofrer - Casa',
        'Win to Nil - Away'                  => 'Vencer sem Sofrer - Fora',
        'Handicap Result'                    => 'Resultado Handicap',
        'Team To Score First'                => 'Primeiro a Marcar',
        'Team To Score Last'                 => 'Último a Marcar',
        'Result/Total Goals'                 => 'Resultado/Total de Gols',
        'Home Team Score a Goal'             => 'Casa Marca Gol',
        'Away Team Score a Goal'             => 'Fora Marca Gol',
        'European Handicap'                  => 'Handicap Europeu',
        'European Handicap (2nd Half)'       => 'Handicap Europeu (2T)',
        'Scoring Draw'                       => 'Empate com Gols',
        'Win Both Halves'                    => 'Vence Ambos os Tempos',
        'To Score in Both Halves'            => 'Marca nos Dois Tempos',
        'Both Teams To Score in Both Halves' => 'Ambas Marcam nos Dois Tempos',
        'First Team to Score (3 way) 1st Half' => 'Primeiro a Marcar (1T)',
        'Home team will score in both halves'  => 'Casa Marca nos Dois Tempos',
        'Away team will score in both halves'  => 'Fora Marca nos Dois Tempos',
        'Handicap Result - First Half'         => 'Resultado Handicap (1T)',
    ];

    private function processOddsEntry(array $oddsEntry, int $fixtureId): void
    {
        $bookmakers = $oddsEntry['bookmakers'] ?? [];

        if (empty($bookmakers)) {
            $this->skipped++;
            return;
        }

        // Processa TODOS os bookmakers (não só o primeiro)
        foreach ($bookmakers as $bookmaker) {
            $markets = $bookmaker['bets'] ?? [];

            foreach ($markets as $market) {
                $marketNameEn = $market['name'] ?? '';
                $marketName   = self::MARKET_MAP[$marketNameEn] ?? $marketNameEn;

                // Pular mercados de jogadores
                if (str_contains($marketNameEn, 'Player') || str_contains($marketNameEn, 'Scorer') || str_contains($marketNameEn, 'Goalkeeper') || str_contains($marketNameEn, 'Shots') || str_contains($marketNameEn, 'Fouls') || str_contains($marketNameEn, 'Offsides')) {
                    continue;
                }

                $values = $market['values'] ?? [];
                $limited = array_slice($values, 0, 6);

                foreach ($limited as $index => $oddValue) {
                    $label = $oddValue['value'] ?? '';
                    $odd   = $oddValue['odd'] ?? null;

                    if ($odd === null || $odd <= 1.0) {
                        continue;
                    }

                    $type = $this->deriveOddType($marketName, $label, $index);

                $existing = Odd::where('event_id', $fixtureId)
                    ->where('market_name', $marketName)
                    ->where('label', $label)
                    ->first();

                if ($existing) {
                    if ((float) $existing->value !== (float) $odd) {
                        $existing->update([
                            'value' => $odd,
                            'type'  => $type,
                        ]);
                        $this->updated++;
                    }
                } else {
                    Odd::create([
                        'event_id'     => $fixtureId,
                        'market_name'  => $marketName,
                        'label'        => $label,
                        'value'        => $odd,
                        'type'         => $type,
                        'status'       => 'active',
                    ]);
                    $this->inserted++;
                }
            }
        }
        } // fim foreach bookmakers
    }

    private function deriveOddType(string $marketName, string $label, int $index): string
    {
        $marketLower = strtolower($marketName);
        $labelLower  = strtolower($label);

        if (str_contains($marketLower, 'vencedor') || str_contains($marketLower, 'match winner') || str_contains($marketLower, '1x2')) {
            return match ($index) {
                0 => '1',
                1 => 'X',
                2 => '2',
                default => '1',
            };
        }

        if (str_contains($marketLower, 'acima') || str_contains($marketLower, 'abaixo') || str_contains($marketLower, 'over') || str_contains($marketLower, 'under')) {
            if (str_contains($labelLower, 'acima') || str_contains($labelLower, 'over')) return 'over';
            if (str_contains($labelLower, 'abaixo') || str_contains($labelLower, 'under')) return 'under';
        }

        if (str_contains($marketLower, 'ambas') || str_contains($marketLower, 'both teams')) {
            if (str_contains($labelLower, 'sim') || str_contains($labelLower, 'yes')) return 'sim';
            if (str_contains($labelLower, 'não') || str_contains($labelLower, 'nao') || str_contains($labelLower, 'no')) return 'não';
        }

        if (str_contains($marketLower, 'dupla') || str_contains($marketLower, 'double')) {
            if (str_contains($labelLower, '1x') || str_contains($labelLower, 'home or draw')) return '1X';
            if (str_contains($labelLower, 'x2') || str_contains($labelLower, 'draw or away')) return 'X2';
            if (str_contains($labelLower, '12') || str_contains($labelLower, 'home or away')) return '12';
        }

        if (str_contains($labelLower, 'home') || str_contains($labelLower, 'casa')) return '1';
        if (str_contains($labelLower, 'draw') || str_contains($labelLower, 'empate')) return 'X';
        if (str_contains($labelLower, 'away') || str_contains($labelLower, 'fora')) return '2';
        if (str_contains($labelLower, 'sim') || str_contains($labelLower, 'yes')) return 'sim';
        if (str_contains($labelLower, 'não') || str_contains($labelLower, 'nao') || str_contains($labelLower, 'no')) return 'não';

        return (string) $index;
    }

    /**
     * Verifica quantas requisicoes ja foram feitas hoje
     */
    private function getRequestsUsedToday(): int
    {
        return RateLimiterService::getRequestsUsedToday();
    }

    /**
     * Salva o contador de requisicoes do dia (agora via Cache, método mantido para compatibilidade)
     */
    private function saveRequestsUsedToday(): void
    {
        // Counter agora é gerenciado pelo RateLimiterService via Cache
        // Este método fica vazio para manter compatibilidade
    }

    /**
     * Incrementa o contador de requisicoes
     */
    private function incrementRequestCount(): void
    {
        $this->requestsUsed = RateLimiterService::incrementRequestCount();
    }

    private function makeRequest(string $url, array $params = [])
    {
        $maxRetries = 2;
        $retryCount = 0;

        while ($retryCount < $maxRetries) {
            try {
                $this->incrementRequestCount();

                $response = Http::withHeaders([
                    'x-apisports-key' => $this->apiKey,
                    'Accept'          => 'application/json',
                ])->timeout(30)->get($url, $params);

                // Rate limit (HTTP 429)
                if ($response->status() === 429) {
                    $this->warn("Rate limit atingido. Parando para evitar bloqueio...");
                    Log::warning("API-Football rate limit (odds). Requests today: {$this->requestsUsed}");
                    return null;
                }

                // Verifica erros de plano free
                $body = $response->json();
                $errors = $body['errors'] ?? [];
                if (!empty($errors)) {
                    $errorMsg = json_encode($errors);
                    if (stripos($errorMsg, 'free') !== false || stripos($errorMsg, 'limit') !== false || stripos($errorMsg, 'quota') !== false) {
                        $this->warn("Plano free atingiu limite. Parando...");
                        Log::warning("API-Football free plan limit: {$errorMsg}");
                        return null;
                    }
                }

                if ($response->failed()) {
                    $this->error("API retornou status {$response->status()}");
                    Log::error("API-Football odds error: {$response->status()} - {$response->body()}");
                    return null;
                }

                // Registra remaining requests
                $remaining = $response->header('x-requests-remaining', 'N/A');
                $this->logRequest($url, $params, $body, $remaining);

                return $response;

            } catch (\Exception $e) {
                $retryCount++;
                $this->error("Erro na requisicao: {$e->getMessage()}");
                Log::error("API-Football odds request error: {$e->getMessage()}");

                if ($retryCount < $maxRetries) {
                    sleep(3 * $retryCount);
                }
            }
        }

        return null;
    }

    private function acquireLock(): bool
    {
        $lockPath = base_path(self::LOCK_FILE);

        if (File::exists($lockPath)) {
            $lockTime = (int) File::get($lockPath);
            if (time() - $lockTime > 600) {
                File::delete($lockPath);
            } else {
                return false;
            }
        }

        File::put($lockPath, (string) time());
        return true;
    }

    private function releaseLock(): void
    {
        $lockPath = base_path(self::LOCK_FILE);
        if (File::exists($lockPath)) {
            File::delete($lockPath);
        }
    }

    private function rotateLog(): void
    {
        $logPath = base_path(self::LOG_FILE);
        if (File::exists($logPath) && File::size($logPath) > 1048576) {
            File::copy($logPath, $logPath . '.' . date('Y-m-d_His') . '.bak');
            File::put($logPath, '');
            $this->info("Log rotacionado.");
        }
    }

    private function logRequest(string $url, array $params, mixed $response, mixed $remaining): void
    {
        $entry = [
            'timestamp' => now()->toIso8601String(),
            'url'       => $url,
            'params'    => $params,
            'status'    => $response['results'] ?? 0,
            'remaining' => $remaining,
            'requests_today' => $this->requestsUsed,
        ];

        File::append(
            base_path(self::LOG_FILE),
            json_encode($entry) . PHP_EOL
        );
    }

    private function tryScraperFallback(): void
    {
        $scraperEnabled = config('services.scraper.enabled', false);
        $scraperMode = config('services.scraper.mode', 'client');

        if (!$scraperEnabled) {
            $this->warn("Scraper Jogadinha nao habilitado no .env (SCRAPER_JOGADINHA_ENABLED=false)");
            return;
        }

        $this->info("Executando scraper:sync-jogadinha como fallback...");
        Artisan::call('scraper:sync-jogadinha', ['--modo' => 'today']);
        Artisan::call('scraper:sync-jogadinha', ['--modo' => 'tomorrow']);
        $this->info("Fallback concluido.");
    }
}
