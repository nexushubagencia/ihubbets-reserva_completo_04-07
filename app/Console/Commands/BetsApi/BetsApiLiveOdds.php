<?php

namespace App\Console\Commands\BetsApi;

use Illuminate\Console\Command;
use App\Models\MatchModel;
use App\Services\BetsApiService;
use App\Services\TranslationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BetsApiLiveOdds extends Command
{
    protected $signature = 'betsapi:live_odds {--sport=football}';
    protected $description = 'Atualiza odds ao vivo da BetsAPI parseando /v1/bet365/inplay';

    private int $updated = 0;
    private int $inserted = 0;
    private int $skipped = 0;

    /**
     * Mapeia nomes de esporte retornados pela BetsAPI (campo CL.NA) para nossos slugs.
     */
    private const SPORT_NAME_MAP = [
        'Soccer'            => 'football',
        'Football'          => 'football',
        'Basketball'        => 'basketball',
        'Tennis'            => 'tennis',
        'Volleyball'        => 'volleyball',
        'Handball'          => 'handball',
        'Futsal'            => 'futsal',
        'Ice Hockey'        => 'ice_hockey',
        'Baseball'          => 'baseball',
        'American Football' => 'american_football',
        'Esports'           => 'esports',
        'E-Sports'          => 'esports',
        'Cricket'           => 'cricket',
        'Darts'             => 'darts',
        'Snooker'           => 'snooker',
        'Table Tennis'      => 'table_tennis',
        'Boxing'            => 'boxing',
        'MMA'               => 'mma',
        'UFC'               => 'mma',
    ];

    private const MARKET_MAP = [
        'Match Winner'                           => 'Vencedor do Encontro',
        'Full Time Result'                       => 'Vencedor do Encontro',
        'Both Teams To Score'                    => 'Ambas Marcam',
        'Double Chance'                          => 'Dupla Chance',
        'Draw No Bet'                            => 'Empate Anula a Aposta',
        'Asian Handicap'                         => 'Handicap Asiático',
        'Handicap'                               => 'Handicap',
        'Goal Line'                              => 'Gols Acima/Abaixo',
        'Goals Over/Under'                       => 'Gols Acima/Abaixo',
        'Goals Over Under'                       => 'Gols Acima/Abaixo',
        'Over/Under Goals'                       => 'Gols Acima/Abaixo',
        'Correct Score'                          => 'Resultado Exato',
        'Half Time Result'                       => 'Vencedor do Encontro (1T)',
        'Half Time Double Chance'                => 'Dupla Chance (1T)',
        'Half Time/Full Time'                    => 'Intervalo/Final',
        'Odd/Even Goals'                         => 'Gols Ímpar/Par',
        'Goals Odd/Even'                         => 'Gols Ímpar/Par',
        'First Goalscorer'                       => 'Primeiro a Marcar',
        'Last Goalscorer'                        => 'Último a Marcar',
        'Anytime Goalscorer'                     => 'Marca Gol na Partida',
        'Clean Sheet'                            => 'Time Sem Sofrer Gol',
        'Team Total Goals'                       => 'Total de Gols Time',
        'Total Goals/Both Teams To Score'        => 'Resultado/Total de Gols',
        'Alternative Handicap Match Goals'       => 'Handicap Asiático',
        'Alternative Asian Handicap'             => 'Handicap Asiático',
        'Alternative Goal Line'                  => 'Gols Acima/Abaixo',
        'Total Goals'                            => 'Total de Gols',
        'Winning Margin'                         => 'Margem de Vitória',
        'Result and Both Teams To Score'         => 'Resultado e Ambas Marcam',
        'Both Teams To Score 1st Half'           => 'Ambas Marcam (1T)',
        'Both Teams To Score 2nd Half'           => 'Ambas Marcam (2T)',
        'First Half Goals'                       => 'Gols Acima/Abaixo (1T)',
        '2nd Half Goals'                         => 'Gols Acima/Abaixo (2T)',
        '1st Half Handicap'                      => 'Handicap Asiático (1T)',
        '1st Half Asian Handicap'                => 'Handicap Asiático (1T)',
        '1st Half Goal Line'                     => 'Gols Acima/Abaixo (1T)',
        'Half Time Correct Score'                => 'Resultado Exato (1T)',
        'Home Team Highest Scoring Half'         => 'Time com Mais Gols',
        'Away Team Highest Scoring Half'         => 'Time com Mais Gols',
        'Result/Total Goals'                     => 'Resultado/Total de Gols',
        'Number of Goals in Match'               => 'Número de Gols na Partida',
        'Total Goals/Both Teams To Score'        => 'Total Gols/Ambas Marcam',
        'Corners 1X2'                            => 'Escanteios 1X2',
        'Corners Over/Under'                     => 'Escanteios Acima/Abaixo',
        'Bookings Over/Under'                    => 'Cartões Acima/Abaixo',
        'Penalty Awarded'                        => 'Pênalti na Partida',
        'Red Card'                               => 'Cartão Vermelho na Partida',
        'To Score In Both Halves'                => 'Marca nos Dois Tempos',
        'To Score In One Half Only'              => 'Marca em Um Tempo',
        'Win Both Halves'                        => 'Vence Ambos os Tempos',
        'Come From Behind And Win'               => 'Gira o Jogo e Vence',
        'Winner'                                 => 'Vencedor do Encontro',
        'Match'                                  => 'Vencedor do Encontro',
        'Set'                                    => 'Set',
        'Set Winner'                             => 'Vencedor do Set',
        'Game'                                   => 'Game',
        'Game Winner'                            => 'Vencedor do Game',
        'Total'                                  => 'Total',
        'Spread'                                 => 'Handicap',
        'Moneyline'                              => 'Vencedor do Encontro',
    ];

    public function handle(BetsApiService $betsApi): int
    {
        if (!$betsApi->isConfigured()) {
            $this->error("Token BetsAPI não configurado.");
            return self::FAILURE;
        }

        $sport = $this->option('sport');
        if (!isset(BetsApiService::SPORT_IDS[$sport])) {
            $this->error("Esporte inválido: {$sport}");
            return self::FAILURE;
        }

        $siteId = config('tenant.site_id', 1);

        $this->info("=== Atualizando odds ao vivo: {$sport} ===");

        // O endpoint /v1/bet365/inplay retorna TODOS os esportes em formato flat.
        $response = $betsApi->getAllInPlay();
        if (!$response || empty($response['results'])) {
            $this->warn("Nenhum jogo ao vivo encontrado.");
            return self::SUCCESS;
        }

        $items = $this->unwrapResults($response['results']);
        $this->info("Itens recebidos: " . count($items));

        $parsed = $this->parseInplayOdds($items, $sport);
        $this->info(count($parsed) . " eventos com odds ao vivo encontrados para {$sport}.");

        foreach ($parsed as $fi => $markets) {
            $this->storeOdds((int)$fi, $markets, $siteId);
        }

        $this->info("Concluído! Inseridas: {$this->inserted} | Atualizadas: {$this->updated} | Ignoradas: {$this->skipped}");
        Log::info("betsapi:live_odds [{$sport}] finalizado. Inserted: {$this->inserted}, Updated: {$this->updated}, Skipped: {$this->skipped}");

        return self::SUCCESS;
    }

    /**
     * Desembrulha o array aninhado retornado pela BetsAPI.
     */
    private function unwrapResults(array $results): array
    {
        if (count($results) === 1 && isset($results[0]) && is_array($results[0]) && isset($results[0][0]['type'])) {
            return $results[0];
        }

        if (isset($results[0]) && is_array($results[0]) && isset($results[0]['type'])) {
            return $results;
        }

        return $results;
    }

    /**
     * Parseia a resposta flat do /v1/bet365/inplay em eventos -> mercados -> odds.
     * Filtra apenas o esporte solicitado usando os registros CL (categoria/esporte).
     */
    private function parseInplayOdds(array $items, string $targetSport): array
    {
        $events = [];
        $currentFi = null;
        $currentMarket = null;
        $currentSport = null;

        foreach ($items as $item) {
            $type = $item['type'] ?? '';

            // CL = categoria/esporte
            if ($type === 'CL') {
                $currentSport = $this->resolveSportSlug($item['NA'] ?? '');
                continue;
            }

            // Ignora esportes que não mapeamos (ex: Virtual Sports)
            if ($currentSport === null) {
                $currentFi = null;
                $currentMarket = null;
                continue;
            }

            // CT = competição/torneio - não precisamos, mas reseta mercado atual
            if ($type === 'CT') {
                $currentMarket = null;
                continue;
            }

            $fi = $item['FI'] ?? $item['OI'] ?? null;

            if ($type === 'EV' && $fi) {
                // Só processa eventos do esporte solicitado
                if ($currentSport !== $targetSport) {
                    $currentFi = null;
                    $currentMarket = null;
                    continue;
                }

                $currentFi = (string)$fi;
                $currentMarket = null;
                if (!isset($events[$currentFi])) {
                    $events[$currentFi] = [];
                }
                continue;
            }

            if (!$currentFi) continue;

            if ($type === 'MA' && $fi && (string)$fi === $currentFi) {
                $marketName = mb_substr($this->translateMarketName($item['NA'] ?? ''), 0, 255);
                $currentMarket = $marketName;
                if (!isset($events[$currentFi][$currentMarket])) {
                    $events[$currentFi][$currentMarket] = [];
                }
                continue;
            }

            if ($type === 'PA' && $currentMarket && $fi && (string)$fi === $currentFi) {
                $oddValue = $this->fractionalToDecimal($item['OD'] ?? '');
                if (!$oddValue || $oddValue <= 1.0) continue;

                $label = mb_substr($this->buildLabel($item), 0, 255);
                if (empty($label)) continue;

                $events[$currentFi][$currentMarket][] = [
                    'value' => $oddValue,
                    'label' => $label,
                ];
            }
        }

        return $events;
    }

    private function resolveSportSlug(string $sportName): ?string
    {
        $sportName = trim($sportName);
        return self::SPORT_NAME_MAP[$sportName] ?? null;
    }

    private function buildLabel(array $item): string
    {
        $name = $item['NA'] ?? '';
        $header = $item['HD'] ?? $item['header'] ?? '';
        $handicap = $item['HA'] ?? $item['handicap'] ?? '';

        $label = trim($header ? $header . ' ' . $name : $name);
        if ($handicap) {
            $label .= ' ' . $handicap;
        }

        return $this->translateOddLabel($label);
    }

    private function translateMarketName(string $name): string
    {
        return self::MARKET_MAP[$name] ?? $name;
    }

    private function translateOddLabel(string $label): string
    {
        $map = [
            'Home'  => 'Casa',
            'Draw'  => 'Empate',
            'Away'  => 'Fora',
            'Yes'   => 'Sim',
            'No'    => 'Não',
            'Odd'   => 'Ímpar',
            'Even'  => 'Par',
            'Over'  => 'Acima de',
            'Under' => 'Abaixo de',
            '1'     => 'Casa',
            'X'     => 'Empate',
            '2'     => 'Fora',
        ];

        $label = trim($label);

        foreach ($map as $en => $pt) {
            if ($label === $en) return $pt;
            if (str_starts_with($label, $en . ' ')) {
                return $pt . substr($label, strlen($en));
            }
        }

        $label = str_replace('Over ', 'Acima de ', $label);
        $label = str_replace('Under ', 'Abaixo de ', $label);

        return $label;
    }

    private function fractionalToDecimal(string $fraction): ?float
    {
        if (empty($fraction) || !str_contains($fraction, '/')) return null;

        [$numerator, $denominator] = explode('/', $fraction, 2);
        $numerator = (float) trim($numerator);
        $denominator = (float) trim($denominator);

        if ($denominator == 0) return null;

        return round(($numerator / $denominator) + 1, 3);
    }

    private function storeOdds(int $fi, array $markets, int $siteId): void
    {
        $match = MatchModel::where('event_id', $fi)->where('site_id', $siteId)->first();
        if (!$match) {
            $this->skipped++;
            return;
        }

        $existingOdds = DB::table('odds')
            ->where('event_id', $fi)
            ->get()
            ->keyBy(fn($o) => $o->market_name . '|' . $o->label);

        $inserts = [];

        foreach ($markets as $marketName => $odds) {
            foreach ($odds as $odd) {
                $key = $marketName . '|' . $odd['label'];

                if (isset($existingOdds[$key])) {
                    DB::table('odds')
                        ->where('id', $existingOdds[$key]->id)
                        ->update([
                            'value' => $odd['value'],
                            'type' => 'live',
                            'updated_at' => now(),
                        ]);
                    $this->updated++;
                } else {
                    $inserts[] = [
                        'event_id'    => $fi,
                        'market_name' => $marketName,
                        'label'       => $odd['label'],
                        'value'       => $odd['value'],
                        'type'        => 'live',
                        'order'       => 1,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ];
                    $this->inserted++;
                }
            }
        }

        if (!empty($inserts)) {
            foreach (array_chunk($inserts, 500) as $chunk) {
                DB::table('odds')->insert($chunk);
            }
        }
    }
}
