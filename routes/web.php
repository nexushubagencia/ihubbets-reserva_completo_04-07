<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\ConfiguracaoController;
use App\Http\Controllers\Admin\ConfrontosController;
use App\Http\Controllers\Admin\FinanceiroController;
use App\Http\Controllers\Admin\RelatorioController;
use App\Http\Controllers\Admin\GerenciamentoRiscos;
use App\Http\Controllers\Admin\MapaController;
use App\Http\Controllers\Admin\MercadosController;
use App\Http\Controllers\Admin\OddsController;
use App\Http\Controllers\Admin\ResultController;
use App\Http\Controllers\Admin\RiskController;
use App\Http\Controllers\Admin\StatisticsController;
use App\Http\Controllers\Admin\LiveStatisticsController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\FeaturedMatchesController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\GerenteController;
use App\Http\Controllers\Admin\BonusController;
use App\Http\Controllers\Admin\CambistaController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\MatchManagementController;
use App\Http\Controllers\Admin\PersonalizedMatchesController;
use App\Http\Controllers\Admin\BilheteController;
use App\Http\Controllers\Admin\TransactionReportController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\PromocaoController;
use App\Http\Controllers\Admin\TraducaoController;
use App\Http\Controllers\Admin\OddsManagementController;
use App\Http\Controllers\Admin\RegionController;
use App\Http\Controllers\Admin\GerenciadorController;
use App\Http\Controllers\Admin\LegacyBridgeController;
use App\Http\Controllers\Admin\BetController;
use App\Http\Controllers\Admin\FinancialAdjustmentController;
use App\Http\Controllers\Admin\PromoCodeController;
use App\Http\Controllers\Admin\ClientesController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [\App\Http\Controllers\Api\ApiController::class, 'index']);
Route::get('/cassino', [\App\Http\Controllers\CassinoController::class, 'index'])->name('cassino.index');
Route::get('/generate-css', [SettingsController::class, 'generateCss'])->name('generate-css');

// Banner de Compartilhamento (Gerador de Imagem)
Route::get('/share/banner/{id}', function($id) {
    $params = \Illuminate\Support\Facades\Cache::get('share_banner_' . $id);
    if (!$params) {
        return response('Banner expirado ou não encontrado.', 404);
    }
    return view('share.banner', $params);
})->name('share.banner');


Auth::routes();

// Email Verification
Route::get('/verificar/{code}', [App\Http\Controllers\Auth\VerificationController::class, 'verify']);

