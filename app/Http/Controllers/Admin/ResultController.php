<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ResultProcessor;
use Illuminate\Support\Facades\DB;

class ResultController extends Controller
{
    private ResultProcessor $processor;

    public function __construct(ResultProcessor $processor)
    {
        $this->middleware('auth');
        $this->processor = $processor;
    }

    /**
     * Lista jogos aguardando resultado (status open, data passada).
     */
    public function pendingEvents(Request $request)
    {
        if (!$request->ajax() && !$request->wantsJson()) {
            return view('admin.jogos-abertos');
        }

        $siteId = config('tenant.site_id', 1);

        $events = DB::table('manual_events')
            ->join('manual_categories', 'manual_events.category_id', '=', 'manual_categories.id')
            ->select(
                'manual_events.id',
                'manual_events.title',
                'manual_events.start_time',
                'manual_categories.name as category',
                'manual_events.status'
            )
            ->where('manual_events.site_id', $siteId)
            ->where('manual_events.status', 'open')
            ->where('manual_events.start_time', '<=', now())
            ->orderBy('manual_events.start_time', 'asc')
            ->get();

        // Para cada evento, buscar o total de bilhetes em aberto
        $events->transform(function ($event) {
            $event->open_bets = DB::table('bet_items')
                ->where('match_id', $event->id)
                ->where('status', 'pending')
                ->count();
            return $event;
        });

        return response()->json($events);
    }

    /**
     * Submete o resultado de um evento e liquida os bilhetes.
     *
     * Body esperado:
     * {
     *   "event_id": 42,
     *   "home_full": 2,
     *   "away_full": 1,
     *   "home_half": 1,
     *   "away_half": 0
     * }
     */
    public function submit(Request $request)
    {
        // Aceita 'home_ful' (legado) ou 'home_full' (novo)
        $homeFull = $request->home_full ?? $request->home_ful;
        $awayFull = $request->away_full ?? $request->away_ful;

        if ($homeFull === null || $awayFull === null) {
            return response()->json(['status' => 'error', 'message' => 'Placar final obrigatório'], 422);
        }

        $result = $this->processor->process(
            (int) $request->event_id,
            (int) $homeFull,
            (int) $awayFull,
            (int) $request->home_half,
            (int) $request->away_half
        );

        $code = $result['status'] === 'success' ? 200 : ($result['status'] === 'skipped' ? 422 : 500);
        return response()->json($result, $code);
    }

    /**
     * Preview: quais mercados ganhariam com um dado placar (sem salvar).
     */
    public function preview(Request $request)
    {
        $homeFull = $request->home_full ?? $request->home_ful;
        $awayFull = $request->away_full ?? $request->away_ful;

        if ($homeFull === null || $awayFull === null) {
            return response()->json(['status' => 'error', 'message' => 'Placar final obrigatório'], 422);
        }

        $winners = $this->processor->deriveWinners(
            (int) $homeFull,
            (int) $awayFull,
            (int) $request->home_half,
            (int) $request->away_half
        );

        return response()->json([
            'score'    => "{$homeFull}-{$awayFull} ({$request->home_half}-{$request->away_half})",
            'markets'  => $winners['winners'],
            'returned' => $winners['returned'],
        ]);
    }

    /**
     * Cancela / estorna todos os bilhetes de um evento (caso de jogo cancelado).
     */
    public function cancel(Request $request)
    {
        $request->validate(['event_id' => 'required|integer|exists:manual_events,id']);

        DB::beginTransaction();
        try {
            $siteId = config('tenant.site_id', 1);

            // Marcar evento como cancelado
            DB::table('manual_events')
                ->where('id', $request->event_id)
                ->update(['status' => 'cancelled', 'updated_at' => now()]);

            // Estornar todos os bilhetes com palpites neste jogo
            $betIds = DB::table('bet_items')
                ->where('match_id', $request->event_id)
                ->pluck('bet_id')
                ->unique();

            foreach ($betIds as $betId) {
                $bet = DB::table('bets')->where('id', $betId)->first();
                if (!$bet || !in_array($bet->status, ['pending', 'open'])) continue;

                // Devolver o valor apostado
                DB::table('bets')->where('id', $betId)->update([
                    'status'     => 'cancelled',
                    'updated_at' => now(),
                ]);

                DB::table('bet_items')
                    ->where('bet_id', $betId)
                    ->update(['status' => 'cancelled']);

                // Creditar de volta na carteira
                DB::table('master_users')->where('id', $bet->user_id)
                    ->increment('balance', $bet->amount);
            }

            DB::table('audit_logs')->insert([
                'site_id'     => $siteId,
                'user_id'     => auth()->id(),
                'action'      => 'CANCEL_EVENT',
                'target_type' => 'manual_events',
                'target_id'   => $request->event_id,
                'ip_address'  => $request->ip(),
                'created_at'  => now(),
            ]);

            DB::commit();
            return response()->json(['status' => 'success', 'cancelled_bets' => count($betIds)]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
