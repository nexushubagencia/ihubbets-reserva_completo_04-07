<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bet;
use App\Models\PreBet;
use App\Models\Aposta; // Legacy compatibility
use App\Models\Site;

class PrintController extends Controller
{
    /**
     * Renderiza o bilhete para impressão ou visualização premium.
     * Suporta códigos de Pré-Bilhete (PIN) e IDs de Apostas Finalizadas.
     */
    public function show($code)
    {
        $siteId = config('tenant.site_id', 1);
        $site = Site::find($siteId);

        // 1. Tentar buscar como Aposta Finalizada (Sistema Moderno)
        $bet = Bet::where('ticket_code', $code)->first();

        // 2. Tentar buscar como Aposta Finalizada (Sistema Legado)
        if (!$bet) {
            $aposta = Aposta::where('codigo_bilhete', $code)->first();
            if ($aposta) {
                // Converter para um objeto genérico para a view não quebrar
                $bet = $this->mapLegacyToModern($aposta);
            }
        }

        // 3. Tentar buscar como Pré-Bilhete (PIN)
        if (!$bet) {
            $preBet = PreBet::where('code', $code)->first();
            if ($preBet) {
                $bet = $this->mapPreBetToModern($preBet);
            }
        }

        if (!$bet) {
            return abort(404, 'Bilhete não encontrado.');
        }

        // Se for uma aposta finalizada moderna, carregar os dados ao vivo dos jogos
        if ($bet instanceof \App\Models\Bet) {
            $bet->pin = $bet->ticket_code; 
            $bet->items->each(function($item) {
                $match = \App\Models\MatchEvent::where('event_id', $item->match_id)->first();
                if ($match) {
                    $item->live = $match;
                    $item->match_date = $match->date;
                }
            });
        }

        $configuracao = \App\Models\Configuracao::where('site_id', $siteId)->first();

        // Escolha de layout (Padrão: Classic)
        $layout = request('layout', 'classic');
        
        $view = 'print.classic_ticket';
        if ($layout == 'modern') $view = 'print.modern_ticket';
        if ($layout == 'live') $view = 'print.live_tracker';

        return view($view, compact('bet', 'site', 'configuracao'));
    }

    private function mapLegacyToModern($aposta)
    {
        $bet = new \stdClass();
        $bet->id = $aposta->id;
        $bet->pin = $aposta->codigo_bilhete;
        $bet->ticket_code = $aposta->codigo_bilhete;
        $bet->client_name = $aposta->cliente ?? 'Cliente';
        $bet->amount = $aposta->valor_apostado;
        $bet->potential_payout = $aposta->retorno_possivel;
        $bet->status = $aposta->status; // Aberto, Venceu, Perdeu
        $bet->created_at = $aposta->created_at;
        $bet->user = $aposta->user; // Relation exists in Aposta
        
        // Mapear palpites
        $bet->items = $aposta->palpites->map(function($p) {
            $item = new \stdClass();
            $item->league_name = $p->league_name ?? 'Liga';
            $item->home_team = $p->home_team;
            $item->away_team = $p->away_team;
            $item->market_name = $p->market_name;
            $item->selection_label = $p->selection_label;
            $item->selection_odd = $p->selection_odd;
            $item->status = $p->status;
            $item->match_date = $p->created_at; // Fallback se não houver data_jogo
            $item->live = \App\Models\MatchEvent::where('event_id', $p->match_id)->first();
            if ($item->live) {
                $item->match_date = $item->live->date;
            }
            return $item;
        });

        return $bet;
    }

    private function mapPreBetToModern($pre)
    {
        $bet = new \stdClass();
        $bet->id = $pre->id;
        $bet->pin = $pre->code;
        $bet->ticket_code = $pre->code;
        $bet->client_name = $pre->client_name;
        $bet->amount = $pre->total_stake;
        $bet->potential_payout = $pre->possible_return;
        $bet->status = 'Pendente';
        $bet->created_at = $pre->created_at;
        $bet->user = null;
        
        $bet->items = collect($pre->selections)->map(function($s) {
            $s = (object) $s;
            $item = new \stdClass();
            $item->league_name = $s->league ?? 'Liga';
            $item->home_team = $s->home ?? 'Mandante';
            $item->away_team = $s->away ?? 'Visitante';
            $item->market_name = $s->group_opp ?? $s->market ?? 'Vencedor';
            $item->selection_label = $s->odd ?? $s->label ?? '';
            $item->selection_odd = $s->cotacao ?? $s->value ?? 0;
            $item->status = 'Aberto';
            $item->match_date = $s->date ?? null;
            
            $eventId = $s->idEvent ?? $s->partida ?? 0;
            $item->live = \App\Models\MatchEvent::where('event_id', $eventId)->first();
            if ($item->live && !$item->match_date) {
                $item->match_date = $item->live->date;
            }
            return $item;
        });

        return $bet;
    }
}
