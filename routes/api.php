<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\PersonalizedMatchesController;
use App\Http\Controllers\Admin\MatchManagementController;
use App\Http\Controllers\Admin\OddsMarketsController;
use App\Http\Controllers\Api\PublicBetController;
use App\Http\Controllers\Api\BetApiController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth');

// Compatibility Bridge for various Frontends (Brasil Sports, V1, V2, V4)
Route::group(['middleware' => ['web', 'api']], function () {
    // Info & Settings
    Route::get('/siteinfo', [App\Http\Controllers\Api\ApiController::class, 'siteInfo']);
    Route::get('/siteinfo.html', [App\Http\Controllers\Api\ApiController::class, 'siteInfo']);
    Route::get('/site-info', [App\Http\Controllers\Api\ApiController::class, 'siteInfo']);
    Route::get('/site-info.html', [App\Http\Controllers\Api\ApiController::class, 'siteInfo']);
    Route::get('/settings', [App\Http\Controllers\Api\ApiController::class, 'siteInfo']);
    Route::get('/settings.html', [App\Http\Controllers\Api\ApiController::class, 'siteInfo']);
    Route::get('/get-live-market-config', [App\Http\Controllers\Api\SportsApiController::class, 'getLiveMarketConfig']);
    Route::get('/list-limites', [App\Http\Controllers\Api\ApiController::class, 'siteInfo']);
    Route::get('/regulamento', [App\Http\Controllers\Api\ApiController::class, 'siteInfo']);
    
    
    // Bankizi & Payments
    Route::get('/check-bankizi-confirmation-account/geral', function() { 
        return response()->json(['status' => 'success', 'active' => true]); 
    });
    
    // Banners
    Route::get('/getbanners', [App\Http\Controllers\Api\MatchApiController::class, 'getBanners']);
    Route::get('/getbanners.html', [App\Http\Controllers\Api\MatchApiController::class, 'getBanners']);
    Route::get('/get-banners', [App\Http\Controllers\Api\MatchApiController::class, 'getBanners']);
    Route::get('/getcarousel', [App\Http\Controllers\Api\MatchApiController::class, 'getBanners']);
    Route::get('/get-carousel', [App\Http\Controllers\Api\MatchApiController::class, 'getBanners']);
    Route::get('/get-banners.html', [App\Http\Controllers\Api\MatchApiController::class, 'getBanners']);
    
    // Games & Matches
    Route::get('/matches', [App\Http\Controllers\Api\MatchApiController::class, 'getMatches']);
    Route::get('/list-matches', [App\Http\Controllers\Api\MatchApiController::class, 'getMatches']);
    Route::get('/list-matches.html', [App\Http\Controllers\Api\MatchApiController::class, 'getMatches']);
    Route::get('/site-list-leagues', [App\Http\Controllers\Api\SportsApiController::class, 'listLeagues']);
    Route::get('/site-list-leagues.html', [App\Http\Controllers\Api\SportsApiController::class, 'listLeagues']);
    Route::get('/site-list-leagues-main', [App\Http\Controllers\Api\SportsApiController::class, 'listLeaguesMain']);
    Route::get('/site-list-leagues-main.html', [App\Http\Controllers\Api\SportsApiController::class, 'listLeaguesMain']);
    Route::get('/site-list-leagues-main-node', [App\Http\Controllers\Api\SportsApiController::class, 'listLeaguesMain']);
    Route::get('/site-list-leagues-main-node.html', [App\Http\Controllers\Api\SportsApiController::class, 'listLeaguesMain']);
    Route::get('/get-featured-match', [App\Http\Controllers\Api\MatchApiController::class, 'getMatches']);
    Route::get('/get-featured-matches', [App\Http\Controllers\Api\MatchApiController::class, 'getMatches']);
    
    Route::get('/site-partidas-home', [App\Http\Controllers\Api\MatchApiController::class, 'getMatchesHome']);
    Route::get('/site-partidas-home-main', [App\Http\Controllers\Api\MatchApiController::class, 'getMatchesHome']);
    Route::get('/partidas-home', [App\Http\Controllers\Api\MatchApiController::class, 'getMatchesHome']);
    Route::get('/soccer/today', [App\Http\Controllers\Api\MatchApiController::class, 'getMatchesHome']);
    Route::get('/soccer/tomorrow', [App\Http\Controllers\Api\MatchApiController::class, 'getMatchesAmanha']);
    Route::get('/site-partidas-amanha', [App\Http\Controllers\Api\MatchApiController::class, 'getMatchesAmanha']);
    Route::get('/site-partidas-amanha-main', [App\Http\Controllers\Api\MatchApiController::class, 'getMatchesAmanha']);
    Route::get('/site-partidas-depois-amanha', [App\Http\Controllers\Api\MatchApiController::class, 'getMatchesDepoisAmanha']);
    Route::get('/site-partidas-ao-vivo', [App\Http\Controllers\Api\MatchApiController::class, 'getMatchesLive']);
    Route::get('/dias-futebol', [App\Http\Controllers\Api\MatchApiController::class, 'getDaysList']);
    Route::get('/site-partidas-search/{search}', [App\Http\Controllers\Api\MatchApiController::class, 'getMatchesSearch']);
    Route::get('/site-partidas-modalidade/{modality}', [App\Http\Controllers\Api\MatchApiController::class, 'getMatchesByModality']);
    Route::get('/modalidades', [App\Http\Controllers\Api\SportsApiController::class, 'getModalities']);
    Route::get('/get-modalities', [App\Http\Controllers\Api\SportsApiController::class, 'getModalities']);
    Route::get('/get-days-list', [App\Http\Controllers\Api\MatchApiController::class, 'getDaysList']);
    Route::get('/site-list-odds/{id}', [App\Http\Controllers\Api\MatchApiController::class, 'getOdds']);
    Route::get('/site-list-odds-live/{id}', [App\Http\Controllers\Api\MatchApiController::class, 'getOdds']);

    // Polling inteligente (profissional - sem piscar)
    Route::get('/live-scores', [App\Http\Controllers\Api\LiveScoresController::class, 'liveScores']);
    Route::get('/home-matches', [App\Http\Controllers\Api\LiveScoresController::class, 'homeMatches']);
    
    // API-Football Status / Quota
    Route::get('/api-status', [App\Http\Controllers\Api\MatchApiController::class, 'getApiStatus']);
    
    // Auth & User Legacy Bridge
    Route::post('/login', [App\Http\Controllers\Api\ApiController::class, 'login'])->middleware('throttle:10,1');
    Route::post('/register', [App\Http\Controllers\Api\FrontendAuthController::class, 'register'])->middleware('throttle:5,1');
    Route::get('/user-logado', [App\Http\Controllers\Api\ApiController::class, 'userLogado']);
    Route::get('/sair', [App\Http\Controllers\Api\ApiController::class, 'logout']);
    
    // Payments (Auth protected below, but webhook is public)
    Route::post('/webhook/mercadopago/{siteId}', [App\Http\Controllers\Api\PaymentController::class, 'webhookMercadoPago']);
    Route::post('/mercadopago/webhook', [App\Http\Controllers\Api\PaymentController::class, 'webhookMercadoPago']); // Alias solicitado

    Route::get('/client-ip', function() { return response()->json(['ip' => '127.0.0.1']); });
    Route::match(['get', 'post'], '/user-notifications', function() { return response()->json([]); });
    Route::get('/list-blocked-leagues', [App\Http\Controllers\Admin\ConfrontosController::class, 'indexLigasBlock']);
    Route::get('/list-blocked-matches', [App\Http\Controllers\Admin\ConfrontosController::class, 'indexMatchsBlock']);
    Route::get('/list-blocked-live-matches', [App\Http\Controllers\Admin\ConfrontosController::class, 'indexMatchsBlock']);
    Route::get('/list-blocked-odds', function() {
        $siteId = config('tenant.site_id', 1);
        return response()->json(App\Models\BlockOddMatch::where('site_id', $siteId)->get());
    });

    // ═══════ STUBS PARA ENDPOINTS DO FRONTEND (EVITAR 404) ═══════
    Route::get('/read-user-notification/{id}', function() { return response()->json(['status' => 'ok']); });
    Route::get('/bilhete-pdf/{id}', function() { return response()->json(['status' => 'not_implemented']); });
    Route::match(['get', 'post'], '/bilhete/{id}', function($id) { return response()->json(['id' => $id]); });
    Route::match(['get', 'post'], '/support-list', function() { return response()->json([]); });
    Route::post('/close-support-chat', function() { return response()->json(['status' => 'ok']); });
    Route::match(['get', 'post'], '/support-chat/{id}', function() { return response()->json([]); });
    Route::post('/support-create', function() { return response()->json(['status' => 'ok']); });
    Route::post('/support-create-message', function() { return response()->json(['status' => 'ok']); });
    Route::get('/check-confirmation-account/{type}', function() { return response()->json(['status' => 'success', 'active' => true]); });
    Route::post('/pay-qr-code', function() { return response()->json(['status' => 'not_configured']); });
    Route::get('/lifetime/{id}', function() { return response()->json(['status' => 'active']); });
    Route::get('/check_pix/{id}', function() { return response()->json(['status' => 'pending']); });
    Route::get('/clientes', function() { return response()->json([]); });
    Route::post('/edit-password-seller', function() { return response()->json(['status' => 'ok']); });
    Route::get('/list-users-termos', function() { return response()->json([]); });
    Route::get('/compartilha-imagem/{id}', function() { return response()->json(['status' => 'not_implemented']); });
    
    // Gerador de Banner de Compartilhamento (POST)
    Route::post('/compartilha-imagem', function(Request $request) {
        $site = App\Models\Site::where('id', config('tenant.site_id', 1))->first() ?? App\Models\Site::first();
        $theme = $request->input('theme', '#1aa6d0');
        
        // Gerar cor escura a partir do tema
        $r = hexdec(substr($theme, 1, 2)); 
        $g = hexdec(substr($theme, 3, 2)); 
        $b = hexdec(substr($theme, 5, 2));
        $theme_dark = sprintf('#%02x%02x%02x', max(0, $r - 40), max(0, $g - 40), max(0, $b - 40));

        $params = [
            'home' => $request->input('home', 'Time A'),
            'away' => $request->input('away', 'Time B'),
            'league' => $request->input('league', 'Liga'),
            'odds' => $request->input('odds', ''),
            'flag_home' => $request->input('flag_home', asset('img/placeholders/shield.png')),
            'flag_away' => $request->input('flag_away', asset('img/placeholders/shield.png')),
            'match_date' => $request->input('match_date', now()->format('d/m/Y H:i')),
            'sport' => $request->input('sport', 'Futebol'),
            'theme' => $theme,
            'theme_dark' => $theme_dark,
            'site_name' => $site->name ?? 'IHUB BETS',
        ];

        // Salvar parâmetros no cache com um ID único para o banner
        $bannerId = md5(json_encode($params) . time());
        \Illuminate\Support\Facades\Cache::put('share_banner_' . $bannerId, $params, 3600);

        // Retornar a URL do banner (que pode ser acessada como imagem/página)
        return response(url('/share/banner/' . $bannerId));
    });
    Route::get('/get-game-list', function() { return response()->json([]); });
    Route::post('/play-game', function() { return response()->json(['status' => 'not_implemented']); });

    // ═══════ ROTAS PÚBLICAS RESTAURADAS DO ORIGINAL ═══════
    Route::post('/site-search-times', [App\Http\Controllers\Api\MatchApiController::class, 'searchTeam']);
    Route::post('/site-search-league', [App\Http\Controllers\Api\MatchApiController::class, 'searchLeague']);
    Route::get('/site-all-matchs', [App\Http\Controllers\Api\MatchApiController::class, 'getMatchesHome']);
    Route::get('/site-live-futebol', [App\Http\Controllers\Api\MatchApiController::class, 'getMatchesLive']);
    Route::get('/print-bilhete-id/{id}', [App\Http\Controllers\Api\BilheteApiController::class, 'printBilheteId']);
    Route::post('/print-bilhete-cod', [App\Http\Controllers\Api\BilheteApiController::class, 'printBilheteCod']);

});