// Logout via GET (AdminLTE user menu)
Route::get('/logout', function () {
    \Illuminate\Support\Facades\Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->name('logout.get');

Route::get('/home', [HomeController::class, 'index'])->name('home');

// Middleware de Tenant e Last Activity aplicados globalmente
Route::middleware(['auth', 'admin', 'tenant', 'activity'])->prefix('admin')->group(function () {
    
    Route::get('/', [HomeController::class, 'index'])->name('admin.home');
    Route::get('/dashboard-stats', [HomeController::class, 'relatorioHome']);
    Route::get('/active-players', [HomeController::class, 'activePlayers']);

    // Theme toggle (dark/light)
    Route::post('/update-theme', [\App\Http\Controllers\Admin\UserController::class, 'updateTheme'])->name('admin.update-theme');

    // ============================================================
    // PERFIL & USUÁRIO
    // ============================================================
    Route::get('perfil', [ProfileController::class, 'index'])->name('admin.profile');
    Route::post('perfil/update', [ProfileController::class, 'update'])->name('admin.profile.update');
    Route::get('editar-perfil', [ProfileController::class, 'edit'])->name('admin.profile.edit');

    // Caixa
    Route::get('list-gerentes', [HomeController::class, 'listGerentes']);

    // Gerentes
    Route::get('gerentes', [GerenteController::class, 'indexView'])->name('admin.gerentes.index');
    Route::get('gerentes-list', [GerenteController::class, 'index'])->name('admin.gerentes.list');
    Route::get('cadastrar-gerente', [GerenteController::class, 'storeView'])->name('admin.gerentes.create');
    Route::get('cadastrar-gerentes', [GerenteController::class, 'storeView']); // Alias plural
    Route::post('cadastrar-gerente', [GerenteController::class, 'store']);
    Route::get('gerente/editar/{id}', [GerenteController::class, 'edtView'])->name('admin.gerentes.edit');
    Route::get('editar-gerente/{id}', [GerenteController::class, 'edtView']); // Alias
    Route::put('editar-gerente/{id}', [GerenteController::class, 'update']); // Alias PUT
    Route::put('gerentes/{id}', [GerenteController::class, 'update']);
    Route::delete('gerentes/{id}', [GerenteController::class, 'destroy']);
    Route::delete('deletar-gerente/{id}', [GerenteController::class, 'destroy']); // Alias
    Route::post('gerentes-search', [GerenteController::class, 'searchUser']);
    Route::get('search-gerente/{name}', [GerenteController::class, 'searchByName']);
    Route::post('gerente-lancamento', [GerenteController::class, 'storeLancamento']);

    // Cambistas
    Route::get('cambistas', [CambistaController::class, 'indexView'])->name('admin.cambistas.index');
    Route::get('list-cambistas', [CambistaController::class, 'index'])->name('admin.list.cambistas');
    Route::get('list-gerentes-select', [CambistaController::class, 'listGerentes']);
    Route::post('cadastrar-cambista', [CambistaController::class, 'store']);
    Route::get('cambista/editar/{id}', [CambistaController::class, 'edtView'])->name('admin.cambista.edit');
    Route::put('cambistas/{id}', [CambistaController::class, 'update']);
    Route::delete('cambistas/{id}', [CambistaController::class, 'destroy']);
    
    // Bloqueio de Usuários (Centralizado)
    Route::post('alterar-user', [ConfiguracaoController::class, 'bloquearUser']);
    Route::post('bloquear-user', [ConfiguracaoController::class, 'bloquearUser']);
    
    // Lançamentos Financeiros & Limites
    Route::get('lancamentos', [CambistaController::class, 'lancamentosView'])->name('admin.lancamentos');
    Route::get('lancamentos-list', [CambistaController::class, 'lancamentos'])->name('admin.lancamentos.list');
    Route::post('add-lancamento', [CambistaController::class, 'storeLancamento']);
    Route::post('ajustar-limite', [CambistaController::class, 'ajustarLimite']);
    Route::delete('lancamento/deletar/{id}', [CambistaController::class, 'destroyLancamento']);

    // ============================================================
    // CONFIGURAÇÕES
    // ============================================================
    // ============================================================
    // LOTO (QUININHA / SENINHA)
    // ============================================================
    Route::get('loto', [\App\Http\Controllers\Admin\LotoController::class, 'index'])->name('admin.loto');
    Route::get('loto/taxas-quininha', [\App\Http\Controllers\Admin\LotoController::class, 'taxasQuininha'])->name('admin.loto.taxas.quininha');
    Route::get('loto/taxas-seninha', [\App\Http\Controllers\Admin\LotoController::class, 'taxasSeninha'])->name('admin.loto.taxas.seninha');
    Route::get('loto/results', [\App\Http\Controllers\Admin\LotoController::class, 'results'])->name('admin.loto.results');
    Route::post('loto/results', [\App\Http\Controllers\Admin\LotoController::class, 'storeResult']);
    Route::put('loto/taxa-quina/{id}', [\App\Http\Controllers\Admin\LotoController::class, 'updateTaxaQuina']);
    Route::put('loto/status-quina/{id}', [\App\Http\Controllers\Admin\LotoController::class, 'updateStatusQuina']);
    Route::put('loto/taxa-sena/{id}', [\App\Http\Controllers\Admin\LotoController::class, 'updateTaxaSena']);
    Route::put('loto/status-sena/{id}', [\App\Http\Controllers\Admin\LotoController::class, 'updateStatusSena']);
    Route::get('loto/list-taxas-quininha', [\App\Http\Controllers\Admin\LotoController::class, 'listTaxasQuininha']);
    Route::get('loto/list-taxas-seninha', [\App\Http\Controllers\Admin\LotoController::class, 'listTaxasSeninha']);
    Route::get('loto/apostas/{tipo}/{concurso}', [\App\Http\Controllers\Admin\LotoController::class, 'apostasConcurso']);
    Route::post('loto/block-day', [\App\Http\Controllers\Admin\LotoController::class, 'blockDay']);
    Route::delete('loto/unblock-day/{id}', [\App\Http\Controllers\Admin\LotoController::class, 'unblockDay']);
    Route::get('loto/blocked-days', [\App\Http\Controllers\Admin\LotoController::class, 'blockedDays']);

    // ============================================================
    // CASH OUT (ANTECIPAR APOSTA)
    // ============================================================
    Route::get('cashout', [\App\Http\Controllers\Admin\CashOutController::class, 'index'])->name('admin.cashout');
    Route::post('cashout/calcular/{id}', [\App\Http\Controllers\Admin\CashOutController::class, 'calcularCashOut']);
    Route::post('cashout/executar/{id}', [\App\Http\Controllers\Admin\CashOutController::class, 'executarCashOut']);

    // ============================================================
    // BOLAO (POOL BETTING)
    // ============================================================
    Route::get('bolao', [\App\Http\Controllers\Admin\BolaoController::class, 'index'])->name('admin.bolao');
    Route::post('bolao/store-rodada', [\App\Http\Controllers\Admin\BolaoController::class, 'storeRodada']);
    Route::put('bolao/rodada/{id}', [\App\Http\Controllers\Admin\BolaoController::class, 'updateRodada']);
    Route::delete('bolao/rodada/{id}', [\App\Http\Controllers\Admin\BolaoController::class, 'destroyRodada']);
    Route::get('bolao/rodada/{id}/apostas', [\App\Http\Controllers\Admin\BolaoController::class, 'apostasRodada']);
    Route::post('bolao/{id}/fechar', [\App\Http\Controllers\Admin\BolaoController::class, 'fecharRodada']);
    Route::post('bolao/{id}/finalizar', [\App\Http\Controllers\Admin\BolaoController::class, 'finalizarRodada']);

    Route::get('configuracoes', [ConfiguracaoController::class, 'indexView'])->name('configuracoes');
    Route::get('financeiro-gateways', [ConfiguracaoController::class, 'financeiroGatewaysView'])->name('financeiro-gateways');
    Route::get('financeiro-pix-usuarios', [ConfiguracaoController::class, 'financeiroPixUsuariosView'])->name('financeiro-pix-usuarios');
    Route::post('user-financial-update/{id}', [ConfiguracaoController::class, 'userFinancialUpdate']);
    Route::get('list-configuracoes', [ConfiguracaoController::class, 'index']);
    Route::put('configuracoes/{id}', [ConfiguracaoController::class, 'update']);
    Route::put('edit-configuracao/{id}', [ConfiguracaoController::class, 'update']); // Alias PUT
    Route::post('upload-logo', [ConfiguracaoController::class, 'uploadLogo']);
    Route::post('upload-favicon', [ConfiguracaoController::class, 'uploadFavicon']);

    // ============================================================
    // CONFRONTOS & LIGAS
    // ============================================================
    Route::get('confrontos', [ConfrontosController::class, 'indexView'])->name('confrontos');
    Route::get('confrontos-list', [ConfrontosController::class, 'index']);
    Route::get('confrontos-aovivo', [ConfrontosController::class, 'viewAovivo']);
    Route::get('confrontos-aovivo-list', [ConfrontosController::class, 'indexAovivo']);
    Route::put('confrontos/{id}', [ConfrontosController::class, 'update']);
    Route::put('confrontos-odd/{id}', [ConfrontosController::class, 'updateOdd']);
    Route::post('confrontos-search', [ConfrontosController::class, 'searchMatch']);

    // Bloqueio/Desbloqueio de Ligas e Partidas (Restaurado do Legacy)
    Route::post('bloquear-ligas', [ConfrontosController::class, 'blockLeague']);
    Route::post('update-match', [ConfrontosController::class, 'blockMatch']);
    Route::delete('deletar-match/{id}', [ConfrontosController::class, 'deleteMatch']);
    Route::get('list-ligas-bloqueadas', [ConfrontosController::class, 'indexLigasBlock'])->name('admin.ligas.bloqueadas');
    Route::get('list-matchs-bloqueadas', [ConfrontosController::class, 'indexMatchsBlock'])->name('admin.matchs.bloqueadas');

    Route::get('gerenciar-ligas', [ConfiguracaoController::class, 'gerenciarLigas']);
    Route::get('gerenciar-ligas-principais', [ConfiguracaoController::class, 'ligasPrincipaisView'])->name('admin.ligas.principais');
    Route::get('show-ligas-principais', [ConfiguracaoController::class, 'showLigas']);
    Route::delete('deletar-ligas-main/{id}', [ConfiguracaoController::class, 'deleteLeague']);
    Route::post('insert-league-main', [ConfrontosController::class, 'insertLeagueMain']);
    Route::get('list-league-main', [ConfrontosController::class, 'listLeagueMain']);
    
    Route::get('gerenciar-matchs', [MatchManagementController::class, 'getPreJogo'])->name('admin.matchs.block');
    
    // Dados da Banca (Administrador)
    Route::get('dados-banca', [HomeController::class, 'mostraDadosAdm'])->name('admin.dados-banca');
    
    // Lista de todas as ligas (View)
    Route::get('adm-ligas-list', function() {
        return view('admin.list-adm-ligas');
    })->name('admin.adm-ligas-list');

    // ============================================================
    // CAIXA FINANCEIRO
    // ============================================================
    Route::get('caixa-adm-gerente', [FinanceiroController::class, 'indexViewAdmGerente']);
    Route::get('caixa-gerente-list', [FinanceiroController::class, 'caixaGerente']);
    Route::post('search-caixa-adm-gerente', [FinanceiroController::class, 'searchCaixaGerente']);
    Route::get('caixa-adm-cambista', [FinanceiroController::class, 'indexViewAdmCambista']);
    Route::get('caixa-cambista-list', [FinanceiroController::class, 'caixaCambista']);
    Route::get('list-caixa-adm-cambista', [FinanceiroController::class, 'caixaCambista']);
    Route::post('search-caixa-adm-cambista', [FinanceiroController::class, 'searchCaixaCambista']);
    Route::get('caixa-user/{id}', [FinanceiroController::class, 'caixaUser']);
    Route::get('list-gerente-caixa/{id}', [FinanceiroController::class, 'viewCaixaGerente']);
    Route::get('list-caixa-cambista/{id}', [FinanceiroController::class, 'caixaUserCambista']);
    Route::put('encerrar-caixa/{id}', [FinanceiroController::class, 'encerraCaixa']);
    Route::get('preview-caixa/{id}', [FinanceiroController::class, 'previewCaixa'])->name('admin.preview-caixa');
    Route::get('historico-fechamentos/{id}', [FinanceiroController::class, 'historicoFechamentos'])->name('admin.historico-fechamentos');
    Route::get('caixa-do-dia', [FinanceiroController::class, 'caixaDoDia'])->name('admin.caixa-do-dia');
    Route::get('caixa-do-dia/data', [FinanceiroController::class, 'caixaDoDiaData']);
    Route::get('caixa-do-dia/geral', [FinanceiroController::class, 'caixaDoDiaGeral']);

    // ---- SAQUES / RETIRADAS ----
    Route::get('saques', [FinanceiroController::class, 'saquesView'])->name('admin.saques');
    Route::get('saques-list', [FinanceiroController::class, 'listWithdrawals'])->name('admin.saques-list');
    Route::post('approve-withdrawal/{id}', [FinanceiroController::class, 'approveWithdrawal'])->name('admin.approve-withdrawal');
    Route::post('reject-withdrawal/{id}', [FinanceiroController::class, 'rejectWithdrawal'])->name('admin.reject-withdrawal');

    // ---- DEPÓSITOS ----
    Route::get('depositos', [FinanceiroController::class, 'depositosView'])->name('admin.depositos');
    Route::get('depositos-list', [FinanceiroController::class, 'listDepositos'])->name('admin.depositos-list');

    // ---- BILHETES & PIN ----
    Route::get('bilhetes', [BilheteController::class, 'indexView'])->name('admin.bilhetes');
    Route::get('bilhetes-list', [BilheteController::class, 'index']);
    Route::post('bilhetes-search', [BilheteController::class, 'search']);
    Route::put('bilhetes/{id}', [BilheteController::class, 'update']);
    Route::put('bilhete-update/{id}', [BilheteController::class, 'update']); // Alias legado
    Route::put('bilhete-change-status/{id}', [BilheteController::class, 'changeStatus']);
    Route::get('palpites-bilhete/{id}', [BilheteController::class, 'getPrintData']); // Usando método de impressão ou outro que retorne palpites
    Route::get('validar-pin', [BilheteController::class, 'validarPinView'])->name('admin.validar-pin');

    // ============================================================
    // GERENCIAMENTO DE RISCOS & MAPA
    // ============================================================
    Route::get('gerenciamento-riscos', [GerenciamentoRiscos::class, 'viewGerenciamento'])->name('admin.riscos');
    Route::post('gerenciamento-riscos-list', [GerenciamentoRiscos::class, 'riscos']);
    Route::get('risk-dashboard', [RiskController::class, 'dashboard'])->name('admin.risk.dashboard');
    Route::get('risk-map', [RiskController::class, 'betMap'])->name('admin.risk.map');
    Route::get('mapa-apostas', [MapaController::class, 'index'])->name('admin.mapa');
    Route::get('mapa-apostas-list', [MapaController::class, 'mapAposta']);

    // Relatórios
    Route::get('relatorio-cambista', [RelatorioController::class, 'indexViewRelatorioCambista'])->name('admin.reports.cambista');
    Route::post('relatorio-cambista-list', [RelatorioController::class, 'relatorioCambista']);
    Route::get('relatorio-gerente', [RelatorioController::class, 'indexViewRelatorioGerente'])->name('admin.reports.gerente');
    Route::post('relatorio-gerente-list', [RelatorioController::class, 'relatorioGerente']);
    Route::get('relatorio-transacoes', [TransactionReportController::class, 'indexView'])->name('admin.reports.transactions');

    // ============================================================
    // MERCADOS & ODDS
    // ============================================================
    Route::get('mercados', [MercadosController::class, 'indexView']);
    Route::get('mercados-list', [MercadosController::class, 'index']);
    Route::get('mercados/{id}', [MercadosController::class, 'show']);
    Route::put('mercados/{id}', [MercadosController::class, 'update']);
    
    Route::get('odds', [OddsController::class, 'indexView']);
    Route::post('odds-list', [OddsController::class, 'index']);
    Route::put('odds/{id}', [OddsController::class, 'update']);
    Route::get('odds-user', [OddsController::class, 'indexViewCambista'])->name('admin.odds.user');
    Route::get('odds-user/{id}', [OddsController::class, 'oddsUser'])->name('admin.odds.user.detail');
    Route::get('mercados-user', [MercadosController::class, 'indexView'])->name('admin.mercados.user'); // Reusando view de mercados para listar cambistas se necessário ou criar específica
    Route::get('mercado-user/{id}', [MercadosController::class, 'mercadoUser'])->name('admin.mercados.user.detail');

    // ============================================================
    // PERSONALIZAÇÃO & BANNERS
    // ============================================================
    Route::get('banners', [BannerController::class, 'index'])->name('admin.banners.index');
    Route::post('banners', [BannerController::class, 'store'])->name('admin.banners.store');
    Route::post('banners/{id}', [BannerController::class, 'update'])->name('admin.banners.update');
    Route::delete('banners/{id}', [BannerController::class, 'destroy'])->name('admin.banners.destroy');

    // Banner Templates & Backgrounds
    Route::get('banner-templates', [\App\Http\Controllers\Admin\BannerTemplateController::class, 'index'])->name('admin.banner-templates');
    Route::get('banner-templates/active', [\App\Http\Controllers\Admin\BannerTemplateController::class, 'active']);
    Route::get('banner-templates/{id}', [\App\Http\Controllers\Admin\BannerTemplateController::class, 'show']);
    Route::post('banner-templates', [\App\Http\Controllers\Admin\BannerTemplateController::class, 'store']);
    Route::put('banner-templates/{id}', [\App\Http\Controllers\Admin\BannerTemplateController::class, 'update']);
    Route::post('banner-templates/{id}/activate', [\App\Http\Controllers\Admin\BannerTemplateController::class, 'activate']);
    Route::delete('banner-templates/{id}', [\App\Http\Controllers\Admin\BannerTemplateController::class, 'destroy']);
    Route::get('banner-generator', [\App\Http\Controllers\Admin\BannerTemplateController::class, 'geradorView'])->name('admin.banner-generator');
    
    Route::get('settings/general', [SettingsController::class, 'index'])->name('admin.settings.general');
    Route::post('settings/general', [SettingsController::class, 'update'])->name('admin.settings.general.update');
    Route::get('settings/layout', [SettingsController::class, 'layoutView'])->name('admin.settings.layout');
    Route::post('settings/layout', [SettingsController::class, 'updateLayout'])->name('admin.settings.layout.update');
    Route::post('settings/layout/delete-theme', [SettingsController::class, 'deleteCustomTheme'])->name('admin.settings.layout.delete-theme');
    Route::post('settings/layout/save-theme', [SettingsController::class, 'saveCustomTheme'])->name('admin.settings.layout.save-theme');


    // Featured Matches (Destaques)
    Route::get('featured-matches', [FeaturedMatchesController::class, 'index'])->name('admin.featured-matches.index');
    Route::get('featured-matches/available', [FeaturedMatchesController::class, 'getAvailableMatches'])->name('featured-matches.available');
    Route::post('featured-matches/toggle', [FeaturedMatchesController::class, 'toggle'])->name('featured-matches.toggle');
    Route::post('featured-matches/update-meta', [FeaturedMatchesController::class, 'updateMeta'])->name('featured-matches.update-meta');

    // Partidas Personalizadas (Manual Events)
    Route::get('partidas-personalizadas', [PersonalizedMatchesController::class, 'index'])->name('admin.personalized.index');
    Route::post('partidas-personalizadas/store', [PersonalizedMatchesController::class, 'store'])->name('admin.personalized.store');
    Route::post('partidas-personalizadas/{id}/update', [PersonalizedMatchesController::class, 'update'])->name('admin.personalized.update');
    Route::post('partidas-personalizadas/{id}/toggle', [PersonalizedMatchesController::class, 'toggleStatus'])->name('admin.personalized.toggle');
    Route::delete('partidas-personalizadas/{id}', [PersonalizedMatchesController::class, 'destroy'])->name('admin.personalized.destroy');

    // Regulamento e Sobre Nós
    Route::get('regulamento', [SettingsController::class, 'regulamentoView'])->name('admin.regulamento');
    Route::get('regulamento/list', [SettingsController::class, 'regulamentoList'])->name('admin.regulamento.list');
    Route::post('regulamento/update/{id}', [SettingsController::class, 'regulamentoUpdate'])->name('admin.regulamento.update');
    Route::post('regulamento/upload-image', [SettingsController::class, 'regulamentoUploadImage'])->name('admin.regulamento.upload-image');
    
    Route::get('about-us', [SettingsController::class, 'aboutView'])->name('admin.settings.about');
    Route::post('about-us', [SettingsController::class, 'updateAbout'])->name('admin.settings.about.update');
    
    // ============================================================
    // MASTER PANEL (SUPER ADMIN) - MANUTENCAO
    // ============================================================
    Route::middleware(['can:isSuperAdmin'])->prefix('master')->group(function () {
        Route::get('dashboard', [\App\Http\Controllers\Admin\MasterPanelController::class, 'index'])->name('admin.master.dashboard');
        Route::get('bancas', [\App\Http\Controllers\Admin\MasterPanelController::class, 'bancasView'])->name('admin.master.bancas');
        Route::get('bancas-list', [\App\Http\Controllers\Admin\MasterPanelController::class, 'bancas']);
        Route::post('banca/criar', [\App\Http\Controllers\Admin\MasterPanelController::class, 'criarBanca']);
        Route::post('banca/update', [\App\Http\Controllers\Admin\MasterPanelController::class, 'updateBanca']);
        Route::post('banca/{id}/toggle', [\App\Http\Controllers\Admin\MasterPanelController::class, 'toggleBanca']);
        Route::get('banca/{id}/backup', [\App\Http\Controllers\Admin\MasterPanelController::class, 'downloadBackup'])->name('admin.master.banca.backup');

        Route::get('financeiro', [\App\Http\Controllers\Admin\MasterPanelController::class, 'financeiraMasterView'])->name('admin.master.financeiro');
        Route::get('financeiro-stats', [\App\Http\Controllers\Admin\MasterPanelController::class, 'stats']);
        Route::get('extrato-global', [\App\Http\Controllers\Admin\MasterPanelController::class, 'extratoGlobal']);

        Route::get('ranking', [\App\Http\Controllers\Admin\MasterPanelController::class, 'ranking'])->name('admin.master.ranking');

        Route::get('temas', [\App\Http\Controllers\Admin\MasterPanelController::class, 'temasView'])->name('admin.master.temas');
        Route::get('temas/criar', [\App\Http\Controllers\Admin\MasterPanelController::class, 'createTemaView'])->name('admin.master.temas.create');
        Route::get('temas/{id}/edit', [\App\Http\Controllers\Admin\MasterPanelController::class, 'editTemaView'])->name('admin.master.temas.edit');
        Route::post('temas', [\App\Http\Controllers\Admin\MasterPanelController::class, 'storeTema'])->name('admin.master.temas.store');
        Route::put('temas/{id}', [\App\Http\Controllers\Admin\MasterPanelController::class, 'updateTema'])->name('admin.master.temas.update');
        Route::delete('temas/{id}', [\App\Http\Controllers\Admin\MasterPanelController::class, 'deleteTema'])->name('admin.master.temas.delete');
        Route::post('temas/{id}/duplicate', [\App\Http\Controllers\Admin\MasterPanelController::class, 'duplicateTema'])->name('admin.master.temas.duplicate');
    });

    // ============================================================
    // API-FOOTBALL & SCRAPER
    // ============================================================
    Route::get('api-football', [\App\Http\Controllers\Admin\ApiFootballAdminController::class, 'index'])->name('admin.api-football');
    Route::post('api-football/leagues', [\App\Http\Controllers\Admin\ApiFootballAdminController::class, 'updateLeagues']);
    Route::post('api-football/sync', [\App\Http\Controllers\Admin\ApiFootballAdminController::class, 'syncNow']);
    Route::post('api-football/provider', [\App\Http\Controllers\Admin\ApiFootballAdminController::class, 'switchProvider']);

    Route::get('scraper', [\App\Http\Controllers\Admin\ApiScraperAdminController::class, 'index'])->name('admin.scraper');
    Route::post('scraper/config', [\App\Http\Controllers\Admin\ApiScraperAdminController::class, 'updateConfig']);
    Route::post('scraper/start', [\App\Http\Controllers\Admin\ApiScraperAdminController::class, 'startScraper']);
    Route::post('scraper/stop', [\App\Http\Controllers\Admin\ApiScraperAdminController::class, 'stopScraper']);
    Route::post('scraper/sync', [\App\Http\Controllers\Admin\ApiScraperAdminController::class, 'syncNow']);

    // ============================================================
    // SAQUES (Admin via SaquesAdminController - integração PrimePag)
    // ============================================================
    Route::get('saques-admin', [\App\Http\Controllers\Admin\SaquesAdminController::class, 'index'])->name('admin.saques-admin');
    Route::get('saques-admin/list', [\App\Http\Controllers\Admin\SaquesAdminController::class, 'list']);
    Route::post('saques-admin/{saque}/confirm', [\App\Http\Controllers\Admin\SaquesAdminController::class, 'confirm']);
    Route::post('saques-admin/{saque}/reject', [\App\Http\Controllers\Admin\SaquesAdminController::class, 'reject']);

    // ============================================================
    // TRADUÇÕES
    // ============================================================
    Route::get('traducoes', [\App\Http\Controllers\Admin\TraducaoController::class, 'index'])->name('admin.traducoes');
    Route::post('traducoes', [\App\Http\Controllers\Admin\TraducaoController::class, 'store']);
    Route::delete('traducoes/{id}', [\App\Http\Controllers\Admin\TraducaoController::class, 'destroy']);

    // ============================================================
    // PLAYFIVER CASINO
    // ============================================================
    Route::get('playfiver', function() { return view('admin.playfiver'); })->name('admin.playfiver');
    Route::get('cassino/apostas', function() { return view('admin.apostas-cassino'); })->name('admin.cassino.apostas');

    // ============================================================
    // PROMOÇÕES (CRUD Completo)
    // ============================================================
    Route::get('promocoes', [PromocaoController::class, 'index'])->name('admin.promocoes');
    Route::get('promocoes/create', [PromocaoController::class, 'create'])->name('admin.promocoes.create');
    Route::post('promocoes', [PromocaoController::class, 'store'])->name('admin.promocoes.store');
    Route::get('promocoes/{id}/edit', [PromocaoController::class, 'edit'])->name('admin.promocoes.edit');
    Route::put('promocoes/{id}', [PromocaoController::class, 'update'])->name('admin.promocoes.update');
    Route::delete('promocoes/{id}', [PromocaoController::class, 'destroy'])->name('admin.promocoes.destroy');

    // ============================================================
    // BÔNUS / CUPONS (CRUD)
    // ============================================================
    Route::get('bonus', [BonusController::class, 'index'])->name('admin.bonus');
    Route::post('bonus', [PromoCodeController::class, 'store'])->name('admin.bonus.store');
    Route::delete('bonus/{id}', [PromoCodeController::class, 'destroy'])->name('admin.bonus.destroy');

    // ============================================================
    // ESTATÍSTICAS & RELATÓRIOS AVANÇADOS
    // ============================================================
    Route::get('statistics/daily', [\App\Http\Controllers\Admin\StatisticsController::class, 'daily'])->name('admin.statistics.daily');
    Route::get('statistics/by-seller', [\App\Http\Controllers\Admin\StatisticsController::class, 'bySeller'])->name('admin.statistics.by-seller');
    Route::get('statistics/by-manager', [\App\Http\Controllers\Admin\StatisticsController::class, 'byManager'])->name('admin.statistics.by-manager');
    Route::get('statistics/live', [\App\Http\Controllers\Admin\LiveStatisticsController::class, 'index'])->name('admin.statistics.live');
    Route::get('statistics/live/data', [\App\Http\Controllers\Admin\LiveStatisticsController::class, 'getLiveData']);

    // ============================================================
    // TRADUÇÕES (CRUD)
    // ============================================================
    Route::get('traducoes', [TraducaoController::class, 'index'])->name('admin.traducoes');
    Route::post('traducoes', [TraducaoController::class, 'store'])->name('admin.traducoes.store');
    Route::delete('traducoes/{id}', [TraducaoController::class, 'destroy'])->name('admin.traducoes.destroy');

    // ============================================================
    // RESULTADOS (Processamento de Resultados)
    // ============================================================
    Route::get('pending-events', [\App\Http\Controllers\Admin\ResultController::class, 'pendingEvents'])->name('admin.pending-events');
    Route::post('results/submit', [\App\Http\Controllers\Admin\ResultController::class, 'submit'])->name('admin.results.submit');
    Route::post('results/preview', [\App\Http\Controllers\Admin\ResultController::class, 'preview'])->name('admin.results.preview');
    Route::post('results/cancel', [\App\Http\Controllers\Admin\ResultController::class, 'cancel'])->name('admin.results.cancel');

    // ============================================================
    // GERENCIAMENTO DE ODDS AVANÇADO
    // ============================================================
    Route::get('markets/odds', [OddsManagementController::class, 'index'])->name('admin.markets.odds');
    Route::post('markets/adjust-league', [OddsManagementController::class, 'adjustLeagueOdds'])->name('admin.markets.adjust-league');
    Route::post('markets/toggle-market', [OddsManagementController::class, 'toggleMarket'])->name('admin.markets.toggle-market');

    // ============================================================
    // REGIÕES
    // ============================================================
    Route::get('regions', [RegionController::class, 'index'])->name('admin.regions');
    Route::post('regions', [RegionController::class, 'store'])->name('admin.regions.store');

    // ============================================================
    // BILHETES AVANÇADO (Cancel, PIN, Cashout)
    // ============================================================
    Route::post('bets/{id}/cancel', [BetController::class, 'cancel'])->name('admin.bets.cancel');
    Route::post('bets/{id}/cashout-pin', [BetController::class, 'generateCashoutPin'])->name('admin.bets.cashout-pin');
    Route::get('bets/validate-pin', [BetController::class, 'validarPinView'])->name('admin.bets.validate-pin');
    Route::post('bets/validate-pin', [BetController::class, 'validatePin'])->name('admin.bets.validate-pin.post');

    // ============================================================
    // AJUSTES FINANCEIROS
    // ============================================================
    Route::get('finance/adjustments', [FinancialAdjustmentController::class, 'index'])->name('admin.finance.adjustments');
    Route::post('finance/adjustments', [FinancialAdjustmentController::class, 'store'])->name('admin.finance.adjustments.store');

    // ============================================================
    // CLIENTES (CRUD - migrado do REI BET)
    // ============================================================
    Route::get('clientes', [ClientesController::class, 'index'])->name('admin.clientes');
    Route::get('clientes/list', [ClientesController::class, 'list'])->name('admin.clientes.list');
    Route::get('clientes/search/{name}', [ClientesController::class, 'searchUser'])->name('admin.clientes.search');
    Route::put('editar-cliente/{id}', [ClientesController::class, 'update'])->name('admin.clientes.update');
    Route::delete('deletar-cliente/{id}', [ClientesController::class, 'destroy'])->name('admin.clientes.destroy');
    Route::get('clientes/{user}/depositos', [ClientesController::class, 'depositos'])->name('admin.clientes.depositos');
    Route::get('clientes/{user}/saques', [ClientesController::class, 'saques'])->name('admin.clientes.saques');

    // ============================================================
    // GERENCIADOR MULTI-TENANT (Super Admin)
    // ============================================================
    Route::middleware(['can:isSuperAdmin'])->prefix('gerenciador')->group(function () {
        Route::get('/', [GerenciadorController::class, 'index'])->name('admin.gerenciador');
        Route::get('sites', [GerenciadorController::class, 'sites'])->name('admin.gerenciador.sites');
        Route::post('/', [GerenciadorController::class, 'store'])->name('admin.gerenciador.store');
        Route::get('{id}/edit', [GerenciadorController::class, 'edit'])->name('admin.gerenciador.edit');
        Route::put('{id}', [GerenciadorController::class, 'update'])->name('admin.gerenciador.update');
        Route::post('{id}/toggle', [GerenciadorController::class, 'toggleSiteStatus'])->name('admin.gerenciador.toggle');
        Route::get('odds', [GerenciadorController::class, 'oddsCorrectionView'])->name('admin.gerenciador.odds');
        Route::post('odds', [GerenciadorController::class, 'applyGlobalOddAdjustment'])->name('admin.gerenciador.odds.apply');
        Route::get('{id}/backup', [GerenciadorController::class, 'downloadBackup'])->name('admin.gerenciador.backup');
    });

    // Configuracoes de env
    Route::get('env-config', [\App\Http\Controllers\Admin\EnvController::class, 'index'])->name('admin.env-config');
    Route::post('env-config', [\App\Http\Controllers\Admin\EnvController::class, 'update']);
});

// ============================================================
// LEGACY BRIDGE API (Compatibilidade Frontend Antigo)
// ============================================================
Route::middleware(['web', 'tenant'])->prefix('api/legacy')->group(function () {
    Route::get('settings', [LegacyBridgeController::class, 'getSettings']);
    Route::get('banners', [LegacyBridgeController::class, 'getBanners']);
    Route::get('leagues', [LegacyBridgeController::class, 'getLeagues']);
    Route::get('matches', [LegacyBridgeController::class, 'getMatches']);
});

// ============================================================
// CLIENT FRONTEND VIEWS
// ============================================================
Route::middleware(['web'])->group(function () {
    Route::get('/live', function () {
        return view('client.live');
    })->name('client.live');

    Route::get('/cambista-app', function () {
        return view('cambista-app');
    })->name('client.cambista-app');

    // Standalone bilhete page for printing
    Route::get('/bilhete/{id}', function ($id) {
        $bilhete = \App\Models\Aposta::with('palpites')->find($id);
        if (!$bilhete) {
            return response('Bilhete não encontrado', 404);
        }
        return view('bilhete', compact('bilhete'));
    })->name('bilhete.print');
});

// ============================================================
// PANEIS SEPARADOS (Rotas por papel de usuario)
// ============================================================
require __DIR__.'/cambista.php';
require __DIR__.'/gerente.php';
require __DIR__.'/cliente.php';

