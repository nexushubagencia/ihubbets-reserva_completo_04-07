<?php

namespace App\Console\Commands\BetsApi;

use Illuminate\Console\Command;
use App\Models\MatchModel;
use App\Services\BetsApiService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class BetsApiUpdateOdds extends Command
{
    protected $signature = 'betsapi:update_odds {--sport=football} {--live=0}';
    protected $description = 'Atualiza odds da BetsAPI (Bet365) - Prematch + Live';

    private const LOG_FILE = 'storage/logs/betsapi_odds.log';
    private int $updated = 0;
    private int $inserted = 0;

    private const MARKET_MAP = [
        'Match Winner'                           => 'Vencedor do Encontro',
        'Full Time Result'                       => 'Vencedor do Encontro',
        'Result/Full Time Result'                => 'Vencedor do Encontro',
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
    ];

    private const ODD_LABEL_MAP = [
        'Home'  => 'Casa',
        'Draw'  => 'Empate',
        'Away'  => 'Fora',
        '1'     => 'Casa',
        'X'     => 'Empate',
        '2'     => 'Fora',
        'Yes'   => 'Sim',
        'No'    => 'Não',
        'Odd'   => 'Ímpar',
        'Even'  => 'Par',
        'Tie'   => 'Empate',
        'Over'  => 'Acima de',
        'Under' => 'Abaixo de',
        'Home or Draw'  => 'Casa ou Empate',
        'Draw or Away'  => 'Empate ou Fora',
        'Home or Away'  => 'Casa ou Fora',
        'Fulham or Draw' => 'Casa ou Empate',
        'Draw or Arsenal' => 'Empate ou Fora',
        'Fulham or Arsenal' => 'Casa ou Fora',
    ];

    public function handle(BetsApiService $betsApi): int
    {
        if (!$betsApi->isConfigured()) {
            $this->error("Token BetsAPI não configurado.");
            return self::FAILURE;
        }

        $sport = $this->option('sport');
        $isLive = (bool) $this->option('live');

        $siteId = config('tenant.site_id', 1);
        $sportMap = [
            'football' => 1, 'basketball' => 2, 'tennis' => 3,
            'volleyball' => 4, 'mma' => 22, 'boxing' => 21,
        ];

        $sportId = $sportMap[$sport] ?? 1;

        $mode = $isLive ? 'LIVE' : 'PREMATCH';
        $this->info("=== Atualizando Odds ({$mode}) - {$sport} ===");

        $matches = MatchModel::where('site_id', $siteId)
            ->where('sport_id', $sportId)
            ->where('time_status', $isLive ? 1 : 0)
            ->where('date', '>=', now()->subDay())
            ->where('date', '<=', now()->addDays(5))
            ->limit(100)
            ->get();

        if ($matches->isEmpty()) {
            $this->warn("Nenhum jogo encontrado para atualizar odds.");
            return self::SUCCESS;
        }

        $this->info(count($matches) . " jogos para atualizar.");

        foreach ($matches as $match) {
            $this->processMatchOdds($betsApi, $match, $siteId);
            sleep(1);
        }

        $this->info("Concluído! Inseridas: {$this->inserted} | Atualizadas: {$this->updated}");
        Log::info("betsapi:update_odds [{$sport}] finalizado. Inserted: {$this->inserted}, Updated: {$this->updated}");

        return self::SUCCESS;
    }

    private function processMatchOdds(BetsApiService $betsApi, $match, int $siteId): void
    {
        $response = $betsApi->getPrematchOdds($match->event_id);

        if (!$response || empty($response['results'])) {
            return;
        }

        $result = $response['results'][0] ?? [];
        $allMarkets = [];

        $categoryMap = [
            'goals' => $result['goals']['sp'] ?? [],
            'half' => $result['half']['sp'] ?? [],
            'asian_lines' => $result['asian_lines']['sp'] ?? [],
            'main' => $result['main']['sp'] ?? [],
        ];

        foreach ($categoryMap as $category) {
            foreach ($category as $marketKey => $marketData) {
                if (!is_array($marketData) || empty($marketData['odds'])) {
                    continue;
                }
                $allMarkets[] = $marketData;
            }
        }

        $existingOdds = DB::table('odds')
            ->where('event_id', $match->event_id)
            ->get()
            ->keyBy(fn($o) => $o->market_name . '|' . $o->label);

        $inserts = [];

        foreach ($allMarkets as $market) {
            $marketName = $this->translateMarketName($market['name'] ?? $marketKey ?? '');

            foreach ($market['odds'] as $odd) {
                $oddValue = $odd['odds'] ?? $odd['VA'] ?? 0;
                if (!$oddValue || $oddValue <= 1.0) continue;

                $header = $odd['header'] ?? '';
                $name = $odd['name'] ?? '';
                $handicap = $odd['handicap'] ?? '';

                $label = $this->buildOddLabel($header, $name, $handicap);

                $finalLabel = $this->translateOddLabel($label);

                $key = $marketName . '|' . $finalLabel;

                if (isset($existingOdds[$key])) {
                    DB::table('odds')
                        ->where('id', $existingOdds[$key]->id)
                        ->update([
                            'value' => (float) $oddValue,
                            'type' => $match->time_status == 1 ? 'live' : 'pre',
                            'updated_at' => now(),
                        ]);
                    $this->updated++;
                } else {
                    $inserts[] = [
                        'event_id'   => $match->event_id,
                        'market_name'=> $marketName,
                        'label'      => $finalLabel,
                        'value'      => (float) $oddValue,
                        'type'       => $match->time_status == 1 ? 'live' : 'pre',
                        'order'      => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    $this->inserted++;
                }
            }
        }

        if (count($inserts) > 0) {
            foreach (array_chunk($inserts, 500) as $chunk) {
                DB::table('odds')->insert($chunk);
            }
        }
    }

    private function buildOddLabel(string $header, string $name, string $handicap): string
    {
        $header = trim($header);
        $name = trim($name);

        if (!empty($handicap)) {
            if (!empty($header) && $header !== '1' && $header !== '2' && $header !== 'Draw') {
                return $header . ' ' . $name . ' ' . $handicap;
            }
            return $name . ' ' . $handicap;
        }

        if (!empty($header) && $header !== $name) {
            if ($header === 'Over' || $header === 'Under') {
                return $header . ' ' . $name;
            }
            if ($header === '1' || $header === '2' || $header === 'Draw') {
                return $header;
            }
            return $header . ' ' . $name;
        }

        return $name ?: $header;
    }

    private function translateMarketName(string $name): string
    {
        return self::MARKET_MAP[$name] ?? $name;
    }

    private function translateOddLabel(string $label): string
    {
        foreach (self::ODD_LABEL_MAP as $en => $pt) {
            if ($label === $en) return $pt;
            if (str_starts_with($label, $en . ' ')) {
                $label = $pt . substr($label, strlen($en));
                break;
            }
        }

        $label = str_replace('Over ', 'Acima de ', $label);
        $label = str_replace('Under ', 'Abaixo de ', $label);

        return $label;
    }
}