// Admin API Routes
Route::group(['prefix' => 'admin', 'middleware' => ['auth']], function () {
    // Partidas Personalizadas
    Route::get('/personalized-matches', [PersonalizedMatchesController::class, 'index']);
    Route::post('/personalized-matches', [PersonalizedMatchesController::class, 'store']);
    Route::delete('/personalized-matches/{id}', [PersonalizedMatchesController::class, 'destroy']);

    // Partidas em destaque
    Route::get('/featured-matches', [MatchManagementController::class, 'getFeatured']);
    Route::post('/featured-matches', [MatchManagementController::class, 'addToFeatured']);
    Route::put('/featured-matches/{id}/meta', [MatchManagementController::class, 'updateFeaturedMeta']);
    Route::delete('/featured-matches/{id}', [MatchManagementController::class, 'removeFromFeatured']);
    
    // Cotações e Mercados
    Route::get('/global-markets', [OddsMarketsController::class, 'getGlobalMarkets']);
    Route::post('/global-markets', [OddsMarketsController::class, 'updateGlobalMarket']);
    Route::get('/global-odds', [OddsMarketsController::class, 'getGlobalOdds']);
    
    Route::get('/user-markets/{userId}', [OddsMarketsController::class, 'getUserMarkets']);
    Route::post('/user-markets/{userId}', [OddsMarketsController::class, 'updateUserMarket']);
    
    // Lista de jogos Pre-Jogo (Consolidado)
    Route::get('/games-list', [MatchManagementController::class, 'getPreJogo']);
});

