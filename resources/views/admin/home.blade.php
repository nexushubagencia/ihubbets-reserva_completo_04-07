@extends('adminlte::page')

@section('title', config('adminlte.title_adm_geral'))

@section('content_header')
    <div class="row mb-2 align-items-center">
        <div class="col-sm-6">
            <h1 class="m-0" style="font-weight: 700; font-size: 1.6rem; color: #1e293b;">
                Pagina Inicial
                <small style="font-size: 0.5em; color: #94a3b8; font-weight: 500; margin-left: 8px;">2.1.0</small>
            </h1>
        </div>
        <div class="col-sm-6 text-right">
            <span style="color: #64748b; font-size: 0.85rem;">
                <i class="fas fa-clock mr-1"></i>
                <span id="live-clock"></span>
            </span>
        </div>
    </div>
@stop

@section('content')
<style>
    .dash-card-top {
        border-radius: 12px;
        padding: 28px 24px 22px;
        color: #fff;
        position: relative;
        overflow: hidden;
        min-height: 130px;
    }
    .dash-card-top .card-label {
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        opacity: 0.85;
        margin-bottom: 6px;
    }
    .dash-card-top .card-value {
        font-size: 2.2rem;
        font-weight: 900;
        line-height: 1.1;
    }
    .dash-card-top .card-sub {
        font-size: 0.78rem;
        margin-top: 14px;
        opacity: 0.7;
        cursor: pointer;
    }
    .dash-card-top .card-sub:hover { opacity: 1; }
    .dash-card-top .card-bg-icon {
        position: absolute;
        top: -15px;
        right: -15px;
        width: 100px;
        height: 100px;
        background: rgba(255,255,255,0.08);
        border-radius: 50%;
    }

    .section-header {
        border-radius: 10px 10px 0 0;
        padding: 14px 20px;
        font-size: 0.9rem;
        font-weight: 700;
        color: #fff;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .metric-box {
        display: flex;
        align-items: center;
        border: 1px solid #e9ecef;
        border-radius: 10px;
        overflow: hidden;
        background: #fff;
        transition: box-shadow 0.2s;
    }
    .metric-box:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    .metric-icon {
        width: 56px;
        height: 56px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 1.3rem;
        color: #fff;
    }
    .metric-content {
        padding: 10px 14px;
        flex: 1;
    }
    .metric-label {
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: #64748b;
        margin-bottom: 2px;
    }
    .metric-value {
        font-size: 1.05rem;
        font-weight: 800;
        color: #1e293b;
    }

    .info-section {
        border-radius: 10px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        background: #fff;
        margin-bottom: 24px;
    }
</style>

