<?php

namespace App\Services;

use App\Models\MatchModel;
use App\Models\Odd;
use App\Models\ApifootballLeague;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class JogadinhaFallback
{
    public static function syncGamesAndOdds($modo = 'today')
    {
        DB::connection()->disableQueryLog();

        $fileName = match ($modo) {
            'live'     => 'jogos-jogadinha-live.json',
            'tomorrow' => 'jogos-jogadinha-tomorrow.json',
            default    => 'jogos-jogadinha.json',
        };

        $networkFile  = storage_path('app/scraper_config.json');
        $scraperMode  = 'master';
        $masterUrl    = '';
        $masterToken  = '';

        if (file_exists($networkFile)) {
            $config      = json_decode(file_get_contents($networkFile), true);
            $scraperMode = $config['scraper_mode'] ?? 'master';
            $masterUrl   = $config['master_url'] ?? '';
            $masterToken = $config['master_token'] ?? '';
        }

        if ($scraperMode === 'client') {
            if (empty($masterUrl) || empty($masterToken)) {
                self::logToFile("Modo Cliente: URL do Mestre ou Token não configurados.");
                return false;
            }

            $url = rtrim($masterUrl, '/') . "/api/scraper/export?modo={$modo}&token={$masterToken}";
            self::logToFile("Modo Cliente: Baixando JSON do servidor mestre...");

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL            => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_TIMEOUT        => 30,
            ]);
            $jsonContent = curl_exec($ch);
            $httpCode    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError   = curl_error($ch);
            curl_close($ch);

            if ($httpCode !== 200 || empty($jsonContent)) {
                self::logToFile("Erro ao baixar JSON do mestre. HTTP: {$httpCode}. {$curlError}");
                return false;
            }
        } else {
            $jsonPath = base_path('scraper-jogadinha/' . $fileName);

            if (!file_exists($jsonPath)) {
                self::logToFile("Arquivo JSON não encontrado: {$jsonPath}");
                return false;
            }

            $jsonContent = file_get_contents($jsonPath);
        }

        $data = json_decode($jsonContent, true);

        if (!$data || !isset($data['jogos'])) {
            self::logToFile("JSON do Jogadinha vazio ou inválido.");
            return false;
        }

        $siteId = config('tenant.site_id', 1);
        self::logToFile("Iniciando sincronização Jogadinha (modo: {$modo}). Jogos: " . count($data['jogos']));

        DB::beginTransaction();
        try {
            foreach ($data['jogos'] as $jogo) {
                $eventId = $jogo['eventId'];

                // 1. Registrar ou atualizar a Liga
                $leagueId   = hexdec(substr(md5($jogo['liga'] ?? 'Outros'), 0, 8));
                $leagueName = $jogo['liga'] ?? 'Outros';
                $country    = $jogo['pais'] ?? 'World';

                ApifootballLeague::updateOrCreate(
                    ['league_id' => $leagueId, 'sport' => 'football'],
                    [
                        'name'    => $leagueName,
                        'country' => $country,
                        'active'  => 1,
                    ]
                );

                // 2. Registrar ou atualizar o Jogo
                $matchDB = MatchModel::where('event_id', $eventId)->where('site_id', $siteId)->first();

                $dateString = Carbon::createFromFormat('Y-m-d H:i:s', $jogo['dataHora'], 'America/Sao_Paulo')
                    ->setTimezone('UTC')->format('Y-m-d H:i:s');
                $timeUnix = Carbon::createFromFormat('Y-m-d H:i:s', $jogo['dataHora'], 'America/Sao_Paulo')
                    ->setTimezone('UTC')->timestamp;
                $confronto = $jogo['mandante'] . ' x ' . $jogo['visitante'];

                $imgHome = null;
                if (!empty($jogo['mandanteImg']) && $jogo['mandanteImg'] !== 'no-flag.png') {
                    $imgHome = 'joga_' . str_replace('.png', '', $jogo['mandanteImg']);
                }
                $imgAway = null;
                if (!empty($jogo['visitanteImg']) && $jogo['visitanteImg'] !== 'no-flag.png') {
                    $imgAway = 'joga_' . str_replace('.png', '', $jogo['visitanteImg']);
                }

                if (!$matchDB) {
                    $matchDB = MatchModel::create([
                        'site_id'        => $siteId,
                        'event_id'       => $eventId,
                        'our_event_id'   => 0,
                        'sport_id'       => 1,
                        'sport_name'     => 'Futebol',
                        'league_id'      => $leagueId,
                        'league_cc'      => $jogo['ligaCC'] ?? null,
                        'league'         => $leagueName . ' - ' . $country,
                        'home'           => $jogo['mandante'],
                        'home_true'      => $jogo['mandante'],
                        'away'           => $jogo['visitante'],
                        'away_true'      => $jogo['visitante'],
                        'image_id_home'  => $imgHome,
                        'image_id_away'  => $imgAway,
                        'confronto'      => $confronto,
                        'time'           => $timeUnix,
                        'time_status'    => ($modo === 'live') ? 1 : 0,
                        'date'           => $dateString,
                        'visible'        => 'Sim',
                        'order'          => 0,
                    ]);
                } else {
                    if ($modo === 'live') {
                        $matchDB->time_status = 1;
                    }
                    if (isset($jogo['ligaCC'])) {
                        $matchDB->league_cc = $jogo['ligaCC'];
                    }
                    if ($imgHome) {
                        $matchDB->image_id_home = $imgHome;
                    }
                    if ($imgAway) {
                        $matchDB->image_id_away = $imgAway;
                    }
                    $matchDB->save();
                }

                // 3. Inserir ou atualizar as Odds
                if (!empty($jogo['mercados']) && is_array($jogo['mercados'])) {
                    $existingOdds = DB::table('odds')
                        ->where('event_id', $eventId)
                        ->get()
                        ->keyBy(fn($o) => $o->market_name . '|' . $o->label);

                    $inserts = [];

                    foreach ($jogo['mercados'] as $marketName => $odds) {
                        $finalMarketName = self::translateMarketName($marketName);

                        foreach ($odds as $oddName => $oddPrice) {
                            $finalOddName = self::translateOddName($oddName);
                            $key = $finalMarketName . '|' . $finalOddName;

                            if (isset($existingOdds[$key])) {
                                DB::table('odds')
                                    ->where('id', $existingOdds[$key]->id)
                                    ->update([
                                        'value'  => $oddPrice,
                                        'type'   => $modo === 'live' ? 'live' : 'pre',
                                    ]);
                            } else {
                                $inserts[] = [
                                    'event_id'   => $eventId,
                                    'market_name'=> $finalMarketName,
                                    'label'      => $finalOddName,
                                    'value'      => $oddPrice,
                                    'type'       => $modo === 'live' ? 'live' : 'pre',
                                    'order'      => 1,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ];
                            }
                        }
                    }

                    if (count($inserts) > 0) {
                        foreach (array_chunk($inserts, 500) as $chunk) {
                            DB::table('odds')->insert($chunk);
                        }
                    }

                    MatchModel::where('id', $matchDB->id)->update(['visible' => 'Sim']);
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            self::logToFile("Erro fatal: " . $e->getMessage() . " na linha " . $e->getLine());
            return false;
        }

        self::logToFile("Sincronização Jogadinha concluída ({$modo}).");

        try {
            \Illuminate\Support\Facades\Artisan::call('command:atualizaHome');
            \Illuminate\Support\Facades\Artisan::call('command:liveHoje');
            \Illuminate\Support\Facades\Artisan::call('command:liveAmanha');
        } catch (\Throwable $e) {
            self::logToFile("Erro ao atualizar cache: " . $e->getMessage());
        }

        return true;
    }

    private static function translateMarketName($name)
    {
        $map = [
            'Ambas as equipes marcarão'            => 'Ambas Marcam',
            'Resultado Final'                       => 'Vencedor do Encontro',
            'Empate Anula Aposta'                   => 'Empate Anula a Aposta',
            'Placar Exato Tempo Completo'           => 'Resultado Exato',
            'Total de Gols Mais/Menos'              => 'Total de Gols',
            'Handicap Asiático'                     => 'Handicap',
            'Dupla Hipótese'                        => 'Dupla Chance',
            'Resultado Final - 1º Tempo'            => 'Resultado 1º Tempo',
            'Ambas equipes marcarão - 1º Tempo'     => 'Ambas Marcam 1º Tempo',
        ];

        return $map[$name] ?? $name;
    }

    private static function translateOddName($name)
    {
        return $name;
    }

    private static function logToFile($msg)
    {
        $path = storage_path('logs/apifootball.log');
        $date = Carbon::now()->format('Y-m-d H:i:s');
        @file_put_contents($path, "[{$date}] [JOGADINHA_FALLBACK] {$msg}\n", FILE_APPEND);
    }
}
