<?php

namespace App\Traits;

use App\Models\Game;
use App\Models\ManualEvent;

trait MatchFormattingTrait
{
    protected function getPlaceholder($sport, $type = 'team')
    {
        if ($type == 'league') {
            return 'trophy.svg';
        }
        
        $sport = strtolower((string)$sport);
        
        // Se for Luta, Boxe, MMA, Tênis ou qualquer esporte individual
        if (
            str_contains($sport, 'luta') || 
            str_contains($sport, 'mma') || 
            str_contains($sport, 'boxe') || 
            str_contains($sport, 'tênis') || 
            str_contains($sport, 'tenis') || 
            str_contains($sport, 'lutador') ||
            str_contains($sport, 'ufc') ||
            str_contains($sport, 'person')
        ) {
            return 'person.png';
        }

        // Se for Futebol, Basquete ou esportes de equipe (Padrão: Escudo)
        return 'shield.png';
    }

    protected function formatMatch($match)
    {
        $isManual = ($match instanceof \App\Models\ManualEvent);
        $assetsCdn = config('services.assets_cdn_url', 'https://assets.betsapi.com/v1/');
        
        $sport = (string)($isManual ? ($match->category->name ?? 'Futebol') : ($match->sport_name ?? $match->sport ?? 'Futebol'));
        $placeholder = $this->getPlaceholder($sport);
        
        if ($isManual) {
            $home_img = !empty($match->home_flag) ? asset($match->home_flag) : asset('img/placeholders/' . $placeholder);
            $away_img = !empty($match->away_flag) ? asset($match->away_flag) : asset('img/placeholders/' . $placeholder);
            $league_img = asset('img/placeholders/trophy.svg');
            $id = 'm' . $match->id;
            $home = $match->home_team;
            $away = $match->away_team;
            $date = \Carbon\Carbon::parse($match->start_time)->format('Y-m-d H:i:s');
            $time = \Carbon\Carbon::parse($match->start_time)->format('H:i');
            $league = $match->league_name;

            $odds = [
                ['id' => $id.'_1', 'uuid' => $id.'_1', 'type' => 'pre', 'group_opp' => 'Vencedor do Encontro', 'odd' => 'Casa', 'cotacao' => (float)$match->odd_home, 'cotacaoOriginal' => (float)$match->odd_home],
                ['id' => $id.'_x', 'uuid' => $id.'_x', 'type' => 'pre', 'group_opp' => 'Vencedor do Encontro', 'odd' => 'Empate', 'cotacao' => (float)$match->odd_draw, 'cotacaoOriginal' => (float)$match->odd_draw],
                ['id' => $id.'_2', 'uuid' => $id.'_2', 'type' => 'pre', 'group_opp' => 'Vencedor do Encontro', 'odd' => 'Fora', 'cotacao' => (float)$match->odd_away, 'cotacaoOriginal' => (float)$match->odd_away],
            ];
            // Calcula o total de odds dinamicamente
            $count_extra = 0;
            if (!empty($match->extra_markets)) {
                foreach ($match->extra_markets as $group) {
                    $count_extra += count($group['selections'] ?? []);
                }
            }
            $count_odd = 3 + $count_extra;
        } else {
            $home_img = $this->resolveTeamImage($match->image_id_home, $sport, $placeholder);
            $away_img = $this->resolveTeamImage($match->image_id_away, $sport, $placeholder);
            $league_img = $this->resolveLeagueImage($match->league_id);
            
            $id = $match->id;
            $home = $match->home_name ?? $match->home ?? 'Mandante';
            $away = $match->away_name ?? $match->away ?? 'Visitante';
            $date = is_string($match->date) ? $match->date : ($match->date ? $match->date->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'));
            $time = $match->time ?? (is_string($match->date) ? substr($match->date, 11, 5) : ($match->date ? $match->date->format('H:i') : '00:00'));
            $league = $match->league_name ?? $match->league ?? 'Liga';
            
            // Pega TODAS as odds para calcular o count real
            $allOdds = $match->odds ?? collect([]);

            // Usa unique_markets_count do controller (subquery) se disponível
            if (isset($match->unique_markets_count) && $match->unique_markets_count > 0) {
                $count_odd = $match->unique_markets_count;
            } else {
                // Fallback: conta mercados únicos das odds carregadas
                $uniqueMarkets = $allOdds->pluck('market_name')->unique()->count();
                $count_odd = $uniqueMarkets;
            }

            // Filtra para mostrar apenas o mercado principal (1X2) na listagem inicial
            $mainMarketOdds = $allOdds->filter(function($o) {
                $m = strtoupper($o->market_name ?? '');
                return str_contains($m, 'VENCEDOR DO ENCONTRO') 
                    || str_contains($m, 'MATCH WINNER') 
                    || str_contains($m, '1X2') 
                    || str_contains($m, 'RESULTADO FINAL')
                    || (str_contains($m, 'VENCEDOR') && !str_contains($m, 'TEMPO'));
            })->values()->take(3);

            // Fallback if main market not found or incomplete
            if ($mainMarketOdds->count() < 3) {
                $mainMarketOdds = $allOdds->values()->take(3);
            }

            // Se ainda assim não tem odds, gera placeholders (cotacao=0 = cadeado no frontend)
            if ($mainMarketOdds->isEmpty()) {
                $mainMarketOdds = collect([
                    (object)['id' => $id . '_1', 'value' => 0, 'market_name' => 'Vencedor do Encontro'],
                    (object)['id' => $id . '_x', 'value' => 0, 'market_name' => 'Vencedor do Encontro'],
                    (object)['id' => $id . '_2', 'value' => 0, 'market_name' => 'Vencedor do Encontro'],
                ]);
            }

            $odds = $mainMarketOdds->map(function($odd, $index) {
                // Force labels for the first 3 slots to match UI boxes
                $label = 'Casa';
                if ($index == 1) $label = 'Empate';
                if ($index == 2) $label = 'Fora';

                return [
                    'id' => $odd->id,
                    'uuid' => $odd->id,
                    'type' => 'pre',
                    'group_opp' => $odd->market_name ?? 'Vencedor do Encontro',
                    'odd' => $label,
                    'cotacao' => (float)($odd->value ?? 0),
                    'cotacaoOriginal' => (float)($odd->value ?? 0)
                ];
            })->toArray();
        }

        // Label do botao de mercados
        $buttonLabel = 'Apostar Agora';
        if ($count_odd > 3) {
            $buttonLabel = '+ ' . ($count_odd - 3) . ' odds';
        } elseif ($count_odd == 0) {
            $buttonLabel = 'Em Breve';
        }

        // Dados ao vivo
        $isLive = !$isManual && ($match->time_status ?? 0) == 1;
        $score = $isLive ? ($match->score ?? '') : '';
        $elapsed = $isLive ? ($match->time ?? 0) : 0;
        $homeScore = $isLive ? ($match->home_true ?? 0) : null;
        $awayScore = $isLive ? ($match->away_true ?? 0) : null;

        return [
            'id' => $id,
            'match_id' => $id,
            'event_id' => $id,
            'sport' => $sport,
            'modalidade' => $sport,
            'league' => $league,
            'home' => $home,
            'away' => $away,
            'confronto' => $home . ' x ' . $away,
            'date' => $date,
            'time' => $time,
            'logo_home' => $home_img,
            'logo_away' => $away_img,
            'home_img' => $home_img,
            'away_img' => $away_img,
            'img_home' => $home_img,
            'img_away' => $away_img,
            'league_img' => $league_img,
            'league_cc' => $isManual ? null : $this->resolveLeagueCc($league, $match->league_cc ?? null),
            'flag' => $isManual ? asset('img/countries/trophy.svg') : $this->resolveFlagUrl($this->resolveLeagueCc($league, $match->league_cc ?? null)),
            'is_manual' => $isManual,
            'background' => asset('images/featured_bg.jpg'),
            'img_featured' => $match->img_featured ?? asset('images/featured_bg.jpg'),
            'badge_color' => '#1aa6d0',
            'cor_badge' => $match->cor_badge ?? '#1aa6d0',
            'cor_texto' => '#ffffff',
            'cor_descricao' => '#dddddd',
            'odds' => $odds,
            'count_odd' => (int)$count_odd,
            'count_odd_label' => $buttonLabel,
            'label_odds' => $buttonLabel,
            'odds_label' => $buttonLabel,
            'mais_odds' => $buttonLabel,
            'button_text' => $buttonLabel,
            'badge_text' => $buttonLabel,
            'time_status' => $isManual ? 0 : ($match->time_status ?? 0),
            'timer' => $isManual ? "" : ($match->timer ?? ''),
            // Campos ao vivo
            'score' => $score,
            'elapsed' => (int)$elapsed,
            'home_score' => $homeScore,
            'away_score' => $awayScore,
            'is_live' => $isLive,
            'minute' => $isLive ? ($elapsed . "'") : '',
        ];
    }

    protected function resolveTeamImage($imageId, $sportName = 'Futebol', $placeholder = 'shield.png')
    {
        $placeholderUrl = asset('img/placeholders/' . $placeholder);

        if (!$imageId || $imageId == '0' || $imageId === '') {
            return $placeholderUrl;
        }

        // URL completa (local ou CDN) - retorna direto
        if (filter_var($imageId, FILTER_VALIDATE_URL)) {
            return $imageId;
        }

        $sport = strtolower((string)$sportName);

        // Scraper Jogadinha usa prefixo 'joga_' (ex: joga_flamengo)
        if (str_starts_with($imageId, 'joga_')) {
            $name = str_replace('joga_', '', $imageId);
            return 'https://d2x9mcd4yw5kj3.cloudfront.net/flags/soccer/m/' . $name . '.png';
        }

        // Prefixo 'custom_'
        if (str_starts_with($imageId, 'custom_')) {
            return 'https://d2x9mcd4yw5kj3.cloudfront.net/flags/soccer/m/' . $imageId . '.png';
        }

        // ID numérico - tenta CDN da BetsAPI
        if (is_numeric($imageId)) {
            return 'https://assets.b365api.com/images/team/m/' . $imageId . '.png';
        }

        return $placeholderUrl;
    }

    protected function resolveLeagueImage($leagueId)
    {
        if (!$leagueId || $leagueId == '0') {
            return asset('img/placeholders/trophy.svg');
        }

        // Se for URL completa
        if (filter_var($leagueId, FILTER_VALIDATE_URL)) {
            return $leagueId;
        }

        // ID numérico - CDN da BetsAPI
        return 'https://assets.b365api.com/images/league/m/' . $leagueId . '.png';
    }

    protected function resolveFlagUrl($cc)
    {
        if (!$cc) {
            return asset('img/countries/trophy.svg');
        }

        $cc = strtolower($cc);
        $localPath = public_path('img/countries/' . $cc . '.svg');
        
        if (file_exists($localPath)) {
            return asset('img/countries/' . $cc . '.svg');
        }

        // Fallback: FlagPedia / FlagCDN
        return 'https://flagcdn.com/w40/' . $cc . '.png';
    }

    protected function getCountryNameFromCc($cc)
    {
        $map = [
            'br' => 'Brasil',
            'gb' => 'Inglaterra',
            'es' => 'Espanha',
            'de' => 'Alemanha',
            'it' => 'Itália',
            'fr' => 'França',
            'pt' => 'Portugal',
            'us' => 'EUA',
            'ar' => 'Argentina',
            'nl' => 'Holanda',
            'mx' => 'México',
            'tr' => 'Turquia',
            'gr' => 'Grécia',
            'co' => 'Colômbia',
            'cl' => 'Chile',
            'pe' => 'Peru',
            'py' => 'Paraguai',
            'uy' => 'Uruguai',
            'ec' => 'Equador',
            'jp' => 'Japão',
            'kr' => 'Coreia',
            'cn' => 'China',
            'au' => 'Austrália',
        ];
        return $map[strtolower($cc)] ?? strtoupper($cc);
    }

    protected function resolveLeagueCc($leagueName, $currentCc = null)
    {
        // 1. Torneios Internacionais / Genéricos (Usar Troféu)
        $internationalKeywords = [
            'Champions League', 'Liga dos Campeões', 'Europa League', 'Conference League',
            'Libertadores', 'Sul-Americana', 'Sudamericana', 'Recopa', 'Copa America', 'Copa América',
            'Eurocopa', 'Copa do Mundo', 'World Cup', 'Mundial', 'FIFA', 'Qualificação', 'Eliminatórias',
            'Play-offs', 'Amigáveis', 'Friendlies', 'International', 'Inter-Clubes', 'Supercopa',
            'UEFA', 'CONMEBOL', 'AFC', 'CAF', 'CONCACAF'
        ];

        foreach ($internationalKeywords as $keyword) {
            if (stripos($leagueName, $keyword) !== false) {
                // Exceções: Torneios nacionais que usam "Copa" ou "Supercopa"
                if (stripos($leagueName, 'Brasil') !== false) return 'br';
                if (stripos($leagueName, 'Espanha') !== false || stripos($leagueName, 'España') !== false) return 'es';
                if (stripos($leagueName, 'Itália') !== false || stripos($leagueName, 'Italia') !== false) return 'it';
                if (stripos($leagueName, 'Inglaterra') !== false || stripos($leagueName, 'England') !== false) return 'gb';
                if (stripos($leagueName, 'Alemanha') !== false || stripos($leagueName, 'Germany') !== false) return 'de';
                if (stripos($leagueName, 'França') !== false || stripos($leagueName, 'France') !== false) return 'fr';
                if (stripos($leagueName, 'Portugal') !== false) return 'pt';
                return null; // Retorna Trophy
            }
        }

        // 2. Mapeamento de Países/Ligas Famosas
        $leagueMap = [
            'Premier League' => 'gb', 'Championship' => 'gb', 'League One' => 'gb', 'League Two' => 'gb', 'FA Cup' => 'gb',
            'La Liga' => 'es', 'Copa del Rey' => 'es', 'Segunda Division' => 'es',
            'Serie A' => 'it', 'Serie B' => 'it', 'Coppa Italia' => 'it',
            'Bundesliga' => 'de', 'DFB Pokal' => 'de',
            'Ligue 1' => 'fr', 'Ligue 2' => 'fr', 'Coupe de France' => 'fr',
            'Eredivisie' => 'nl',
            'Primeira Liga' => 'pt', 'Taça de Portugal' => 'pt',
            'Brasileirão' => 'br', 'Série A' => 'br', 'Série B' => 'br', 'Série C' => 'br', 'Série D' => 'br',
            'Copa do Brasil' => 'br', 'Carioca' => 'br', 'Paulista' => 'br', 'Mineiro' => 'br', 'Gaúcho' => 'br',
            'NBA' => 'us', 'NFL' => 'us', 'MLS' => 'us', 'NHL' => 'us', 'MLB' => 'us',
            'Liga MX' => 'mx',
            'Argentina' => 'ar', 'Uruguay' => 'uy', 'Chile' => 'cl', 'Colombia' => 'co', 'Peru' => 'pe', 'Ecuador' => 'ec', 'Paraguay' => 'py',
            'Japan' => 'jp', 'China' => 'cn', 'Korea' => 'kr',
        ];

        foreach ($leagueMap as $keyword => $cc) {
            if (stripos($leagueName, $keyword) !== false) {
                return $cc;
            }
        }

        $cc = strtolower($currentCc ?? '');
        // Se for 'br' mas o nome da liga não tiver 'Brasil', provavelmente é um fallback errado do banco
        if ($cc === 'br' && stripos($leagueName, 'Brasil') === false) {
            return null;
        }

        return $cc ?: null;
    }

    protected function groupMatchesByLeague($games, $assetsCdn)
    {
        $leagues = $games->groupBy('league');
        $result = [];

        foreach($leagues as $leagueName => $matches) {
            $firstMatch = $matches->first();
            
            // Resolver o CC da liga para pegar a bandeira usando o novo método centralizado
            $resolvedCc = $this->resolveLeagueCc($leagueName, $firstMatch->league_cc ?? null);

            $leagueArr = [
                'league' => $leagueName,
                'league_image' => $this->resolveLeagueImage($firstMatch->league_id),
                'flag' => $this->resolveFlagUrl($resolvedCc),
                'match' => []
            ];

            foreach($matches as $match) {
                $leagueArr['match'][] = $this->formatMatch($match);
            }
            $result[] = $leagueArr;
        }

        return $result;
    }
}
