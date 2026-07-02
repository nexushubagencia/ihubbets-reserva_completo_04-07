<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MatchManagementController extends Controller
{
    /**
     * Lista jogos do dia (manuais + API) para o painel admin.
     */
    public function getPreJogo(Request $request)
    {
        $siteId = config('tenant.site_id', 1);
        $search = $request->query('search');
        $date   = $request->query('date', Carbon::today()->format('Y-m-d'));

        // 1. Manuais
        $manual = DB::table('manual_events')
            ->leftJoin('manual_categories', 'manual_events.category_id', '=', 'manual_categories.id')
            ->where('manual_events.site_id', $siteId)
            ->where('manual_events.status', 'open')
            ->whereDate('manual_events.start_time', $date)
            ->select(
                'manual_events.id',
                'manual_events.title',
                'manual_events.home_team',
                'manual_events.away_team',
                'manual_events.odd_home',
                'manual_events.odd_draw',
                'manual_events.odd_away',
                'manual_events.start_time',
                'manual_categories.name as league_name',
                DB::raw('"manual" as type')
            );

        // 2. API (Automáticos)
        $automatic = DB::table('matchs')
            ->whereDate('date', $date)
            ->where('visible', 'Sim')
            ->select(
                'id',
                'confronto as title',
                'home as home_team',
                'away as away_team',
                DB::raw('0 as odd_home'), // Placeholder, odds vêm da tabela odds
                DB::raw('0 as odd_draw'),
                DB::raw('0 as odd_away'),
                'date as start_time',
                'league as league_name',
                DB::raw('"api" as type')
            );

        if ($search) {
            $manual->where(function ($q) use ($search) {
                $q->where('manual_events.title', 'like', "%{$search}%")
                  ->orWhere('manual_events.home_team', 'like', "%{$search}%")
                  ->orWhere('manual_events.away_team', 'like', "%{$search}%");
            });
            $automatic->where('confronto', 'like', "%{$search}%");
        }

        $games = $manual->union($automatic)->orderBy('start_time', 'asc')->get();

        return response()->json($games);
    }

    /**
     * Retorna as partidas em destaque do site.
     */
    public function getFeatured(Request $request)
    {
        $siteId = config('tenant.site_id', 1);

        $featured = DB::table('featured_matches')
            ->leftJoin('manual_events', 'featured_matches.manual_event_id', '=', 'manual_events.id')
            ->where('featured_matches.site_id', $siteId)
            ->select(
                'featured_matches.id',
                'featured_matches.match_id',
                'featured_matches.manual_event_id',
                'featured_matches.is_manual',
                'featured_matches.background_path',
                'featured_matches.badge_color',
                'manual_events.title',
                'manual_events.start_time',
                'manual_events.home_team',
                'manual_events.away_team'
            )
            ->orderBy('featured_matches.created_at', 'desc')
            ->get();

        return response()->json($featured);
    }

    /**
     * Atualiza o fundo e cor de um destaque.
     */
    public function updateFeaturedMeta(Request $request, $id)
    {
        $request->validate([
            'background_path' => 'nullable|string',
            'badge_color'     => 'nullable|string',
            'order'           => 'nullable|integer'
        ]);

        $update = [
            'updated_at' => now(),
        ];

        if ($request->has('background_path')) $update['background_path'] = $request->background_path;
        if ($request->has('badge_color'))     $update['badge_color'] = $request->badge_color;
        if ($request->has('order'))           $update['order'] = $request->order;

        DB::table('featured_matches')->where('id', $id)->update($update);

        return response()->json(['success' => true]);
    }

    /**
     * Adiciona uma partida manual à lista de destaques.
     */
    public function addToFeatured(Request $request)
    {
        $siteId = config('tenant.site_id', 1);

        $request->validate([
            'match_id'  => 'required|integer',
            'is_manual' => 'boolean',
        ]);

        $isManual = (bool) ($request->is_manual ?? true);
        $column   = $isManual ? 'manual_event_id' : 'match_id';

        // Evitar duplicatas
        $exists = DB::table('featured_matches')
            ->where('site_id', $siteId)
            ->where($column, $request->match_id)
            ->exists();

        if (!$exists) {
            $insert = [
                'site_id'    => $siteId,
                'is_manual'  => $isManual,
                'status'     => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $insert[$column] = $request->match_id;
            DB::table('featured_matches')->insert($insert);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Remove uma partida dos destaques.
     */
    public function removeFromFeatured($id)
    {
        DB::table('featured_matches')->where('id', $id)->delete();
        return response()->json(['success' => true]);
    }

    /**
     * Retorna ligas bloqueadas (filtragem no frontend).
     */
    public function getBlockedLeagues(Request $request)
    {
        $siteId = config('tenant.site_id', 1);
        $blocked = DB::table('block_leagues')
            ->where('site_id', $siteId)
            ->pluck('league');
        return response()->json($blocked);
    }

    /**
     * Bloqueia ou desbloqueia uma liga.
     */
    public function toggleBlockLeague(Request $request)
    {
        $siteId = config('tenant.site_id', 1);
        $request->validate(['league_name' => 'required|string']);

        $exists = DB::table('block_leagues')
            ->where('site_id', $siteId)
            ->where('league', $request->league_name)
            ->first();

        if ($exists) {
            DB::table('block_leagues')->where('id', $exists->id)->delete();
            $msg = 'Liga desbloqueada.';
        } else {
            DB::table('block_leagues')->insert([
                'site_id'     => $siteId,
                'league'      => $request->league_name,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
            $msg = 'Liga bloqueada.';
        }

        return response()->json(['success' => true, 'message' => $msg]);
    }
}
