@extends('adminlte::page')

@section('title', 'Monitor Real-Time')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-satellite-dish text-danger animate-pulse"></i> Monitoramento em Tempo Real</h1>
        <div id="live-indicator" class="badge badge-success px-3 py-2">
            <i class="fas fa-sync-alt fa-spin mr-1"></i> ATIVO: <span id="last-update">--:--:--</span>
        </div>
    </div>
@stop

@section('content')
<div class="row">
    <!-- KPIs -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info shadow-sm">
            <div class="inner">
                <h3 id="stat-total-in">R$ 0,00</h3>
                <p>Entradas (Hoje)</p>
            </div>
            <div class="icon"><i class="fas fa-arrow-down"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success shadow-sm">
            <div class="inner">
                <h3 id="stat-total-out">R$ 0,00</h3>
                <p>Prêmios Pagos (Hoje)</p>
            </div>
            <div class="icon"><i class="fas fa-trophy"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning shadow-sm">
            <div class="inner">
                <h3 id="stat-bets-count">0</h3>
                <p>Total de Bilhetes</p>
            </div>
            <div class="icon"><i class="fas fa-ticket-alt"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger shadow-sm">
            <div class="inner">
                <h3 id="stat-active-players">0</h3>
                <p>Clientes Online</p>
            </div>
            <div class="icon"><i class="fas fa-users"></i></div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Gráfico de Volume -->
    <div class="col-md-8">
        <div class="card card-dark card-outline shadow-sm">
            <div class="card-header border-0 bg-dark">
                <h3 class="card-title fw-bold text-white"><i class="fas fa-chart-line me-2"></i> Fluxo de Apostas (24h)</h3>
            </div>
            <div class="card-body">
                <canvas id="volumeChart" style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%;"></canvas>
            </div>
        </div>
    </div>

    <!-- Alertas de Risco -->
    <div class="col-md-4">
        <div class="card card-danger card-outline shadow-sm">
            <div class="card-header border-0 bg-danger text-white text-center">
                <h3 class="card-title fw-bold">⚠️ ALERTAS DE ALTO VALOR</h3>
            </div>
            <div class="card-body p-0">
                <ul class="products-list product-list-in-card pl-2 pr-2" id="alerts-list">
                    <li class="item text-center py-4 text-muted">Aguardando novos dados...</li>
                </ul>
            </div>
            <div class="card-footer text-center bg-light">
                <a href="{{ route('admin.risks') }}" class="small-box-footer text-danger fw-bold">Ver Gerenciamento Completo <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .animate-pulse { animation: pulse-red 2s infinite; }
    @keyframes pulse-red { 
        0% { transform: scale(0.95); opacity: 0.7; }
        70% { transform: scale(1); opacity: 1; }
        100% { transform: scale(0.95); opacity: 0.7; }
    }
    .badge-success { background-color: #10b981 !important; }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let volumeChart;
    
    function refreshData() {
        $.get('/admin/statistics/api-live', function(data) {
            // Atualiza KPIs
            $('#stat-total-in').text('R$ ' + parseFloat(data.kpis.total_in).toLocaleString('pt-BR', {minimumFractionDigits: 2}));
            $('#stat-total-out').text('R$ ' + parseFloat(data.kpis.total_out).toLocaleString('pt-BR', {minimumFractionDigits: 2}));
            $('#stat-bets-count').text(data.kpis.bets_count);
            $('#stat-active-players').text(data.kpis.active_players);
            $('#last-update').text(data.server_time);

            // Atualiza Gráfico
            const labels = data.chart_volume.map(i => i.hour + 'h');
            const values = data.chart_volume.map(i => i.total);
            
            if (volumeChart) {
                volumeChart.data.labels = labels;
                volumeChart.data.datasets[0].data = values;
                volumeChart.update();
            } else {
                initChart(labels, values);
            }

            // Atualiza Alertas
            const alertsHtml = data.recent_alerts.map(bet => `
                <li class="item">
                    <div class="product-info ml-0">
                        <span class="product-title text-danger fw-bold">R$ ${bet.amount} <span class="badge badge-warning float-right">ALTA APOSTA</span></span>
                        <span class="product-description small">Bilhete #${bet.id} | Cambista: ${bet.user_id}</span>
                    </div>
                </li>
            `).join('');
            $('#alerts-list').html(alertsHtml || '<li class="item text-center py-3">Sem alertas no momento.</li>');
        });
    }

    function initChart(labels, values) {
        const ctx = document.getElementById('volumeChart').getContext('2d');
        volumeChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Volume de Apostas (R$)',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderColor: '#10b981',
                    pointBackgroundColor: '#10b981',
                    data: values,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: { maintainAspectRatio: false, responsive: true }
        });
    }

    // Refresh a cada 10 segundos
    refreshData();
    setInterval(refreshData, 10000);
</script>
@stop
