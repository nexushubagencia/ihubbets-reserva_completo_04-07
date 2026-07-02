@extends('adminlte::page')

@section('title', 'Financeiro & Gateways | IHUB BETS')

@section('content_header')
    <h1><i class="fas fa-wallet" style="color: #3b82f6;"></i> Financeiro & Gateways <small class="text-muted">(Regras de Pagamento)</small></h1>
    
@stop

@section('content')
<div class="container-fluid">
    <form id="form-financeiro">
        @csrf
        <input type="hidden" id="conf-id" value="">

        @php
            $siteId = config('tenant.site_id');
            $tenantSite = $siteId ? \App\Models\Site::find($siteId) : null;
        @endphp

        <!-- CARD 1: LIMITES DE DEPÓSITO E SAQUE -->
        <div class="card card-outline card-primary mb-3 shadow-sm">
            <div class="card-header pb-2 pt-2" data-toggle="collapse" data-target="#collapseFinanceiro" style="cursor: pointer;">
                <h3 class="card-title text-md font-weight-bold"><i class="fas fa-money-bill-wave text-primary"></i> LIMITES DE TRANSAÇÃO (POR OPERAÇÃO)</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool"><i class="fas fa-chevron-down"></i></button>
                </div>
            </div>
            <div id="collapseFinanceiro" class="collapse show">
                <div class="card-body">
                    <p class="text-xs text-muted mb-3"><i class="fas fa-info-circle"></i> Estes valores são aplicados **por operação/transação única** de cada cliente, e não como limites acumulados diários.</p>
                    <div class="row">
                        @if(!$tenantSite || $tenantSite->active_gateway_deposito)
                        <div class="col-md-3">
                            <div class="form-group mb-2">
                                <label class="text-xs font-weight-bold">Depósito Mínimo (R$):</label>
                                <input type="number" id="conf-min_deposit" class="form-control form-control-sm" step="0.01">
                                <small class="text-muted text-xs d-block mt-1">Valor mínimo permitido para cada recarga via PIX.</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-2">
                                <label class="text-xs font-weight-bold">Depósito Máximo (R$):</label>
                                <input type="number" id="conf-max_deposit" class="form-control form-control-sm" step="0.01">
                                <small class="text-muted text-xs d-block mt-1">Limite máximo por recarga via PIX.</small>
                            </div>
                        </div>
                        @endif
                        @if(!$tenantSite || $tenantSite->active_payments)
                        <div class="col-md-3">
                            <div class="form-group mb-2">
                                <label class="text-xs font-weight-bold">Saque Mínimo (R$):</label>
                                <input type="number" id="conf-min_withdrawal" class="form-control form-control-sm" step="0.01">
                                <small class="text-muted text-xs d-block mt-1">Valor mínimo exigido p/ solicitar saque.</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-2">
                                <label class="text-xs font-weight-bold">Saque Máximo (R$):</label>
                                <input type="number" id="conf-max_withdrawal" class="form-control form-control-sm" step="0.01">
                                <small class="text-muted text-xs d-block mt-1">Teto máximo p/ cada pedido de saque individual.</small>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- CARD 2: CONFIGURAÇÃO DE INTEGRAÇÃO (MERCADO PAGO) -->
        <div class="card card-outline card-success mb-3 shadow-sm">
            <div class="card-header pb-2 pt-2" data-toggle="collapse" data-target="#collapseGateways" style="cursor: pointer;">
                <h3 class="card-title text-md font-weight-bold"><i class="fas fa-network-wired text-success"></i> INTEGRAÇÃO DE DEPÓSITO (MERCADO PAGO PIX)</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool"><i class="fas fa-chevron-down"></i></button>
                </div>
            </div>
            <div id="collapseGateways" class="collapse show">
                <div class="card-body">
                    <p class="text-xs text-muted mb-3"><i class="fas fa-info-circle text-info"></i> O Mercado Pago será utilizado **exclusivamente** para recebimento de depósitos via PIX (Geração de QR Code e Copia/Cola). O pagamento de prêmios (Saques) continuará sendo avaliado e pago de forma manual.</p>

                    @if(!$tenantSite || $tenantSite->active_gateway_deposito)
                    <div class="row mt-3 p-3 bg-light rounded shadow-xs" id="mercadopago-keys" style="border: 1px solid #e3e6f0;">
                        <div class="col-md-12 mb-2">
                            <h5 class="text-sm font-weight-bold">
                                <i class="fas fa-wallet text-info"></i> Chaves Mercado Pago (Access Token)
                                <i class="fas fa-info-circle text-muted" data-toggle="tooltip" title="Insira o Token de Integração do Mercado Pago."></i>
                            </h5>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group mb-2">
                                <label class="text-xs font-weight-bold">Mercado Pago Access Token:</label>
                                <input type="password" id="conf-mercadopago_access_token" class="form-control form-control-sm" placeholder="Ex: APP_USR-...">
                                <small class="text-muted text-xs d-block mt-1">
                                    <i class="fas fa-lightbulb text-warning"></i> O Access Token garante que o sistema identifique pagamentos instantâneos realizados pelos clientes.
                                </small>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>




        <div class="text-right pb-4">
            <button type="button" class="btn btn-primary px-5 shadow-sm" id="btn-salvar-financeiro"><i class="fas fa-save"></i> Atualizar Parâmetros Financeiros</button>
        </div>
    </form>
</div>
@stop

@section('css')
    <style>
        .card-title { font-size: 0.95rem !important; }
        .form-control-sm { border-radius: 4px; }
        label { color: #495057; }
        .card-header { border-bottom: none; }
        .collapse.show { border-top: 1px solid #f4f6f9; }
    </style>
@stop

@section('js')
<script>
var configFields = [
    'min_deposit', 'max_deposit', 'min_withdrawal', 'max_withdrawal',
    'mercadopago_access_token'
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
    $('#btn-salvar-financeiro').click(function() {
        var id = $('#conf-id').val();
        if (!id) { toastr.info('Configuração financeira não encontrada!'); return; }
        
        var payload = { 
            _token: $('meta[name="csrf-token"]').attr('content'), 
            data: [{
                // Forçar Mercado Pago como gateway e Saque como Manual, pois removemos os selects
                active_deposit_gateway: 'mercadopago',
                active_withdrawal_gateway: 'manual'
            }] 
        };
        configFields.forEach(function(field) {
            payload.data[0][field] = $('#conf-'+field).val();
        });

        $.ajax({
            url: '/admin/edit-configuracao/'+id,
            type: 'PUT',
            data: payload,
            beforeSend: function() {
                toastr.info('Salvando dados financeiros...');
            },
            success: function(response) { 
                toastr.success(response.message || 'Configurações atualizadas com sucesso!'); 
            },
            error: function() { 
                toastr.error('Erro ao alterar os dados financeiros!'); 
            }
        });
    });
});
</script>
@stop
