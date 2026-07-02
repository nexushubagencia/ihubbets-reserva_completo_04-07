@extends('adminlte::page')

@section('title', 'Gerenciador Master | IHUB V2 Pro')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>👑 Gerenciador Master <small class="text-muted">White-Label Control</small></h1>
        <div>
            <button class="btn btn-success shadow-sm" data-toggle="modal" data-target="#modalCreateSite">
                <i class="fas fa-plus-circle"></i> NOVA BANCA
            </button>
        </div>
    </div>
@endsection

@section('content')
<div class="row">
    <!-- Widgets de Resumo -->
    <div class="col-md-3">
        <div class="small-box bg-gradient-navy shadow-sm border-0">
            <div class="inner">
                <h3>R$ {{ number_format($expectedRevenue, 2, ',', '.') }}</h3>
                <p>Receita Mensal Esperada</p>
            </div>
            <div class="icon"><i class="fas fa-coins"></i></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="small-box bg-gradient-success shadow-sm border-0">
            <div class="inner">
                <h3>{{ $activeSites }}</h3>
                <p>Bancas Ativas</p>
            </div>
            <div class="icon"><i class="fas fa-check-double"></i></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="small-box bg-gradient-danger shadow-sm border-0">
            <div class="inner">
                <h3>{{ $billingStats['overdue'] }}</h3>
                <p>Bancas em Atraso</p>
            </div>
            <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="small-box bg-gradient-info shadow-sm border-0">
            <div class="inner">
                <h3>R$ {{ number_format($totalVolume, 2, ',', '.') }}</h3>
                <p>Volume Global de Apostas</p>
            </div>
            <div class="icon"><i class="fas fa-chart-line"></i></div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Gráfico de Volume Semanal -->
    <div class="col-md-8">
        <div class="card card-outline card-navy shadow-sm">
            <div class="card-header border-0">
                <h3 class="card-title"><i class="fas fa-chart-area mr-1"></i> Volume de Apostas (7 Dias)</h3>
            </div>
            <div class="card-body">
                <canvas id="volumeChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
            </div>
        </div>
    </div>

    <!-- Status de Pagamentos (Donut) -->
    <div class="col-md-4">
        <div class="card card-outline card-info shadow-sm">
            <div class="card-header border-0">
                <h3 class="card-title"><i class="fas fa-wallet mr-1"></i> Saúde Financeira</h3>
            </div>
            <div class="card-body">
                <canvas id="billingChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- INSTRUÇÕES CÓPIA RÁPIDA DE DNS (CLOUD & VPS) -->
<div class="row">
    <div class="col-md-12">
        <div class="card card-dark card-outline shadow-sm mb-4">
            <div class="card-header bg-dark border-0">
                <h3 class="card-title text-white font-weight-bold"><i class="fas fa-network-wired mr-2"></i> Como Conectar o Domínio do Cliente (DNS Wildcard)</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus text-white"></i></button>
                </div>
            </div>
            <div class="card-body bg-light">
                <p class="text-muted small mb-3">Para que uma nova banca funcione, o cliente (ou você) precisa apontar o domínio dele no <strong>Cloudflare</strong> ou <strong>Hostinger/GoDaddy</strong> para o nosso Servidor Central (VPS). Informe a ele os seguintes dados:</p>
                
                <div class="row text-center mb-2">
                    <div class="col-md-4">
                        <label class="d-block small font-weight-bold text-secondary mb-1">TIPO DO APONTAMENTO</label>
                        <div class="p-2 border rounded bg-white font-weight-bold">Registro A (A Record)</div>
                    </div>
                    <div class="col-md-4">
                        <label class="d-block small font-weight-bold text-secondary mb-1">NOME / HOST</label>
                        <div class="p-2 border rounded bg-white font-weight-bold">@ <span class="text-muted font-weight-normal">(ou deixe em branco se não permitir @)</span></div>
                    </div>
                    <div class="col-md-4">
                        <label class="d-block small font-weight-bold text-secondary mb-1">DESTINO (IP DA VPS)</label>
                        <div class="p-2 border border-primary rounded bg-white font-weight-bold text-primary" style="font-size: 1.2rem; user-select: all; cursor: pointer;">
                            <i class="far fa-copy mr-1"></i> {{ env('SERVER_IP_PUBLIC', '127.0.0.1') }}
                        </div>
                    </div>
                </div>
                
                <hr class="my-2">
                
                <div class="row">
                    <div class="col-md-12 d-flex align-items-center">
                        <i class="fas fa-info-circle text-info fa-2x mr-3"></i>
                        <p class="mb-0 small text-muted"><strong>A Mágica do Multi-Tenant:</strong> Ao apontar o DNS dele para o nosso IP, e você cadastrar o domínio dele no botão <span class="badge badge-success text-white">Nova Banca</span>, o sistema cruza as informações automaticamente pelo <code>$_SERVER['HTTP_HOST']</code> e exibe exclusivamete os clientes, escudos e apostas daquela banca! Nenhuma instalação separada é necessária.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Ranking de Bancas -->
    <div class="col-md-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-navy text-white">
                <h3 class="card-title font-weight-bold"><i class="fas fa-list-ol mr-1"></i> Monitoramento de Parceiros White-Label</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-valign-middle mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Banca</th>
                                <th>Status</th>
                                <th>Financeiro</th>
                                <th>Volume (R$)</th>
                                <th>Usuários</th>
                                <th>Vencimento</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sites as $site)
                            <tr>
                                <td>
                                    <strong>{{ $site->name }}</strong><br>
                                    <small class="text-muted">{{ $site->domain }}</small>
                                </td>
                                <td>
                                    @if($site->status === 'active')
                                        <span class="badge badge-success">Ativa</span>
                                    @else
                                        <span class="badge badge-danger">Suspensa</span>
                                    @endif
                                </td>
                                <td>
                                    @if($site->billing_status === 'paid')
                                        <span class="text-success"><i class="fas fa-check-circle"></i> Pago</span>
                                    @elseif($site->billing_status === 'pending')
                                        <span class="text-warning"><i class="fas fa-clock"></i> Pendente</span>
                                    @else
                                        <span class="text-danger font-weight-bold"><i class="fas fa-times-circle"></i> Atrasado</span>
                                    @endif
                                </td>
                                <td>R$ {{ number_format($site->bets_sum_amount ?? 0, 2, ',', '.') }}</td>
                                <td>{{ $site->users_count }}</td>
                                <td>{{ $site->next_due_date ? date('d/m/Y', strtotime($site->next_due_date)) : 'N/A' }}</td>
                                <td>
                                    <div class="btn-group">
                                        <button class="btn btn-xs btn-default shadow-sm"><i class="fas fa-eye"></i></button>
                                        <a href="{{ route('admin.gerenciador.edit', $site->id) }}" class="btn btn-xs btn-primary shadow-sm" title="Editar Banca"><i class="fas fa-edit"></i></a>
                                        <a href="{{ route('admin.gerenciador.backup', $site->id) }}" class="btn btn-xs btn-warning shadow-sm" title="Baixar Backup JSON"><i class="fas fa-download"></i></a>
                                        <form action="{{ route('admin.gerenciador.suspend', $site->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-xs btn-danger shadow-sm" onclick="return confirm('ATENÇÃO: Isso bloqueará TODOS os usuários desta banca. Confirmar?')" title="Suspender Banca">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Create Site -->