// Public Routes (Pré-Bilhete e Consultas)
Route::group(['prefix' => 'public'], function () {
    Route::get('/games', [MatchManagementController::class, 'getPreJogo']);
    Route::post('/pre-bet', [PublicBetController::class, 'store']);
    Route::get('/pre-bet/{code}', [PublicBetController::class, 'show']);
});

// Authenticated API Routes (Cambistas/Gerentes/Clientes)
// IMPORTANTE: usa middleware 'web' + 'auth' para aceitar sessão Laravel (cookie)
// O Vue.js autentica via sessão após login em /api/login
Route::group(['middleware' => ['web', 'auth', 'throttle:30,1']], function () {
    Route::post('/bet', [App\Http\Controllers\Api\BilheteApiController::class, 'sendAposta']);

    // ═══════ ROTAS RESTAURADAS DO ORIGINAL — FASE 5 ═══════

    // Dados do cambista logado
    Route::get('/user-logado-auth', [App\Http\Controllers\Api\BilheteApiController::class, 'dadosLogado']);

    // Bilhetes
    Route::get('/bilhetes', [App\Http\Controllers\Api\BilheteApiController::class, 'bilhetes']);
    Route::post('/search-bilhetes', [App\Http\Controllers\Api\BilheteApiController::class, 'searchBilhete']);

    // Print bilhete (autenticado)
    Route::post('/print-bilhete-get-cod', [App\Http\Controllers\Api\BilheteApiController::class, 'printBilheteGetCod']);

    // Validação de código (converte pré-bilhete em aposta)
    Route::post('/valida-cod', [App\Http\Controllers\Api\BilheteApiController::class, 'validaCod']);

    // Envio de apostas (Autenticado - Aposta Direta)
    Route::post('/send-aposta-auth', [App\Http\Controllers\Api\BilheteApiController::class, 'sendAposta']);
    Route::post('/send-aposta-site-auth', [App\Http\Controllers\Api\BilheteApiController::class, 'sendApostaSite']);

    // Envio aposta Live
    Route::post('/send-aposta-live', [App\Http\Controllers\Api\BilheteApiController::class, 'sendApostaLive']);
    Route::post('/send-valid-live', [App\Http\Controllers\Api\BilheteApiController::class, 'validLive']);
    Route::post('/send-aposta-live-app', [App\Http\Controllers\Api\BilheteApiController::class, 'sendApostaLiveApp']);
    Route::post('/send-valid-live-app', [App\Http\Controllers\Api\BilheteApiController::class, 'validLiveApp']);

    // Envio aposta Live
    Route::post('/cancela-bilhete/{id}', [App\Http\Controllers\Api\BilheteApiController::class, 'cancelaBilhete']);

    // Relatório cambista
    Route::post('/relatorio-cambista', [App\Http\Controllers\Api\BilheteApiController::class, 'relatorio']);

    // Payments
    Route::post('/deposit/pix', [App\Http\Controllers\Api\PaymentController::class, 'createPix']);
    Route::post('/withdrawal/request', [App\Http\Controllers\Api\WithdrawalController::class, 'requestWithdrawal']);
    Route::get('/withdrawals', [App\Http\Controllers\Api\WithdrawalController::class, 'listWithdrawals']);
    
    // Admin Withdrawal management
    Route::post('/admin/withdrawal/{id}/approve', [App\Http\Controllers\Api\WithdrawalController::class, 'approve']);
    Route::post('/admin/withdrawal/{id}/reject', [App\Http\Controllers\Api\WithdrawalController::class, 'reject']);

    // Bonus system
    Route::post('/bonus/apply', [App\Http\Controllers\Api\BonusController::class, 'applyCode']);
    Route::get('/bonus/my', [App\Http\Controllers\Api\BonusController::class, 'myBonus']);

    // User settings
    Route::post('/change-password', [App\Http\Controllers\Api\FrontendAuthController::class, 'changePassword']);

    // ═══════ PALPITE API (MIGRADO DO REI BET) ═══════
    Route::get('/palpites/{id}', [App\Http\Controllers\Api\PalpiteController::class, 'show']);
});

