<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Banner;
use App\Models\Site;
use App\Models\User;
use App\Models\Game;
use App\Models\Mercado;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class MatchApiController extends ApiController
{

    /**
     * Rota genérica: /data/{sport}/{day}
     * O frontend chama, por exemplo: /data/soccer/today, /data/soccer/tomorrow, /data/soccer/2026-04-25
     */
    public function getMatchesByDay($sport, $day)
    {
        $siteId = config('tenant.site_id', 1);
        $return = [];
        $assetsCdn = config('services.assets_cdn_url', 'https://assets.betsapi.com/v1/');

        // Determinar a data baseado no parâmetro
        $targetDate = match(strtolower($day)) {
            'today', 'hoje' => \Carbon\Carbon::today('America/Fortaleza'),
            'tomorrow', 'amanha' => \Carbon\Carbon::tomorrow('America/Fortaleza'),
            'after_tomorrow', 'depois_amanha' => \Carbon\Carbon::today('America/Fortaleza')->addDays(2),
            'live', 'ao_vivo' => null, // Flag para buscar ao vivo
            default => \Carbon\Carbon::parse($day),
        };

        // Mapear esporte
        $sportMap = [
            'soccer' => 'Futebol',
            'futebol' => 'Futebol',
            'fight' => 'Luta',
            'luta' => 'Luta',
            'mma' => 'Luta',
            'ufc' => 'Luta',
            'basketball' => 'Basquete',
            'basquete' => 'Basquete',
            'tennis' => 'Tênis',
            'tenis' => 'Tênis',
            'volleyball' => 'Vôlei',
            'volei' => 'Vôlei',
            'esports' => 'E-Sports',
            'vaquejada' => 'Vaquejada',
            'x1' => 'X1 Futebol',
        ];
        $sportName = $sportMap[strtolower($sport)] ?? ucfirst($sport);

        $cacheKey = "api_matches_site_{$siteId}_sport_{$sport}_day_{$day}";

        $return = \Illuminate\Support\Facades\Cache::remember($cacheKey, 180, function () use ($siteId, $targetDate, $sport, $sportName) {
            $return = [];
            try {
                // Construir query base
                // Otimização: remove o take(3) enganoso que limita globalmente e só traz odds do mercado principal (1x2 ou Match Winner) se o DB for grande. 
                // Por agora, trazendo todas as odds (limitando por query e não subquery) ou deixando o eager loading sem limites globais incorretos.
                $query = Game::with('odds')
                    ->where('visible', 'Sim')
                ->where('site_id', $siteId);

            // Filtrar por data
            if ($targetDate) {
                $query->whereDate('date', $targetDate->format('Y-m-d'));
            } else {
                // Ao vivo
                $query->where('time_status', 1);
            }

            // Filtrar por esporte (se não for "todos")
            if (strtolower($sport) !== 'soccer' && strtolower($sport) !== 'futebol') {
                $query->where('sport_name', 'LIKE', '%' . $sportName . '%');
            }

            $games = $query->orderBy('date', 'asc')->limit(200)->get();

            $leagues = $games->groupBy('league');
            foreach($leagues as $leagueName => $matches) {
                $firstMatch = $matches->first();
                $leagueArr = [
                    'league' => $leagueName,
                    'league_cc' => null,
                    'league_country' => 'Outros',
                    'match' => []
                ];
                foreach($matches as $match) {
                    $leagueArr['match'][] = $this->formatMatch($match);
                }
                $return[] = $leagueArr;
            }

            // 2. Jogos Manuais
            $manualQuery = \App\Models\ManualEvent::with('category')
                ->where('site_id', $siteId)
                ->where('status', 'open');

            if ($targetDate) {
                $manualQuery->whereDate('start_time', $targetDate->format('Y-m-d'))
                            ->where('start_time', '>=', now()); 
            } else {
                $manualQuery->where('start_time', '>=', now());
            }

            $manualEvents = $manualQuery->get();
            $manualLeagues = $manualEvents->groupBy('league_name');

            foreach($manualLeagues as $leagueName => $matches) {
                $leagueArr = [
                    'league' => $leagueName,
                    'league_cc' => null,
                    'league_country' => 'Brasil',
                    'match' => []
                ];
                foreach($matches as $match) {
                    $leagueArr['match'][] = $this->formatMatch($match);
                }
                $return[] = $leagueArr;
            }

            } catch (\Exception $e) {
                \Log::warning("Erro getMatchesByDay({$sport}/{$day}): " . $e->getMessage());
            }

            return $return;
        });

        return response()->json($return);
    }

    public function getMatchesLive()
    {
        $siteId = config('tenant.site_id', 1);
        $return = [];
        $assetsCdn = "https://assets.betsapi.com/v2/image/";

        $cacheKey = "api_live_matches_site_{$siteId}";

        $return = \Illuminate\Support\Facades\Cache::remember($cacheKey, 15, function () use ($siteId, $assetsCdn) {
            $return = [];
            try {
                // Buscar jogos ao vivo do banco (atualizados por apifootball:live)
                $games = \App\Models\MatchModel::with('fullOdds')
                    ->where('visible', 'Sim')
                    ->where('site_id', $siteId)
                    ->where('time_status', 1)
                    ->orderBy('league', 'asc')
                    ->orderBy('date', 'asc')
                    ->get();

            $leagues = $games->groupBy('league');

            foreach($leagues as $leagueName => $matches) {
                $firstMatch = $matches->first();
                $resolvedCc = $this->resolveLeagueCc($leagueName, $firstMatch->league_cc ?? null);
                
                $leagueArr = [
                    'league' => $leagueName,
                    'league_image' => $this->resolveLeagueImage($firstMatch->league_id),
                    'flag' => $this->resolveFlagUrl($resolvedCc),
                    'match' => []
                ];

                foreach($matches as $match) {
                    $formatted = $this->formatMatch($match);
                    // Adicionar dados extras ao vivo
                    $formatted['score'] = $match->score ?? '';
                    $formatted['elapsed'] = $match->time ?? 0;
                    $formatted['home_score'] = $match->home_true ?? 0;
                    $formatted['away_score'] = $match->away_true ?? 0;
                    $formatted['live_status'] = $match->live_status ?? 'Live';
                    $leagueArr['match'][] = $formatted;
                }
                $return[] = $leagueArr;
            }

            // Incluir eventos manuais ao vivo também
            $manualLive = \App\Models\ManualEvent::with('category')
                ->where('site_id', $siteId)
                ->where('status', 'open')
                ->where('start_time', '<=', now())
                ->where('start_time', '>=', now()->subHours(3))
                ->get();

            if ($manualLive->isNotEmpty()) {
                $manualLeagues = $manualLive->groupBy('league_name');
                foreach($manualLeagues as $leagueName => $matches) {
                    $leagueArr = [
                        'league' => $leagueName,
                        'league_image' => asset('img/placeholders/trophy.svg'),
                        'flag' => asset('img/countries/br.svg'),
                        'match' => []
                    ];
                    foreach($matches as $match) {
                        $formatted = $this->formatMatch($match);
                        $formatted['score'] = '';
                        $formatted['elapsed'] = 0;
                        $formatted['home_score'] = 0;
                        $formatted['away_score'] = 0;
                        $formatted['live_status'] = 'Live';
                        $leagueArr['match'][] = $formatted;
                    }
                    $return[] = $leagueArr;
                }
            }

            } catch (\Exception $e) {
                \Log::warning("Erro ao buscar jogos ao vivo: " . $e->getMessage());
            }

            return $return;
        });

        return response()->json($return);
    }

    public function getDaysList()
    {
        $days = [];
        $weekMap = [
            0 => 'DOM',
            1 => 'SEG',
            2 => 'TER',
            3 => 'QUA',
            4 => 'QUI',
            5 => 'SEX',
            6 => 'SÁB',
        ];

        for ($i = 0; $i < 10; $i++) {
            $date = \Carbon\Carbon::today('America/Fortaleza')->addDays($i);
            $portuguesDays = [
                'Sunday' => 'domingo', 'Monday' => 'segunda', 'Tuesday' => 'terça', 
                'Wednesday' => 'quarta', 'Thursday' => 'quinta', 'Friday' => 'sexta', 'Saturday' => 'sábado'
            ];
            $label = ($i == 0) ? 'Hoje' : $portuguesDays[$date->format('l')];
            $name = ($i == 0) ? 'Hoje' : ($i == 1 ? 'Amanhã' : ucfirst($label));


            $days[] = [
                'id' => $i,
                'idx' => $i,
                'day' => $date->format('d/m'),
                'label' => $label,
                'name' => $name,
                'value' => $date->format('Y-m-d'),
                'full_date' => $date->format('Y-m-d'),
                'data_formatada' => $date->format('d/m'),
                'active' => ($i == 0) ? 1 : 0
            ];
        }
        return response()->json($days);
    }

    public function getMatchesByModality($modality)
    {
        $siteId = config('tenant.site_id', 1);
        $return = [];
        $assetsCdn = config('services.assets_cdn_url', 'https://assets.betsapi.com/v1/');

        try {
            // 1. Jogos da API
            $games = Game::with(['odds' => function($query) {
                    $query->take(3);
                }])
                ->where('visible', 'Sim')
                ->where('site_id', $siteId)
                ->where('sport_name', 'LIKE', '%' . $modality . '%')
                ->where('date', '>=', now())
                ->orderBy('date', 'asc')
                ->limit(100)
                ->get();

            $return = $this->groupMatchesByLeague($games, $assetsCdn);

            // 2. Jogos Manuais por Modalidade
            $manualEvents = \App\Models\ManualEvent::with('category')
                ->where('site_id', $siteId)
                ->whereHas('category', function($q) use ($modality) {
                    $q->where('name', 'LIKE', '%' . $modality . '%');
                })
                ->where('status', 'open')
                ->where('start_time', '>=', now()->subMinutes(5))
                ->get();

            $manualLeagues = $manualEvents->groupBy('league_name');
            foreach($manualLeagues as $leagueName => $matches) {
                $leagueArr = [
                    'league' => $leagueName,
                    'league_image' => asset('img/countries/trophy.svg'),
                    'flag' => asset('img/countries/trophy.svg'),
                    'match' => []
                ];

                foreach($matches as $match) {
                    $leagueArr['match'][] = $this->formatMatch($match);
                }
                $return[] = $leagueArr;
            }

        } catch (\Exception $e) {
            \Log::warning("Erro na busca por modalidade: " . $e->getMessage());
        }

        return response()->json($return);
    }

    public function getMatchesHome()
    {
        return $this->getMatchesByDate(0);
    }

    public function getMatchesAmanha()
    {
        return $this->getMatchesByDate(1);
    }

    public function getMatchesDepoisAmanha()
    {
        return $this->getMatchesByDate(2);
    }

    private function getMatchesByDate($daysFromNow)
    {
        $siteId = config('tenant.site_id', 1);
        $date = \Carbon\Carbon::today('America/Fortaleza')->addDays($daysFromNow);
        $return = [];
        
        // CDN de Assets (Team/League Logos)
        $assetsCdn = config('services.assets_cdn_url', 'https://assets.betsapi.com/v1/');

        try {
            // 1. Jogos da API (Game Model)
            $games = Game::withCount('odds')
                ->with(['odds' => function($query) {
                    $query->take(3); // Apenas os 3 principais na Home (1 X 2)
                }])
                ->where('visible', 'Sim')
                ->where('site_id', $siteId)
                ->whereDate('date', $date)
                ->orderBy('date', 'asc')
                ->get();

            // Carrega contagem de mercados únicos via subquery
            $gameIds = $games->pluck('id')->toArray();
            if (!empty($gameIds)) {
                $marketCounts = \DB::table('odds')
                    ->selectRaw('event_id, COUNT(DISTINCT market_name) as unique_markets')
                    ->whereIn('event_id', $games->pluck('event_id')->toArray())
                    ->groupBy('event_id')
                    ->get()
                    ->keyBy('event_id');

                foreach ($games as $game) {
                    $game->unique_markets_count = $marketCounts->has($game->event_id)
                        ? $marketCounts[$game->event_id]->unique_markets
                        : 0;
                }
            }

            $leagues = $games->groupBy('league');

            $return = $this->groupMatchesByLeague($games, $assetsCdn);

            // 2. Jogos Personalizados (ManualEvent Model)
            $manualEvents = \App\Models\ManualEvent::with('category')
                ->where('site_id', $siteId)
                ->where('status', 'open')
                ->whereDate('start_time', $date)
                ->where('start_time', '>=', now()->subMinutes(5))
                ->orderBy('start_time', 'asc')
                ->get();

            $manualLeagues = $manualEvents->groupBy('league_name');
            foreach($manualLeagues as $leagueName => $matches) {
                $leagueArr = [
                    'league' => $leagueName,
                    'league_image' => asset('img/countries/trophy.svg'),
                    'flag' => asset('img/countries/trophy.svg'),
                    'match' => []
                ];

                foreach($matches as $match) {
                    $leagueArr['match'][] = $this->formatMatch($match);
                }
                $return[] = $leagueArr;
            }

        } catch (\Exception $e) {
            \Log::warning("Erro ao buscar jogos: " . $e->getMessage());
        }

        return response()->json($return);
    }
    public function getOdds($id)
    {
        // Se começar com 'm', é um evento manual
        if (strpos($id, 'm') === 0) {
            $realId = substr($id, 1);
            $match = \App\Models\ManualEvent::where('status', 'open')->find($realId);
            if (!$match) return response()->json(['error' => 'Not found'], 404);

            $markets = [
                [
                    'id' => 'market_m'.$match->id.'_1x2',
                    'name' => 'Vencedor do Encontro',
                    'odds' => [
                        [
                            'id' => 'm'.$match->id.'_1', 
                            'odd' => 'Casa', 
                            'cotacao' => (float)$match->odd_home, 
                            'group_opp' => 'Vencedor do Encontro',
                            'uuid' => md5('m'.$match->id . 'Vencedor do Encontro' . 'Casa' . (float)$match->odd_home),
                            'cotacaoOriginal' => (float)$match->odd_home,
                            'type' => 'manual'
                        ],
                        [
                            'id' => 'm'.$match->id.'_X', 
                            'odd' => 'Empate', 
                            'cotacao' => (float)$match->odd_draw, 
                            'group_opp' => 'Vencedor do Encontro',
                            'uuid' => md5('m'.$match->id . 'Vencedor do Encontro' . 'Empate' . (float)$match->odd_draw),
                            'cotacaoOriginal' => (float)$match->odd_draw,
                            'type' => 'manual'
                        ],
                        [
                            'id' => 'm'.$match->id.'_2', 
                            'odd' => 'Fora', 
                            'cotacao' => (float)$match->odd_away, 
                            'group_opp' => 'Vencedor do Encontro',
                            'uuid' => md5('m'.$match->id . 'Vencedor do Encontro' . 'Fora' . (float)$match->odd_away),
                            'cotacaoOriginal' => (float)$match->odd_away,
                            'type' => 'manual'
                        ],
                    ]
                ]
            ];

            // Adiciona Mercados Extras (Dinâmicos)
            if (!empty($match->extra_markets)) {
                foreach ($match->extra_markets as $gIdx => $group) {
                    $selections = [];
                    foreach ($group['selections'] as $sIdx => $sel) {
                        $selections[] = [
                            'id' => 'm'.$match->id.'_extra_'.$gIdx.'_'.$sIdx,
                            'odd' => $sel['name'],
                            'cotacao' => (float)$sel['odd'],
                            'group_opp' => $group['group_name'],
                            'uuid' => md5('m'.$match->id . $group['group_name'] . $sel['name'] . (float)$sel['odd']),
                            'cotacaoOriginal' => (float)$sel['odd'],
                            'type' => 'manual'
                        ];
                    }

                    $markets[] = [
                        'id' => 'market_m'.$match->id.'_extra_'.$gIdx,
                        'name' => $group['group_name'],
                        'odds' => $selections
                    ];
                }
            }


            return response()->json($markets);
        }

        // Evento Automático
        $game = Game::find($id);
        if (!$game) {
            // Tentar pelo MatchModel (para jogos ao vivo)
            $game = \App\Models\MatchModel::find($id);
        }
        if (!$game) return response()->json(['error' => 'Not found'], 404);

        // Carrega TODAS as odds do jogo (não apenas 3)
        $allOdds = \App\Models\Odd::where('event_id', $game->event_id)->get();

        $mercados = [];
        $groupedOdds = $allOdds->groupBy('market_name'); 

        foreach($groupedOdds as $marketName => $odds) {
            $mercados[] = [
                'id' => md5($game->id . $marketName),
                'name' => $marketName,
                'odds' =>                 $odds->map(function($odd) {
                    $label = $odd->label;

                    $labelMap = [
                        'Home' => 'Casa',
                        'Draw' => 'Empate',
                        'Away' => 'Fora',
                        'Yes' => 'Sim',
                        'No' => 'Não',
                        'Odd' => 'Ímpar',
                        'Even' => 'Par',
                    ];

                    if (isset($labelMap[$label])) {
                        $label = $labelMap[$label];
                    } else {
                        foreach ($labelMap as $en => $pt) {
                            if (str_starts_with($label, $en . ' ')) {
                                $label = $pt . ' ' . substr($label, strlen($en) + 1);
                                break;
                            }
                        }
                    }

                    $label = str_replace('Over ', 'Acima de ', $label);
                    $label = str_replace('Under ', 'Abaixo de ', $label);
                    $label = str_replace('Home/', 'Casa/', $label);
                    $label = str_replace('Draw/', 'Empate/', $label);
                    $label = str_replace('Away/', 'Fora/', $label);
                    $label = str_replace('/Home', '/Casa', $label);
                    $label = str_replace('/Draw', '/Empate', $label);
                    $label = str_replace('/Away', '/Fora', $label);

                    return [
                        'id' => $odd->id,
                        'odd' => $label,
                        'cotacao' => (float)$odd->value,
                        'group_opp' => $odd->market_name,
                        'uuid' => md5($odd->event_id . $odd->market_name . $odd->label . (float)$odd->value),
                        'cotacaoOriginal' => (float)$odd->value,
                        'type' => 'automatic'
                    ];
                })
            ];
        }

        // Verificar se é uma requisição de live (rota /site-list-odds-live/)
        $isLiveRequest = str_contains(request()->path(), 'odds-live');
        if ($isLiveRequest) {
            // Formato esperado pelo frontend para live: [{ mercados: [...] }]
            return response()->json([['mercados' => $mercados]]);
        }

        return response()->json($mercados);
    }

    /**
     * Retorna status da API-Football e uso de quota diaria
     */
    public function getApiStatus()
    {
        $apiKey = config('services.apifootball.api_key');
        $apiUrl = config('services.apifootball.api_url');

        // Lê contador local de requests
        $counterFile = storage_path('logs/apifootball_requests_today.log');
        $localRequests = 0;
        if (file_exists($counterFile)) {
            $data = json_decode(file_get_contents($counterFile), true);
            if (($data['date'] ?? '') === now()->format('Y-m-d')) {
                $localRequests = $data['count'] ?? 0;
            }
        }

        // Tenta consultar status na API (consome 1 request)
        $apiStatus = null;
        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'x-apisports-key' => $apiKey,
                'Accept' => 'application/json',
            ])->timeout(10)->get($apiUrl . '/status');

            if ($response->successful()) {
                $body = $response->json();
                $apiStatus = $body['response'] ?? null;
            }
        } catch (\Exception $e) {
            // Ignora erro - usa apenas dados locais
        }

        return response()->json([
            'api_key_last8' => substr($apiKey, -8),
            'plan' => $apiStatus['account']['plan'] ?? 'Free',
            'requests_today' => $apiStatus['requests']['current'] ?? $localRequests,
            'requests_limit' => $apiStatus['requests']['limit_day'] ?? 100,
            'requests_remaining' => $apiStatus['requests']['remaining'] ?? max(0, 100 - $localRequests),
            'local_counter' => $localRequests,
            'last_update' => file_exists($counterFile) ? date('Y-m-d H:i:s', filemtime($counterFile)) : null,
        ]);
    }

    public function getMatchesSearch($search)
    {
        $siteId = config('tenant.site_id', 1);
        $searchQuery = '%' . $search . '%';
        $return = [];

        try {
            // 1. Automáticos
            $games = Game::with(['odds' => function($q) { $q->take(3); }])
                ->where('visible', 'Sim')
                ->where('site_id', $siteId)
                ->where(function($q) use ($searchQuery) {
                    $q->where('home', 'LIKE', $searchQuery)
                      ->orWhere('away', 'LIKE', $searchQuery)
                      ->orWhere('league', 'LIKE', $searchQuery);
                })
                ->orderBy('date', 'asc')
                ->limit(50)
                ->get();

            $leagues = $games->groupBy('league');
            foreach($leagues as $leagueName => $matches) {
                $leagueArr = ['league' => $leagueName, 'match' => []];
                foreach($matches as $m) { $leagueArr['match'][] = $this->formatMatch($m); }
                $return[] = $leagueArr;
            }

            $manuals = \App\Models\ManualEvent::with('category')->where('site_id', $siteId)
                ->where('status', 'open')
                ->where('start_time', '>=', now()->subMinutes(5))
                ->where(function($q) use ($searchQuery) {
                    $q->where('home_team', 'LIKE', $searchQuery)
                      ->orWhere('away_team', 'LIKE', $searchQuery)
                      ->orWhere('league_name', 'LIKE', $searchQuery)
                      ->orWhereHas('category', function($cq) use ($searchQuery) {
                          $cq->where('name', 'LIKE', $searchQuery);
                      });
                })
                ->get();

            $manualLeagues = $manuals->groupBy('league_name');
            foreach($manualLeagues as $name => $matches) {
                $leagueArr = ['league' => $name, 'match' => []];
                foreach($matches as $m) { $leagueArr['match'][] = $this->formatMatch($m); }
                $return[] = $leagueArr;
            }
        } catch (\Exception $e) { \Log::warning("Erro na busca ($search): " . $e->getMessage()); }

        return response()->json($return);
    }

    public function getLiveMarketConfig()
    {
        // Resposta padrão de configuração de mercados para destravar o Frontend
        return response()->json([
            "status" => "online",
            "market_config" => [
                "active" => true,
                "delay" => 5,
                "max_odd" => 100,
                "min_odd" => 1.05
            ],
            "settings" => [
                "live_betting_enabled" => true,
                "cashout_enabled" => true
            ]
        ]);
    }

    public function getMatches()
    {
        $siteId = config('tenant.site_id', 1);
        $matches = [];
        
        try {
            // Pega os jogos marcados EXPLICITAMENTE como destaque na tabela featured_matches
            $featuredIds = \DB::table('featured_matches')
                ->where('site_id', $siteId)
                ->orderBy('order', 'asc')
                ->get();

            foreach($featuredIds as $f) {
                $gameData = null;
                if ($f->is_manual) {
                    $gameData = \App\Models\ManualEvent::where('status', 'open')->find($f->manual_event_id);
                    if ($gameData) {
                        $startTime = \Carbon\Carbon::parse($gameData->start_time);
                        if ($gameData->status != 'open' || $startTime->isPast()) {
                            continue; 
                        }
                        $formatted = $this->formatMatch($gameData);
                        if (method_exists($this, 'applyFeaturedFormatting')) {
                            $this->applyFeaturedFormatting($formatted, $f);
                        }
                        $matches[] = $formatted;
                    }
                } else {
                    $gameData = Game::find($f->match_id);
                    if ($gameData) {
                        $gameDate = \Carbon\Carbon::parse($gameData->date);
                        if ($gameData->time_status >= 3 || $gameDate->isPast()) {
                            continue;
                        }
                        $formatted = $this->formatMatch($gameData);
                        if (method_exists($this, 'applyFeaturedFormatting')) {
                            $this->applyFeaturedFormatting($formatted, $f);
                        }
                        $matches[] = $formatted;
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::warning("Erro ao buscar featured matches: " . $e->getMessage());
        }

        return response()->json($matches);
    }

    protected function applyFeaturedFormatting(&$formatted, $f)
    {
        if (!empty($f->background_path)) {
            $bg = (strpos($f->background_path, 'http') === 0) ? $f->background_path : asset($f->background_path);
            $formatted['background'] = $bg;
            $formatted['bg_image'] = $bg;
            $formatted['img'] = $bg;
            $formatted['foto'] = $bg;
            $formatted['banner'] = $bg;
            $formatted['capa'] = $bg;
            $formatted['img_featured'] = $bg;
        }

        if (!empty($f->badge_color)) {
            $formatted['badge_color'] = $f->badge_color;
            $formatted['cor_badge'] = $f->badge_color;
        }
    }

    public function getFeaturedMatches()
    {
        $siteId = config('tenant.site_id', 1);
        $date = \Carbon\Carbon::today('America/Fortaleza');
        $matches = [];
        
        // 1. Jogos da API
        $games = Game::with('odds')
            ->where('visible', 'Sim')
            ->where('site_id', $siteId)
            ->whereDate('date', $date)
            ->orderBy('date', 'asc')
            ->take(15)
            ->get();

        foreach($games as $game) {
            $matches[] = $this->formatMatch($game);
        }

        // 2. Jogos Manuais
        $manualEvents = \App\Models\ManualEvent::with('category')
            ->where('site_id', $siteId)
            ->where('status', 'open')
            ->whereDate('start_time', $date)
            ->where('start_time', '>=', now()->subMinutes(5))
            ->orderBy('start_time', 'asc')
            ->take(5)
            ->get();

        foreach($manualEvents as $me) {
            $matches[] = $this->formatMatch($me);
        }

        return response()->json($matches);
    }

    public function getBanners()
    {
        $site = Site::where('id', config('tenant.site_id', 1))->first() ?? Site::first();

        if (!$site) {
            return response()->json([]);
        }

        // carrosel_ativado = null ou 1 = ativado; so desativa se explicitamente = 0
        if ((int)($site->carrosel_ativado ?? 1) === 0) {
            return response()->json([]);
        }

        $banners = Banner::withoutGlobalScopes()
            ->where('site_id', $site->id)
            ->where('status', 1)
            ->orderBy('order_index')
            ->get()
            ->map(function($b) {
                $imgUrl = (str_starts_with($b->image_path, 'http'))
                    ? $b->image_path
                    : asset($b->image_path);
                return [
                    'id'    => $b->id,
                    'image' => $imgUrl,
                    'img'   => $imgUrl,
                    'foto'  => $imgUrl,
                    'link'  => $b->link ?? '#',
                    'position' => $b->position ?? 'home_main',
                ];
            });

        return response()->json($banners);
    }

    /**
     * 🔍 Busca liga por nome
     */
    public function searchLeague(Request $request)
    {
        $siteId = config('tenant.site_id', 1);
        $search = '%' . ($request->league ?? $request->search ?? '') . '%';

        $games = Game::with(['odds' => fn($q) => $q->take(3)])
            ->where('visible', 'Sim')
            ->where('site_id', $siteId)
            ->where('league', 'LIKE', $search)
            ->where('date', '>=', now())
            ->orderBy('date', 'asc')
            ->limit(50)
            ->get();

        $return = [];
        $leagues = $games->groupBy('league');
        foreach ($leagues as $leagueName => $matches) {
            $leagueArr = ['league' => $leagueName, 'match' => []];
            foreach ($matches as $m) {
                $leagueArr['match'][] = $this->formatMatch($m);
            }
            $return[] = $leagueArr;
        }

        return response()->json($return);
    }

    /**
     * 🔍 Busca time por nome (original: MatchController@searchTime)
     */
    public function searchTeam(Request $request)
    {
        $siteId = config('tenant.site_id', 1);
        $search = '%' . ($request->team ?? $request->search ?? '') . '%';

        $games = Game::with(['odds' => fn($q) => $q->take(3)])
            ->where('visible', 'Sim')
            ->where('site_id', $siteId)
            ->where(function($q) use ($search) {
                $q->where('home', 'LIKE', $search)
                  ->orWhere('away', 'LIKE', $search);
            })
            ->where('date', '>=', now())
            ->orderBy('date', 'asc')
            ->limit(50)
            ->get();

        $return = [];
        $leagues = $games->groupBy('league');
        foreach ($leagues as $leagueName => $matches) {
            $leagueArr = ['league' => $leagueName, 'match' => []];
            foreach ($matches as $m) {
                $leagueArr['match'][] = $this->formatMatch($m);
            }
            $return[] = $leagueArr;
        }

        return response()->json($return);
    }
}
