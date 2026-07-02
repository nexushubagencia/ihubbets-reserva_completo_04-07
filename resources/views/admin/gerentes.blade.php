@extends('adminlte::page')

@section('title', config('adminlte.title_adm_geral'))

@section('content_header')
    <h1><i class="fas fa-users"></i> Gerentes</h1>
    
@stop

@section('content')
@php
    $siteId = config('tenant.site_id');
    $tenantSite = $siteId ? \App\Models\Site::find($siteId) : null;
@endphp
<div class="card card-primary card-outline">
    <div class="card-header">
        <div class="row">
            <div class="col-md-4">
                <div class="input-group">
                    <input type="text" class="form-control" id="search-gerente" placeholder="Buscar por nome">
                    <div class="input-group-append">
                        <button class="btn btn-success" onclick="searchGerente()"><i class="fas fa-search"></i></button>
                    </div>
                </div>
            </div>
            <div class="col-md-8 text-right">
                <a href="/admin/cadastrar-gerentes" class="btn btn-success"><i class="fas fa-plus"></i> Novo Gerente</a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="bg-primary text-white" style="font-size:12px;text-align:center;">
                    <tr>
                        <th>NOME</th>
                        <th>CADASTRADO</th>
                        <th>SITUAÇÃO</th>
                        <th>SALDO</th>
                        <th>CONTATO</th>
                        <th>EDITAR</th>
                        <th>AÇÃO</th>
                        <th>EXCLUIR</th>
                    </tr>
                </thead>
                <tbody id="gerentes-tbody" style="text-align:center;font-size:13px;"></tbody>
            </table>
            <div id="gerentes-loading" class="text-center p-3" style="display:none;"><i class="fas fa-spinner fa-spin fa-2x"></i></div>
        </div>
    </div>
</div>

<!-- Modal Editar -->
<div class="modal fade" id="modal-editar-gerente">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><i class="fas fa-user"></i> <b id="edit-gerente-name"></b></h4>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="edit-gerente-id">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group"><label>Nome</label><input type="text" id="edit-name" class="form-control"></div>
                        <div class="form-group"><label>Login</label><input type="text" id="edit-username" class="form-control"></div>
                        <div class="form-group"><label>Alterar Senha</label><input type="text" id="edit-password" class="form-control" placeholder="Nova senha"></div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group"><label>Contato</label><input type="text" id="edit-contato" class="form-control"></div>
                        <div class="form-group"><label>Endereço</label><input type="text" id="edit-endereco" class="form-control"></div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group"><label>Saldo</label><input type="number" id="edit-saldo" class="form-control" step="0.01"></div>
                        <div class="form-group"><label>Comissão Sobre Lucro % <i class="fas fa-info-circle text-secondary ml-1" data-toggle="tooltip" title="Percentual sobre o lucro líquido gerado pelas apostas físicas."></i></label><input type="number" id="edit-comissao" class="form-control" step="0.01"></div>
                        <div class="form-group"><label>Taxa de Gestão Online % <i class="fas fa-info-circle text-info ml-1" data-toggle="tooltip" title="Percentual sobre o volume total das apostas online."></i></label><input type="number" id="edit-comissao-online" class="form-control" step="0.01"></div>
                        @if(!$tenantSite || $tenantSite->active_bonus)
                        <div class="form-group">
                            <label>Pode Criar Cupons/Bônus?</label>
                            <select id="edit-can-create-coupons" class="form-control">
                                <option value="1">Sim</option>
                                <option value="0">Não</option>
                            </select>
                        </div>
                        @else
                        <input type="hidden" id="edit-can-create-coupons" value="0">
                        @endif
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success" onclick="editGerente()"><i class="fas fa-save"></i> ALTERAR</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Lançamento -->
<!-- Modal Lançamento -->
<div class="modal fade" id="modal-lancamento-gerente">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white border-0">
                <h4 class="modal-title font-weight-bold"><i class="fas fa-file-invoice-dollar text-info mr-2"></i> Lançamento: <span id="lan-gerente-name" class="text-info"></span></h4>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <input type="hidden" id="lan-gerente-id">

                <div class="form-group">
                    <label class="font-weight-bold text-muted">Ação de Lançamento <i class="fas fa-info-circle text-info ml-1" data-toggle="tooltip" title="ATENÇÃO: Esta tela é APENAS para lançamentos financeiros reais. Para aumentar teto, use o botão azul 'Ajustar Limite'."></i></label>
                    <select id="lan-tipo" class="form-control font-weight-bold shadow-sm">
                        <option value="credito">Crédito (Diminui dívida na prestação de contas)</option>
                        <option value="debito">Débito (Aumenta dívida na prestação de contas)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold text-muted">Valor (R$)</label>
                    <input type="number" id="lan-valor" class="form-control form-control-lg font-weight-bold text-success shadow-sm" step="0.01" value="0">
                </div>
                <button class="btn btn-info btn-block btn-lg mt-4 font-weight-bold shadow-sm" onclick="salvarLancamentoGerente()"><i class="fas fa-check-circle"></i> CONCLUIR LANÇAMENTO</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ajustar Limite -->
