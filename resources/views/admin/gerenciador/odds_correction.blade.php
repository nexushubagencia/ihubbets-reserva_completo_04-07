@extends('adminlte::page')

@section('title', 'Correção Global de Odds')

@section('content_header')
    <h1><i class="fas fa-tools"></i> Correção Global de Odds</h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('home') }}"><i class="fa fa-dashboard"></i> Início</a></li>
        <li><a href="{{ route('admin.gerenciador') }}">Gerenciador</a></li>
        <li class="active">Correção Odds</li>
    </ol>
@stop

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Ajuste Global por Liga/Esporte</h3>
            </div>
            <form action="{{ route('admin.gerenciador.odds.apply') }}" method="POST">
                @csrf
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Esporte:</label>
                                <select name="sport" class="form-control">
                                    <option value="football">Futebol</option>
                                    <option value="basketball">Basquete</option>
                                    <option value="volleyball">Vôlei</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Nome da Liga:</label>
                                <input type="text" name="league_name" class="form-control" placeholder="Ex: Premier League" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Ajuste (%):</label>
                                <input type="number" step="0.01" name="adjustment_percent" class="form-control" placeholder="Ex: -5" required>
                                <small class="text-muted">Negativo diminui odds, positivo aumenta.</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-primary" onclick="return confirm('Aplicar ajuste global a todas as odds desta liga?')">
                        <i class="fas fa-save"></i> Aplicar Correção
                    </button>
                </div>
            </form>
        </div>
    </div>
    <div class="col-md-4">
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fas fa-exclamation-triangle"></i> Atenção</h3>
            </div>
            <div class="box-body">
                <p>Esta ferramenta aplica um ajuste percentual a <strong>TODAS</strong> as odds de uma liga específica.</p>
                <ul>
                    <li><strong>Exemplo -5:</strong> Odds de 2.00 viram 1.90</li>
                    <li><strong>Exemplo +10:</strong> Odds de 2.00 viram 2.20</li>
                </ul>
                <p class="text-danger"><strong>Use com cuidado!</strong> O ajuste é irreversível.</p>
            </div>
        </div>
    </div>
</div>
@stop
