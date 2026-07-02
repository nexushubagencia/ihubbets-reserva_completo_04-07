@extends('gerente.layouts.app')
@section('title', 'Bilhetes')
@section('content_header')
    <h1><i class="fas fa-receipt text-primary mr-2"></i> Bilhetes dos Cambistas</h1>
@stop

@section('content')
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-dark text-white border-0 py-3">
        <h3 class="card-title font-weight-bold"><i class="fas fa-filter text-primary mr-2"></i> Filtros</h3>
    </div>
    <div class="card-body bg-white pt-0">
        <div class="row">
            <div class="col-md-2 mb-3">
                <label class="text-muted small font-weight-bold">CAMBISTA:</label>
                <select id="filtro-cambista" class="form-control form-control-sm">
                    <option value="Todos">Todos</option>
                    @foreach($cambistas as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 mb-3">
                <label class="text-muted small font-weight-bold">STATUS:</label>
                <select id="filtro-status" class="form-control form-control-sm">
                    <option value="Todos">Todos</option>
                    <option value="Aberto">Aberto</option>
                    <option value="Ganhou">Ganhou</option>
                    <option value="Venceu">Venceu</option>
                    <option value="Perdeu">Perdeu</option>
                    <option value="Cancelado">Cancelado</option>
                </select>
            </div>
            <div class="col-md-2 mb-3">
                <label class="text-muted small font-weight-bold">ESPORTE:</label>
                <select id="filtro-esporte" class="form-control form-control-sm">
                    <option value="Todos">Todos</option>
                    <option value="Futebol">Futebol</option>
                    <option value="Basquete">Basquete</option>
                    <option value="Tenis">Tênis</option>
                    <option value="Volei">Vôlei</option>
                </select>
            </div>
            <div class="col-md-2 mb-3">
                <label class="text-muted small font-weight-bold">VALOR MÍN:</label>
                <input type="number" id="filtro-valor-min" class="form-control form-control-sm" placeholder="R$ 10">
            </div>
            <div class="col-md-2 mb-3">
                <label class="text-muted small font-weight-bold">VALOR MÁX:</label>
                <input type="number" id="filtro-valor-max" class="form-control form-control-sm" placeholder="R$ 1000">
            </div>
            <div class="col-md-2 mb-3">
                <label class="text-muted small font-weight-bold">CUPOM:</label>
                <input type="text" id="filtro-cupom" class="form-control form-control-sm" placeholder="Ex: ABC123">
            </div>
            <div class="col-md-2 mb-3">
                <label class="text-muted small font-weight-bold">DE:</label>
                <input type="date" id="filtro-data1" class="form-control form-control-sm">
            </div>
            <div class="col-md-2 mb-3">
                <label class="text-muted small font-weight-bold">ATÉ:</label>
                <input type="date" id="filtro-data2" class="form-control form-control-sm">
            </div>
            <div class="col-md-2 mb-3">
                <label class="text-muted small font-weight-bold">CLIENTE:</label>
                <input type="text" id="filtro-cliente" class="form-control form-control-sm" placeholder="Nome do cliente">
            </div>
            <div class="col-md-4 mb-3 d-flex align-items-end">
                <button class="btn btn-success btn-sm px-4 mr-2 font-weight-bold" onclick="searchBilhetes()">
                    <i class="fas fa-search mr-1"></i> PESQUISAR
                </button>
                <button class="btn btn-warning btn-sm px-4 font-weight-bold text-white" onclick="limparFiltros()">
                    <i class="fas fa-trash-alt mr-1"></i> LIMPAR
                </button>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm mb-0" id="tabela-bilhetes">
                <thead class="bg-dark text-white">
                    <tr style="font-size: 0.78rem; text-transform: uppercase;">
                        <th class="px-3 py-2">Cupom</th>
                        <th class="py-2">Data</th>
                        <th class="py-2 text-center">Status</th>
                        <th class="py-2 text-right">Apostado</th>
                        <th class="py-2 text-right">Retorno</th>
                        <th class="py-2">Vendedor</th>
                        <th class="py-2">Cliente</th>
                        <th class="py-2 text-center">Ações</th>
                    </tr>
                </thead>
                <tbody id="bilhetes-tbody">
                </tbody>
                <tfoot id="bilhetes-tfoot" class="bg-light font-weight-bold" style="display:none;">
                    <tr>
                        <td colspan="3" class="px-3 py-3">TOTAL PERÍODO (<span id="total-count">0</span>)</td>
                        <td class="text-right py-3" id="total-apostado">R$ 0,00</td>
                        <td class="text-right py-3" id="total-retorno">R$ 0,00</td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div id="loading-bilhetes" class="text-center py-5" style="display:none;">
            <i class="fas fa-circle-notch fa-spin fa-3x text-primary mb-3"></i>
            <p class="text-muted">Carregando bilhetes...</p>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-ver-bilhete">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-receipt mr-2"></i> Detalhes do Bilhete</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body" id="modal-bilhete-body">
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .st-ganhou, .st-venceu { background-color: #008D4C; color: #fff; font-weight: 700; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; }
    .st-perdeu { background-color: #FF0000; color: #fff; font-weight: 700; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; }
    .st-aberto { background-color: #00C0EF; color: #fff; font-weight: 700; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; }
    .st-cancelado { background-color: #E69222; color: #fff; font-weight: 700; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; }
    #bilhetes-tbody td { vertical-align: middle; padding: 10px 8px; font-size: 0.82rem; border-bottom: 1px solid #f1f5f9; }
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        var today = new Date().toISOString().split('T')[0];
        $('#filtro-data1, #filtro-data2').val(today);
        searchBilhetes();
    });

    function fm(n) { return "R$ " + parseFloat(n||0).toFixed(2).replace('.', ',').replace(/(\d)(?=(\d{3})+\,)/g, "$1."); }
    function fd(ds) {
        if (!ds) return '-';
        var d = new Date(ds);
        return d.getDate().toString().padStart(2,'0') + '/' + (d.getMonth()+1).toString().padStart(2,'0') + ' ' + d.getHours().toString().padStart(2,'0') + ':' + d.getMinutes().toString().padStart(2,'0');
    }
    function getStatusClass(s) {
        if (!s) return 'st-aberto';
        s = s.toString().toLowerCase().trim();
        if (s === 'ganhou' || s === 'venceu') return 'st-ganhou';
        if (s === 'perdeu') return 'st-perdeu';
        if (s === 'cancelado') return 'st-cancelado';
        return 'st-aberto';
    }

    function searchBilhetes() {
        $('#bilhetes-tbody').empty();
        $('#bilhetes-tfoot').hide();
        $('#loading-bilhetes').show();

        $.post('{{ route("gerente.bilhetes.search") }}', {
            _token: $('meta[name="csrf-token"]').attr('content'),
            cambista: $('#filtro-cambista').val(),
            esporte: $('#filtro-esporte').val(),
            status: $('#filtro-status').val(),
            valor_min: $('#filtro-valor-min').val(),
            valor_max: $('#filtro-valor-max').val(),
            cliente: $('#filtro-cliente').val(),
            cupom: $('#filtro-cupom').val(),
            date1: $('#filtro-data1').val(),
            date2: $('#filtro-data2').val()
        }, function(res) {
            $('#loading-bilhetes').hide();
            var list = res.bilhetes;
            var totals = res.totals;

            if (!list || list.length === 0) {
                $('#bilhetes-tbody').html('<tr><td colspan="8" class="text-center text-muted py-4">Nenhum bilhete encontrado</td></tr>');
                return;
            }

            list.forEach(function(b) {
                var row = '<tr>' +
                    '<td class="px-3 font-weight-bold">' + b.cupom + '</td>' +
                    '<td class="text-muted small">' + fd(b.created_at) + '</td>' +
                    '<td class="text-center"><span class="' + getStatusClass(b.status) + '">' + b.status + '</span></td>' +
                    '<td class="text-right">' + fm(b.valor_apostado) + '</td>' +
                    '<td class="text-right font-weight-bold">' + fm(b.retorno_possivel) + '</td>' +
                    '<td>' + (b.vendedor || '-') + '</td>' +
                    '<td>' + (b.cliente || '-') + '</td>' +
                    '<td class="text-center">' +
                        '<div class="btn-group btn-group-sm">' +
                            '<button class="btn btn-primary" title="Ver" onclick="verBilhete(' + b.id + ')"><i class="fas fa-eye"></i></button>' +
                            (b.status !== 'Cancelado' ? '<button class="btn btn-danger" title="Cancelar" onclick="cancelarBilhete(' + b.id + ', \'' + b.cupom + '\')"><i class="fas fa-times"></i></button>' : '') +
                        '</div>' +
                    '</td>' +
                '</tr>';
                $('#bilhetes-tbody').append(row);
            });

            $('#total-count').text(totals.count);
            $('#total-apostado').text(fm(totals.apostado));
            $('#total-retorno').text(fm(totals.retorno));
            $('#bilhetes-tfoot').show();
        }).fail(function() {
            $('#loading-bilhetes').hide();
            toastr.error('Erro ao buscar bilhetes.');
        });
    }

    function limparFiltros() {
        $('#filtro-cambista').val('Todos');
        $('#filtro-status').val('Todos');
        $('#filtro-esporte').val('Todos');
        $('#filtro-valor-min, #filtro-valor-max, #filtro-cupom, #filtro-cliente').val('');
        var today = new Date().toISOString().split('T')[0];
        $('#filtro-data1, #filtro-data2').val(today);
        searchBilhetes();
    }

    function verBilhete(id) {
        $.get('/gerente/bilhetes/' + id, function(b) {
            var html = '<table class="table table-borderless" style="font-size:0.85rem;">';
            html += '<tr><td class="font-weight-bold text-muted">Cupom</td><td class="font-weight-bold">' + (b.codigo_bilhete || b.cupom || '-') + '</td></tr>';
            html += '<tr><td class="font-weight-bold text-muted">Status</td><td><span class="badge badge-info">' + b.status + '</span></td></tr>';
            html += '<tr><td class="font-weight-bold text-muted">Tipo</td><td>' + (b.tipo || 'Simples') + '</td></tr>';
            html += '<tr><td class="font-weight-bold text-muted">Data</td><td>' + fd(b.created_at) + '</td></tr>';
            html += '<tr><td class="font-weight-bold text-muted">Cliente</td><td>' + (b.cliente || '-') + '</td></tr>';
            html += '<tr><td class="font-weight-bold text-muted">Apostado</td><td class="font-weight-bold">' + fm(b.valor_apostado) + '</td></tr>';
            html += '<tr><td class="font-weight-bold text-muted">Retorno</td><td class="font-weight-bold text-success">' + fm(b.retorno_possivel) + '</td></tr>';
            html += '</table>';
            $('#modal-bilhete-body').html(html);
            $('#modal-ver-bilhete').modal('show');
        });
    }

    function cancelarBilhete(id, cupom) {
        if (confirm('Deseja cancelar o bilhete ' + cupom + '?')) {
            $.post('/gerente/bilhetes/' + id + '/cancelar', {
                _token: $('meta[name="csrf-token"]').attr('content')
            }, function(res) {
                toastr.success(res.message || 'Bilhete cancelado!');
                searchBilhetes();
            }).fail(function(xhr) {
                toastr.error(xhr.responseJSON?.message || 'Erro ao cancelar.');
            });
        }
    }
</script>
@stop