<div class="modal fade" id="modal-ajustar-limite-ger">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white border-0">
                <h4 class="modal-title font-weight-bold"><i class="fas fa-wallet text-primary mr-2"></i> Ajustar Limite: <span id="lim-ger-name" class="text-primary"></span></h4>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <input type="hidden" id="lim-ger-id">

                <div class="form-group">
                    <label class="font-weight-bold text-muted">Ação <i class="fas fa-info-circle text-primary ml-1" data-toggle="tooltip" title="AJUSTE SEGURO: Use esta tela para liberar saldo sem gerar dívidas financeiras na prestação de contas."></i></label>
                    <select id="lim-ger-tipo" class="form-control font-weight-bold shadow-sm">
                        <option value="aumentar">Aumentar Limite (+)</option>
                        <option value="diminuir">Reduzir Limite (-)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold text-muted">Valor a Ajustar (R$)</label>
                    <input type="number" id="lim-ger-valor" class="form-control form-control-lg font-weight-bold text-primary shadow-sm" step="0.01" value="0">
                </div>
                
                <button class="btn btn-primary btn-block btn-lg mt-4 font-weight-bold shadow-sm" onclick="salvarAjusteLimiteGerente()"><i class="fas fa-save"></i> SALVAR NOVO LIMITE</button>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .ativo { color: #008D4C; }
    .bloqueado { color: #D73925; }
</style>
@stop

@section('js')
<script>
$(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip();
});

function fm(n) { return "R$ " + parseFloat(n||0).toFixed(2).replace('.', ',').replace(/(\d)(?=(\d{3})+\,)/g, "$1."); }
function fd(dateString) {
    if (!dateString) return '';
    var date = new Date(dateString);
    var day = String(date.getDate()).padStart(2, '0');
    var month = String(date.getMonth() + 1).padStart(2, '0');
    var year = date.getFullYear();
    var hours = String(date.getHours()).padStart(2, '0');
    var minutes = String(date.getMinutes()).padStart(2, '0');
    return day + '/' + month + '/' + year + ' ' + hours + ':' + minutes;
}

$(document).ready(function() { loadGerentes(); });

function loadGerentes() {
    $('#gerentes-loading').show();
    $.get('/admin/gerentes-list', function(data) { renderGerentes(data); }).fail(function(){ $('#gerentes-loading').hide(); });
}

function searchGerente() {
    var q = $('#search-gerente').val();
    if (!q) { loadGerentes(); return; }
    $('#gerentes-loading').show();
    $.get('/admin/search-gerente/'+q, function(data) { renderGerentes(data); });
}

function renderGerentes(list) {
    var tb = $('#gerentes-tbody'); tb.empty(); $('#gerentes-loading').hide();
    if (!list) return;
    list.forEach(function(g) {
        if (g.nivel === 'adm') return;
        var safeName = (g.name||"").replace(/'/g, "\\'").replace(/"/g, '&quot;');
        var isAtivo = (g.status == 1);
        var sit = isAtivo ? '<i class="fas fa-check ativo"></i> Ativo' : '<i class="fas fa-times bloqueado"></i> Bloqueado';
        var actBtn = isAtivo ?
            '<button class="btn btn-warning btn-sm" title="Bloquear" onclick="toggleSituacao('+g.id+', 0)"><i class="fas fa-lock"></i></button>' :
            '<button class="btn btn-success btn-sm" title="Desbloquear" onclick="toggleSituacao('+g.id+', 1)"><i class="fas fa-unlock"></i></button>';
        
        var lanBtn = '<button class="btn btn-info btn-sm shadow-sm ml-1" title="Lançamento Financeiro" onclick="abrirLancamento('+g.id+',\''+safeName+'\')"><i class="fas fa-money-bill"></i></button>';
        var limBtn = '<button class="btn btn-primary btn-sm shadow-sm ml-1" title="Ajustar Limite" onclick="abrirAjusteLimite('+g.id+',\''+safeName+'\')"><i class="fas fa-wallet"></i></button>';

        tb.append('<tr><td><strong class="text-dark">'+g.name+'</strong></td><td>'+fd(g.created_at)+'</td><td>'+sit+'</td><td class="font-weight-bold text-success">'+fm(g.saldo_gerente)+'</td><td>'+(g.contato||'<i class="text-muted">-</i>')+'</td><td><button class="btn btn-secondary btn-sm shadow-sm" title="Editar" onclick=\'viewGerente('+JSON.stringify(g).replace(/'/g,"&#39;")+')\' ><i class="fas fa-edit"></i></button></td><td>'+actBtn+' '+limBtn+' '+lanBtn+'</td><td><button class="btn btn-danger btn-sm shadow-sm" title="Excluir" onclick="deleteGerente('+g.id+',\''+safeName+'\')"><i class="fas fa-trash"></i></button></td></tr>');
    });
}

function abrirLancamento(id, name) {
    $('#lan-gerente-id').val(id);
    $('#lan-gerente-name').text(name);
    $('#lan-valor').val('');
    $('#modal-lancamento-gerente').modal('show');
}

function salvarLancamentoGerente() {
    var id = $('#lan-gerente-id').val();
    var valor = $('#lan-valor').val();
    var tipo = $('#lan-tipo').val();

    if (!valor || valor <= 0) {
        toastr.error('Informe um valor válido');
        return;
    }

    $.post('/admin/gerente-lancamento', {
        _token: $('meta[name="csrf-token"]').attr('content'),
        id: id,
        valor: valor,
        tipo: tipo
    }, function(res) {
        $('#modal-lancamento-gerente').modal('hide');
        loadGerentes();
        toastr.success('Lançamento realizado com sucesso!');
    }).fail(function(err) {
        toastr.error('Erro ao realizar lançamento');
    });
}

function abrirAjusteLimite(id, name) {
    $('#lim-ger-id').val(id);
    $('#lim-ger-name').text(name);
    $('#lim-ger-valor').val('');
    $('#modal-ajustar-limite-ger').modal('show');
}

function salvarAjusteLimiteGerente() {
    var v = $('#lim-ger-valor').val();
    if(!v || v<=0){ toastr.error('Valor inválido'); return; }
    $.post('/admin/ajustar-limite',{
        _token: $('meta[name="csrf-token"]').attr('content'),
        user_id: $('#lim-ger-id').val(),
        tipo: $('#lim-ger-tipo').val(),
        carteira: 'simples', // Gerente só tem saldo geral
        valor: v
    },function(){
        toastr.success('Limite atualizado com sucesso!');
        $('#modal-ajustar-limite-ger').modal('hide');
        loadGerentes();
    }).fail(function(xhr){
        toastr.error('Erro: ' + (xhr.responseJSON ? xhr.responseJSON.error : 'Desconhecido'));
    });
}

function viewGerente(g) {
    $('#edit-gerente-id').val(g.id); $('#edit-gerente-name').text(g.name);
    $('#edit-name').val(g.name); $('#edit-username').val(g.username);
    $('#edit-contato').val(g.contato); $('#edit-endereco').val(g.endereco);
    $('#edit-saldo').val(g.saldo_gerente); $('#edit-comissao').val(g.comissao_gerente);
    $('#edit-comissao-online').val(g.comissao_gerente_online);
    $('#edit-can-create-coupons').val(g.can_create_coupons || 0);
    $('#edit-password').val(''); $('#modal-editar-gerente').modal('show');
}

function editGerente() {
    var id = $('#edit-gerente-id').val();
    $.ajax({ url:'/admin/editar-gerente/'+id, type:'PUT', data: {
        _token: $('meta[name="csrf-token"]').attr('content'),
        name: $('#edit-name').val(), username: $('#edit-username').val(),
        password: $('#edit-password').val(), contato: $('#edit-contato').val(),
        endereco: $('#edit-endereco').val(), saldo_gerente: $('#edit-saldo').val(),
        comissao_gerente: $('#edit-comissao').val(),
        comissao_gerente_online: $('#edit-comissao-online').val(),
        can_create_coupons: $('#edit-can-create-coupons').val()
    }, success: function() { $('#modal-editar-gerente').modal('hide'); loadGerentes(); toastr.info('Dados alterados!'); },
       error: function() { toastr.error('Erro ao alterar!'); } });
}

function toggleSituacao(id, statusVal) {
    $.post('/admin/alterar-user', { _token:$('meta[name="csrf-token"]').attr('content'), id:id, status:statusVal }, function() { loadGerentes(); toastr.info('Status atualizado!'); }).fail(function(){ toastr.error('Erro ao alterar status!'); });
}

function deleteGerente(id, name) {
    if (!confirm('Deseja realmente excluir o gerente: '+name+'?')) return;
    $.ajax({ url:'/admin/deletar-gerente/'+id, type:'DELETE',
        success: function() { loadGerentes(); toastr.success('Excluído!'); }, 
        error: function(xhr) { toastr.error(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Erro ao excluir!'); } 
    });
}
</script>
@stop