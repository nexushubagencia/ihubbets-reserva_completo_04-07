@extends('adminlte::page')

@section('title', 'Configurações de Saque')

@section('content_header')
    <h1><i class="fas fa-university mr-2"></i> Limites de Saque</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Definições Financeiras</h3>
                </div>
                <form action="{{ route('admin.settings.withdrawal.update') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <h5><i class="icon fas fa-check"></i> Sucesso!</h5>
                                {{ session('success') }}
                            </div>
                        @endif

                        <div class="form-group">
                            <label for="min_withdrawal">Valor Mínimo por Saque (R$)</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">R$</span>
                                </div>
                                <input type="number" step="0.01" name="min_withdrawal" id="min_withdrawal" class="form-control" value="{{ $settings->min_withdrawal }}" required>
                            </div>
                            <small class="text-muted">Valor mínimo que um usuário pode solicitar por vez.</small>
                        </div>

                        <div class="form-group">
                            <label for="max_withdrawal">Valor Máximo por Saque (R$)</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">R$</span>
                                </div>
                                <input type="number" step="0.01" name="max_withdrawal" id="max_withdrawal" class="form-control" value="{{ $settings->max_withdrawal }}" required>
                            </div>
                            <small class="text-muted">Valor máximo permitido em uma única transação de saque.</small>
                        </div>

                        <div class="form-group">
                            <label for="daily_withdrawal_limit">Limite Diário Total (R$)</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">R$</span>
                                </div>
                                <input type="number" step="0.01" name="daily_withdrawal_limit" id="daily_withdrawal_limit" class="form-control" value="{{ $settings->daily_withdrawal_limit }}" required>
                            </div>
                            <small class="text-muted">Soma total máxima de saques que um usuário pode fazer em 24 horas.</small>
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save mr-1"></i> Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-info card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle mr-1"></i> Informações</h3>
                </div>
                <div class="card-body">
                    <p>Estes limites são aplicados globalmente para todos os usuários deste site (White Label).</p>
                    <ul>
                        <li><strong>Mínimo:</strong> Evita processamento de saques de valores muito baixos que podem gerar taxas de operação.</li>
                        <li><strong>Máximo:</strong> Controle de segurança para grandes retiradas.</li>
                        <li><strong>Limite Diário:</strong> Proteção contra fraudes ou esvaziamento rápido de caixa.</li>
                    </ul>
                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        Certifique-se de que o saldo do seu banco (Mercado Pago ou outro) é suficiente para cobrir os saques dentro destes limites.
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .card-outline.card-primary { border-top: 3px solid #007bff; }
        .card-outline.card-info { border-top: 3px solid #17a2b8; }
    </style>
@stop
