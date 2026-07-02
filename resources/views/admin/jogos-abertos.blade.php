@extends('adminlte::page')

@section('title', 'Jogos em Aberto - IHUB V2')

@section('content_header')
    <h1><i class="fas fa-clock text-primary"></i> Jogos em Aberto <small class="text-muted">Inserção de Resultados</small></h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white border-0">
                    <h3 class="card-title fw-bold"><i class="fas fa-futbol me-2"></i> Aguardando Placar</h3>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>ID</th>
                                <th>Jogo</th>
                                <th>Categoria</th>
                                <th>Data/Hora</th>
                                <th class="text-center">Bilhetes</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody id="eventsList">
                            <!-- Carregado via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Resultado -->
<div class="modal fade" id="modalResult" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title font-weight-bold" id="modalTitle">Lançar Resultado</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="resultForm">
                <input type="hidden" name="event_id" id="event_id">
                <div class="modal-body">
                    <div class="row text-center mb-4">
                        <div class="col-5">
                            <h4 id="teamHome" class="font-weight-bold small">CASA</h4>
                            <input type="number" name="home_full" class="form-control form-control-lg text-center" value="0" min="0" required>
                        </div>
                        <div class="col-2 d-flex align-items-center justify-content-center">
                            <span class="h2">x</span>
                        </div>
                        <div class="col-5">
                            <h4 id="teamAway" class="font-weight-bold small">FORA</h4>
                            <input type="number" name="away_full" class="form-control form-control-lg text-center" value="0" min="0" required>
                        </div>
                    </div>
                    <hr>
                    <div class="row text-center">
                        <div class="col-12 mb-2"><label class="text-muted small uppercase">Placar 1º Tempo</label></div>
                        <div class="col-5">
                            <input type="number" name="home_half" class="form-control text-center" value="0" min="0" required>
                        </div>
                        <div class="col-2">x</div>
                        <div class="col-5">
                            <input type="number" name="away_half" class="form-control text-center" value="0" min="0" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-success px-4 font-weight-bold shadow-sm">FINALIZAR E LIQUIDAR</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        loadEvents();

        $('#resultForm').on('submit', function(e) {
            e.preventDefault();
            if(!confirm('Tem certeza? Esta ação liquidará todos os bilhetes deste jogo e não pode ser desfeita.')) return;

            const btn = $(this).find('button[type="submit"]');
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processando...');

            $.post('{{ route("admin.send-result") }}', $(this).serialize() + '&_token={{ csrf_token() }}')
            .done(function(res) {
                Swal.fire('Sucesso!', 'Resultado processado com sucesso.', 'success');
                $('#modalResult').modal('hide');
                loadEvents();
            })
            .fail(function(err) {
                Swal.fire('Erro', err.responseJSON.message || 'Erro ao processar', 'error');
            })
            .always(function() {
                btn.prop('disabled', false).text('FINALIZAR E LIQUIDAR');
            });
        });
    });

    function loadEvents() {
        $('#eventsList').html('<tr><td colspan="6" class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Carregando...</td></tr>');
        $.get('{{ route("admin.jogos.abertos") }}').done(function(data) {
            let html = '';
            data.forEach(ev => {
                html += `
                    <tr>
                        <td>${ev.id}</td>
                        <td class="font-weight-bold">${ev.title}</td>
                        <td><span class="badge badge-light border">${ev.category}</span></td>
                        <td>${new Date(ev.start_time).toLocaleString()}</td>
                        <td class="text-center"><span class="badge badge-info">${ev.open_bets} Bilhetes</span></td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-primary shadow-sm px-3" onclick="openModal(${ev.id}, '${ev.title}')">
                                <i class="fas fa-edit mr-1"></i> Resultado
                            </button>
                        </td>
                    </tr>
                `;
            });
            if(data.length == 0) html = '<tr><td colspan="6" class="text-center py-5">Nenhum jogo aguardando resultado.</td></tr>';
            $('#eventsList').html(html);
        });
    }

    function openModal(id, title) {
        $('#event_id').val(id);
        const teams = title.split(' x ');
        $('#teamHome').text(teams[0] || 'CASA');
        $('#teamAway').text(teams[1] || 'FORA');
        $('#modalResult').modal('show');
    }
</script>
@stop