// Gateway de Aposta (Híbrido: PIN ou Aposta Direta)
// Usando middleware 'web' para acessar a sessão mas permitir acesso público (Convidados)
Route::group(['middleware' => ['web', 'throttle:15,1']], function () {
    Route::post('/send-aposta', [App\Http\Controllers\Api\PublicBetController::class, 'store']);
    Route::post('/send-pre-aposta', [App\Http\Controllers\Api\PublicBetController::class, 'store']);
    Route::post('/send-aposta-site', [App\Http\Controllers\Api\PublicBetController::class, 'store']);
    Route::post('/print-bilhete-get-cod-site', [App\Http\Controllers\Api\PublicBetController::class, 'showByCupom']);
    Route::get('/print-bilhete-id/{id}', [App\Http\Controllers\Api\PublicBetController::class, 'show']);
    Route::post('/user-online-send-bet', [App\Http\Controllers\Api\UserBetController::class, 'store']);
});

// ═══════ ROTAS API-FOOTBALL & SCRAPER (NOVAS) ═══════
Route::group(['prefix' => 'scraper', 'middleware' => ['web']], function () {
    Route::get('/export', function() {
        $modo = request('modo', 'today');
        $token = request('token');
        $masterToken = config('services.scraper.master_token');
        if ($masterToken && $token !== $masterToken) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $fileName = match($modo) {
            'live' => 'jogos-jogadinha-live.json',
            'tomorrow' => 'jogos-jogadinha-tomorrow.json',
            default => 'jogos-jogadinha.json',
        };
        $path = base_path('scraper-jogadinha/' . $fileName);
        if (!file_exists($path)) {
            return response()->json(['error' => 'File not found'], 404);
        }
        return response()->file($path);
    });
});

