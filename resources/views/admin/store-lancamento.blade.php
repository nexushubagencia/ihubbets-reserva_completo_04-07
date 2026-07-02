@extends('adminlte::page')

@section('title', 'Ajustes de Saldo | IHUB BETS')

@section('content_header')
    <h1 class="text-dark font-weight-bold">
        <i class="fas fa-edit text-primary mr-2"></i> Lançamentos Manuais
        <small class="text-muted text-sm font-weight-normal">Ajustes de Crédito e Débito</small>
    </h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="card card-outline card-primary shadow-lg border-0 mb-4">
        <div class="card-header bg-white border-0 pt-4">
            <h3 class="card-title text-md font-weight-bold text-secondary">
                <i class="fas fa-plus-circle text-success mr-2"></i> Novo Ajuste Financeiro
            </h3>
        </div>
        <div class="card-body">
            <form id="form-add-lancamento">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <div class="form-group mb-0">
                            <label class="text-xs font-weight-bold text-muted uppercase">Selecionar Usuário <span class="text-danger">*</span></label>
                            <select class="form-control select2 shadow-none" id="lanc-user" style="width: 100%;">
                                <option value="">Pesquisar por nome ou ID...</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-0">
                            <label class="text-xs font-weight-bold text-muted uppercase">Tipo de Operação <span class="text-danger">*</span></label>
                            <select class="form-control shadow-none" id="lanc-tipo" style="height: 38px; border-radius: 4px;">
                                <option value="credito">➕ Adicionar Saldo</option>
                                <option value="debito">➖ Remover Saldo</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-0">
                            <label class="text-xs font-weight-bold text-muted uppercase">Valor (R$) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light border-right-0 text-xs font-weight-bold">R$</span>
                                </div>
                                <input type="number" class="form-control font-weight-bold" id="lanc-valor" step="0.01" placeholder="0.00" style="border-left: 0;">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-0">
                            <label class="text-xs font-weight-bold text-muted uppercase">Descrição / Motivo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="lanc-desc" placeholder="Ex: Ajuste de erro ou bônus especial...">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-primary btn-block font-weight-bold shadow-sm" onclick="addLancamento()" style="height: 38px;">
                            <i class="fas fa-paper-plane mr-1"></i> EXECUTAR
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-outline card-secondary shadow-lg border-0">
        <div class="card-header bg-white border-0 pt-4">
            <h3 class="card-title text-md font-weight-bold text-secondary">
                <i class="fas fa-history text-muted mr-2"></i> Histórico de Movimentações Recentes
            </h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle m-0" style="width:100%">
                    <thead class="bg-light text-muted text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.05em;">
                        <tr>
                            <th class="py-3 px-4">Beneficiário</th>
                            <th class="py-3 text-center">Operação</th>
                            <th class="py-3 text-right">Valor Alterado</th>
                            <th class="py-3 px-4">Justificativa</th>
                            <th class="py-3">Data/Hora</th>
                            <th class="py-3 text-center">Ação</th>
                        </tr>
                    </thead>
                    <tbody id="lanc-tbody" style="font-size: 0.85rem;"></tbody>
                </table>
                <div id="lanc-loading" class="text-center p-5" style="display:none;">
                    <i class="fas fa-circle-notch fa-spin fa-2x text-primary mb-2"></i>
                    <div class="text-muted small">Sincronizando dados...</div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .card { border-radius: 12px; }
    .select2-container--default .select2-selection--single { height: 38px !important; line-height: 38px !important; border: 1px solid #ced4da !important; border-radius: 4px !important; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 36px !important; padding-left: 12px !important; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px !important; }
    
    .table td { padding: 0.85rem 0.75rem !important; }
    .badge-op { padding: 0.4em 0.7em; border-radius: 4px; font-weight: 700; font-size: 0.7rem; }
    .op-credito { background-color: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
    .op-debito { background-color: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
    
    .btn-undo { width: 28px; height: 28px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; }
</style>
@stop

@section('js')
<script>
function fm(n){ return parseFloat(n||0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }); }
function fd(d){
    if(!d) return '';
    const dt = new Date(d);
    return `<div class="text-dark">${dt.toLocaleDateString('pt-BR')}</div><div class="text-xs text-muted">${dt.toLocaleTimeString('pt-BR')}</div>`;
}

$(document).ready(function(){
    $('.select2').select2({
        placeholder: 'Selecione um usuário...',
        allowClear: true,
        theme: 'default'
    });

    $.get('/admin/list-cambistas', function(d){
        if(d) {
            d.forEach(function(c){
                $('#lanc-user').append(`<option value="${c.id}">${c.name} (@${c.username})</option>`);
            });
        }
    });

    loadLancamentos();
});

function loadLancamentos(){
    $('#lanc-loading').show();
    $('#lanc-tbody').empty();
    
    $.get('/admin/list-lancamentos', function(d){
        $('#lanc-loading').hide();
        if(!d || d.length === 0){
            $('#lanc-tbody').append('<tr><td colspan="6" class="text-center p-5 text-muted"><i class="fas fa-ghost fa-2x mb-2 d-block"></i> Nenhum registro recente</td></tr>');
            return;
        }
        
        const tb = $('#lanc-tbody');
        d.forEach(function(l){
            const isCred = l.tipo.toLowerCase().includes('credito') || l.tipo.toLowerCase().includes('crédito');
            const cls = isCred ? 'op-credito' : 'op-debito';
            const icon = isCred ? 'fa-plus' : 'fa-minus';
            const valCls = isCred ? 'text-success' : 'text-danger';
            
            tb.append(`<tr>
                <td class="px-4"><strong>${l.colaborador||'-'}</strong></td>
                <td class="text-center"><span class="badge-op ${cls} text-uppercase"><i class="fas ${icon} mr-1"></i> ${l.tipo}</span></td>
                <td class="text-right"><span class="font-weight-bold ${valCls}">${fm(l.valor)}</span></td>
                <td class="px-4"><span class="text-muted small">${l.descricao||'-'}</span></td>
                <td>${fd(l.created_at)}</td>
                <td class="text-center">
                    <button class="btn btn-outline-danger btn-undo" onclick="delLanc(${l.id})" title="Estornar Ajuste">
                        <i class="fas fa-undo"></i>
                    </button>
                </td>
            </tr>`);
        });
    }).fail(function(){
        $('#lanc-loading').hide();
        toastr.error('Falha ao sincronizar histórico.');
    });
}

function addLancamento(){
    const uid = $('#lanc-user').val();
    const valor = $('#lanc-valor').val();
    const desc = $('#lanc-desc').val();
    
    if(!uid){ toastr.error('Selecione um beneficiário!'); return; }
    if(!valor || valor <= 0){ toastr.error('Valor deve ser maior que zero!'); return; }
    if(!desc){ toastr.warning('Informe uma justificativa.'); return; }

    const btn = $(event.currentTarget);
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

    $.post('/admin/add-lancamento',{
        _token: $('meta[name="csrf-token"]').attr('content'),
        user_id: uid,
        tipo: $('#lanc-tipo').val(),
        valor: valor,
        descricao: desc
    }, function(){
        toastr.success('Ajuste financeiro executado!');
        loadLancamentos();
        $('#lanc-valor').val('');
        $('#lanc-desc').val('');
        $('#lanc-user').val('').trigger('change');
    }).fail(function(err){
        toastr.error(err.responseJSON?.error || 'Erro na operação.');
    }).always(function(){
        btn.prop('disabled', false).html('<i class="fas fa-paper-plane mr-1"></i> EXECUTAR');
    });
}

function delLanc(id){
    Swal.fire({
        title: 'Estornar Ajuste?',
        text: "O saldo será revertido e o registro removido do histórico.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        confirmButtonText: 'Confirmar Estorno',
        cancelButtonText: 'Voltar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/admin/lancamento/deletar/'+id,
                type: 'DELETE',
                data: { _token: $('meta[name="csrf-token"]').attr('content') },
                success: function(){
                    toastr.success('Registro estornado com sucesso!');
                    loadLancamentos();
                },
                error: function(){ toastr.error('Erro ao processar estorno.'); }
            });
        }
    });
}
</script>
@stop
