@extends('adminlte::page')

@section('title', 'Gestão de Bônus')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-gift text-danger mr-2"></i> Gestão de Bônus</h1>
        <button class="btn btn-danger font-weight-bold shadow-sm" data-toggle="modal" data-target="#modalCreateBonus">
            <i class="fas fa-plus mr-1"></i> Criar Novo Bônus
        </button>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h5><i class="icon fas fa-check"></i> Sucesso!</h5>
                {{ session('success') }}
            </div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-header bg-dark text-white">
                <h3 class="card-title mt-1"><i class="fas fa-ticket-alt mr-2"></i> Cupons de Bônus Ativos</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover table-striped mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Código</th>
                            <th>Tipo</th>
                            <th>Valor</th>
                            <th>Depósito Mín.</th>
                            <th class="text-center">Rollover</th>
                            <th class="text-center">Expiração</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bonuses as $bonus)
                            <tr>
                                <td class="font-weight-bold">
                                    <span class="badge badge-warning p-2 px-3 border shadow-sm">{{ $bonus->code }}</span>
                                </td>
                                <td>{{ $bonus->type == 'percentage' ? 'Percentual' : 'Fixo' }}</td>
                                <td class="font-weight-bold text-success">
                                    {{ $bonus->type == 'percentage' ? $bonus->value.'%' : 'R$ '.number_format($bonus->value, 2, ',', '.') }}
                                </td>
                                <td>R$ {{ number_format($bonus->min_deposit, 2, ',', '.') }}</td>
                                <td class="text-center"><span class="badge badge-info">{{ $bonus->rollover_multiplier }}x</span></td>
                                <td class="text-center">{{ $bonus->expires_at ? date('d/m/Y', strtotime($bonus->expires_at)) : 'Ilimitado' }}</td>
                                <td class="text-center">
                                    <form action="{{ route('admin.bonus.toggle', $bonus->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-xs btn-{{ $bonus->is_active ? 'success' : 'secondary' }} rounded-pill px-3">
                                            {{ $bonus->is_active ? 'Ativo' : 'Inativo' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="text-center">
                                    <form action="{{ route('admin.bonus.destroy', $bonus->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger shadow-sm" onclick="return confirm('Excluir este bônus permanentemente?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fas fa-info-circle fa-2x mb-3 d-block"></i>
                                    Ainda não há campanhas de bônus configuradas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Create Bonus -->
<div class="modal fade" id="modalCreateBonus" tabindex="-1" role="dialog" aria-labelledby="modalCreateBonusLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalCreateBonusLabel">Nova Campanha de Bônus</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.bonus.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="code">Código do Cupom</label>
                        <input type="text" name="code" id="code" class="form-control text-uppercase font-weight-bold" placeholder="EX: BEMVINDO100" required>
                        <small class="text-muted">Este é o código que o usuário digitará no painel.</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="type">Tipo de Bônus</label>
                                <select name="type" id="type" class="form-control" required>
                                    <option value="percentage">Percentual (%)</option>
                                    <option value="fixed">Valor Fixo (R$)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="value">Valor/Porcentagem</label>
                                <input type="number" step="0.01" name="value" id="value" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="min_deposit">Depósito Mínimo (R$)</label>
                                <input type="number" step="0.01" name="min_deposit" id="min_deposit" class="form-control" value="0.00" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="rollover_multiplier">Rollover (Vezes)</label>
                                <input type="number" name="rollover_multiplier" id="rollover_multiplier" class="form-control" value="10" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="expires_at">Data de Expiração (Opcional)</label>
                        <input type="date" name="expires_at" id="expires_at" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger px-4">Criar Bônus</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .table td, .table th { vertical-align: middle; }
    .badge { font-weight: 500; }
</style>
@stop
