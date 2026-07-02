@extends('adminlte::page')

@section('title', 'Loto - Quininha & Seninha')

@section('content_header')
    <h1><i class="fas fa lottery-ticket"></i> Loto - Quininha & Seninha</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-info"><i class="fas fa-ticket-alt"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Quininha</span>
                <span class="info-box-number">{{ $totalQuininha }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-success"><i class="fas fa-ticket-alt"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Seninha</span>
                <span class="info-box-number">{{ $totalSeninha }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Apostas Abertas</span>
                <span class="info-box-number">{{ $totalAbertos }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-danger"><i class="fas fa-trophy"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Ganhadores</span>
                <span class="info-box-number">{{ $totalGanhos }}</span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-star text-warning"></i> Quininha</h3>
            </div>
            <div class="card-body">
                <p>Numeros: <strong>01 a 80</strong> | Escolha: <strong>5 numeros</strong></p>
                <p>Dias: <strong>Seg-Sex</strong> (excl. Domingo) | Sorteio: <strong>19:00</strong></p>
                <div class="btn-group">
                    <a href="{{ route('admin.loto.taxas.quininha') }}" class="btn btn-warning">
                        <i class="fas fa-cog"></i> Taxas / Cotacoes
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card card-success card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-star text-success"></i> Seninha (Mega-Sena)</h3>
            </div>
            <div class="card-body">
                <p>Numeros: <strong>01 a 60</strong> | Escolha: <strong>6 numeros</strong></p>
                <p>Dias: <strong>Qua & Sab</strong> | Sorteio: <strong>19:00</strong></p>
                <div class="btn-group">
                    <a href="{{ route('admin.loto.taxas.seninha') }}" class="btn btn-success">
                        <i class="fas fa-cog"></i> Taxas / Cotacoes
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-calendar-alt"></i> Concursos em Aberto</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Quininha</h5>
                        @if($concursosQuina->isEmpty())
                            <p class="text-muted">Nenhum concurso aberto</p>
                        @else
                            @foreach($concursosQuina as $concurso)
                                <span class="badge badge-warning mr-1">{{ $concurso }}</span>
                            @endforeach
                        @endif
                    </div>
                    <div class="col-md-6">
                        <h5>Seninha</h5>
                        @if($concursosSena->isEmpty())
                            <p class="text-muted">Nenhum concurso aberto</p>
                        @else
                            @foreach($concursosSena as $concurso)
                                <span class="badge badge-success mr-1">{{ $concurso }}</span>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