// ═══════ WEBHOOKS (NOVOS) ═══════
Route::group(['prefix' => 'webhook', 'middleware' => ['web']], function () {
    Route::post('/shipay/{siteId?}', [App\Http\Controllers\Api\PaymentController::class, 'webhookShipay']);
    Route::post('/paggue/{siteId?}', [App\Http\Controllers\Api\PaymentController::class, 'webhookPaggue']);
});

// ═══════ AUTH API (NOVA) ═══════
Route::group(['prefix' => 'auth', 'middleware' => ['web']], function () {
    Route::post('/login', [App\Http\Controllers\Api\ApiAuthController::class, 'login']);
    Route::post('/register', [App\Http\Controllers\Api\ApiAuthController::class, 'register']);
    Route::post('/logout', [App\Http\Controllers\Api\ApiAuthController::class, 'logout'])->middleware('auth');
    Route::get('/me', [App\Http\Controllers\Api\ApiAuthController::class, 'me'])->middleware('auth');
});

// ═══════ CONFIG API (NOVA) ═══════
Route::group(['prefix' => 'config', 'middleware' => ['web', 'auth']], function () {
    Route::get('/', [App\Http\Controllers\Api\ConfiguracaoController::class, 'index']);
    Route::put('/', [App\Http\Controllers\Api\ConfiguracaoController::class, 'update']);
    Route::get('/limits', [App\Http\Controllers\Api\ConfiguracaoController::class, 'getLimits']);
});

