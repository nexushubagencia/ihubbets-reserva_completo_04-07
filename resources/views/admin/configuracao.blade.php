@extends('adminlte::page')

@section('title', 'Limites e Regras de Apostas | IHUB BETS')

@section('content_header')
    <h1><i class="fas fa-sliders-h" style="color: #10b981;"></i> Regras & Limites de Apostas <small class="text-muted">(Configuração Geral)</small></h1>
    
@stop

@section('content')
<div class="container-fluid">
    <form id="form-configuracao">
        @csrf
        <input type="hidden" id="conf-id" value="">

        @php
            $siteId = config('tenant.site_id');
            $tenantSite = $siteId ? \App\Models\Site::find($siteId) : null;
        @endphp

        <!-- CARD 1: EVENTOS PRÉ-JOGO -->
        <div class="card card-outline card-primary mb-3 shadow-sm">
            <div class="card-header pb-2 pt-2" data-toggle="collapse" data-target="#collapsePre" style="cursor: pointer;">
                <h3 class="card-title text-md font-weight-bold"><i class="fas fa-calendar-alt text-primary"></i> EVENTOS PRÉ-JOGO & GERAL</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool"><i class="fas fa-chevron-down"></i></button>
                </div>
            </div>
            <div id="collapsePre" class="collapse show">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group mb-2">
                                <label class="text-xs font-weight-bold">
                                    Valor mínimo por aposta (R$): 
                                    <i class="fas fa-info-circle text-primary" data-toggle="tooltip" title="Aposta mínima em futebol tradicional."></i>
                                </label>
                                <input type="number" id="conf-valor_mini_aposta" class="form-control form-control-sm" value="1">
                            </div>
                            <div class="form-group mb-2">
                                <label class="text-xs font-weight-bold">
                                    Valor máximo por aposta (R$): 
                                    <i class="fas fa-info-circle text-primary" data-toggle="tooltip" title="Limite máximo de risco por bilhete."></i>
                                </label>
                                <input type="number" id="conf-valor_max_aposta" class="form-control form-control-sm" value="1000">
                            </div>
                            @if(!$tenantSite || $tenantSite->active_loto)
                            <div class="form-group mb-2">
                                <label class="text-xs font-weight-bold">
                                    Valor mínimo (Loto) (R$): 
                                    <i class="fas fa-info-circle text-primary" data-toggle="tooltip" title="Aposta mínima para Quininha/Seninha."></i>
                                </label>
                                <input type="number" id="conf-menor_valor_loto" class="form-control form-control-sm" value="1">
                            </div>
                            @endif
                        </div>

                        <div class="col-md-3">
                            @if(!$tenantSite || $tenantSite->active_loto)
                            <div class="form-group mb-2">
                                <label class="text-xs font-weight-bold">
                                    Valor máximo (Loto) (R$): 
                                    <i class="fas fa-info-circle text-primary" data-toggle="tooltip" title="Limite de aposta máximo na loteria."></i>
                                </label>
                                <input type="number" id="conf-max_valor_loto" class="form-control form-control-sm" value="1000">
                            </div>
                            @endif
                            <div class="form-group mb-2">
                                <label class="text-xs font-weight-bold">
                                    Prêmio Máximo (R$): 
                                    <i class="fas fa-info-circle text-primary" data-toggle="tooltip" title="Teto de pagamento do prêmio final."></i>
                                </label>
                                <input type="number" id="conf-premio_max" class="form-control form-control-sm" value="20000">
                            </div>
                            @if(!$tenantSite || $tenantSite->active_bonus)
                            <div class="form-group mb-2">
                                <label class="text-xs font-weight-bold">
                                    Teto Conversão Bônus (R$): 
                                    <i class="fas fa-info-circle text-primary" data-toggle="tooltip" title="Valor máximo que o saldo bônus vira saldo real após o rollover."></i>
                                </label>
                                <input type="number" id="conf-max_bonus_conversion" class="form-control form-control-sm" value="500">
                            </div>
                            @endif
                        </div>

                        <div class="col-md-3">
                            <div class="form-group mb-2">
                                <label class="text-xs font-weight-bold">
                                    Cotação Mínima no Bilhete: 
                                    <i class="fas fa-info-circle text-primary" data-toggle="tooltip" title="Exemplo: O sistema bloqueia odds de 1.01 se configurado."></i>
                                </label>
                                <input type="text" id="conf-cotacao_mini_bilhete" class="form-control form-control-sm" value="1.01">
                            </div>
                            <div class="form-group mb-2">
                                <label class="text-xs font-weight-bold">
                                    Cotação Máxima no Bilhete: 
                                    <i class="fas fa-info-circle text-primary" data-toggle="tooltip" title="Exemplo: Máximo multiplicador aceito no bilhete (ex: 1000x)."></i>
                                </label>
                                <input type="text" id="conf-cotacao_max_bilhete" class="form-control form-control-sm" value="1000.00">
                            </div>
                            <div class="form-group mb-2">
                                <label class="text-xs font-weight-bold">
                                    Travar odd acima de: 
                                    <i class="fas fa-info-circle text-primary" data-toggle="tooltip" title="Limita a cotação individual máxima de cada partida isolada."></i>
                                </label>
                                <input type="number" id="conf-travar_odd_acima" class="form-control form-control-sm" value="500">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group mb-2">
                                <label class="text-xs font-weight-bold">
                                    Qtd mín jogos p/ bilhete: 
                                    <i class="fas fa-info-circle text-primary" data-toggle="tooltip" title="Número mínimo de palpites no carrinho para validar o bilhete."></i>
                                </label>
                                <input type="number" id="conf-quantidade_jogos_mini_bilhete" class="form-control form-control-sm" value="1">
                            </div>
                            <div class="form-group mb-2">
                                <label class="text-xs font-weight-bold">
                                    Qtd máx jogos p/ bilhete: 
                                    <i class="fas fa-info-circle text-primary" data-toggle="tooltip" title="Número máximo de palpites aceitos em um bilhete combinado."></i>
                                </label>
                                <input type="number" id="conf-quantidade_jogos_max_bilhete" class="form-control form-control-sm" value="30">
                            </div>
                            <div class="form-group mb-2">
                                <label class="text-xs font-weight-bold">
                                    Bloquear odd abaixo de: 
                                    <i class="fas fa-info-circle text-primary" data-toggle="tooltip" title="Desabilita jogos com cotações perigosamente baixas."></i>
                                </label>
                                <input type="number" id="conf-bloquear_odd_abaixo" class="form-control form-control-sm" step="0.01" value="1.01">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CARD 2: EVENTOS AO VIVO -->
        <div class="card card-outline card-success mb-3 shadow-sm">
            <div class="card-header pb-2 pt-2" data-toggle="collapse" data-target="#collapseLive" style="cursor: pointer;">
                <h3 class="card-title text-md font-weight-bold"><i class="fas fa-broadcast-tower text-success"></i> EVENTOS AO VIVO</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool"><i class="fas fa-chevron-down"></i></button>
                </div>
            </div>
            <div id="collapseLive" class="collapse show">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-2">
                                <label class="text-xs font-weight-bold">
                                    Permitir Futebol Ao Vivo? 
                                    <i class="fas fa-info-circle text-success" data-toggle="tooltip" title="Habilitar ou desabilitar apostas em partidas em andamento."></i>
                                </label>
                                <select class="form-control form-control-sm" id="conf-futebol_ao_vivo">
                                    <option value="Sim">Sim</option>
                                    <option value="Não">Não</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-2">
                                <label class="text-xs font-weight-bold">
                                    Tempo Limite Ao Vivo: 
                                    <i class="fas fa-info-circle text-success" data-toggle="tooltip" title="Exemplo: Bloquear apostas quando a partida passar dos 80 minutos."></i>
                                </label>
                                <input type="number" id="conf-time_live" class="form-control form-control-sm" value="80">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-2">
                                <label class="text-xs font-weight-bold">
                                    Cotação Ao Vivo Máxima: 
                                    <i class="fas fa-info-circle text-success" data-toggle="tooltip" title="Valor teto para cotações oscilantes do mercado em tempo real."></i>
                                </label>
                                <input type="number" id="conf-cotacao_live" class="form-control form-control-sm" step="0.01" value="1.01">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CARD 3: PERMISSÕES & OPERACIONAL -->
        <div class="card card-outline card-warning mb-3 shadow-sm">
            <div class="card-header pb-2 pt-2" data-toggle="collapse" data-target="#collapseOps" style="cursor: pointer;">
                <h3 class="card-title text-md font-weight-bold"><i class="fas fa-shield-alt text-warning"></i> REGRAS OPERACIONAIS</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool"><i class="fas fa-chevron-down"></i></button>
                </div>
            </div>
            <div id="collapseOps" class="collapse show">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group mb-2">
                                <label class="text-xs font-weight-bold">
                                    Cambistas Cancelam Bilhetes? 
                                    <i class="fas fa-info-circle text-warning" data-toggle="tooltip" title="Permite ou bloqueia cancelamento de apostas no perfil do cambista."></i>
                                </label>
                                <select class="form-control form-control-sm" id="conf-cambista_pode_cancelar">
                                    <option value="Sim">Sim</option>
                                    <option value="Não">Não</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-2">
                                <label class="text-xs font-weight-bold">
                                    Tempo limite p/ cancelamento: 
                                    <i class="fas fa-info-circle text-warning" data-toggle="tooltip" title="Tempo máximo para desfazer uma aposta em minutos."></i>
                                </label>
                                <input type="number" id="conf-tempo_limite_camb_cancela_aposta" class="form-control form-control-sm" value="30">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-2">
                                <label class="text-xs font-weight-bold">
                                    Bloquear apostas na madrugada? 
                                    <i class="fas fa-info-circle text-warning" data-toggle="tooltip" title="Garante maior controle do caixa pausando jogos no horário noturno."></i>
                                </label>
                                <select class="form-control form-control-sm" id="conf-bloq_aposta_madrugada">
                                    <option value="Não">Não</option>
                                    <option value="Sim">Sim (01:00 às 05:59)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-2">
                                <label class="text-xs font-weight-bold">
                                    Status Geral das Apostas: 
                                    <i class="fas fa-info-circle text-warning" data-toggle="tooltip" title="Permite fechar as vendas globais do site temporariamente."></i>
                                </label>
                                <select class="form-control form-control-sm" id="conf-aposta_ativa">
                                    <option value="Sim">Apostas Ativadas</option>
                                    <option value="Não">Apostas Pausadas</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col-md-4">
                            <div class="form-group mb-2">
                                <label class="text-xs font-weight-bold">Email p/ Alertas:</label>
                                <input type="text" id="conf-email_alerta" class="form-control form-control-sm">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-2">
                                <label class="text-xs font-weight-bold">Alertar apostas acima de (R$):</label>
                                <input type="number" id="conf-alerta_aposta_acima" class="form-control form-control-sm" value="100">
                            </div>
                        </div>
                        @if(!$tenantSite || $tenantSite->active_affiliates)
                        <div class="col-md-4">
                            <div class="form-group mb-2">
                                <label class="text-xs font-weight-bold">Comissão sobre prêmio (%):</label>
                                <input type="number" id="conf-comissao_premio" class="form-control form-control-sm" value="0">
                            </div>
                        </div>
                        @endif
                    </div>

                    <div class="row mt-2">
                        <div class="col-md-12">
                            <div class="form-group mb-2">
                                <label class="text-xs font-weight-bold">Texto Rodapé do Bilhete:</label>
                                <textarea class="form-control form-control-sm" id="conf-texto_rodape" rows="3" placeholder="Insira o texto que aparecerá na base do comprovante..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- REDES SOCIAIS -->
        <div class="card card-outline card-secondary mb-2 shadow-sm">
            <div class="card-header pb-2 pt-2 bg-light" data-toggle="collapse" data-target="#collapseRedesSociais" style="cursor: pointer;">
                <h3 class="card-title text-sm"><i class="fas fa-share-alt"></i> REDES SOCIAIS</h3>
            </div>
            <div id="collapseRedesSociais" class="collapse">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group mb-2">
                                <label class="text-xs text-muted"><i class="fab fa-instagram" style="color:#E1306C"></i> Link do Instagram:</label>
                                <input type="url" id="conf-social_instagram" class="form-control form-control-sm" placeholder="https://instagram.com/...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-2">
                                <label class="text-xs text-muted"><i class="fab fa-facebook" style="color:#4267B2"></i> Link do Facebook:</label>
                                <input type="url" id="conf-social_facebook" class="form-control form-control-sm" placeholder="https://facebook.com/...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-2">
                                <label class="text-xs text-muted"><i class="fab fa-twitter" style="color:#1DA1F2"></i> Link do Twitter/X:</label>
                                <input type="url" id="conf-social_twitter" class="form-control form-control-sm" placeholder="https://x.com/...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-2">
                                <label class="text-xs text-muted"><i class="fab fa-youtube" style="color:#FF0000"></i> Link do YouTube:</label>
                                <input type="url" id="conf-social_youtube" class="form-control form-control-sm" placeholder="https://youtube.com/...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-2">
                                <label class="text-xs text-muted"><i class="fab fa-whatsapp" style="color:#25D366"></i> Número do WhatsApp:</label>
                                <input type="text" id="conf-whatsapp_number" class="form-control form-control-sm" placeholder="Ex: 5511999999999">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SOBRE NÓS -->
        <div class="card card-outline card-secondary mb-2 shadow-sm">
            <div class="card-header pb-2 pt-2 bg-light" data-toggle="collapse" data-target="#collapseSobreNos" style="cursor: pointer;">
                <h3 class="card-title text-sm"><i class="fas fa-info-circle"></i> SOBRE NÓS <span class="badge badge-info text-xs">rodapé</span></h3>
            </div>
            <div id="collapseSobreNos" class="collapse">
                <div class="card-body">
                    <div class="form-group">
                        <label class="text-xs text-muted">Texto "Sobre Nós" exibido no rodapé do site:</label>
                        <textarea id="conf-about_us" class="form-control" rows="5" placeholder="Escreva sobre a sua plataforma..."></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- CARD 4: ESPORTES ATIVOS -->
        <div class="card card-outline card-info mb-3 shadow-sm">
            <div class="card-header pb-2 pt-2" data-toggle="collapse" data-target="#collapseEsportes" style="cursor: pointer;">
                <h3 class="card-title text-md font-weight-bold"><i class="fas fa-futbol text-info"></i> ESPORTES & MODALIDADES</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool"><i class="fas fa-chevron-down"></i></button>
                </div>
            </div>
            <div id="collapseEsportes" class="collapse">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group mb-2">
                                <label class="text-xs font-weight-bold">Futebol:</label>
                                <select class="form-control form-control-sm" id="conf-op_futebol">
                                    <option value="Sim">Ativado</option>
                                    <option value="Não">Desativado</option>
                                </select>
                            </div>
                        </div>
                        @if(!$tenantSite || $tenantSite->active_loto)
                        <div class="col-md-2">
                            <div class="form-group mb-2">
                                <label class="text-xs font-weight-bold">Quininha:</label>
                                <select class="form-control form-control-sm" id="conf-op_quininha">
                                    <option value="Sim">Ativado</option>
                                    <option value="Não">Desativado</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-2">
                                <label class="text-xs font-weight-bold">Seninha:</label>
                                <select class="form-control form-control-sm" id="conf-op_seninha">
                                    <option value="Sim">Ativado</option>
                                    <option value="Não">Desativado</option>
                                </select>
                            </div>
                        </div>
                        @endif
                        <div class="col-md-2">
                            <div class="form-group mb-2">
                                <label class="text-xs font-weight-bold">UFC / Boxe:</label>
                                <select class="form-control form-control-sm" id="conf-op_ufcbox">
                                    <option value="Sim">Ativado</option>
                                    <option value="Não">Desativado</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-2">
                                <label class="text-xs font-weight-bold">Basquete:</label>
                                <select class="form-control form-control-sm" id="conf-op_basquete">
                                    <option value="Sim">Ativado</option>
                                    <option value="Não">Desativado</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-2">
                                <label class="text-xs font-weight-bold">Tênis:</label>
                                <select class="form-control form-control-sm" id="conf-op_tenis">
                                    <option value="Sim">Ativado</option>
                                    <option value="Não">Desativado</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-2">
                                <label class="text-xs font-weight-bold">Vôlei:</label>
                                <select class="form-control form-control-sm" id="conf-op_volei">
                                    <option value="Sim">Ativado</option>
                                    <option value="Não">Desativado</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-right pb-4">
            <button type="button" class="btn btn-success px-5 shadow-sm" id="btn-salvar-config"><i class="fas fa-save"></i> Salvar Todas as Regras</button>
        </div>
    </form>
