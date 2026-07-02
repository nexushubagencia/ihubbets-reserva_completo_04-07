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

class SportsApiController extends ApiController
{
    /**
     * Rota genérica: /data/{sport}/{day}
     * O frontend chama, por exemplo: /data/soccer/today, /data/soccer/tomorrow, /data/soccer/2026-04-25
     */

    public function listLeagues()
    {
        // Mapa de nomes exatos de ligas para país (ligas que NÃO contêm o nome do país)
        $exactLeagueMap = [
            'Bundesliga' => ['cc' => 'de', 'country' => 'Alemanha'],
            'La Liga' => ['cc' => 'es', 'country' => 'Espanha'],
            'Ligue 1' => ['cc' => 'fr', 'country' => 'França'],
            'Ligue 2' => ['cc' => 'fr', 'country' => 'França'],
            'Premier League' => ['cc' => 'gb', 'country' => 'Grã Bretanha'],
            'Championship' => ['cc' => 'gb', 'country' => 'Grã Bretanha'],
            'Serie A Italia' => ['cc' => 'it', 'country' => 'Itália'],
            'Serie A Itália' => ['cc' => 'it', 'country' => 'Itália'],
            'Serie B Italia' => ['cc' => 'it', 'country' => 'Itália'],
            'Serie B Itália' => ['cc' => 'it', 'country' => 'Itália'],
            'Eredivisie' => ['cc' => 'nl', 'country' => 'Países Baixos'],
            'NBA' => ['cc' => 'us', 'country' => 'EUA'],
            'NFL' => ['cc' => 'us', 'country' => 'EUA'],
            'MLS' => ['cc' => 'us', 'country' => 'EUA'],
            'Liga MX' => ['cc' => 'mx', 'country' => 'México'],
            'Copa América' => ['cc' => null, 'country' => 'Outros'],
            'Copa Libertadores' => ['cc' => null, 'country' => 'Outros'],
            'Copa Sul-Americana' => ['cc' => null, 'country' => 'Outros'],
            'Liga dos Campeões' => ['cc' => null, 'country' => 'Outros'],
            'Champions League' => ['cc' => null, 'country' => 'Outros'],
            'Europa League' => ['cc' => null, 'country' => 'Outros'],
            'Copa do Mundo' => ['cc' => null, 'country' => 'Outros'],
        ];

        // Mapa de prefixos no nome da liga para código de país
        $countryPrefixMap = [
            'Brasil' => ['cc' => 'br', 'country' => 'Brasil'],
            'Série A Brasil' => ['cc' => 'br', 'country' => 'Brasil'],
            'Série B Brasil' => ['cc' => 'br', 'country' => 'Brasil'],
            'Série C' => ['cc' => 'br', 'country' => 'Brasil'],
            'Série D' => ['cc' => 'br', 'country' => 'Brasil'],
            'Campeonato' => ['cc' => 'br', 'country' => 'Brasil'],
            'Argentina' => ['cc' => 'ar', 'country' => 'Argentina'],
            'Inglaterra' => ['cc' => 'gb', 'country' => 'Grã Bretanha'],
            'Escócia' => ['cc' => 'gb', 'country' => 'Grã Bretanha'],
            'Espanha' => ['cc' => 'es', 'country' => 'Espanha'],
            'Itália' => ['cc' => 'it', 'country' => 'Itália'],
            'Italia' => ['cc' => 'it', 'country' => 'Itália'],
            'Alemanha' => ['cc' => 'de', 'country' => 'Alemanha'],
            'França' => ['cc' => 'fr', 'country' => 'França'],
            'Portugal' => ['cc' => 'pt', 'country' => 'Portugal'],
            'Holanda' => ['cc' => 'nl', 'country' => 'Países Baixos'],
            'Países Baixos' => ['cc' => 'nl', 'country' => 'Países Baixos'],
            'Bélgica' => ['cc' => 'be', 'country' => 'Bélgica'],
            'Turquia' => ['cc' => 'tr', 'country' => 'Turquia'],
            'Grécia' => ['cc' => 'gr', 'country' => 'Grécia'],
            'Suíça' => ['cc' => 'ch', 'country' => 'Suíça'],
            'Áustria' => ['cc' => 'at', 'country' => 'Áustria'],
            'Dinamarca' => ['cc' => 'dk', 'country' => 'Dinamarca'],
            'Suécia' => ['cc' => 'se', 'country' => 'Suécia'],
            'Noruega' => ['cc' => 'no', 'country' => 'Noruega'],
            'Finlândia' => ['cc' => 'fi', 'country' => 'Finlândia'],
            'Polônia' => ['cc' => 'pl', 'country' => 'Polônia'],
            'Polónia' => ['cc' => 'pl', 'country' => 'Polônia'],
            'Romênia' => ['cc' => 'ro', 'country' => 'Romênia'],
            'Roménia' => ['cc' => 'ro', 'country' => 'Romênia'],
            'Hungria' => ['cc' => 'hu', 'country' => 'Hungria'],
            'Chéquia' => ['cc' => 'cz', 'country' => 'Chéquia'],
            'Eslováquia' => ['cc' => 'sk', 'country' => 'Eslováquia'],
            'Croácia' => ['cc' => 'hr', 'country' => 'Croácia'],
            'Sérvia' => ['cc' => 'rs', 'country' => 'Sérvia'],
            'Eslovénia' => ['cc' => 'si', 'country' => 'Eslovénia'],
            'Bósnia' => ['cc' => 'ba', 'country' => 'Bósnia e Herzegovina'],
            'Bulgária' => ['cc' => 'bg', 'country' => 'Bulgária'],
            'Ucrânia' => ['cc' => 'ua', 'country' => 'Ucrânia'],
            'Rússia' => ['cc' => 'ru', 'country' => 'Rússia'],
            'Israel' => ['cc' => 'il', 'country' => 'Israel'],
            'Chipre' => ['cc' => 'cy', 'country' => 'Chipre'],
            'EUA' => ['cc' => 'us', 'country' => 'EUA'],
            'México' => ['cc' => 'mx', 'country' => 'México'],
            'Colômbia' => ['cc' => 'co', 'country' => 'Colômbia'],
            'Chile' => ['cc' => 'cl', 'country' => 'Chile'],
            'Peru' => ['cc' => 'pe', 'country' => 'Peru'],
            'Paraguai' => ['cc' => 'py', 'country' => 'Paraguai'],
            'Uruguai' => ['cc' => 'uy', 'country' => 'Uruguai'],
            'Equador' => ['cc' => 'ec', 'country' => 'Equador'],
            'Venezuela' => ['cc' => 've', 'country' => 'Venezuela'],
            'Bolívia' => ['cc' => 'bo', 'country' => 'Bolívia'],
            'Japão' => ['cc' => 'jp', 'country' => 'Japão'],
            'Coreia' => ['cc' => 'kr', 'country' => 'Coreia do Sul'],
            'China' => ['cc' => 'cn', 'country' => 'China'],
            'Índia' => ['cc' => 'in', 'country' => 'Índia'],
            'Austrália' => ['cc' => 'au', 'country' => 'Austrália'],
            'Canadá' => ['cc' => 'ca', 'country' => 'Canadá'],
            'Irlanda' => ['cc' => 'ie', 'country' => 'Irlanda'],
            'Angola' => ['cc' => 'ao', 'country' => 'Angola'],
            'Nigéria' => ['cc' => 'ng', 'country' => 'Nigéria'],
            'África do Sul' => ['cc' => 'za', 'country' => 'África do Sul'],
            'Copa' => ['cc' => null, 'country' => 'Outros'],
            'UEFA' => ['cc' => null, 'country' => 'Outros'],
            'CAF' => ['cc' => null, 'country' => 'Outros'],
            'AFC' => ['cc' => null, 'country' => 'Outros'],
            'Libertadores' => ['cc' => null, 'country' => 'Outros'],
            'Sul-Americana' => ['cc' => null, 'country' => 'Outros'],
            'Taça' => ['cc' => null, 'country' => 'Outros'],
        ];

        $mainLeaguesIds = \App\Models\MainLeague::where('site_id', config('tenant.site_id', 1))
            ->pluck('league_id')
            ->toArray();

        $leagues = Game::select('league', 'league_cc', 'league_id')
            ->where('site_id', config('tenant.site_id', 1))
            ->whereNotIn('league_id', $mainLeaguesIds)
            ->groupBy('league', 'league_cc', 'league_id')
            ->orderBy('league', 'asc')
            ->get();


        $grouped = [];

        foreach ($leagues as $g) {
            $leagueName = $g->league;
            $cc = strtolower($g->league_cc ?? '');
            $countryName = null;
            $countryCode = null;

            // 1. Usar a nova lógica centralizada resolveLeagueCc
            $countryCode = $this->resolveLeagueCc($leagueName, $cc);
            
            if ($countryCode) {
                $countryName = $this->getCountryNameFromCc($countryCode);
            } else {
                $countryName = null;
            }

            // 3. Se ainda não encontrou, usa league_cc do banco (se não for 'br' genérico)
            if ($countryName === null && $cc && $cc !== 'br') {
                $countryCode = $cc;
                $countryName = strtoupper($cc);
            }

            // 4. Fallback: Outros
            if ($countryName === null) {
                $countryCode = null;
                $countryName = 'Outros';
            }

            $key = $countryCode ?? 'other';
            
            // Lógica de Bandeira: Local (.svg) -> FlagCDN -> Trophy
            $flagUrl = null;
            if ($countryCode) {
                $localPath = public_path('img/countries/' . $countryCode . '.svg');
                if (file_exists($localPath)) {
                    $flagUrl = asset('img/countries/' . $countryCode . '.svg');
                } else {
                    $flagUrl = 'https://flagcdn.com/w40/' . $countryCode . '.png';
                }
            } else {
                $flagUrl = asset('img/countries/trophy.svg');
            }

            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'country' => $countryName,
                    'cc' => $countryCode,
                    'flag' => $flagUrl,
                    'leagues' => []
                ];
            }

