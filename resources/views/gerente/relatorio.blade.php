@extends('gerente.layouts.app')
@section('title', 'Relatório')
@section('content_header')
    <h1><i class="fas fa-chart-bar text-primary mr-2"></i> Relatório por Cambista</h1>
@stop

@section('content')
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-dark text-white border-0 py-3">
        <h3 class="card-title font-weight-bold"><i class="fas fa-filter text-primary mr-2"></i> Filtros</h3>
    </div>
    <div class="card-body bg-white">
        <div class="row align-items-end">
            <div class="col-md-3">
                <label class="text-muted small font-weight-bold">CAMBISTA:</label>
                <select id="rel-cambista" class="form-control form-control-sm">
                    <option value="Todos">Todos</option>
                    @foreach($cambistas as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="text-muted small font-weight-bold">DATA INICIAL:</label>
                <input type="date" id="rel-date1" class="form-control form-control-sm" value="{{ $today }}">
            </div>
            <div class="col-md-3">
                <label class="text-muted small font-weight-bold">DATA FINAL:</label>
                <input type="date" id="rel-date2" class="form-control form-control-sm" value="{{ $today }}">
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary btn-block btn-sm font-weight-bold" onclick="buscarRelatorio()">
                    <i class="fas fa-search mr-1"></i> GERAR RELATÓRIO
                </button>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="bg-primary text-white">
                    <tr style="font-size: 0.78rem; text-transform: uppercase;">
                        <th class="px-3 py-3">Cambista</th>
                        <th class="py-3 text-center">Qtd. Apostas</th>
                        <th class="py-3 text-right">Entradas</th>
                        <th class="py-3 text-right">Saídas (Prêmios)</th>
                        <th class="py-3 text-right">Comissões</th>
                        <th class="py-3 text-right">Líquido</th>
                    </tr>
                </thead>
                <tbody id="rel-tbody">
                    <tr><td colspan="6" class="text-center text-muted py-4">Clique em "Gerar Relatório" para visualizar</td></tr>
                </tbody>
                <tfoot id="rel-tfoot" style="display:none;">
                    <tr class="bg-dark text-white font-weight-bold" style="font-size: 0.85rem;">
                        <td class="px-3">TOTAL</td>
                        <td class="text-center" id="rel-total-qtd">0</td>
                        <td class="text-right" id="rel-total-ent">R$ 0,00</td>
                        <td class="text-right" id="rel-total-sai">R$ 0,00</td>
                        <td class="text-right" id="rel-total-com">R$ 0,00</td>
                        <td class="text-right" id="rel-total-liq">R$ 0,00</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div id="rel-loading" class="text-center py-5" style="display:none;">
            <i class="fas fa-circle-notch fa-spin fa-3x text-primary mb-3"></i>
            <p class="text-muted">Gerando relatório...</p>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
    function fm(n) { return "R$ " + parseFloat(n||0).toFixed(2).replace('.', ',').replace(/(\d)(?=(\d{3})+\,)/g, "$1."); }

    function buscarRelatorio() {
        $('#rel-tbody').empty();
        $('#rel-tfoot').hide();
        $('#rel-loading').show();

        $.post('{{ route("gerente.relatorio.filtrar") }}', {
            _token: $('meta[name="csrf-token"]').attr('content'),
            cambista: $('#rel-cambista').val(),
            date1: $('#rel-date1').val(),
            date2: $('#rel-date2').val()
        }, function(res) {
            $('#rel-loading').hide();
            var list = res.cambistas;
            var totals = res.totals;

            if (!list || list.length === 0) {
                $('#rel-tbody').html('<tr><td colspan="6" class="text-center text-muted py-4">Nenhum dado encontrado para o período</td></tr>');
                return;
            }

            list.forEach(function(c) {
                var liqClass = c.total >= 0 ? 'text-success' : 'text-danger';
                var row = '<tr style="font-size:0.85rem;">' +
                    '<td class="px-3 font-weight-bold">' + c.name + '</td>' +
                    '<td class="text-center">' + c.quantidade + '</td>' +
                    '<td class="text-right text-success font-weight-bold">' + fm(c.entradas) + '</td>' +
                    '<td class="text-right text-danger font-weight-bold">' + fm(c.saidas) + '</td>' +
                    '<td class="text-right font-weight-bold">' + fm(c.comissoes) + '</td>' +
                    '<td class="text-right font-weight-bold ' + liqClass + '">' + fm(c.total) + '</td>' +
                '</tr>';
                $('#rel-tbody').append(row);
            });

            $('#rel-total-qtd').text(totals.quantidade);
            $('#rel-total-ent').text(fm(totals.entradas));
            $('#rel-total-sai').text(fm(totals.saidas));
            $('#rel-total-com').text(fm(totals.comissoes));
            $('#rel-total-liq').text(fm(totals.total));
            $('#rel-tfoot').show();
        }).fail(function() {
            $('#rel-loading').hide();
            toastr.error('Erro ao gerar relatório.');
        });
    }
</script>
@stop
