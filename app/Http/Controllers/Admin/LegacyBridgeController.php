<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Site;
use App\Models\Banner;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LegacyBridgeController extends Controller
{
    public function getSettings()
    {
        $site_id = config('tenant.site_id', 1);
        $site = Site::find($site_id) ?? Site::first();

        return response()->json([[
            "id" => $site->id,
            "site_id" => "site_".$site->id,
            "complete_name" => $site->complete_name ?? 'IHUB V4 PRO',
            "name" => $site->name ?? 'IHUB',
            "theme_color" => $site->theme_color ?? 'verde-claro',
            "status" => 1,
            "prefixo_moeda" => "R$",
            "valor_mini_aposta" => 1,
            "valor_max_aposta" => 1000,
            "premio_max" => 50000,
            "op_futebol" => "Sim",
            "op_hoje" => "sim",
            "op_aovivo" => "sim",
            "carrosel_ativado" => $site->carrosel_ativado ?? 1,
            "created_at" => $site->created_at,
            "updated_at" => $site->updated_at
        ]]);
    }

    public function getBanners()
    {
        $site_id = config('tenant.site_id', 1);
        $banners = Banner::where('site_id', $site_id)->where('status', 1)->get();

        $formatted = $banners->map(function($b) {
            return [
                "id" => $b->id,
                "titulo" => "Banner",
                "link" => "#",
                "foto" => $b->image_path,
                "status" => 1
            ];
        });

        return response()->json($formatted);
    }

    public function getLeagues()
    {
        $siteId = config('tenant.site_id', 1);
        
        $leagues = DB::table('manual_events')
            ->join('manual_categories', 'manual_events.category_id', '=', 'manual_categories.id')
            ->select('manual_categories.name as league')
            ->where('manual_events.site_id', $siteId)
            ->whereNotNull('manual_categories.name')
            ->distinct()
            ->get();

        $grouped = [];
        
        foreach($leagues as $l) {
            $country = (strpos($l->league, 'X1') !== false) ? 'Brasil' : 'Outros';
            $cc = (strpos($l->league, 'X1') !== false) ? 'br' : 'us';
            
            if(!isset($grouped[$country])) {
                $grouped[$country] = [
                    "country" => $country,
                    "cc" => $cc,
                    "leagues" => []
                ];
            }
            
            $grouped[$country]['leagues'][] = [
                "sport" => "Futebol",
                "cc" => $cc,
                "league" => $l->league
            ];
        }

        return response()->json(array_values($grouped));
    }

    public function getMatches(Request $request)
    {
        $siteId = config('tenant.site_id', 1);
        $league = $request->query('league');

        $query = DB::table('manual_events')
            ->join('manual_categories', 'manual_events.category_id', '=', 'manual_categories.id')
            ->select('manual_events.*', 'manual_categories.name as league_name')
            ->where('manual_events.site_id', $siteId);

        if ($league) {
            $query->where('manual_categories.name', $league);
        }

        $events = $query->get();

        $formatted = $events->map(function($e) {
            // Extract home and away team from the generated title: "HomeTeam vs AwayTeam (Category)"
            $titleParts = explode(' vs ', $e->title);
            $homeTeam = trim($titleParts[0] ?? 'Time A');
            $awayTeamInfo = explode('(', $titleParts[1] ?? '');
            $awayTeam = trim($awayTeamInfo[0] ?? 'Time B');

            // Parse odds if we need them, we actually should fetch them from manual_odds
            $odds = DB::table('manual_odds')
                ->whereIn('market_id', function($q) use ($e) {
                    $q->select('id')->from('manual_markets')->where('event_id', $e->id);
                })->get();

            $odd1 = $odds->where('label', $homeTeam)->first()->value ?? ($odds->where('label', 'Casa')->first()->value ?? 1.90);
            $oddX = $odds->whereIn('label', ['Empate', 'Draw', 'X'])->first()->value ?? 3.00;
            $odd2 = $odds->where('label', $awayTeam)->first()->value ?? ($odds->where('label', 'Fora')->first()->value ?? 1.90);

            return [
                "id" => $e->id,
                "event_id" => "manual_" . $e->id,
                "home_team" => $homeTeam,
                "away_team" => $awayTeam,
                "start_time" => $e->start_time,
                "league_name" => $e->league_name,
                "odds" => [
                    "1" => $odd1,
                    "X" => $oddX,
                    "2" => $odd2
                ]
            ];
        });

        return response()->json($formatted);
    }
}
