@extends('admin.layouts.app')

@section('title', 'Scraper | IHUB BETS')

@section('content_header')
    <h1><i class="fas fa-robot text-warning"></i> Scraper <small class="text-muted">Configurações & Controle</small></h1>
@stop

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <h5><i class="icon fas fa-check"></i> Sucesso!</h5>
            {{ session('success') }}
        </div>
    @endif

    <div class="row">
        <div class="col-md-5">
            <div class="card card-outline card-warning shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-cog mr-2"></i> Configurações do Scraper</h3>
                </div>
                <form action="{{ route('admin.scraper.update') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label class="font-weight-bold">Modo de Operação</label>
                            <select name="mode" class="form-control form-control-sm" id="scraper-mode">
                                <option value="master" {{ ($settings->mode ?? 'master') == 'master' ? 'selected' : '' }}>
                                    Master (Servidor Principal)
                                </option>
                                <option value="client" {{ ($settings->mode ?? '') == 'client' ? 'selected' : '' }}>
                                    Client (Cliente Remoto)
                                </option>
                            </select>
                            <small class="text-muted">Master coleta dados. Client recebe dados de um Master.</small>
                        </div>

                        <div id="client-config" style="display: {{ ($settings->mode ?? 'master') == 'client' ? 'block' : 'none' }};">
                            <hr>
                            <h6 class="text-muted font-weight-bold mb-3"><i class="fas fa-link mr-1"></i> Configuração do Client</h6>
                            <div class="form-group">
                                <label class="font-weight-bold">URL do Master</label>
                                <input type="url" name="master_url" class="form-control form-control-sm"
                                       value="{{ $settings->master_url ?? '' }}"
                                       placeholder="https://exemplo.com/api/scraper">
                            </div>
                            <div class="form-group mb-0">
                                <label class="font-weight-bold">Token de Acesso</label>
                                <input type="text" name="master_token" class="form-control form-control-sm"
                                       value="{{ $settings->master_token ?? '' }}"
                                       placeholder="Token de autenticação">
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-light">
                        <button type="submit" class="btn btn-warning btn-block btn-sm font-weight-bold">
                            <i class="fas fa-save mr-1"></i> Salvar Configurações
                        </button>
                    </div>
                </form>
            </div>

            <div class="card card-outline card-danger shadow-sm">
                <div class="card-header bg-danger text-white">
                    <h3 class="card-title"><i class="fas fa-power-off mr-2"></i> Controle do Scraper</h3>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <span class="font-weight-bold">Status:</span>
                            <span id="scraper-status-badge" class="badge badge-{{ ($settings->is_running ?? false) ? 'success' : 'secondary' }} ml-2">
                                {{ ($settings->is_running ?? false) ? 'Rodando' : 'Parado' }}
                            </span>
                        </div>
                        <div id="scraper-status-icon" class="text-{{ ($settings->is_running ?? false) ? 'success' : 'muted' }}">
                            <i class="fas fa-circle fa-2x {{ ($settings->is_running ?? false) ? 'fa-beat' : '' }}"></i>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <button class="btn btn-success btn-block btn-sm" id="btn-start-scraper"
                                    {{ ($settings->is_running ?? false) ? 'disabled' : '' }}>
                                <i class="fas fa-play mr-1"></i> Iniciar
                            </button>
                        </div>
                        <div class="col-6">
                            <button class="btn btn-danger btn-block btn-sm" id="btn-stop-scraper"
                                    {{ !($settings->is_running ?? false) ? 'disabled' : '' }}>
                                <i class="fas fa-stop mr-1"></i> Parar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h3 class="card-title"><i class="fas fa-sync-alt mr-2"></i> Sincronização Manual</h3>
                    <button class="btn btn-sm btn-light" id="btn-sync-now">
                        <i class="fas fa-cloud-download-alt mr-1"></i> Sync Agora
                    </button>
                </div>
                <div class="card-body">
                    <div class="alert alert-info border-0 mb-3" style="background: linear-gradient(135deg, #e0f2fe 0%, #f0f9ff 100%); color: #0369a1;">
                        <small><i class="fas fa-info-circle mr-1"></i> Forçar uma sincronização imediata de todos os dados do scraper.</small>
                    </div>
                    <div id="sync-result" style="display:none;">
                        <div class="text-center py-3">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Sincronizando...</span>
                            </div>
                            <p class="text-muted small mt-2">Buscando dados...</p>
                        </div>
                    </div>
                    @if($settings->last_sync ?? null)
                        <p class="text-muted small mb-0"><i class="fas fa-clock mr-1"></i> Última sincronização: {{ $settings->last_sync }}</p>
                    @endif
                </div>
            </div>

            <div class="card card-outline card-secondary shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h3 class="card-title"><i class="fas fa-chart-bar mr-2"></i> Estatísticas do Scraper</h3>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="info-box bg-info">
                                <span class="info-box-icon"><i class="fas fa-futbol"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Jogos Sincronizados</span>
                                    <span class="info-box-number" id="stat-matches">{{ $stats->total_matches ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="fas fa-trophy"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Ligas Ativas</span>
                                    <span class="info-box-number" id="stat-leagues">{{ $stats->active_leagues ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="info-box bg-warning">
                                <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Última Atualização</span>
                                    <span class="info-box-text small" id="stat-last-update">{{ $stats->last_update ?? 'N/A' }}</span>
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
$(document).ready(function() {
    $('#scraper-mode').change(function() {
        if ($(this).val() === 'client') {
            $('#client-config').slideDown();
        } else {
            $('#client-config').slideUp();
        }
    });

    $('#btn-start-scraper').click(function() {
        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Iniciando...');
        $.post('{{ route("admin.scraper.start") }}', {
            _token: $('meta[name="csrf-token"]').attr('content')
        }, function(response) {
            toastr.success(response.message || 'Scraper iniciado!');
            updateStatus(true);
        }).fail(function() {
            toastr.error('Erro ao iniciar scraper.');
            btn.prop('disabled', false).html('<i class="fas fa-play mr-1"></i> Iniciar');
        });
    });

    $('#btn-stop-scraper').click(function() {
        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Parando...');
        $.post('{{ route("admin.scraper.stop") }}', {
            _token: $('meta[name="csrf-token"]').attr('content')
        }, function(response) {
            toastr.info(response.message || 'Scraper parado!');
            updateStatus(false);
        }).fail(function() {
            toastr.error('Erro ao parar scraper.');
            btn.prop('disabled', false).html('<i class="fas fa-stop mr-1"></i> Parar');
        });
    });

    $('#btn-sync-now').click(function() {
        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Sincronizando...');
        $('#sync-result').show();
        $.post('{{ route("admin.scraper.sync") }}', {
            _token: $('meta[name="csrf-token"]').attr('content')
        }, function(response) {
            toastr.success(response.message || 'Sincronização concluída!');
            $('#sync-result').hide();
            btn.prop('disabled', false).html('<i class="fas fa-cloud-download-alt mr-1"></i> Sync Agora');
        }).fail(function() {
            toastr.error('Erro na sincronização.');
            $('#sync-result').hide();
            btn.prop('disabled', false).html('<i class="fas fa-cloud-download-alt mr-1"></i> Sync Agora');
        });
    });

    function updateStatus(running) {
        $('#scraper-status-badge')
            .removeClass('badge-success badge-secondary')
            .addClass(running ? 'badge-success' : 'badge-secondary')
            .text(running ? 'Rodando' : 'Parado');
        $('#scraper-status-icon')
            .removeClass('text-success text-muted')
            .addClass(running ? 'text-success' : 'text-muted')
            .find('i').toggleClass('fa-beat', running);
        $('#btn-start-scraper').prop('disabled', running);
        $('#btn-stop-scraper').prop('disabled', !running);
    }
});
</script>
@stop
