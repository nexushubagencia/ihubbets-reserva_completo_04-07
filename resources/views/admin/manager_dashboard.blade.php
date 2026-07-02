@extends('adminlte::page')

@title('Painel do Gerente - IHUB BETS')

@section('content_header')
    <h1>Dashboard do Gerente</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-4 col-12">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['total_bets'] }}</h3>
                    <p>Apostas da Equipe (Período)</p>
                </div>
                <div class="icon"><i class="fas fa-ticket-alt"></i></div>
            </div>
        </div>
        <div class="col-lg-4 col-12">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>R$ {{ number_format($stats['total_amount'], 2, ',', '.') }}</h3>
                    <p>Volume Arrecadado</p>
                </div>
                <div class="icon"><i class="fas fa-hand-holding-usd"></i></div>
            </div>
        </div>
        <div class="col-lg-4 col-12">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['team_size'] }}</h3>
                    <p>Cambistas sob sua Gestão</p>
                </div>
                <div class="icon"><i class="fas fa-users"></i></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card card-dark">
                <div class="card-header">
                    <h3 class="card-title">Ações Rápidas</h3>
                </div>
                <div class="card-body">
                    <a href="{{ route('admin.stats.seller') }}" class="btn btn-app bg-primary">
                        <i class="fas fa-chart-bar"></i> Relatório Cambistas
                    </a>
                    <a href="{{ route('admin.report.print', ['type' => 'manager', 'id' => auth()->id()]) }}?date1={{ date('Y-m-d') }}&date2={{ date('Y-m-d') }}" class="btn btn-app bg-success">
                        <i class="fas fa-print"></i> Meu Fechamento Hoje
                    </a>
                </div>
            </div>
        </div>
    </div>
@stop
