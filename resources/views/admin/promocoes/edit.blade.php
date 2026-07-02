@extends('adminlte::page')

@section('title', 'Editar Promoção')

@section('content_header')
    <h1><i class="fa fa-edit text-warning"></i> Editar Promoção <small>{{ $promocao->nome ?? '' }}</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('home') }}"><i class="fa fa-dashboard"></i> Página Inicial</a></li>
        <li><a href="{{ route('admin.promocoes') }}">Promoções</a></li>
        <li class="active">Editar</li>
    </ol>
@stop

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Dados da Promoção</h3>
            </div>
            <form action="{{ route('admin.promocoes.update', $promocao->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="box-body">
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
                        <label>Nome da Promoção *</label>
                        <input type="text" name="nome" class="form-control" value="{{ old('nome', $promocao->nome) }}" required>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Tipo *</label>
                                <select name="tipo" class="form-control" required>
                                    <option value="porcentagem" {{ old('tipo', $promocao->tipo) == 'porcentagem' ? 'selected' : '' }}>Porcentagem (%)</option>
                                    <option value="valor_fixo" {{ old('tipo', $promocao->tipo) == 'valor_fixo' ? 'selected' : '' }}>Valor Fixo (R$)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Porcentagem</label>
                                <input type="number" step="0.01" name="porcentagem" class="form-control" value="{{ old('porcentagem', $promocao->porcentagem) }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Valor Máximo (R$) *</label>
                                <input type="number" step="0.01" name="valor_maximo" class="form-control" value="{{ old('valor_maximo', $promocao->valor_maximo) }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Rollover (Vezes) *</label>
                                <input type="number" name="rollover_multiplicador" class="form-control" value="{{ old('rollover_multiplicador', $promocao->rollover_multiplicador) }}" min="1" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Status *</label>
                                <select name="status" class="form-control" required>
                                    <option value="1" {{ old('status', $promocao->status) ? 'selected' : '' }}>Ativo</option>
                                    <option value="0" {{ old('status', $promocao->status) ? '' : 'selected' }}>Inativo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <a href="{{ route('admin.promocoes') }}" class="btn btn-default">
                        <i class="fa fa-arrow-left"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary pull-right">
                        <i class="fa fa-save"></i> Atualizar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop
