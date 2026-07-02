<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ManualEvent;
use Illuminate\Support\Facades\DB;

class FeaturedMatchesController extends Controller
{
    /**
     * Lista de Partidas para fixar em Destaque
     */
    public function index()
    {
        $siteId = config('tenant.site_id', 1);

        // Limpeza automática de partidas que já passaram do horário
        $this->cleanupPastFeatured($siteId);

        // Pega as partidas que já estão em destaque
        $featured = DB::table('featured_matches')
            ->where('site_id', $siteId)
            ->get();

        // Pega as partidas manuais (Personalizadas) para listar também
        if (class_exists(ManualEvent::class)) {
            $manualEvents = ManualEvent::where('site_id', $siteId)
                ->where('status', 'active')
                ->orderBy('start_time', 'asc')
                ->get();
        } else {
            $manualEvents = collect();
        }

        $site = \App\Models\Site::find($siteId);
        $settings = \DB::table('site_settings')->where('site_id', $site->id)->first();
        $themeColor = $settings->button_odds_color ?? '#1aa6d0';

        return view('admin.featured.index', compact('featured', 'manualEvents', 'themeColor'));
    }

    public function data()
    {
        return DB::table('featured_matches')
            ->where('site_id', config('tenant.site_id', 1))
            ->get();
    }

    /**
     * Alternar Destaque (Toggle)
     */
    public function toggle(Request $request)
    {
        $siteId  = config('tenant.site_id', 1);
        $matchId = $request->match_id;
        $isManual = filter_var($request->is_manual, FILTER_VALIDATE_BOOLEAN);
        $column   = $isManual ? 'manual_event_id' : 'match_id';

        $exists = DB::table('featured_matches')
            ->where('site_id', $siteId)
            ->where($column, $matchId)
            ->first();

        if ($exists) {
            DB::table('featured_matches')
                ->where('site_id', $siteId)
                ->where($column, $matchId)
                ->delete();
            $msg = 'Removido dos destaques';
            $status = 'removed';
        } else {
            // Pegar dados da partida para preencher colunas extras (Denormalização)
            $matchData = null;
            if (!$isManual) {
                $matchData = DB::table('matchs')->where('id', $matchId)->first();
                if ($matchData && \Carbon\Carbon::parse($matchData->date)->isPast()) {
                    return response()->json(['success' => false, 'message' => 'Não é possível destacar jogos que já passaram!']);
                }
            } else {
                $matchData = ManualEvent::find($matchId);
                if ($matchData && \Carbon\Carbon::parse($matchData->start_time)->isPast()) {
                    return response()->json(['success' => false, 'message' => 'Não é possível destacar eventos passados!']);
                }
            }

            if (!$matchData) {
                return response()->json(['success' => false, 'message' => 'Partida não encontrada']);
            }

            // Pegar cor padrão do tema
            $settings = \DB::table('site_settings')->where('site_id', $siteId)->first();
            $defaultBadgeColor = $settings->button_odds_color ?? '#1aa6d0';

            $insert = [
                'site_id'   => $siteId,
                'is_manual' => $isManual,
                'home_team' => $isManual ? $matchData->home_team : $matchData->home,
                'away_team' => $isManual ? $matchData->away_team : $matchData->away,
                'match_date'=> $isManual ? $matchData->start_time : $matchData->date,
                'league_name'=> $isManual ? $matchData->league_name : $matchData->league,
                'badge_color'=> $defaultBadgeColor,
                'created_at'=> now(),
                'updated_at'=> now(),
            ];

            
            $insert[$column] = $matchId;

            DB::table('featured_matches')->insert($insert);
            $msg = 'Adicionado aos destaques!';
            $status = 'added';
        }

        return response()->json([
            'success' => true, 
            'message' => $msg,
            'status' => $status
        ]);
    }

