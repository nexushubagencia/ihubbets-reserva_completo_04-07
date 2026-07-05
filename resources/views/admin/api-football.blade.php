@extends('adminlte::page')

@section('title', 'API-Football | IHUB BETS')

@section('content_header')
    <h1><i class="fas fa-futbol text-success"></i> API-Football <small class="text-muted">Configurações & Sincronização</small></h1>
@stop

@section('content')
<div class="row">
    @if(session('success'))
        <div class="col-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('success') }}</div></div>
    @endif
    @if(session('error'))
        <div class="col-12"><div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('error') }}</div></div>
    @endif

    <!-- COLUNA ESQUERDA -->
    <div class="col-md-4">
        <!-- STATUS DA API -->
        <div class="card card-outline card-info shadow-sm">
            <div class="card-header bg-info text-white">
                <h3 class="card-title"><i class="fas fa-signal mr-2"></i> Status da API</h3>
                <button class="btn btn-sm btn-light float-right" id="btn-refresh-status"><i class="fas fa-sync"></i></button>
            </div>
            <div class="card-body text-center">
                <div id="api-status-loading"><div class="spinner-border text-info" role="status"></div></div>
                <div id="api-status-content" style="display:none;">
                    <h4 id="api-status-text" class="mb-1"></h4>
                    <div class="progress mb-2" style="height: 20px;">
                        <div id="api-usage-bar" class="progress-bar bg-success" role="progressbar" style="width: 0%"></div>
                    </div>
                    <small id="api-usage-text" class="text-muted"></small>
                </div>
            </div>
        </div>

        <!-- API KEY -->
        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title"><i class="fas fa-key mr-2"></i> API Key</h3>
            </div>
            <form action="{{ route('admin.api-football.update-key') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group mb-0">
                        <input type="text" name="api_key" class="form-control form-control-sm" value="{{ $apiKey }}" placeholder="x-apisports-key">
                        <small class="text-muted">Obtenha em <a href="https://www.api-sports.io/" target="_blank">api-sports.io</a></small>
                    </div>
                </div>
                <div class="card-footer bg-light"><button type="submit" class="btn btn-primary btn-block btn-sm"><i class="fas fa-save mr-1"></i> Salvar</button></div>
            </form>
        </div>

        <!-- PROVEDOR -->
        <div class="card card-outline card-warning shadow-sm">
            <div class="card-header bg-warning text-dark">
                <h3 class="card-title"><i class="fas fa-exchange-alt mr-2"></i> Provedor</h3>
            </div>
            <form method="POST" action="{{ route('admin.api-football.provider') }}">
                @csrf
                <div class="card-body">
                    <div class="custom-control custom-radio mb-2">
                        <input type="radio" id="pf" name="provider" value="api-football" class="custom-control-input" {{ $activeProvider === 'api-football' ? 'checked' : '' }}>
                        <label class="custom-control-label" for="pf"><b>API-Football</b> <small class="text-muted">(Grátis 100req/dia)</small></label>
                    </div>
                    <div class="custom-control custom-radio">
                        <input type="radio" id="ba" name="provider" value="bets-api" class="custom-control-input" {{ $activeProvider === 'bets-api' ? 'checked' : '' }}>
                        <label class="custom-control-label" for="ba"><b>BetsAPI</b> <small class="text-muted">(Premium)</small></label>
                    </div>
                </div>
                <div class="card-footer bg-light"><button type="submit" class="btn btn-warning btn-block btn-sm"><i class="fas fa-save mr-1"></i> Alterar</button></div>
            </form>
        </div>

        <!-- AÇÕES RÁPIDAS -->
        <div class="card card-outline card-success shadow-sm">
            <div class="card-header bg-success text-white">
                <h3 class="card-title"><i class="fas fa-bolt mr-2"></i> Ações Rápidas</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.api-football.sync') }}" method="POST" class="mb-2">
                    @csrf
                    <button type="submit" class="btn btn-success btn-block btn-sm"><i class="fas fa-download mr-1"></i> Sincronizar Jogos</button>
                </form>
                <form action="{{ route('admin.api-football.run-odds') }}" method="POST" class="mb-2">
                    @csrf
                    <button type="submit" class="btn btn-info btn-block btn-sm"><i class="fas fa-chart-line mr-1"></i> Atualizar Odds</button>
                </form>
                <form action="{{ route('admin.api-football.run-live') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-block btn-sm"><i class="fas fa-broadcast-tower mr-1"></i> Atualizar Ao Vivo</button>
                </form>
            </div>
        </div>
    </div>

    <!-- COLUNA DIREITA -->
    <div class="col-md-8">
        <!-- LIGAS -->
        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between">
                <h3 class="card-title"><i class="fas fa-trophy mr-2"></i> Ligas ({{ $leagues->count() }})</h3>
                <div>
                    <button class="btn btn-sm btn-light" id="btn-toggle-all"><i class="fas fa-check-double"></i></button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover table-sm mb-0">
                        <thead class="bg-light sticky-top"><tr><th>Liga</th><th>País</th><th class="text-center" style="width:60px;">Ativa</th></tr></thead>
                        <tbody>
                            @forelse($leagues as $league)
                                <tr>
                                    <td class="font-weight-bold" style="font-size:0.85rem;">{{ $league->name }}</td>
                                    <td style="font-size:0.85rem;">{{ $league->country }}</td>
                                    <td class="text-center">
                                        <label class="switch mb-0"><input type="checkbox" class="league-toggle" data-id="{{ $league->id }}" {{ $league->active ? 'checked' : '' }}><span class="slider round"></span></label>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center py-4 text-muted">Nenhuma liga. Clique em "Sincronizar Jogos".</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- MERCADOS (FILTRO) -->
        <div class="card card-outline card-secondary shadow-sm">
            <div class="card-header bg-secondary text-white d-flex justify-content-between">
                <h3 class="card-title"><i class="fas fa-filter mr-2"></i> Filtro de Mercados</h3>
                <button class="btn btn-sm btn-light" id="btn-save-markets"><i class="fas fa-save"></i> Salvar</button>
            </div>
            <div class="card-body" style="max-height: 250px; overflow-y: auto;">
                <div class="row">
                    @foreach($allMarkets as $market)
                        <div class="col-md-4">
                            <div class="custom-control custom-checkbox mb-1">
                                <input type="checkbox" class="market-cb" id="m{{ $loop->index }}" value="{{ $market }}" {{ in_array($market, $activeMarkets) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="m{{ $loop->index }}" style="font-size:0.8rem;">{{ $market }}</label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- LOGS -->
        <div class="card card-outline card-dark shadow-sm">
            <div class="card-header bg-dark text-white d-flex justify-content-between">
                <h3 class="card-title"><i class="fas fa-terminal mr-2"></i> Logs</h3>
                <button class="btn btn-sm btn-light" id="btn-refresh-logs"><i class="fas fa-sync"></i></button>
            </div>
            <div class="card-body p-0">
                <pre id="logs-content" style="max-height:250px; overflow-y:auto; background:#1a1a2e; color:#0f0; padding:10px; font-size:0.75rem; margin:0;">Carregando...</pre>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .switch{position:relative;display:inline-block;width:40px;height:22px}.switch input{opacity:0;width:0;height:0}.slider{position:absolute;cursor:pointer;top:0;left:0;right:0;bottom:0;background-color:#ccc;transition:.3s;border-radius:22px}.slider:before{position:absolute;content:"";height:16px;width:16px;left:3px;bottom:3px;background-color:white;transition:.3s;border-radius:50%}input:checked+.slider{background-color:#28a745}input:checked+.slider:before{transform:translateX(18px)}.table td,.table th{vertical-align:middle}
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    loadStatus();
    loadLogs();

    $('#btn-refresh-status').click(loadStatus);
    $('#btn-refresh-logs').click(loadLogs);

    function loadStatus() {
        $.getJSON('{{ route("admin.api-football.status") }}', function(d) {
            $('#api-status-loading').hide();
            $('#api-status-content').show();
            $('#api-status-text').text(d.status).removeClass().addClass(d.status.includes('Ativa') ? 'text-success' : 'text-danger');
            var pct = d.requests_limit > 0 ? Math.min(100, (d.requests_current / d.requests_limit) * 100) : 0;
            $('#api-usage-bar').css('width', pct + '%').removeClass('bg-success bg-warning bg-danger').addClass(pct > 80 ? 'bg-danger' : pct > 50 ? 'bg-warning' : 'bg-success');
            $('#api-usage-text').text(d.requests_current + ' / ' + d.requests_limit + ' requests hoje');
        }).fail(function() {
            $('#api-status-loading').hide();
            $('#api-status-content').show();
            $('#api-status-text').text('Erro ao consultar').addClass('text-danger');
        });
    }

    function loadLogs() {
        $.getJSON('{{ route("admin.api-football.logs") }}', function(d) {
            $('#logs-content').text(d.logs || 'Nenhum log.');
        });
    }

    $(document).on('change', '.league-toggle', function() {
        $.post('{{ route("admin.api-football.toggle-league") }}', {
            _token: $('meta[name="csrf-token"]').attr('content'),
            league_id: $(this).data('id')
        }, function(r) { toastr.success(r.message); }).fail(function() { toastr.error('Erro ao atualizar liga.'); });
    });

    $('#btn-toggle-all').click(function() {
        var allChecked = $('.league-toggle:checked').length === $('.league-toggle').length;
        $('.league-toggle').prop('checked', !allChecked).each(function() { $(this).trigger('change'); });
    });

    $('#btn-save-markets').click(function() {
        var markets = [];
        $('.market-cb:checked').each(function() { markets.push($(this).val()); });
        $.post('{{ route("admin.api-football.save-markets") }}', {
            _token: $('meta[name="csrf-token"]').attr('content'),
            markets: markets
        }, function(r) { toastr.success(r.message); });
    });
});
</script>
@stop