</div>
@stop

@section('css')
    <style>
        .card-title { font-size: 0.95rem !important; }
        .form-control-sm { border-radius: 4px; }
        label { font-weight: 500; color: #495057; }
        .card-header { border-bottom: none; }
        .collapse.show { border-top: 1px solid #f4f6f9; }
    </style>
@stop

@section('js')
<script>
var configFields = [
    'valor_mini_aposta','valor_max_aposta','menor_valor_loto','max_valor_loto',
    'premio_max','cotacao_mini_bilhete','cotacao_max_bilhete','time_live','cotacao_live',
    'quantidade_jogos_mini_bilhete','quantidade_jogos_max_bilhete','texto_rodape','comissao_premio',
    'email_alerta','alerta_aposta_acima','cambista_pode_cancelar','tempo_limite_camb_cancela_aposta',
    'travar_odd_acima','bloquear_odd_abaixo','aposta_ativa','bloq_aposta_madrugada',
    'data_limite_jogos','futebol_ao_vivo','op_futebol','op_quininha','op_seninha','op_ufcbox','op_basquete','op_tenis','op_volei',
    'max_bonus_conversion','social_instagram','social_facebook','social_twitter','social_youtube','whatsapp_number','about_us'
];

$(document).ready(function() {
    if (typeof $.fn.tooltip === 'function') {
        $('[data-toggle="tooltip"]').tooltip();
    }

    // Carregar configurações
    $.get('/admin/list-configuracoes', function(data) {
        if (data && data.length > 0) {
            var conf = data[0];
            $('#conf-id').val(conf.id);
            configFields.forEach(function(field) {
                var el = $('#conf-'+field);
                if (el.length && conf[field] !== undefined && conf[field] !== null) {
                    el.val(conf[field]);
                }
            });
        }
    });

    // Salvar
    $('#btn-salvar-config').click(function() {
        var id = $('#conf-id').val();
        if (!id) { toastr.info('Configuração não encontrada!'); return; }
        
        var payload = { _token: $('meta[name="csrf-token"]').attr('content'), data: [{}] };
        configFields.forEach(function(field) {
            payload.data[0][field] = $('#conf-'+field).val();
        });

        $.ajax({
            url: '/admin/edit-configuracao/'+id,
            type: 'PUT',
            data: payload,
            beforeSend: function() {
                toastr.info('Salvando alterações...');
            },
            success: function(response) { 
                toastr.success(response.message || 'Regras salvas com sucesso!'); 
            },
            error: function() { 
                toastr.error('Erro ao alterar os dados!'); 
            }
        });
    });
});
</script>
@stop
