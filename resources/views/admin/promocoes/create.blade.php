@extends('admin.layouts.app')

@section('title', ($promo->id ?? null) ? 'Editar Promoção' : 'Criar Promoção' . ' | IHUB BETS')

@section('content_header')
    <h1>
        <i class="fas fa-{{ ($promo->id ?? null) ? 'edit text-warning' : 'plus-circle text-success' }}"></i>
        {{ ($promo->id ?? null) ? 'Editar Promoção' : 'Criar Nova Promoção' }}
    </h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title">
                        <i class="fas fa-tag mr-2"></i>
                        {{ ($promo->id ?? null) ? 'Editar Dados da Promoção' : 'Dados da Nova Promoção' }}
                    </h3>
                </div>
                <form action="{{ ($promo->id ?? null) ? route('admin.promocoes.update', $promo->id) : route('admin.promocoes.store') }}" method="POST">
                    @csrf
                    @if($promo->id ?? null)
                        @method('PUT')
                    @endif
                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="form-group">
                            <label class="font-weight-bold">Nome da Promoção *</label>
                            <input type="text" name="name" class="form-control form-control-sm"
                                   value="{{ old('name', $promo->name ?? '') }}"
                                   placeholder="Ex: Bônus de Boas-Vindas 100%" required>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold">Tipo *</label>
                                    <select name="type" class="form-control form-control-sm" id="promo-type" required>
                                        <option value="bonus" {{ old('type', $promo->type ?? '') == 'bonus' ? 'selected' : '' }}>Bônus (%)</option>
                                        <option value="freebet" {{ old('type', $promo->type ?? '') == 'freebet' ? 'selected' : '' }}>Freebet (R$)</option>
                                        <option value="cashback" {{ old('type', $promo->type ?? '') == 'cashback' ? 'selected' : '' }}>Cashback</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold">Valor *</label>
                                    <input type="number" step="0.01" name="value" class="form-control form-control-sm"
                                           value="{{ old('value', $promo->value ?? '') }}"
                                           placeholder="Ex: 100" required>
                                    <small class="text-muted" id="value-hint">Porcentagem do bônus</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold">Rollover (Vezes)</label>
                                    <input type="number" name="rollover" class="form-control form-control-sm"
                                           value="{{ old('rollover', $promo->rollover ?? 10) }}" min="1">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold">Depósito Mínimo (R$)</label>
                                    <input type="number" step="0.01" name="min_deposit" class="form-control form-control-sm"
                                           value="{{ old('min_deposit', $promo->min_deposit ?? '0.00') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold">Depósito Máximo (R$)</label>
                                    <input type="number" step="0.01" name="max_deposit" class="form-control form-control-sm"
                                           value="{{ old('max_deposit', $promo->max_deposit ?? '') }}"
                                           placeholder="0 = Sem limite">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold">Status *</label>
                                    <select name="is_active" class="form-control form-control-sm" required>
                                        <option value="1" {{ old('is_active', $promo->is_active ?? 1) ? 'selected' : '' }}>Ativo</option>
                                        <option value="0" {{ old('is_active', $promo->is_active ?? 1) ? '' : 'selected' }}>Inativo</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Descrição (Opcional)</label>
                            <textarea name="description" class="form-control form-control-sm" rows="3"
                                      placeholder="Descrição da promoção para exibição ao usuário...">{{ old('description', $promo->description ?? '') }}</textarea>
                        </div>
                    </div>
                    <div class="card-footer bg-light d-flex justify-content-between">
                        <a href="{{ route('admin.promocoes.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-success px-5 font-weight-bold">
                            <i class="fas fa-save mr-1"></i> {{ ($promo->id ?? null) ? 'Atualizar' : 'Criar Promoção' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    $('#promo-type').change(function() {
        var type = $(this).val();
        var hint = $('#value-hint');
        if (type === 'bonus') {
            hint.text('Porcentagem do bônus');
        } else if (type === 'freebet') {
            hint.text('Valor em R$ do freebet');
        } else {
            hint.text('Porcentagem de cashback');
        }
    });
    $('#promo-type').trigger('change');
});
</script>
@stop
