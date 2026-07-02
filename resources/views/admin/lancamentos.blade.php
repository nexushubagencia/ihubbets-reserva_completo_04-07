@extends('adminlte::page')

@section('title', 'Lançamentos Financeiros | IHUB BETS')

@section('content_header')
    <h1><i class="fas fa-file-invoice-dollar" style="color: #10b981;"></i> Lançamentos Financeiros <small class="text-muted">(Créditos e Débitos)</small></h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Dashboard Rápido -->
        <div class="col-md-3">
            <div class="small-box bg-info shadow-sm">
                <div class="inner">
                    <h3 id="total-creditos">R$ 0,00</h3>
                    <p>Total Créditos (Período)</p>
                </div>
                <div class="icon">
                    <i class="fas fa-plus-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-danger shadow-sm">
                <div class="inner">
                    <h3 id="total-debitos">R$ 0,00</h3>
                    <p>Total Débitos (Período)</p>
                </div>
                <div class="icon">
                    <i class="fas fa-minus-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-md-6 text-right pt-4">
             <button class="btn btn-success btn-lg shadow-sm" data-toggle="modal" data-target="#modal-novo-lancamento">
                <i class="fas fa-plus"></i> NOVO LANÇAMENTO MANUAL
             </button>
        </div>
    </div>

    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header">
            <h3 class="card-title font-weight-bold"><i class="fas fa-history"></i> Histórico de Movimentações</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" onclick="loadLancamentos()"><i class="fas fa-sync-alt"></i> Atualizar</button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped" id="table-lancamentos">
                    <thead class="bg-dark text-white">
                        <tr>
                            <th>DATA/HORA</th>
                            <th>COLABORADOR</th>
                            <th>TIPO</th>
                            <th>VALOR</th>
                            <th>DESCRIÇÃO</th>
                            <th class="text-center">AÇÕES</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-lancamentos">
                        <!-- Carregado via AJAX -->
                    </tbody>
                </table>
            </div>
            <div id="loading-lancamentos" class="text-center p-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Carregando...</span>
                </div>
                <p class="mt-2 text-muted">Buscando lançamentos...</p>
            </div>
        </div>
    </div>
</div>

<!-- Modal Novo Lançamento -->
<div class="modal fade" id="modal-novo-lancamento">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h4 class="modal-title font-weight-bold"><i class="fas fa-plus-circle"></i> Novo Lançamento Manual</h4>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="form-novo-lancamento">
                    @csrf
                    <div class="form-group">
                        <label>Colaborador (Cambista/Gerente)</label>
                        <select class="form-control select2" name="user_id" id="lan-user-id" required style="width: 100%;">
                            <option value="">Selecione o Colaborador...</option>
                            @foreach(\App\Models\User::where('site_id', config('tenant.site_id'))->whereIn('nivel', ['gerente', 'cambista'])->orderBy('name')->get() as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ strtoupper($u->nivel) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tipo</label>
                                <select class="form-control" name="tipo" id="lan-tipo" required>
                                    <option value="credito">CRÉDITO (+)</option>
                                    <option value="debito">DÉBITO (-)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Valor (R$)</label>
                                <input type="number" class="form-control" name="valor" id="lan-valor" step="0.01" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Carteira Destino</label>
                        <select class="form-control" name="carteira" id="lan-carteira">
                            <option value="simples">Saldo Esportes (Geral)</option>
                            <option value="casadinha">Saldo Casadinha (Bônus)</option>
                            <option value="loto">Saldo Loto</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Motivo / Descrição</label>
                        <textarea class="form-control" name="descricao" id="lan-descricao" rows="2" placeholder="Ex: Ajuste de prestação de contas" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success px-4 font-weight-bold" onclick="saveLancamento()">CONCLUIR LANÇAMENTO</button>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container--default .select2-selection--single { height: 38px; border: 1px solid #ced4da; }
    .badge-credito { background-color: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
    .badge-debito { background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('.select2').select2({
        dropdownParent: $('#modal-novo-lancamento')
    });
    loadLancamentos();
});

function fm(n) { return "R$ " + parseFloat(n||0).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2}); }

function loadLancamentos() {
    $('#loading-lancamentos').show();
    $('#tbody-lancamentos').empty();
    
    $.get('/admin/lancamentos-list', function(data) {
        $('#loading-lancamentos').hide();
        var totalC = 0, totalD = 0;
        
        if (data.length === 0) {
            $('#tbody-lancamentos').append('<tr><td colspan="6" class="text-center text-muted py-4">Nenhum lançamento encontrado.</td></tr>');
            return;
        }

        data.forEach(function(l) {
            var isCred = (l.tipo.toLowerCase() === 'crédito' || l.tipo.toLowerCase() === 'credito');
            var badge = isCred ? 'badge-credito' : 'badge-debito';
            var valorStr = (isCred ? '+ ' : '- ') + fm(l.valor);
            
            if (isCred) totalC += parseFloat(l.valor); else totalD += parseFloat(l.valor);

            $('#tbody-lancamentos').append(`
                <tr>
                    <td class="text-muted font-weight-bold">${l.created_at}</td>
                    <td><span class="badge badge-light p-2 border"><i class="fas fa-user text-primary mr-1"></i> ${l.colaborador}</span></td>
                    <td><span class="badge ${badge} p-2 px-3">${l.tipo.toUpperCase()}</span></td>
                    <td class="font-weight-bold ${isCred ? 'text-success' : 'text-danger'}">${valorStr}</td>
                    <td class="text-muted small">${l.descricao || '-'}</td>
                    <td class="text-center">
                        <button class="btn btn-outline-danger btn-sm" onclick="deleteLancamento(${l.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `);
        });

        $('#total-creditos').text(fm(totalC));
        $('#total-debitos').text(fm(totalD));
    });
}

function saveLancamento() {
    var userId = $('#lan-user-id').val();
    var valor = $('#lan-valor').val();
    if (!userId || !valor || valor <= 0) { toastr.warning('Preencha os campos corretamente!'); return; }

    $.post('/admin/add-lancamento', {
        _token: $('input[name="_token"]').val(),
        user_id: userId,
        tipo: $('#lan-tipo').val(),
        valor: valor,
        carteira: $('#lan-carteira').val(),
        descricao: $('#lan-descricao').val()
    }, function(res) {
        $('#modal-novo-lancamento').modal('hide');
        toastr.success('Lançamento realizado!');
        loadLancamentos();
    }).fail(function(xhr) {
        toastr.error('Erro: ' + (xhr.responseJSON ? xhr.responseJSON.error : 'Ocorreu um erro'));
    });
}

function deleteLancamento(id) {
    if (!confirm('Deseja realmente remover este lançamento? O saldo do colaborador será estornado.')) return;
    $.ajax({
        url: '/admin/lancamento/deletar/' + id,
        type: 'DELETE',
        data: { _token: $('input[name="_token"]').val() },
        success: function() {
            toastr.info('Lançamento removido e saldo estornado.');
            loadLancamentos();
        },
        error: function() { toastr.error('Erro ao deletar.'); }
    });
}
</script>
@stop