<div class="modal fade" id="modalCreateSite" tabindex="-1" role="dialog">
    <div class="modal-dialog shadow-lg" role="document">
        <form action="{{ route('admin.gerenciador.store') }}" method="POST">
            @csrf
            <div class="modal-content border-0">
                <div class="modal-header bg-navy text-white">
                    <h5 class="modal-title">Provisionar Nova Banca</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nome da Banca</label>
                        <input type="text" name="name" class="form-control" placeholder="Ex: Play Bets VIP" required>
                    </div>
                    <div class="form-group">
                        <label>Domínio</label>
                        <input type="text" name="domain" class="form-control" placeholder="exemplo.com" required>
                    </div>
                    <hr class="mt-4 mb-4">
                    <h6 class="font-weight-bold text-navy"><i class="fas fa-user-shield"></i> Dados do Dono da Banca</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>E-mail de Login</label>
                                <input type="email" name="admin_email" class="form-control" placeholder="admin@exemplo.com" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Senha Inicial</label>
                                <input type="text" name="admin_password" class="form-control" placeholder="Gerar ou digitar..." required>
                            </div>
                        </div>
                    </div>
                    <hr class="mt-2 mb-4">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Valor da Mensalidade (R$)</label>
                                <input type="number" step="0.01" name="due_value" class="form-control" value="500.00" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Dia do Vencimento</label>
                                <input type="number" name="billing_day" class="form-control" value="10" min="1" max="31" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success px-4 font-weight-bold">ATIVAR BANCA AGORA</button>
                </div>
            </div>
        </form>
    </div>
</div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Gráfico de Volume (Linha)
    const ctxVolume = document.getElementById('volumeChart').getContext('2d');
    new Chart(ctxVolume, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartVolume->pluck('date')) !!},
            datasets: [{
                label: 'Volume de Apostas (R$)',
                data: {!! json_encode($chartVolume->pluck('total')) !!},
                backgroundColor: 'rgba(28, 52, 100, 0.1)',
                borderColor: '#1c3464',
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#28a745',
                pointBorderColor: '#fff',
                pointRadius: 4
            }]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // Gráfico de Pagamentos (Donut)
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
            cutout: '70%',
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
</script>
@stop

@section('css')
<style>
    .bg-gradient-navy { background: linear-gradient(45deg, #1c3464, #2a4b8d) !important; color: #fff; }
    .card-navy.card-outline { border-top: 3px solid #1c3464; }
    .table-valign-middle td { vertical-align: middle; }
    .badge { padding: 0.5em 0.8em; font-weight: 500; }
    .small-box { border-radius: 12px; }
    .small-box h3 { font-weight: 700; font-size: 1.8rem; }
</style>
@stop
