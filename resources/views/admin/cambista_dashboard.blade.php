@extends('adminlte::page')

@title('Painel do Cambista - IHUB BETS')

@section('content_header')
    <h1>Minha banca</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-6 col-12">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>R$ {{ number_format($stats['my_balance'], 2, ',', '.') }}</h3>
                    <p>Meu Saldo Disponível</p>
                </div>
                <div class="icon"><i class="fas fa-wallet"></i></div>
            </div>
        </div>
        <div class="col-lg-6 col-12">
            <div class="small-box bg-dark">
                <div class="inner">
                    <h3>R$ {{ number_format($stats['my_commission'], 2, ',', '.') }}</h3>
                    <p>Comissões Acumuladas</p>
                </div>
                <div class="icon"><i class="fas fa-coins"></i></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-success">
                    <h3 class="card-title">Ações do Cambista</h3>
                </div>
                <div class="card-body">
                    <a href="/" target="_blank" class="btn btn-app bg-primary">
                        <i class="fas fa-plus"></i> Nova Aposta (Abrir Site)
                    </a>
                    <a href="{{ route('admin.report.print', ['type' => 'cambista', 'id' => auth()->id()]) }}?date1={{ date('Y-m-d') }}&date2={{ date('Y-m-d') }}" class="btn btn-app bg-info">
                        <i class="fas fa-file-invoice-dollar"></i> Ver Meu Extrato Hoje
                    </a>
                </div>
            </div>
        </div>
    </div>
@stop
