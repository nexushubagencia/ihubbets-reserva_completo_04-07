@extends('admin.layouts.app')

@section('title', 'API-Football | IHUB BETS')

@section('content_header')
    <h1><i class="fas fa-futbol text-success"></i> API-Football <small class="text-muted">Configurações & Sincronização</small></h1>
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
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <h5><i class="icon fas fa-exclamation-triangle"></i> Erro!</h5>
            {{ session('error') }}
        </div>
    @endif

    <div class="row">
        <div class="col-md-4">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title"><i class="fas fa-key mr-2"></i> Credenciais da API</h3>
                </div>
                <form action="{{ route('admin.api-football.update') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label class="font-weight-bold">API Key</label>
                            <input type="text" name="api_key" class="form-control form-control-sm"
                                   value="{{ $settings->api_key ?? '' }}"
                                   placeholder="Insira sua chave da API-Football">
                            <small class="text-muted">Obtenha em <a href="https://www.api-football.com/" target="_blank">api-football.com</a></small>
                        </div>
                        <div class="form-group mb-0">
                            <label class="font-weight-bold">Status da Conexão</label>
                            <div id="api-status" class="p-2 rounded text-center font-weight-bold
                                {{ ($settings->api_key ?? '') ? 'bg-success text-white' : 'bg-secondary text-white' }}">
                                <i class="fas fa-{{ ($settings->api_key ?? '') ? 'check-circle' : 'times-circle' }} mr-1"></i>
                                {{ ($settings->api_key ?? '') ? 'Configurado' : 'Não Configurado' }}
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-light">
                        <button type="submit" class="btn btn-primary btn-block btn-sm">
                            <i class="fas fa-save mr-1"></i> Salvar API Key
                        </button>
                    </div>
                </form>
            </div>

            <div class="card card-outline card-warning shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h3 class="card-title"><i class="fas fa-exchange-alt mr-2"></i> Provedor de Dados</h3>
                </div>
                <form method="POST" action="{{ route('admin.api-football.provider') }}">
                    @csrf
                    <div class="card-body">
                        <p class="text-muted small">Selecione qual API usar como fonte de dados:</p>
                        <div class="form-group mb-2">
                            <div class="custom-control custom-radio mb-2">
                                <input type="radio" id="provider-api-football" name="provider" value="api-football"
                                       class="custom-control-input" {{ $activeProvider === 'api-football' ? 'checked' : '' }}>
                                <label class="custom-control-label font-weight-bold" for="provider-api-football">
                                    API-Football <span class="text-muted small">(api-sports.io)</span>
                                </label>
                                <small class="d-block text-muted pl-4">Grátis: 100 req/dia. Tem odds.</small>
                            </div>
                            <div class="custom-control custom-radio">
                                <input type="radio" id="provider-bets-api" name="provider" value="bets-api"
                                       class="custom-control-input" {{ $activeProvider === 'bets-api' ? 'checked' : '' }}>
                                <label class="custom-control-label font-weight-bold" for="provider-bets-api">
                                    BetsAPI <span class="text-muted small">(betsapi.com)</span>
                                </label>
                                <small class="d-block text-muted pl-4">Premium: muitos mercados. Token atual expirado (403).</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-light">
                        <button type="submit" class="btn btn-warning btn-block btn-sm">
                            <i class="fas fa-save mr-1"></i> Alterar Provedor
                        </button>
                    </div>
                </form>
            </div>

            <div class="card card-outline card-success shadow-sm">
                <div class="card-header bg-success text-white">
                    <h3 class="card-title"><i class="fas fa-sync-alt mr-2"></i> Sincronização</h3>
                </div>
                <div class="card-body text-center">
                    <p class="text-muted small">Sincronizar jogos e odds agora.</p>
                    <button class="btn btn-success btn-block" id="btn-sync-now">
                        <i class="fas fa-cloud-download-alt mr-1"></i> Sincronizar Agora
                    </button>
                    <div id="sync-status" class="mt-3" style="display:none;">
                        <div class="spinner-border text-success" role="status">
                            <span class="sr-only">Sincronizando...</span>
                        </div>
                        <p class="text-muted small mt-2">Sincronizando dados...</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card card-outline card-info shadow-sm">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h3 class="card-title"><i class="fas fa-trophy mr-2"></i> Ligas Disponíveis</h3>
                    <button class="btn btn-sm btn-light" id="btn-toggle-all-leagues">
                        <i class="fas fa-check-double mr-1"></i> Selecionar Todas
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0" id="table-leagues">
                            <thead class="bg-light text-muted" style="font-size: 0.8rem;">
                                <tr>
                                    <th>ID</th>
                                    <th>Liga</th>
                                    <th>País</th>
                                    <th class="text-center">Ativa</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($leagues as $league)
                                    <tr>
                                        <td class="text-muted">{{ $league->api_id }}</td>
                                        <td class="font-weight-bold">{{ $league->name }}</td>
                                        <td>{{ $league->country }}</td>
                                        <td class="text-center">
                                            <label class="switch mb-0">
                                                <input type="checkbox" class="league-toggle"
                                                       data-id="{{ $league->id }}"
                                                       {{ $league->is_active ? 'checked' : '' }}>
                                                <span class="slider round"></span>
                                            </label>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">
                                            <i class="fas fa-info-circle fa-2x mb-2 d-block"></i>
                                            Nenhuma liga encontrada. Clique em "Sincronizar Agora" para buscar.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .switch { position: relative; display: inline-block; width: 40px; height: 22px; }
    .switch input { opacity: 0; width: 0; height: 0; }
    .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .3s; border-radius: 22px; }
    .slider:before { position: absolute; content: ""; height: 16px; width: 16px; left: 3px; bottom: 3px; background-color: white; transition: .3s; border-radius: 50%; }
    input:checked + .slider { background-color: #28a745; }
    input:checked + .slider:before { transform: translateX(18px); }
    .table td, .table th { vertical-align: middle; }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    $('#btn-sync-now').click(function() {
        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Sincronizando...');
        $('#sync-status').show();

        $.ajax({
            url: '{{ route("admin.api-football.sync") }}',
            type: 'POST',
            data: { _token: $('meta[name="csrf-token"]').attr('content') },
            success: function(response) {
                toastr.success(response.message || 'Sincronização concluída!');
                setTimeout(function() { location.reload(); }, 1500);
            },
            error: function() {
                toastr.error('Erro ao sincronizar. Verifique a API Key.');
                btn.prop('disabled', false).html('<i class="fas fa-cloud-download-alt mr-1"></i> Sincronizar Agora');
                $('#sync-status').hide();
            }
        });
    });

    $(document).on('change', '.league-toggle', function() {
        var leagueId = $(this).data('id');
        var isActive = $(this).is(':checked') ? 1 : 0;
        $.post('{{ route("admin.api-football.toggle-league") }}', {
            _token: $('meta[name="csrf-token"]').attr('content'),
            league_id: leagueId,
            is_active: isActive
        }, function(response) {
            toastr.success(response.message || 'Liga atualizada!');
        }).fail(function() {
            toastr.error('Erro ao atualizar liga.');
        });
    });

    $('#btn-toggle-all-leagues').click(function() {
        var allChecked = $('.league-toggle:checked').length === $('.league-toggle').length;
        $('.league-toggle').prop('checked', !allChecked).trigger('change');
    });
});
</script>
@stop
