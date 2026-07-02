<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PreBet;
use Illuminate\Support\Str;

class PublicBetController extends Controller
{
    /**
     * Gera um novo Pré-Bilhete (pelo cliente no site)
     */
    public function store(Request $request)
    {
        \Log::info('PublicBetController store called', $request->all());
        try {
        // Aceita tanto o formato novo quanto o legado do Geral.vue
        $valorApostado = $request->valor_apostado ?? $request->total_stake;
        $palpites = $request->palpites ?? $request->selections;
        $retorno = $request->retorno_possivel ?? $request->possible_return ?? 0;
        if (is_null($retorno) || $retorno === '') {
            $retorno = 0;
        }
        $cliente = $request->cliente ?? $request->client_name;

        // 🚀 INTELIGÊNCIA: Se o usuário estiver logado como cambista/admin, 
        // redireciona para a criação de aposta REAL em vez de gerar PIN.
        if (auth()->check()) {
            $user = auth()->user();
            if (in_array($user->nivel, ['cambista', 'vendedor', 'adm', 'admin']) || in_array($user->role, ['seller', 'admin'])) {
                // Normaliza o request para o formato que os Controllers esperam
                $request->merge([
                    'valor_apostado'   => $valorApostado,
                    'palpites'         => $palpites,
                    'retorno_possivel' => $retorno,
                    'cliente'          => $cliente
                ]);

                return (new BilheteApiController)->sendAposta($request);
            }
        }

        if (!$valorApostado || !$palpites) {
             return response()->json(['status' => 'error', 'message' => 'Dados incompletos'], 400);
        }

        $isLoto = false;

        // Gera um código (PIN) no formato XXXX-0000 (4 letras, hífen, 4 números)
        $letters = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 4);
        $cupom = $letters . '-' . random_int(1000, 9999);
        
        while (PreBet::where('code', $cupom)->exists()) {
            $letters = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 4);
            $cupom = $letters . '-' . random_int(1000, 9999);
        }

        // Prepara os palpites com campos extras que o modal e o validator esperam
        $formattedPalpites = [];
        foreach ($palpites as $p) {
            $sel = is_array($p) ? $p : (array) $p;
            $sel['match_temp'] = $sel['match_temp'] ?? $sel['date'] ?? null;
            $sel['match_id'] = $sel['match_id'] ?? (int) str_replace('m', '', ($sel['idEvent'] ?? $sel['partida'] ?? 0));
            $sel['palpite'] = $sel['palpite'] ?? $sel['odd'] ?? '';
            $sel['cotacao'] = $sel['cotacao'] ?? $sel['odd'] ?? 1.0;
            $sel['odd'] = $sel['cotacao'];
            $sel['status'] = $sel['status'] ?? 'Aberto';
            $formattedPalpites[] = $sel;
        }

        $preBet = PreBet::create([
            'code' => $cupom,
            'site_id' => config('tenant.site_id', 1),
            'selections' => $formattedPalpites,
            'total_stake' => $valorApostado ?? 0,
            'possible_return' => $retorno ?? 0,
            'client_name' => $cliente ?? 'Bilhete Site',
            'modalidade' => $isLoto ? 'Loto' : 'Esporte',
            'tipo' => $isLoto ? $request->tipo : (count($formattedPalpites) > 1 ? 'Múltipla' : 'Simples'),
            'concurso' => $request->concurso
        ]);

        $cotacaoTotal = $request->cotacao ?? 0;

        $response = [
            'status' => 'success', // Importante para o Geral.vue
            'message' => 'Bilhete gerado!',
            'cupom' => $cupom,
            'id' => $preBet->id,
            'cliente' => $preBet->client_name,
            'vendedor' => 'Site',
            'valor_apostado' => $preBet->total_stake,
            'retorno_possivel' => $preBet->possible_return,
            'retorno_cambista' => $preBet->possible_return,
            'total_palpites' => count($formattedPalpites),
            'acertos_palpites' => 0,
            'andamento_palpites' => count($formattedPalpites),
            'erros_palpites' => 0,
            'devolvidos_palpites' => 0,
            'modalidade' => $isLoto ? 'Loto' : 'Esporte',
            'cotacao' => $cotacaoTotal,
            'palpites' => $isLoto ? [] : $formattedPalpites,
            'created_at' => $preBet->created_at,
            'tipo' => $isLoto ? $request->tipo : (count($formattedPalpites) > 1 ? 'Múltipla' : 'Simples'),
            'concurso' => $preBet->concurso
        ];

        if ($isLoto) {
            $response['palpites_loto'] = $formattedPalpites;
        }

        return response()->json($response);
        } catch (\Exception $e) {
            \Log::error('PublicBetController Error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Erro ao processar bilhete: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Busca um Pré-Bilhete pelo código (pelo cambista no painel)
     */
    public function show($idOrCode)
    {
        // Busca por ID ou por Código (PIN)
        $preBet = PreBet::where('site_id', config('tenant.site_id', 1))
                        ->where(function($query) use ($idOrCode) {
                            $query->where('id', $idOrCode)
                                  ->orWhere('code', strtoupper($idOrCode));
                        })->first();

        if (!$preBet) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bilhete não encontrado.'
            ], 404);
        }

        $selections = $preBet->selections;
        $isLoto = ($preBet->modalidade == 'Loto' || substr($preBet->code, 0, 5) === 'LOTO-');

        $data = [
            'id' => $preBet->id,
            'cupom' => $preBet->code,
            'codigo_bilhete' => $preBet->code,
            'cliente' => $preBet->client_name,
            'vendedor' => 'Site',
            'valor_apostado' => $preBet->total_stake,
            'retorno_possivel' => $preBet->possible_return,
            'total_palpites' => count($selections),
            'palpites' => $isLoto ? [] : array_map(function($p) {
                return [
                    'id' => $p['idEvent'] ?? 0,
                    'sport' => $p['sport'] ?? 'Futebol',
                    'league' => $p['league'] ?? 'Liga',
                    'home' => $p['home'] ?? 'Mandante',
                    'away' => $p['away'] ?? 'Visitante',
                    'group_opp' => $p['group_opp'] ?? 'Mercado',
                    'palpite' => $p['palpite'] ?? 'Seleção',
                    'cotacao' => $p['cotacao'] ?? 1.0,
                    'status' => 'Aberto',
                    'match_temp' => $p['date'] ?? null
                ];
            }, $selections),
            'palpites_loto' => $isLoto ? array_map(function($p) {
                $p = (array)$p;
                return [
                    'dezena' => $p['dezena'] ?? $p['palpite'] ?? '',
                    'status' => $p['status'] ?? 'Aberto'
                ];
            }, $selections) : [],
            'modalidade' => $isLoto ? 'Loto' : 'Esporte',
            'status' => 'PRÉ-APOSTA',
            'created_at' => $preBet->created_at,
            'tipo' => $isLoto ? ($preBet->tipo ?? 'Loto') : (count($selections) > 1 ? 'Múltipla' : 'Simples')
        ];

        // O Frontend espera um array de bilhetes
        return response()->json([$data]);
    }

    /**
     * Helper para o frontend que envia 'cupom' no corpo do POST
     */
    public function showByCupom(Request $request)
    {
        return $this->show($request->cupom);
    }
}