<div class="container-fluid">

    {{-- ═══ DOIS CARDS GRANDES NO TOPO ═══ --}}
    <div class="row mb-4">

        {{-- CAIXA CAMBISTA --}}
        <div class="col-lg-6 col-md-6 mb-3">
            <div class="dash-card-top" style="background: linear-gradient(135deg, #10b981 0%, #059669 50%, #047857 100%); box-shadow: 0 8px 24px rgba(16,185,129,0.35);">
                <div class="card-bg-icon"></div>
                <div class="card-label">Saldo Cambista</div>
                <div class="card-value" id="dash-saldo-cambista">R$ 0,00</div>
                <div class="card-sub" id="toggle-caixa-cambista">
                    <i class="fas fa-plus-circle mr-1"></i> Mais info
                </div>
            </div>
        </div>

        {{-- SALDO USUARIOS ONLINE --}}
        <div class="col-lg-6 col-md-6 mb-3">
            <div class="dash-card-top" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 50%, #b45309 100%); box-shadow: 0 8px 24px rgba(245,158,11,0.35);">
                <div class="card-bg-icon"></div>
                <div class="card-label">Saldo Usuarios Online</div>
                <div class="card-value" id="dash-saldo-online">R$ 0,00</div>
                <div class="card-sub" id="toggle-usuarios-online">
                    <i class="fas fa-plus-circle mr-1"></i> Mais info
                </div>
            </div>
        </div>

    </div>

    {{-- ═══ DUAS COLUNAS: CAIXA CAMBISTAS + USUARIOS ═══ --}}
    <div class="row">

        {{-- ═══ COLUNA ESQUERDA: CAIXA DOS CAMBISTAS ═══ --}}
        <div class="col-lg-6 mb-4">
            <div class="info-section">
                <div class="section-header" style="background: #10b981;">
                    <i class="fas fa-money-bill-wave"></i> Caixa dos Cambistas
                </div>
                <div class="p-3">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <div class="metric-box">
                                <div class="metric-icon" style="background: #3b82f6;"><i class="fas fa-ticket-alt"></i></div>
                                <div class="metric-content">
                                    <div class="metric-label">Bilhetes</div>
                                    <div class="metric-value" id="m-bilhetes">0</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="metric-box">
                                <div class="metric-icon" style="background: #10b981;"><i class="fas fa-arrow-circle-down"></i></div>
                                <div class="metric-content">
                                    <div class="metric-label">Entradas</div>
                                    <div class="metric-value" id="m-entradas">R$ 0,00</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="metric-box">
                                <div class="metric-icon" style="background: #ef4444;"><i class="fas fa-trophy"></i></div>
                                <div class="metric-content">
                                    <div class="metric-label">Saidas</div>
                                    <div class="metric-value" id="m-saidas">R$ 0,00</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="metric-box">
                                <div class="metric-icon" style="background: #0ea5e9;"><i class="fas fa-exchange-alt"></i></div>
                                <div class="metric-content">
                                    <div class="metric-label">Lancamentos</div>
                                    <div class="metric-value" id="m-lancamentos">R$ 0,00</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="metric-box">
                                <div class="metric-icon" style="background: #f59e0b;"><i class="fas fa-hourglass-half"></i></div>
                                <div class="metric-content">
                                    <div class="metric-label">Em Aberto</div>
                                    <div class="metric-value" id="m-aberto">R$ 0,00</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="metric-box">
                                <div class="metric-icon" style="background: #8b5cf6;"><i class="fas fa-handshake"></i></div>
                                <div class="metric-content">
                                    <div class="metric-label">Comissoes</div>
                                    <div class="metric-value" id="m-comissoes">R$ 0,00</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 mb-1">
                            <div class="metric-box" style="border: 2px solid #10b981; background: #f0fdf4;">
                                <div class="metric-icon" style="background: #059669;"><i class="fas fa-chart-line"></i></div>
                                <div class="metric-content">
                                    <div class="metric-label" style="color: #059669;">Saldo</div>
                                    <div class="metric-value" style="color: #059669; font-size: 1.2rem;" id="m-saldo">R$ 0,00</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══ COLUNA DIREITA: USUARIOS ═══ --}}
        <div class="col-lg-6 mb-4">
            <div class="info-section">
                <div class="section-header" style="background: #f59e0b;">
                    <i class="fas fa-users"></i> Usuarios
                </div>
                <div class="p-3">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <div class="metric-box">
                                <div class="metric-icon" style="background: #3b82f6;"><i class="fas fa-ticket-alt"></i></div>
                                <div class="metric-content">
                                    <div class="metric-label">Bilhetes</div>
                                    <div class="metric-value" id="u-bilhetes">0</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="metric-box">
                                <div class="metric-icon" style="background: #6366f1;"><i class="fas fa-users"></i></div>
                                <div class="metric-content">
                                    <div class="metric-label">Usuarios</div>
                                    <div class="metric-value" id="u-usuarios">0</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="metric-box">
                                <div class="metric-icon" style="background: #10b981;"><i class="fas fa-arrow-circle-down"></i></div>
                                <div class="metric-content">
                                    <div class="metric-label">Entradas</div>
                                    <div class="metric-value" id="u-entradas">R$ 0,00</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="metric-box">
                                <div class="metric-icon" style="background: #f59e0b;"><i class="fas fa-hourglass-half"></i></div>
                                <div class="metric-content">
                                    <div class="metric-label">Em Aberto</div>
                                    <div class="metric-value" id="u-aberto">R$ 0,00</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="metric-box">
                                <div class="metric-icon" style="background: #0ea5e9;"><i class="fas fa-chart-line"></i></div>
                                <div class="metric-content">
                                    <div class="metric-label">Disponivel Para...</div>
                                    <div class="metric-value" id="u-disponivel">R$ 0,00</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="metric-box">
                                <div class="metric-icon" style="background: #ef4444;"><i class="fas fa-money-check-alt"></i></div>
                                <div class="metric-content">
                                    <div class="metric-label">Saque</div>
                                    <div class="metric-value" id="u-saque">R$ 0,00</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="metric-box">
                                <div class="metric-icon" style="background: #10b981;"><i class="fas fa-arrow-circle-down"></i></div>
                                <div class="metric-content">
                                    <div class="metric-label">Depositos</div>
                                    <div class="metric-value" id="u-depositos">R$ 0,00</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>
@stop

@section('js')
<script>
function formatMoeda(n) {
    n = parseFloat(n || 0);
    return "R$ " + n.toFixed(2).replace('.', ',').replace(/(\d)(?=(\d{3})+\,)/g, "$1.");
}

// Relogio ao vivo
(function tick() {
    var now = new Date();
    var el = document.getElementById('live-clock');
    if (el) el.textContent = now.toLocaleTimeString('pt-BR') + ' - ' + now.toLocaleDateString('pt-BR', {weekday:'long', day:'2-digit', month:'long', year:'numeric'});
    setTimeout(tick, 1000);
})();

$(document).ready(function () {

    $.ajax({
        url: '/admin/dashboard-stats',
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            var e = parseFloat(data.entradas || 0);
            var s = parseFloat(data.saidas || 0);
            var a = parseFloat(data.entradas_abertas || 0);
            var c = parseFloat(data.comissoes || 0);
            var l = parseFloat(data.lancamentos || 0);
            var total = parseFloat(data.total || 0);
            var qtd = parseInt(data.quantidade || 0);

            // ═══ TOPO: Dois Cards Grandes ═══
            $('#dash-saldo-cambista').text(formatMoeda(total));
            $('#dash-saldo-online').text(formatMoeda(data.saldo_usuarios || 0));

            // ═══ CAIXA DOS CAMBISTAS ═══
            $('#m-bilhetes').text(qtd);
            $('#m-entradas').text(formatMoeda(e));
            $('#m-saidas').text(formatMoeda(s));
            $('#m-lancamentos').text(formatMoeda(l));
            $('#m-aberto').text(formatMoeda(a));
            $('#m-comissoes').text(formatMoeda(c));
            $('#m-saldo').text(formatMoeda(total));

            // ═══ USUARIOS ═══
            $('#u-bilhetes').text(data.bilhetes_usuarios || 0);
            $('#u-usuarios').text(data.total_usuarios || 0);
            $('#u-entradas').text(formatMoeda(data.entradas_usuarios || 0));
            $('#u-aberto').text(formatMoeda(data.entradas_abertas_usuarios || 0));
            $('#u-disponivel').text(formatMoeda(data.saldo_usuarios || 0));
            $('#u-saque').text(formatMoeda(data.saques_pendentes || 0));
            $('#u-depositos').text(formatMoeda(data.depositos_hoje || 0));
        }
    });
});
</script>
@stop