// ═══════ SAQUE API (COMPLETA) ═══════
Route::group(['prefix' => 'saque', 'middleware' => ['web', 'auth']], function () {
    // WithdrawalRequest (tabela withdrawal_requests)
    Route::post('/request', [App\Http\Controllers\Api\SaqueApiController::class, 'request']);
    Route::get('/status/{id}', [App\Http\Controllers\Api\SaqueApiController::class, 'status']);
    Route::get('/history', [App\Http\Controllers\Api\SaqueApiController::class, 'history']);

    // Legacy Saque (tabela saques - compatibilidade REI BET)
    Route::post('/novo', [App\Http\Controllers\Api\SaquesApiController::class, 'novoSaque']);
    Route::get('/listar', [App\Http\Controllers\Api\SaquesApiController::class, 'listSaques']);
});

// ═══════ PLAYFIVER CASINO API ═══════
Route::group(['middleware' => ['web', 'auth']], function () {
    Route::get('/playfiver/games', [App\Http\Controllers\Api\PlayfiverController::class, 'getGames']);
    Route::post('/playfiver/launch', [App\Http\Controllers\Api\PlayfiverController::class, 'launchGame']);
});

// ═══════ WEBHOOK PLAYFIVER ═══════
Route::post('/webhook/playfiver', [App\Http\Controllers\Api\PlayfiverWebhookController::class, 'handle']);

// ═══════ DEPOSITOS PIX (PRIMEPAG) ═══════
Route::group(['middleware' => ['web', 'auth']], function () {
    Route::post('/depositos/novo', [App\Http\Controllers\Api\DepositosController::class, 'novoDeposito']);
    Route::get('/depositos/listar', [App\Http\Controllers\Api\DepositosController::class, 'listDepositos']);
});

// ═══════ WEBHOOK CONFIRMAR DEPOSITO ═══════
Route::post('/webhook/confirmar-deposito', [App\Http\Controllers\PixController::class, 'confirmarDeposito']);

// ═══════ CASH-OUT API ═══════
Route::group(['middleware' => ['web', 'auth']], function () {
    Route::post('/cash-out/{id}', [App\Http\Controllers\Api\BilheteApiController::class, 'cashOutAposta']);
});

// ═══════ BONUS/PROMOCOES API ═══════
Route::group(['middleware' => ['web', 'auth']], function () {
    Route::get('/bonus/promocoes', [App\Http\Controllers\Api\BonusController::class, 'getActivePromotions']);
    Route::post('/bonus/reivindicar', [App\Http\Controllers\Api\BonusController::class, 'claimBonus']);
    Route::post('/bonus/cancelar', [App\Http\Controllers\Api\BonusController::class, 'cancelBonus']);
});

// ═══════ LOTO API (QUININHA / SENINHA) ═══════
Route::group(['middleware' => ['web']], function () {
    Route::get('/num-quina', [App\Http\Controllers\Api\LotoApiController::class, 'geraQuina']);
    Route::get('/taxas-quina', [App\Http\Controllers\Api\LotoApiController::class, 'viewCotacaoQuina']);
    Route::get('/concursos-quina', [App\Http\Controllers\Api\LotoApiController::class, 'viewDiasSorteioQuina']);
    Route::get('/num-sena', [App\Http\Controllers\Api\LotoApiController::class, 'geraSena']);
    Route::get('/taxas-sena', [App\Http\Controllers\Api\LotoApiController::class, 'viewCotacaoSena']);
    Route::get('/concursos-sena', [App\Http\Controllers\Api\LotoApiController::class, 'viewDiasSorteioSena']);
});

// ═══════ BOLAO API (POOL BETTING) ═══════
Route::group(['middleware' => ['web']], function () {
    Route::get('/rodadas-abertas', [App\Http\Controllers\Api\BolaoApiController::class, 'rodadasAbertas']);
    Route::get('/rodada/{id}', [App\Http\Controllers\Api\BolaoApiController::class, 'rodadaDetalhes']);
});

Route::group(['middleware' => ['web', 'auth']], function () {
    Route::post('/send-bolao', [App\Http\Controllers\Api\BolaoApiController::class, 'sendBolao']);
});