            $grouped[$key]['leagues'][] = [
                'sport' => 'Futebol',
                'cc' => $countryCode,
                'flag' => $flagUrl,
                'league' => $leagueName
            ];
        }

        // Ordena: Brasil primeiro, depois alfabeticamente, Outros por último
        $result = array_values($grouped);
        usort($result, function($a, $b) {
            if ($a['cc'] === 'br') return -1;
            if ($b['cc'] === 'br') return 1;
            if ($a['cc'] === null) return 1;
            if ($b['cc'] === null) return -1;
            return strcmp($a['country'], $b['country']);
        });
            
        return response()->json($result);
    }

    public function listLeaguesMain()
    {
        $siteId = config('tenant.site_id', 1);
        $assetsCdn = env('ASSETS_CDN_URL', 'https://assets.betsapi.com/v1/');
        
        // Mapa de nomes exatos de ligas para país (Mantido para compatibilidade, mas resolveLeagueCc é a prioridade)
        $exactLeagueMap = [
            'Bundesliga' => 'de', 'La Liga' => 'es', 'Ligue 1' => 'fr', 'Ligue 2' => 'fr',
            'Premier League' => 'gb', 'Championship' => 'gb',
            'Serie A Italia' => 'it', 'Serie A Itália' => 'it', 'Serie B Italia' => 'it',
            'Eredivisie' => 'nl', 'NBA' => 'us', 'NFL' => 'us', 'MLS' => 'us', 'Liga MX' => 'mx',
            'Copa América' => null, 'Copa Libertadores' => null, 'Copa Sul-Americana' => null,
            'Liga dos Campeões' => null, 'Champions League' => null, 'Europa League' => null,
            'Copa do Mundo' => null,
        ];
        
        // Mapa de prefixos 
        $prefixMap = [
            'Brasil' => 'br', 'Série' => 'br', 'Campeonato' => 'br',
            'Argentina' => 'ar', 'Inglaterra' => 'gb', 'Espanha' => 'es',
            'Itália' => 'it', 'Italia' => 'it', 'Alemanha' => 'de',
            'França' => 'fr', 'Portugal' => 'pt', 'Holanda' => 'nl',
            'Turquia' => 'tr', 'Grécia' => 'gr', 'México' => 'mx',
            'Colômbia' => 'co', 'Chile' => 'cl', 'Peru' => 'pe',
            'Paraguai' => 'py', 'Uruguai' => 'uy', 'Equador' => 'ec',
            'Japão' => 'jp', 'Coreia' => 'kr', 'China' => 'cn',
            'Austrália' => 'au', 'EUA' => 'us',
            'Copa' => null, 'UEFA' => null, 'Libertadores' => null, 'Sul-Americana' => null,
        ];

        $leagues = [];

        // 1. Busca as Ligas que você marcou como principais no Painel
        $mainLeaguesConfig = \App\Models\MainLeague::where('site_id', $siteId)->get();
        
        // Se não houver nada configurado, pegamos as top ligas do sistema como fallback (para não ficar vazio)
        if ($mainLeaguesConfig->isEmpty()) {
             $autoLeagues = Game::select('league', 'league_id', 'league_cc')
                ->where('site_id', $siteId)
                ->where('date', '>=', now())
                ->groupBy('league', 'league_id', 'league_cc')
                ->limit(10) // Pega as 10 primeiras como exemplo
                ->get();
        } else {
            $autoLeagues = $mainLeaguesConfig;
        }


        foreach($autoLeagues as $l) {
            $leagueName = $l->league;
            $resolvedCc = null;
            
            // 1. Tenta mapeamento exato
            foreach ($exactLeagueMap as $exact => $cc) {
                if (strcasecmp($leagueName, $exact) === 0 || stripos($leagueName, $exact) !== false) {
                    $resolvedCc = $cc;
                    break;
                }
            }
            
            // 2. Tenta prefixo
            if ($resolvedCc === null) {
                foreach ($prefixMap as $prefix => $cc) {
                    if (stripos($leagueName, $prefix) !== false) {
                        $resolvedCc = $cc;
                        break;
                    }
                }
            }
            
            // 3. Fallback para league_cc do banco (se não for 'br' genérico)
            if ($resolvedCc === null) {
                $dbCc = strtolower($l->league_cc ?? '');
                if ($dbCc && $dbCc !== 'br') {
                    $resolvedCc = $dbCc;
                }
            }

            // Lógica de Bandeira: Local -> FlagCDN -> Trophy
            $flagUrl = null;
            if ($resolvedCc) {
                $localPath = public_path('img/countries/' . $resolvedCc . '.svg');
                if (file_exists($localPath)) {
                    $flagUrl = asset('img/countries/' . $resolvedCc . '.svg');
                } else {
                    $flagUrl = 'https://flagcdn.com/w40/' . $resolvedCc . '.png';
                }
            } else {
                $flagUrl = asset('img/countries/trophy.svg');
            }

            $leagues[] = [
                'name' => $leagueName,
                'league' => $leagueName,
                'cc' => $resolvedCc,
                'flag' => $flagUrl,
                'image' => $assetsCdn . 'league/' . ($l->league_id ?? '0') . '.png',
                'id' => $l->league_id,
                'sport' => 'Futebol'
            ];
        }

        // 2. Ligas Manuais
        $manualLeagues = \App\Models\ManualEvent::with('category')
            ->where('site_id', $siteId)
            ->where('status', 'open')
            ->where('start_time', '>=', now()->subMinutes(5))
            ->get()
            ->groupBy('league_name');

        foreach($manualLeagues as $name => $events) {
            $leagues[] = [
                'name' => $name,
                'league' => $name,
                'cc' => null,
                'flag' => asset('img/countries/trophy.svg'),
                'image' => $this->getPlaceholder($events->first()->category->name ?? 'Outros', 'league'),
                'id' => 'm' . $events->first()->id,
                'sport' => $events->first()->category->name ?? 'Outros'
            ];
        }

        return response()->json($leagues);
    }

    public function getModalities()
    {
        // O frontend legado espera um array simples de strings para evitar erros de processamento
        return response()->json([
            'Futebol',
            'Luta',
            'Basquete',
            'Tênis',
            'Vôlei',
            'Vaquejada',
            'X1 Futebol',
            'E-Sports'
        ]);
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

    /**
     * 🔍 Busca liga por nome (original: MatchController@searchLeague)
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
            $leagueArr = ['league' => $leagueName, 'flag' => asset('img/countries/trophy.svg'), 'match' => []];
            foreach ($matches as $m) {
                $leagueArr['match'][] = $this->formatMatch($m);
            }
            $return[] = $leagueArr;
        }

        // Busca Manuais
        $manuals = \App\Models\ManualEvent::where('site_id', $siteId)
            ->where('status', 'open')
            ->where('league_name', 'LIKE', $search)
            ->where('start_time', '>=', now())
            ->get();

        $manualLeagues = $manuals->groupBy('league_name');
        foreach ($manualLeagues as $name => $matches) {
            $leagueArr = ['league' => $name, 'flag' => asset('img/countries/trophy.svg'), 'match' => []];
            foreach ($matches as $m) {
                $leagueArr['match'][] = $this->formatMatch($m);
            }
            $return[] = $leagueArr;
        }

        return response()->json($return);
    }
}

