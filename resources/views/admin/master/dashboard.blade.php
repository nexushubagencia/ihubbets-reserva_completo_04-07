@extends('adminlte::page')

@section('title', 'Dashboard Master | IHUB V2 Pro')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark font-weight-bold">
            <i class="fas fa-chess-king text-warning mr-2"></i> Gerenciador Master 
            <small class="text-muted ml-2">Visão Consolidada White-Label</small>
        </h1>
        <div class="breadcrumb-item active text-muted small">
            Última atualização: {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <!-- WIDGETS GLOBAIS -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-navy shadow-sm border-0 h-100">
                <div class="inner">
                    <h3>R$ {{ number_format($totalVolume, 2, ',', '.') }}</h3>
                    <p>Volume Global (Total)</p>
                </div>
                <div class="icon"><i class="fas fa-chart-line"></i></div>
                <a href="#" class="small-box-footer">Ver Relatórios <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success shadow-sm border-0 h-100">
                <div class="inner">
                    <h3>R$ {{ number_format($todayVolume, 2, ',', '.') }}</h3>
                    <p>Volume de Hoje</p>
                </div>
                <div class="icon"><i class="fas fa-calendar-check"></i></div>
                <a href="#" class="small-box-footer">Ver Movimentação <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info shadow-sm border-0 h-100">
                <div class="inner">
                    <h3>{{ $activeSites }} / {{ $totalSites }}</h3>
                    <p>Bancas Ativas</p>
                </div>
                <div class="icon"><i class="fas fa-network-wired"></i></div>
                <a href="{{ route('admin.master.bancas') }}" class="small-box-footer">Gerenciar Bancas <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger shadow-sm border-0 h-100">
                <div class="inner">
                    <h3>{{ $billingStats['overdue'] }}</h3>
                    <p>Bancas em Atraso</p>
                </div>
                <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                <a href="{{ route('admin.master.financeiro') }}" class="small-box-footer">Ver Pendências <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>

    <!-- GRÁFICOS E PERFORMANCE -->
    <div class="row mt-4">
        <div class="col-md-8">
            <div class="card card-outline card-navy shadow-sm">
                <div class="card-header border-0">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-chart-area mr-1"></i> Volume de Apostas Global (Últimos 7 dias)</h3>
                </div>
                <div class="card-body">
                    <canvas id="volumeChart" style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-outline card-info shadow-sm">
                <div class="card-header border-0">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-wallet mr-1"></i> Saúde Financeira (Bancas)</h3>
                </div>
                <div class="card-body">
                    <canvas id="billingChart" style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%;"></canvas>
                    <hr>
                    <div class="d-flex justify-content-between small text-muted">
                        <span>Total Mensal Esperado:</span>
                        <span class="font-weight-bold text-dark">R$ {{ number_format($billingStats['total_due'], 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- DNS QUICK REFERENCE -->
    <div class="card bg-light border-0 shadow-sm mb-4 mt-2">
        <div class="card-body d-flex align-items-center justify-content-between p-3">
            <div class="d-flex align-items-center">
                <div class="bg-navy p-3 rounded mr-3"><i class="fas fa-globe fa-2x"></i></div>
                <div>
                    <h6 class="mb-0 font-weight-bold">Configuração Central de DNS</h6>
                    <p class="mb-0 small text-muted">Aponte o domínio do cliente para o IP: <strong>{{ env('SERVER_IP_PUBLIC', '127.0.0.1') }}</strong> (Registro A)</p>
                </div>
            </div>
            <button class="btn btn-outline-navy btn-sm font-weight-bold" onclick="copyIP()">COPIAR IP</button>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function copyIP() {
        navigator.clipboard.writeText('{{ env("SERVER_IP_PUBLIC", "127.0.0.1") }}');
        toastr.success('IP copiado para a área de transferência!');
    }

    // Gráfico de Volume
    const ctxVolume = document.getElementById('volumeChart').getContext('2d');
    new Chart(ctxVolume, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartVolume->pluck('date')->map(fn($d) => date('d/m', strtotime($d)))) !!},
            datasets: [{
                label: 'Volume Total (R$)',
                data: {!! json_encode($chartVolume->pluck('total')) !!},
                backgroundColor: 'rgba(28, 52, 100, 0.1)',
                borderColor: '#1c3464',
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#28a745',
                pointRadius: 5
            }]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            scales: { y: { beginAtZero: true } }
        }
    });

    // Saúde Financeira
    const ctxBilling = document.getElementById('billingChart').getContext('2d');
    new Chart(ctxBilling, {
        type: 'doughnut',
        data: {
            labels: ['Pagos', 'Pendentes', 'Atrasados'],
            datasets: [{
                data: [{{ $billingStats['paid'] }}, {{ $billingStats['pending'] }}, {{ $billingStats['overdue'] }}],
                backgroundColor: ['#28a745', '#ffc107', '#dc3545'],
                borderWidth: 0
            }]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            cutout: '75%',
            plugins: { legend: { position: 'bottom' } }
        }
    });
</script>
@stop

@section('css')
<style>
    .bg-navy { background-color: #001f3f !important; color: #fff; }
    .card-navy.card-outline { border-top: 3px solid #001f3f; }
    .btn-outline-navy { color: #001f3f; border-color: #001f3f; }
    .btn-outline-navy:hover { background-color: #001f3f; color: #fff; }
    .small-box { border-radius: 12px; transition: 0.3s; }
    .small-box:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.2) !important; }
</style>
@stop
