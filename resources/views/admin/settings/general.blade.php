@extends('adminlte::page')

@section('title', 'Configurações do Site - IHUB BETS')

@section('content_header')
    <h1><i class="fas fa-cogs"></i> Configurações <small class="text-muted">Do sistema (V4 Pro)</small></h1>
@stop

@section('content')
    <form action="{{ route('admin.settings.general.update') }}" method="POST">
        @csrf
        
        <!-- EVENTOS PRÉ-JOGO -->
        <div class="card card-outline card-secondary mb-2 shadow-sm">
            <div class="card-header pb-2 pt-2" data-toggle="collapse" data-target="#collapsePre" style="cursor: pointer;">
                <h3 class="card-title text-sm"><i class="fas fa-calendar-alt"></i> EVENTOS PRÉ-JOGO</h3>
            </div>
            <div id="collapsePre" class="collapse">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group mb-2">
                                <label class="text-xs">Prêmio Máximo: ?</label>
                                <input type="number" name="prem_max_pre" class="form-control form-control-sm" value="{{ $settings->prem_max_pre ?? 50000 }}">
                            </div>
                            <div class="form-group mb-2">
                                <label class="text-xs">Valor mínimo por aposta: ?</label>
                                <input type="number" name="val_min_pre" class="form-control form-control-sm" value="{{ $settings->val_min_pre ?? 1 }}">
                            </div>
                            <div class="form-group mb-2">
                                <label class="text-xs">Valor máximo por aposta: ?</label>
                                <input type="number" name="val_max_pre" class="form-control form-control-sm" value="{{ $settings->val_max_pre ?? 1000 }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-2">
                                <label class="text-xs">Cotação mínima por aposta: ?</label>
                                <input type="text" name="cot_min_pre" class="form-control form-control-sm" value="{{ $settings->cot_min_pre ?? 1.4 }}">
                            </div>
                            <div class="form-group mb-2">
                                <label class="text-xs">Cotação máxima por aposta: ?</label>
                                <input type="number" name="cot_max_pre" class="form-control form-control-sm" value="{{ $settings->cot_max_pre ?? 1000 }}">
                            </div>
                            <div class="form-group mb-2">
                                <label class="text-xs">Qtd mín eventos p/ aposta: ?</label>
                                <input type="number" name="qtd_min_pre" class="form-control form-control-sm" value="{{ $settings->qtd_min_pre ?? 1 }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-2">
                                <label class="text-xs">Qtd máx eventos p/ aposta: ?</label>
                                <input type="number" name="qtd_max_pre" class="form-control form-control-sm" value="{{ $settings->qtd_max_pre ?? 12 }}">
                            </div>
                            <div class="form-group mb-2">
                                <label class="text-xs">Odd máxima por opção: ?</label>
                                <input type="number" name="odd_max_pre" class="form-control form-control-sm" value="{{ $settings->odd_max_pre ?? 100 }}">
                            </div>
                            <div class="form-group mb-2">
                                <label class="text-xs">Bloquear odds abaixo de: ?</label>
                                <input type="number" name="block_odds_below" class="form-control form-control-sm" value="{{ $settings->block_odds_below ?? 1 }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-2">
                                <label class="text-xs">Minutos para validar PIN: ?</label>
                                <input type="number" name="min_valid_pin" class="form-control form-control-sm" value="{{ $settings->min_valid_pin ?? 500 }}">
                            </div>
                            <div class="form-group mb-2">
                                <label class="text-xs">Minutos antes do jogo: ?</label>
                                <input type="number" name="min_before_game" class="form-control form-control-sm" value="{{ $settings->min_before_game ?? 0 }}">
                            </div>
                            <div class="form-group mb-2">
                                <label class="text-xs">Travar odd acima de: ?</label>
                                <input type="number" name="travar_odd_acima" class="form-control form-control-sm" value="{{ $settings->travar_odd_acima ?? 500 }}">
                            </div>
                            <div class="form-group mb-2">
                                <label class="text-xs">Valor mín Loto (R$): ?</label>
                                <input type="number" name="menor_valor_loto" class="form-control form-control-sm" value="{{ $settings->menor_valor_loto ?? 1 }}">
                            </div>
                            <div class="form-group mb-2">
                                <label class="text-xs">Valor máx Loto (R$): ?</label>
                                <input type="number" name="max_valor_loto" class="form-control form-control-sm" value="{{ $settings->max_valor_loto ?? 1000 }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- EVENTOS AO VIVO -->
        <div class="card card-outline card-secondary mb-2 shadow-sm">
            <div class="card-header pb-2 pt-2" data-toggle="collapse" data-target="#collapseLive" style="cursor: pointer;">
                <h3 class="card-title text-sm"><i class="fas fa-broadcast-tower"></i> EVENTOS AO VIVO</h3>
            </div>
            <div id="collapseLive" class="collapse">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group mb-2">
                                <label class="text-xs">Qtd mín eventos p/ aposta: ?</label>
                                <input type="number" name="qtd_min_live" class="form-control form-control-sm" value="{{ $settings->qtd_min_live ?? 1 }}">
                            </div>
                            <div class="form-group mb-2">
                                <label class="text-xs">Valor mínimo por aposta: ?</label>
                                <input type="number" name="val_min_live" class="form-control form-control-sm" value="{{ $settings->val_min_live ?? 2 }}">
                            </div>
                            <div class="form-group mb-2">
                                <label class="text-xs">Valor máximo por aposta: ?</label>
                                <input type="number" name="val_max_live" class="form-control form-control-sm" value="{{ $settings->val_max_live ?? 500 }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-2">
                                <label class="text-xs">Cotação mínima por aposta: ?</label>
                                <input type="number" name="cot_min_live" class="form-control form-control-sm" value="{{ $settings->cot_min_live ?? 2 }}">
                            </div>
                            <div class="form-group mb-2">
                                <label class="text-xs">Cotação máxima por aposta: ?</label>
                                <input type="number" name="cot_max_live" class="form-control form-control-sm" value="{{ $settings->cot_max_live ?? 1000 }}">
                            </div>
                            <div class="form-group mb-2">
                                <label class="text-xs">Odd máxima por opção: ?</label>
                                <input type="number" name="odd_max_live" class="form-control form-control-sm" value="{{ $settings->odd_max_live ?? 100 }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-2">
                                <label class="text-xs">Cotação mín para comissão: ?</label>
                                <input type="number" name="cot_min_comm" class="form-control form-control-sm" value="{{ $settings->cot_min_comm ?? 2 }}">
                            </div>
                            <div class="form-group mb-2">
                                <label class="text-xs">Prêmio Máximo: ?</label>
                                <input type="number" name="prem_max_live" class="form-control form-control-sm" value="{{ $settings->prem_max_live ?? 10000 }}">
                            </div>
                            <div class="form-group mb-2">
                                <label class="text-xs">Aceita apostas até X min: ?</label>
                                <input type="number" name="accept_bet_until" class="form-control form-control-sm" value="{{ $settings->accept_bet_until ?? 90 }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-2">
                                <label class="text-xs">altera cotações (%) (+/-): ?</label>
                                <input type="number" name="alt_cot_live" class="form-control form-control-sm" value="{{ $settings->alt_cot_live ?? 0 }}">
                            </div>
                            <div class="form-group mb-2">
                                <label class="text-xs">Mesclar préjogo/ao-vivo bilhete: ?</label>
                                <select name="merge_pre_live" class="form-control form-control-sm">
                                    <option value="1" {{ ($settings->merge_pre_live ?? true) ? 'selected' : '' }}>Sim</option>
                                    <option value="0" {{ !($settings->merge_pre_live ?? true) ? 'selected' : '' }}>Não</option>
                                </select>
                            </div>
                            <div class="form-group mb-2">
                                <label class="text-xs">Futebol Ao Vivo: ?</label>
                                <select name="futebol_ao_vivo" class="form-control form-control-sm">
                                    <option value="Sim" {{ ($settings->futebol_ao_vivo ?? 'Sim') == 'Sim' ? 'selected' : '' }}>Sim</option>
                                    <option value="Não" {{ ($settings->futebol_ao_vivo ?? 'Sim') == 'Não' ? 'selected' : '' }}>Não</option>
                                </select>
                            </div>
                            <div class="form-group mb-2">
                                <label class="text-xs">Tempo Limite Ao Vivo (min): ?</label>
                                <input type="number" name="time_live" class="form-control form-control-sm" value="{{ $settings->time_live ?? 80 }}">
                            </div>
                            <div class="form-group mb-2">
                                <label class="text-xs">Cotação Ao Vivo: ?</label>
                                <input type="number" name="cotacao_live" class="form-control form-control-sm" step="0.01" value="{{ $settings->cotacao_live ?? 1.01 }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- EXIBIÇÃO DO SITE -->
        <div class="card card-outline card-secondary mb-2">
            <div class="card-header pb-2 pt-2" data-toggle="collapse" data-target="#collapseExibicao" style="cursor: pointer;">
                <h3 class="card-title text-sm"><i class="fas fa-desktop"></i> EXIBIÇÃO DO SITE</h3>
            </div>
            <div id="collapseExibicao" class="collapse">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group mb-2">
                                <label class="text-xs">Bloq. apostas na madrugada (01h-06h): ?</label>
                                <select name="bloq_aposta_madrugada" class="form-control form-control-sm">
                                    <option value="Não" {{ ($settings->bloq_aposta_madrugada ?? 'Não') == 'Não' ? 'selected' : '' }}>Não</option>
                                    <option value="Sim" {{ ($settings->bloq_aposta_madrugada ?? 'Não') == 'Sim' ? 'selected' : '' }}>Sim</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-2">
                                <label class="text-xs">Data limite de jogos: ?</label>
                                <input type="date" name="data_limite_jogos" class="form-control form-control-sm" value="{{ $settings->data_limite_jogos ?? '' }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- REDES SOCIAIS -->
        <div class="card card-outline card-secondary mb-2 shadow-sm">
            <div class="card-header pb-2 pt-2" data-toggle="collapse" data-target="#collapseRedesSociais" style="cursor: pointer;">
                <h3 class="card-title text-sm"><i class="fas fa-share-alt"></i> REDES SOCIAIS</h3>
            </div>
            <div id="collapseRedesSociais" class="collapse show">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group mb-2">
                                <label class="text-xs text-muted"><i class="fab fa-instagram" style="color:#E1306C"></i> Link do Instagram:</label>
                                <input type="url" name="social_instagram" class="form-control form-control-sm" placeholder="https://instagram.com/..." value="{{ $settings->social_instagram ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-2">
                                <label class="text-xs text-muted"><i class="fab fa-facebook" style="color:#4267B2"></i> Link do Facebook:</label>
                                <input type="url" name="social_facebook" class="form-control form-control-sm" placeholder="https://facebook.com/..." value="{{ $settings->social_facebook ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-2">
                                <label class="text-xs text-muted"><i class="fab fa-twitter" style="color:#1DA1F2"></i> Link do Twitter/X:</label>
                                <input type="url" name="social_twitter" class="form-control form-control-sm" placeholder="https://x.com/..." value="{{ $settings->social_twitter ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-2">
                                <label class="text-xs text-muted"><i class="fab fa-youtube" style="color:#FF0000"></i> Link do YouTube:</label>
                                <input type="url" name="social_youtube" class="form-control form-control-sm" placeholder="https://youtube.com/..." value="{{ $settings->social_youtube ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-2">
                                <label class="text-xs text-muted"><i class="fab fa-whatsapp" style="color:#25D366"></i> Número do WhatsApp:</label>
                                <input type="text" name="whatsapp_number" class="form-control form-control-sm" placeholder="Ex: 5511999999999" value="{{ $settings->whatsapp_number ?? '' }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- GERAL -->
        <div class="card card-outline card-secondary mb-2 shadow-sm">
            <div class="card-header pb-2 pt-2" data-toggle="collapse" data-target="#collapseGeral" style="cursor: pointer;">
                <h3 class="card-title text-sm"><i class="fas fa-cog"></i> GERAL</h3>
            </div>
            <div id="collapseGeral" class="collapse">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group mb-2">
                                <label class="text-xs">Prêmio máx apostas iguais: ?</label>
                                <input type="number" name="prem_max_equal" class="form-control form-control-sm" value="{{ $settings->prem_max_equal ?? 0 }}">
                            </div>
                            <div class="form-group mb-2">
                                <label class="text-xs">Comissão sobre prêmio (%): ?</label>
                                <input type="number" name="comissao_premio" class="form-control form-control-sm" step="0.01" value="{{ $settings->comissao_premio ?? 0 }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-2">
                                <label class="text-xs">Apostas Ativas: ?</label>
                                <select name="active_bets" class="form-control form-control-sm">
                                    <option value="1" {{ ($settings->active_bets ?? true) ? 'selected' : '' }}>Sim</option>
                                    <option value="0" {{ !($settings->active_bets ?? true) ? 'selected' : '' }}>Não</option>
                                </select>
                            </div>
                            <div class="form-group mb-2">
                                <label class="text-xs">Email (Alertas): ?</label>
                                <input type="email" name="email_alerta" class="form-control form-control-sm" value="{{ $settings->email_alerta ?? '' }}">
                            </div>
                            <div class="form-group mb-2">
                                <label class="text-xs">Alertar apostas acima de (R$): ?</label>
                                <input type="number" name="alerta_aposta_acima" class="form-control form-control-sm" value="{{ $settings->alerta_aposta_acima ?? 100 }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-2">
                                <label class="text-xs">Idioma do site: ?</label>
                                <select name="site_lang" class="form-control form-control-sm">
                                    <option value="pt_BR">Português (Brasil)</option>
                                </select>
                            </div>
                            <div class="form-group mb-2">
                                <label class="text-xs">Texto rodapé bilhete:</label>
                                <textarea name="texto_rodape_bilhete" class="form-control form-control-sm" rows="3">{{ $settings->texto_rodape_bilhete ?? '' }}</textarea>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-2">
                                <label class="text-xs">Seletor de idioma: ?</label>
                                <select name="lang_selector" class="form-control form-control-sm">
                                    <option value="1">Sim</option>
                                    <option value="0" selected>Não</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-4">
                            <label class="text-xs">Habilitar Módulos:</label>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" name="seniha_enabled" class="custom-control-input" id="checkSeniha" {{ ($settings->seniha_enabled ?? false) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="checkSeniha">Seniha</label>
                            </div>
                            <div class="custom-control custom-checkbox mt-1">
                                <input type="checkbox" name="queniha_enabled" class="custom-control-input" id="checkQueniha" {{ ($settings->queniha_enabled ?? false) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="checkQueniha">Queniha</label>
                            </div>
                            <div class="custom-control custom-checkbox mt-1">
                                <input type="checkbox" name="active_bonus" class="custom-control-input" id="checkBonus" {{ ($settings->active_bonus ?? false) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="checkBonus">Módulo de Bônus</label>
                            </div>
                            <div class="custom-control custom-checkbox mt-1">
                                <input type="checkbox" name="loto_enabled" class="custom-control-input" id="checkLoto" {{ ($settings->loto_enabled ?? false) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="checkLoto">Módulo Loto (Quininha/Seninha)</label>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <label class="text-xs">Esportes Ativos:</label>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-1">
                                        <label class="text-xs">Futebol:</label>
                                        <select name="op_futebol" class="form-control form-control-sm">
                                            <option value="Sim" {{ ($settings->op_futebol ?? 'Sim') == 'Sim' ? 'selected' : '' }}>Sim</option>
                                            <option value="Não" {{ ($settings->op_futebol ?? 'Sim') == 'Não' ? 'selected' : '' }}>Não</option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-1">
                                        <label class="text-xs">Basquete:</label>
                                        <select name="op_basquete" class="form-control form-control-sm">
                                            <option value="Sim" {{ ($settings->op_basquete ?? 'Sim') == 'Sim' ? 'selected' : '' }}>Sim</option>
                                            <option value="Não" {{ ($settings->op_basquete ?? 'Sim') == 'Não' ? 'selected' : '' }}>Não</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-1">
                                        <label class="text-xs">Tênis:</label>
                                        <select name="op_tenis" class="form-control form-control-sm">
                                            <option value="Sim" {{ ($settings->op_tenis ?? 'Sim') == 'Sim' ? 'selected' : '' }}>Sim</option>
                                            <option value="Não" {{ ($settings->op_tenis ?? 'Sim') == 'Não' ? 'selected' : '' }}>Não</option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-1">
                                        <label class="text-xs">UFC/Box:</label>
                                        <select name="op_ufcbox" class="form-control form-control-sm">
                                            <option value="Sim" {{ ($settings->op_ufcbox ?? 'Sim') == 'Sim' ? 'selected' : '' }}>Sim</option>
                                            <option value="Não" {{ ($settings->op_ufcbox ?? 'Sim') == 'Não' ? 'selected' : '' }}>Não</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-1">
                                        <label class="text-xs">Quininha:</label>
                                        <select name="op_quininha" class="form-control form-control-sm">
                                            <option value="Sim" {{ ($settings->op_quininha ?? 'Sim') == 'Sim' ? 'selected' : '' }}>Sim</option>
                                            <option value="Não" {{ ($settings->op_quininha ?? 'Sim') == 'Não' ? 'selected' : '' }}>Não</option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-1">
                                        <label class="text-xs">Seninha:</label>
                                        <select name="op_seninha" class="form-control form-control-sm">
                                            <option value="Sim" {{ ($settings->op_seninha ?? 'Sim') == 'Sim' ? 'selected' : '' }}>Sim</option>
                                            <option value="Não" {{ ($settings->op_seninha ?? 'Sim') == 'Não' ? 'selected' : '' }}>Não</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- PERMISSÕES -->
        <div class="card card-outline card-secondary mb-2 shadow-sm">
            <div class="card-header pb-2 pt-2" data-toggle="collapse" data-target="#collapsePerms" style="cursor: pointer;">
                <h3 class="card-title text-sm"><i class="fas fa-lock"></i> PERMISSÕES</h3>
            </div>
            <div id="collapsePerms" class="collapse">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-2">
                                <label class="text-xs">Cambistas podem cancelar bilhetes: ?</label>
                                <select name="cambista_pode_cancelar" class="form-control form-control-sm">
                                    <option value="Sim" {{ ($settings->cambista_pode_cancelar ?? 'Sim') == 'Sim' ? 'selected' : '' }}>Sim</option>
                                    <option value="Não" {{ ($settings->cambista_pode_cancelar ?? 'Sim') == 'Não' ? 'selected' : '' }}>Não</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-2">
                                <label class="text-xs">Tempo limite p/ cambista cancelar (min): ?</label>
                                <input type="number" name="tempo_limite_camb_cancela_aposta" class="form-control form-control-sm" value="{{ $settings->tempo_limite_camb_cancela_aposta ?? 30 }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-2">
                                <label class="text-xs">Tempo máximo p/ cancelar bilhete (min): ?</label>
                                <input type="number" name="cancel_time_minutes" class="form-control form-control-sm" value="{{ $settings->cancel_time_minutes ?? 10 }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- LAYOUT -->
        <div class="card card-outline card-primary mb-2 shadow-sm">
            <div class="card-header pb-2 pt-2" data-toggle="collapse" data-target="#collapseLayout" style="cursor: pointer;">
                <h3 class="card-title text-sm"><i class="fas fa-paint-brush"></i> LAYOUT</h3>
            </div>
            <div id="collapseLayout" class="collapse">
                <div class="card-body">
                    <!-- Seção de layout já detalhada antes -->
                </div>
            </div>
        </div>

        <!-- INTEGRAÇÕES -->
        <div class="card card-outline card-secondary mb-4 shadow-sm">
            <div class="card-header pb-2 pt-2" data-toggle="collapse" data-target="#collapseInt" style="cursor: pointer;">
                <h3 class="card-title text-sm"><i class="fas fa-plug"></i> INTEGRAÇÕES <span class="badge badge-danger text-xs">nova</span></h3>
            </div>
            <div id="collapseInt" class="collapse show">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="custom-control custom-switch mb-2">
                                <input type="checkbox" name="ga_enabled" class="custom-control-input" id="switchGA" {{ ($settings->ga_enabled ?? false) ? 'checked' : '' }}>
                                <label class="custom-control-label text-xs" for="switchGA">Ativar Google Analytics</label>
                            </div>
                            <div class="form-group">
                                <label class="text-xs">Código Script &nbsp;</label>
                                <textarea name="ga_code" class="form-control form-control-sm" rows="3" placeholder="script de integração do google analytics...">{{ $settings->ga_code }}</textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="custom-control custom-switch mb-2">
                                <input type="checkbox" name="pixel_enabled" class="custom-control-input" id="switchPixel" {{ ($settings->pixel_enabled ?? false) ? 'checked' : '' }}>
                                <label class="custom-control-label text-xs" for="switchPixel">Status Meta Pixel</label>
                            </div>
                            <div class="form-group">
                                <label class="text-xs">Meta Pixel ID</label>
                                <input type="text" name="pixel_id" class="form-control form-control-sm" placeholder="ID do Meta Pixel..." value="{{ $settings->pixel_id }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-right pb-4">
            <button type="submit" class="btn btn-success px-5 shadow-sm"><i class="fas fa-save"></i> Salvar Configurações</button>
        </div>
    </form>
@stop

@section('css')
    <style>
        .card-title { font-size: 0.85rem !important; font-weight: 600; text-transform: uppercase; }
        .card-header { border-bottom: none; }
        .collapse.show { border-top: 1px solid #f4f6f9; }
        .form-control-sm { border-radius: 4px; border: 1px solid #ced4da; font-size: 13px; }
        label { color: #555; }
        .custom-switch .custom-control-label::before { height: 1.5rem; width: 2.5rem; border-radius: 1rem; }
        .custom-switch .custom-control-label::after { width: calc(1.5rem - 4px); height: calc(1.5rem - 4px); border-radius: 1rem; }
    </style>
@stop