    /**
     * Busca partidas disponíveis para destacar (AJAX)
     */
    public function getAvailableMatches(Request $request)
    {
        $siteId = config('tenant.site_id', 1);
        $search = $request->search;

        // Limpeza antes de buscar novos resultados
        $this->cleanupPastFeatured($siteId);

        // 1. Partidas Automáticas
        $query = DB::table('matchs')
            ->select('id', 'home', 'away', 'date as start_time', 'league', DB::raw('0 as is_manual'))
            ->where('date', '>=', now()->subMinutes(10)) // Mostra apenas jogos futuros ou que acabaram de começar
            ->orderBy('date', 'asc')
            ->limit(300); // Aumentado o limite para evitar cortes na lista

        if ($request->date) {
            $query->whereDate('date', $request->date);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('home', 'like', "%{$search}%")
                  ->orWhere('away', 'like', "%{$search}%")
                  ->orWhere('league', 'like', "%{$search}%");
            });
        }

        $matches = $query->get();

        // 2. Partidas Manuais
        $manualQuery = ManualEvent::where('site_id', $siteId)
            ->where('status', 'open')
            ->where('start_time', '>=', now()->subMinutes(10))
            ->orderBy('start_time', 'asc')
            ->limit(100);

        if ($request->date) {
            $manualQuery->whereDate('start_time', $request->date);
        }

        if ($search) {
            $manualQuery->where(function($q) use ($search) {
                $q->where('home_team', 'like', "%{$search}%")
                  ->orWhere('away_team', 'like', "%{$search}%")
                  ->orWhere('league_name', 'like', "%{$search}%");
            });
        }

        $manuals = $manualQuery->get()->map(function($m) {
            return (object)[
                'id' => $m->id,
                'home' => $m->home_team,
                'away' => $m->away_team,
                'start_time' => $m->start_time,
                'league' => $m->league_name,
                'is_manual' => 1
            ];
        });

        // Unificar as duas coleções
        $allMatches = $matches->concat($manuals);

        // Marcar quais já estão em destaque
        $featured = DB::table('featured_matches')
            ->where('site_id', $siteId)
            ->get();

        foreach ($allMatches as $m) {
            $matchMeta = $featured->first(function($f) use ($m) {
                if ($m->is_manual) {
                    return $f->is_manual && $f->manual_event_id == $m->id;
                } else {
                    return !$f->is_manual && $f->match_id == $m->id;
                }
            });

            $m->is_featured = (bool)$matchMeta;
            $m->background_path = $matchMeta->background_path ?? '';
            $m->badge_color = $matchMeta->badge_color ?? '';
        }

        return response()->json($allMatches);
    }

    public function updateMeta(Request $request)
    {
        $siteId = config('tenant.site_id', 1);
        $matchId = $request->match_id;
        $isManual = (bool) ($request->is_manual ?? false);
        $column = $isManual ? 'manual_event_id' : 'match_id';

        $data = [
            'badge_color' => $request->badge_color,
            'updated_at' => now()
        ];

        // Se houver URL manual
        if ($request->background_path) {
            $data['background_path'] = $request->background_path;
        }

        // Se houver upload de arquivo
        if ($request->hasFile('background_file')) {
            $file = $request->file('background_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = 'uploads/featured/' . $siteId;
            $file->move(public_path($path), $filename);
            $data['background_path'] = '/' . $path . '/' . $filename;
        }

        $updated = DB::table('featured_matches')
            ->where('site_id', $siteId)
            ->where($column, $matchId)
            ->update($data);

        if ($updated) {
            return response()->json(['success' => true, 'message' => 'Configurações salvas com sucesso!']);
        }

        return response()->json(['success' => false, 'message' => 'Nenhuma alteração realizada ou partida não encontrada.']);
    }

    /**
     * Limpa partidas em destaque que já passaram do horário de início
     */
    private function cleanupPastFeatured($siteId)
    {
        // 1. Remover partidas automáticas (matchs) passadas
        DB::table('featured_matches')
            ->where('site_id', $siteId)
            ->where('is_manual', false)
            ->whereIn('match_id', function($query) {
                $query->select('id')
                    ->from('matchs')
                    ->where('date', '<', now());
            })
            ->delete();

        // 2. Remover partidas manuais (manual_events) passadas
        DB::table('featured_matches')
            ->where('site_id', $siteId)
            ->where('is_manual', true)
            ->whereIn('manual_event_id', function($query) {
                $query->select('id')
                    ->from('manual_events')
                    ->where('start_time', '<', now());
            })
            ->delete();
    }
}
