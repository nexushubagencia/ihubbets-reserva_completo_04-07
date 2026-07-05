@extends('adminlte::page')

@section('title', 'BetsAPI | IHUB BETS')

@section('content_header')
    <h1><i class="fas fa-crown text-warning"></i> BetsAPI <small class="text-muted">Provedor Principal de Dados</small></h1>
@stop

@section('content')
<div class="row">
    @if(session('success'))
        <div class="col-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('success') }}</div></div>
    @endif

    <!-- COLUNA ESQUERDA -->
    <div class="col-md-4">
        <!-- STATUS -->
        <div class="card card-outline card-{{ $status['success'] ? 'success' : 'danger' }} shadow-sm">
            <div class="card-header bg-{{ $status['success'] ? 'success' : 'danger' }} text-white">
                <h3 class="card-title"><i class="fas fa-signal mr-2"></i> Status da Conexão</h3>
            </div>
            <div class="card-body text-center">
                <h2 class="{{ $status['success'] ? 'text-success' : 'text-danger' }}">
                    <i class="fas fa-{{ $status['success'] ? 'check-circle' : 'times-circle' }}"></i>
                </h2>
                <p class="font-weight-bold">{{ $status['message'] }}</p>
                @if($status['success'] && isset($status['live_count']))
                    <p class="text-muted">Jogos ao vivo: <span class="badge badge-success">{{ $status['live_count'] }}</span></p>
                @endif
            </div>
        </div>

        <!-- PROVIDER -->
        <div class="card card-outline card-warning shadow-sm">
            <div class="card-header bg-warning text-dark">
                <h3 class="card-title"><i class="fas fa-exchange-alt mr-2"></i> Provedor Ativo</h3>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <span class="badge badge-{{ $activeProvider === 'bets-api' ? 'success' : 'secondary' }} p-2" style="font-size: 1rem;">
                        {{ $activeProvider === 'bets-api' ? 'BetsAPI (ATIVO)' : 'API-Football (ATIVO)' }}
                    </span>
                </div>
                @if($activeProvider !== 'bets-api')
                    <form method="POST" action="{{ route('admin.betsapi.switch') }}">
                        @csrf
                        <button type="submit" class="btn btn-warning btn-block btn-sm">
                            <i class="fas fa-crown mr-1"></i> Ativar BetsAPI como Principal
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- SYNC -->
        <div class="card card-outline card-info shadow-sm">
            <div class="card-header bg-info text-white">
                <h3 class="card-title"><i class="fas fa-sync-alt mr-2"></i> Sincronização</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.betsapi.sync') }}">
                    @csrf
                    <button type="submit" class="btn btn-info btn-block btn-sm">
                        <i class="fas fa-download mr-1"></i> Sincronizar Jogos (Football)
                    </button>
                </form>
                <hr>
                <small class="text-muted">
                    <strong>Comandos disponíveis:</strong><br>
                    <code>php artisan betsapi:insert_matches --sport=football</code><br>
                    <code>php artisan betsapi:insert_matches --sport=basketball</code><br>
                    <code>php artisan betsapi:insert_matches --sport=tennis</code><br>
                    <code>php artisan betsapi:insert_matches --sport=volleyball</code><br>
                    <code>php artisan betsapi:insert_matches --sport=mma</code><br>
                    <code>php artisan betsapi:insert_matches --sport=all</code><br>
                    <code>php artisan betsapi:update_odds --sport=football</code><br>
                    <code>php artisan betsapi:update_odds --sport=football --live=1</code>
                </small>
            </div>
        </div>
    </div>

    <!-- COLUNA DIREITA -->
    <div class="col-md-8">
        <!-- ESPORTES -->
        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title"><i class="fas fa-globe mr-2"></i> Esportes Suportados (BetsAPI)</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="info-box bg-info"><span class="info-box-icon"><i class="fas fa-futbol"></i></span><div class="info-box-content"><span class="info-box-text">Futebol</span><span class="info-box-number">ID: 1</span></div></div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box bg-success"><span class="info-box-icon"><i class="fas fa-basketball-ball"></i></span><div class="info-box-content"><span class="info-box-text">Basquete</span><span class="info-box-number">ID: 2</span></div></div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box bg-warning"><span class="info-box-icon"><i class="fas fa-table-tennis"></i></span><div class="info-box-content"><span class="info-box-text">Tênis</span><span class="info-box-number">ID: 3</span></div></div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box bg-danger"><span class="info-box-icon"><i class="fas fa-volleyball-ball"></i></span><div class="info-box-content"><span class="info-box-text">Vôlei</span><span class="info-box-number">ID: 4</span></div></div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box bg-secondary"><span class="info-box-icon"><i class="fas fa-fist-raised"></i></span><div class="info-box-content"><span class="info-box-text">MMA/UFC</span><span class="info-box-number">ID: 22</span></div></div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box bg-dark"><span class="info-box-icon"><i class="fas fa-hand-rock"></i></span><div class="info-box-content"><span class="info-box-text">Boxe</span><span class="info-box-number">ID: 21</span></div></div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box bg-info"><span class="info-box-icon"><i class="fas fa-hockey-puck"></i></span><div class="info-box-content"><span class="info-box-text">Hóquei</span><span class="info-box-number">ID: 7</span></div></div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box bg-success"><span class="info-box-icon"><i class="fas fa-gamepad"></i></span><div class="info-box-content"><span class="info-box-text">E-Sports</span><span class="info-box-number">ID: 15</span></div></div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box bg-warning"><span class="info-box-icon"><i class="fas fa-table"></i></span><div class="info-box-content"><span class="info-box-text">Futsal</span><span class="info-box-number">ID: 6</span></div></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MERCADOS -->
        <div class="card card-outline card-secondary shadow-sm">
            <div class="card-header bg-secondary text-white">
                <h3 class="card-title"><i class="fas fa-chart-bar mr-2"></i> Mercados (Bet365 via BetsAPI)</h3>
            </div>
            <div class="card-body" style="max-height:300px; overflow-y:auto;">
                <small class="text-muted">BetsAPI retorna 50-150+ mercados por jogo (Bet365). Exemplos mapeados:</small>
                <table class="table table-sm table-striped mt-2" style="font-size:0.8rem;">
                    <thead><tr><th>BetsAPI (EN)</th><th>IHUB (PT)</th></tr></thead>
                    <tbody>
                        <tr><td>Match Winner</td><td>Vencedor do Encontro</td></tr>
                        <tr><td>Both Teams To Score</td><td>Ambas Marcam</td></tr>
                        <tr><td>Double Chance</td><td>Dupla Chance</td></tr>
                        <tr><td>Draw No Bet</td><td>Empate Anula a Aposta</td></tr>
                        <tr><td>Asian Handicap</td><td>Handicap Asiático</td></tr>
                        <tr><td>Goal Line / Over/Under</td><td>Gols Acima/Abaixo</td></tr>
                        <tr><td>Correct Score</td><td>Resultado Exato</td></tr>
                        <tr><td>Half Time Result</td><td>Vencedor do Encontro (1T)</td></tr>
                        <tr><td>Half Time Double Chance</td><td>Dupla Chance (1T)</td></tr>
                        <tr><td>Half Time/Full Time</td><td>Intervalo/Final</td></tr>
                        <tr><td>Odd/Even Goals</td><td>Gols Ímpar/Par</td></tr>
                        <tr><td>First Goalscorer</td><td>Primeiro a Marcar</td></tr>
                        <tr><td>Corners Over/Under</td><td>Escanteios Acima/Abaixo</td></tr>
                        <tr><td>Cards Over/Under</td><td>Cartões Acima/Abaixo</td></tr>
                        <tr><td>+ 100 mercados</td><td>Traduzidos automaticamente</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TOKEN -->
        <div class="card card-outline card-dark shadow-sm">
            <div class="card-header bg-dark text-white">
                <h3 class="card-title"><i class="fas fa-key mr-2"></i> Configuração</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="font-weight-bold">Token BetsAPI</label>
                    <input type="text" class="form-control form-control-sm" value="{{ substr(config('services.bets_api.token', ''), 0, 8) }}..." disabled>
                    <small class="text-muted">Configure em <code>.env</code> → <code>BETS_API_TOKEN</code></small>
                </div>
                <div class="form-group mb-0">
                    <label class="font-weight-bold">Endpoint</label>
                    <input type="text" class="form-control form-control-sm" value="{{ config('services.bets_api.base_url') }}" disabled>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
