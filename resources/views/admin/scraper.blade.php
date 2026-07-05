@extends('adminlte::page')

@section('title', 'Scraper Jogadinha | IHUB BETS')

@section('content_header')
    <h1><i class="fas fa-spider text-warning"></i> Scraper Jogadinha <small class="text-muted">Configurações & Controle</small></h1>
@stop

@section('content')
<div class="row">
    @if(session('success'))
        <div class="col-12"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('success') }}</div></div>
    @endif

    <!-- COLUNA ESQUERDA -->
    <div class="col-md-4">
        <!-- CONTROLE -->
        <div class="card card-outline card-danger shadow-sm">
            <div class="card-header bg-danger text-white">
                <h3 class="card-title"><i class="fas fa-power-off mr-2"></i> Controle</h3>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="font-weight-bold">Status:</span>
                    <span id="scraper-badge" class="badge badge-secondary">Parado</span>
                </div>
                <div class="row">
                    <div class="col-6"><button class="btn btn-success btn-block btn-sm" id="btn-start"><i class="fas fa-play mr-1"></i> Iniciar</button></div>
                    <div class="col-6"><button class="btn btn-danger btn-block btn-sm" id="btn-stop"><i class="fas fa-stop mr-1"></i> Parar</button></div>
                </div>
            </div>
        </div>

        <!-- CONFIGURAÇÃO -->
        <div class="card card-outline card-warning shadow-sm">
            <div class="card-header bg-warning text-dark">
                <h3 class="card-title"><i class="fas fa-cog mr-2"></i> Configuração</h3>
            </div>
            <form action="{{ route('admin.scraper.update') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Modo</label>
                        <select name="scraper_mode" class="form-control form-control-sm">
                            <option value="master" {{ ($config->scraper_mode ?? 'master') == 'master' ? 'selected' : '' }}>Master (Servidor Principal)</option>
                            <option value="client" {{ ($config->scraper_mode ?? '') == 'client' ? 'selected' : '' }}>Client (Recebe de um Master)</option>
                        </select>
                    </div>
                    <div id="client-fields" style="display:{{ ($config->scraper_mode ?? 'master') == 'client' ? 'block' : 'none' }}">
                        <div class="form-group"><label class="font-weight-bold">URL do Master</label><input type="url" name="scraper_url" class="form-control form-control-sm" value="{{ $config->scraper_url ?? '' }}" placeholder="https://exemplo.com/api/scraper"></div>
                        <div class="form-group mb-0"><label class="font-weight-bold">Token</label><input type="text" name="scraper_token" class="form-control form-control-sm" value="{{ $config->scraper_token ?? '' }}"></div>
                    </div>
                </div>
                <div class="card-footer bg-light"><button type="submit" class="btn btn-warning btn-block btn-sm"><i class="fas fa-save mr-1"></i> Salvar</button></div>
            </form>
        </div>

        <!-- ESTATÍSTICAS -->
        <div class="card card-outline card-info shadow-sm">
            <div class="card-header bg-info text-white">
                <h3 class="card-title"><i class="fas fa-chart-bar mr-2"></i> Estatísticas</h3>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-4">
                        <div class="info-box bg-info"><span class="info-box-icon"><i class="fas fa-futbol"></i></span><div class="info-box-content"><span class="info-box-text">Jogos</span><span class="info-box-number">{{ $stats['total_matches'] ?? 0 }}</span></div></div>
                    </div>
                    <div class="col-4">
                        <div class="info-box bg-success"><span class="info-box-icon"><i class="fas fa-trophy"></i></span><div class="info-box-content"><span class="info-box-text">Ligas</span><span class="info-box-number">{{ $stats['active_leagues'] ?? 0 }}</span></div></div>
                    </div>
                    <div class="col-4">
                        <div class="info-box bg-warning"><span class="info-box-icon"><i class="fas fa-clock"></i></span><div class="info-box-content"><span class="info-box-text">Última Sync</span><span class="info-box-text small">{{ $stats['last_update'] ?? 'N/A' }}</span></div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- COLUNA DIREITA -->
    <div class="col-md-8">
        <!-- SINCRONIZAÇÃO -->
        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between">
                <h3 class="card-title"><i class="fas fa-sync-alt mr-2"></i> Sincronização</h3>
                <button class="btn btn-sm btn-light" id="btn-sync"><i class="fas fa-cloud-download-alt mr-1"></i> Sync Agora</button>
            </div>
            <div class="card-body">
                <small class="text-muted">Força sincronização dos arquivos JSON do scraper para o banco de dados.</small>
            </div>
        </div>

        <!-- MERCADOS (FILTRO) -->
        <div class="card card-outline card-secondary shadow-sm">
            <div class="card-header bg-secondary text-white d-flex justify-content-between">
                <h3 class="card-title"><i class="fas fa-filter mr-2"></i> Filtro de Mercados</h3>
                <button class="btn btn-sm btn-light" id="btn-save-markets"><i class="fas fa-save"></i> Salvar</button>
            </div>
            <div class="card-body" style="max-height:250px; overflow-y:auto;">
                <div class="row">
                    @foreach($allMarkets as $market)
                        <div class="col-md-4">
                            <div class="custom-control custom-checkbox mb-1">
                                <input type="checkbox" class="market-cb" id="sm{{ $loop->index }}" value="{{ $market }}" {{ in_array($market, $activeMarkets) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="sm{{ $loop->index }}" style="font-size:0.8rem;">{{ $market }}</label>
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

@section('js')
<script>
$(document).ready(function() {
    loadLogs();

    $('#btn-refresh-logs').click(loadLogs);

    $('select[name="scraper_mode"]').change(function() {
        $('#client-fields').toggle($(this).val() === 'client');
    });

    $('#btn-start').click(function() {
        var btn = $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Iniciando...');
        $.post('{{ route("admin.scraper.start") }}', {_token: $('meta[name="csrf-token"]').attr('content')}, function(r) {
            toastr.success(r.message); updateStatus(true);
        }).fail(function() { toastr.error('Erro ao iniciar.'); btn.prop('disabled', false).html('<i class="fas fa-play mr-1"></i> Iniciar'); });
    });

    $('#btn-stop').click(function() {
        var btn = $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Parando...');
        $.post('{{ route("admin.scraper.stop") }}', {_token: $('meta[name="csrf-token"]').attr('content')}, function(r) {
            toastr.info(r.message); updateStatus(false);
        }).fail(function() { toastr.error('Erro ao parar.'); btn.prop('disabled', false).html('<i class="fas fa-stop mr-1"></i> Parar'); });
    });

    $('#btn-sync').click(function() {
        var btn = $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Sincronizando...');
        $.post('{{ route("admin.scraper.sync") }}', {_token: $('meta[name="csrf-token"]').attr('content')}, function(r) {
            toastr.success(r.message); btn.prop('disabled', false).html('<i class="fas fa-cloud-download-alt mr-1"></i> Sync Agora');
        }).fail(function() { toastr.error('Erro na sincronização.'); btn.prop('disabled', false).html('<i class="fas fa-cloud-download-alt mr-1"></i> Sync Agora'); });
    });

    $('#btn-save-markets').click(function() {
        var markets = [];
        $('.market-cb:checked').each(function() { markets.push($(this).val()); });
        $.post('{{ route("admin.scraper.save-markets") }}', {
            _token: $('meta[name="csrf-token"]').attr('content'),
            markets: markets
        }, function(r) { toastr.success(r.message); });
    });

    function updateStatus(running) {
        $('#scraper-badge').removeClass('badge-success badge-secondary').addClass(running ? 'badge-success' : 'badge-secondary').text(running ? 'Rodando' : 'Parado');
        $('#btn-start').prop('disabled', running);
        $('#btn-stop').prop('disabled', !running);
    }

    function loadLogs() {
        $.getJSON('{{ route("admin.scraper.logs") }}', function(d) {
            $('#logs-content').text(d.logs || 'Nenhum log encontrado.');
        });
    }
});
</script>
@stop
