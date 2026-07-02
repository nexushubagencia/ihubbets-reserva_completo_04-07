@extends('adminlte::page')
@section('title', config('adminlte.title_adm_geral'))
@section('content_header')
    <h1><i class="fas fa-users"></i> Cambistas</h1>
@stop
@section('content')
<div class="card card-primary card-outline card-outline-tabs">
    <div class="card-header p-0 border-bottom-0">
        <ul class="nav nav-tabs" id="cambistas-tabs" role="tablist" style="padding-top: 10px; padding-left: 10px;">
            <li class="nav-item">
                <a class="nav-link active font-weight-bold" id="tab-lista-tab" data-toggle="pill" href="#tab-lista" role="tab" aria-controls="tab-lista" aria-selected="true">
                    <i class="fas fa-users mr-1" style="color: #007bff;"></i> Lista de Cambistas
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link font-weight-bold" id="tab-cad-tab" data-toggle="pill" href="#tab-cad" role="tab" aria-controls="tab-cad" aria-selected="false">
                    <i class="fas fa-user-plus mr-1" style="color: #10b981;"></i> Cadastrar Novo Cambista
                </a>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content" id="cambistas-tabsContent">
            {{-- TAB 1: LISTAGEM --}}
            <div class="tab-pane fade show active" id="tab-lista" role="tabpanel" aria-labelledby="tab-lista-tab">
                <div class="row mb-3 align-items-center">
                    <div class="col-md-3">
                        <div class="input-group">
                            <input type="text" class="form-control" id="search-cambista" placeholder="Buscar por nome do cliente">
                            <div class="input-group-append">
                                <button class="btn btn-success" onclick="filterCambistas()"><i class="fas fa-search"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="input-group">
                            <select class="form-control" id="filter-situacao">
                                <option value="todos">Todos</option>
                                <option value="1">Ativo</option>
                                <option value="0">Bloqueado</option>
                            </select>
                            <div class="input-group-append">
                                <button class="btn btn-success" onclick="filterCambistas()"><i class="fas fa-search"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 text-right">
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-hover shadow-sm">
                        <thead class="bg-primary text-white" style="font-size:12px;text-align:center;">
                            <tr>
                                <th>NOME</th>
                                <th>QTD. CLIENTES</th>
                                <th>CADASTRADO</th>
                                <th>SITUAÇÃO</th>
                                <th>CONTATO</th>
                                <th>SALDO SIMPLES</th>
                                <th>SALDO CASADINHA</th>
                                <th>SALDO TOTAL</th>
                                <th>EM USO</th>
                                <th>EDITAR</th>
                                <th>AÇÃO</th>
                                <th>EXCLUIR</th>
                            </tr>
                        </thead>
                        <tbody id="cambistas-tbody" style="text-align:center;font-size:13px;"></tbody>
                    </table>
                    <div id="cambistas-loading" class="text-center p-3" style="display:none;"><i class="fas fa-spinner fa-spin fa-2x"></i></div>
                </div>
            </div>

            {{-- TAB 2: CADASTRO --}}
            <div class="tab-pane fade" id="tab-cad" role="tabpanel" aria-labelledby="tab-cad-tab">
                <form id="form-cad-cambista" autocomplete="off">
                    @csrf

                    {{-- SEÇÃO 1 — DADOS PESSOAIS --}}
                    <div class="card card-outline card-success mb-3 shadow-sm">
                        <div class="card-header py-2">
                            <h3 class="card-title text-sm font-weight-bold"><i class="fas fa-id-card text-success"></i> DADOS PESSOAIS</h3>
                        </div>
                        <div class="card-body pb-2">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-2">
                                        <label class="text-xs font-weight-bold">Nome Completo <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control form-control-sm" placeholder="Ex: João Silva" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-2">
                                        <label class="text-xs font-weight-bold">Login (Usuário) <span class="text-danger">*</span></label>
                                        <input type="text" name="username" class="form-control form-control-sm" placeholder="Ex: joao.silva" required autocomplete="new-password">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-2">
                                        <label class="text-xs font-weight-bold">Senha <span class="text-danger">*</span></label>
                                        <div class="input-group input-group-sm">
                                            <input type="password" name="password" id="inp-password" class="form-control form-control-sm" placeholder="Mín. 4 caracteres" required autocomplete="new-password">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-outline-secondary" onclick="togglePassword()"><i class="fas fa-eye" id="icon-eye"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group mb-2">
                                        <label class="text-xs font-weight-bold">Gerente Responsável</label>
                                        <select name="gerente_id" class="form-control form-control-sm" id="cad-gerente">
                                            <option value="0">Nenhum (Admin)</option>
                                        </select>
                                        <small class="text-muted text-xs">Deixe vazio se controlado pelo Admin.</small>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-2">
                                        <label class="text-xs font-weight-bold">Contato / Telefone</label>
                                        <input type="text" name="contato" class="form-control form-control-sm" placeholder="(XX) XXXXX-XXXX">
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="form-group mb-2">
                                        <label class="text-xs font-weight-bold">Endereço</label>
                                        <input type="text" name="endereco" class="form-control form-control-sm" placeholder="Rua, Bairro, Cidade...">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- SEÇÃO 2 — SALDOS INICIAIS --}}
                    <div class="card card-outline card-primary mb-3 shadow-sm">
                        <div class="card-header py-2">
                            <h3 class="card-title text-sm font-weight-bold"><i class="fas fa-wallet text-primary"></i> SALDOS INICIAIS (OPERAÇÃO DE RUA)</h3>
                        </div>
                        <div class="card-body pb-2">
                            <p class="text-xs text-muted mb-2"><i class="fas fa-info-circle"></i> Defina os créditos iniciais que este Cambista terá para operar no modo <strong>Rua</strong> (bilhetes físicos).</p>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group mb-2">
                                        <label class="text-xs font-weight-bold">Saldo Simples (R$)</label>
                                        <input type="number" name="saldo_simples" class="form-control form-control-sm" step="0.01" value="0">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-2">
                                        <label class="text-xs font-weight-bold">Saldo Casadinha (R$)</label>
                                        <input type="number" name="saldo_casadinha" class="form-control form-control-sm" step="0.01" value="0">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-2">
                                        <label class="text-xs font-weight-bold">Saldo Loto (R$)</label>
                                        <input type="number" name="saldo_loto" class="form-control form-control-sm" step="0.01" value="0">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-2">
                                        <label class="text-xs font-weight-bold">Comissão Loto (%)</label>
                                        <input type="number" name="comissao_loto" class="form-control form-control-sm" step="0.01" value="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- SEÇÃO 3 — COMISSÕES --}}
                    <div class="row">
                        <div class="col-md-7">
                            <div class="card card-outline card-secondary mb-3 shadow-sm">
                                <div class="card-header py-2">
                                    <h3 class="card-title text-sm font-weight-bold"><i class="fas fa-store text-secondary"></i> COMISSÃO DE RUA (por jogos)</h3>
                                </div>
                                <div class="card-body pb-2">
                                    <div class="row">
                                        @for($i = 1; $i <= 10; $i++)
                                        <div class="col-md-4 col-6">
                                            <div class="form-group mb-2">
                                                <label class="text-xs font-weight-bold">{{ $i }} {{ $i == 1 ? 'Jogo' : 'Jogos' }} (%)</label>
                                                <input type="number" name="comissao{{ $i }}" class="form-control form-control-sm" step="0.01" value="0">
                                            </div>
                                        </div>
                                        @endfor
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="card card-outline card-info mb-3 shadow-sm">
                                <div class="card-header py-2">
                                    <h3 class="card-title text-sm font-weight-bold"><i class="fas fa-globe text-info"></i> COMISSÃO ONLINE (AFILIADO)</h3>
                                </div>
                                <div class="card-body">
                                    <div class="form-group mb-3">
                                        <label class="text-sm font-weight-bold text-info">Comissão Fixa Online (%)</label>
                                        <input type="number" name="comissao_online" class="form-control" step="0.01" value="0" style="font-size: 1.4rem; font-weight: bold; color: #0dcaf0; border: 2px solid #0dcaf0; text-align: center;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-right">
                        <button type="submit" class="btn btn-success px-5 shadow-sm" id="btn-submit-cambista">
                            <i class="fas fa-save"></i> Finalizar Cadastro
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-editar-cambista">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h4 class="modal-title"><i class="fas fa-user"></i> <b id="edit-camb-name"></b></h4><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
            <div class="modal-body">
                <input type="hidden" id="edit-camb-id">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group"><label>Nome</label><input type="text" id="camb-name" class="form-control"></div>
                        <div class="form-group"><label>Login</label><input type="text" id="camb-username" class="form-control"></div>
                        <div class="form-group"><label>Alterar Senha</label><input type="text" id="camb-password" class="form-control" placeholder="Nova senha"></div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group"><label>Contato</label><input type="text" id="camb-contato" class="form-control"></div>
                        <div class="form-group"><label>Endereço</label><input type="text" id="camb-endereco" class="form-control"></div>
                        <hr>
                        <h6 class="text-info font-weight-bold"><i class="fas fa-wallet"></i> Saldos (Leitura)</h6>
                        <div class="form-group mb-1"><label class="text-xs mb-0">Saldo Simples</label><input type="text" id="camb-saldo-simples" class="form-control form-control-sm text-success font-weight-bold" readonly></div>
                        <div class="form-group mb-1"><label class="text-xs mb-0">Saldo Casadinha</label><input type="text" id="camb-saldo-casadinha" class="form-control form-control-sm text-success font-weight-bold" readonly></div>
                        <div class="form-group"><label class="text-xs mb-0">Total</label><input type="text" id="camb-saldo-total" class="form-control form-control-sm text-primary font-weight-bold" readonly></div>
                    </div>
                    <div class="col-md-4">
                        @for($i=1;$i<=10;$i++)
                        <div class="form-group"><label>Comissão {{$i}} %</label><input type="number" id="camb-comissao{{$i}}" class="form-control" step="0.01"></div>
                        @endfor
                    </div>
                </div>
            </div>
            <div class="modal-footer"><button class="btn btn-success" onclick="editCambista()"><i class="fas fa-save"></i> ALTERAR</button></div>
        </div>
    </div>
</div>

<!-- Modal Lançamento Cambista -->
<div class="modal fade" id="modal-lancamento-cambista">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white border-0">
                <h4 class="modal-title font-weight-bold"><i class="fas fa-file-invoice-dollar text-info mr-2"></i> Lançamento: <span id="lan-camb-name" class="text-info"></span></h4>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <input type="hidden" id="lan-camb-id">
                
                <div class="form-group">
                    <label class="font-weight-bold text-muted">Ação de Lançamento <i class="fas fa-info-circle text-info ml-1" data-toggle="tooltip" title="ATENÇÃO: Esta tela é APENAS para lançamentos financeiros reais (Pagamentos, Dívidas ou Abatimentos). Para apenas aumentar o teto de apostas (crédito), use o botão azul 'Ajustar Limite'."></i></label>
                    <select id="lan-camb-tipo" class="form-control font-weight-bold shadow-sm">
                        <option value="credito">Crédito (Diminui dívida na prestação de contas)</option>
                        <option value="debito">Débito (Aumenta dívida na prestação de contas)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold text-muted">Valor (R$)</label>
                    <input type="number" id="lan-camb-valor" class="form-control form-control-lg font-weight-bold text-success shadow-sm" step="0.01" value="0">
                </div>
                <div class="form-group">
                    <label class="font-weight-bold text-muted">Carteira de Destino <i class="fas fa-info-circle text-info" data-toggle="tooltip" title="Escolha qual saldo será afetado por este lançamento."></i></label>
                    <select id="lan-camb-carteira" class="form-control shadow-sm">
                        <option value="simples">Saldo Esportes (Simples/Geral)</option>
                        <option value="casadinha">Saldo Casadinha</option>
                        <option value="loto">Saldo Loteria (Seninha/Quininha)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold text-muted">Descrição</label>
                    <input type="text" id="lan-camb-desc" class="form-control shadow-sm" placeholder="Ex: Carga de saldo semanal">
                </div>
                <button class="btn btn-info btn-block btn-lg mt-4 font-weight-bold shadow-sm" onclick="lancarCambista()"><i class="fas fa-check-circle"></i> CONCLUIR LANÇAMENTO</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ajustar Limite -->
<div class="modal fade" id="modal-ajustar-limite">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white border-0">
                <h4 class="modal-title font-weight-bold"><i class="fas fa-wallet text-primary mr-2"></i> Ajustar Limite: <span id="lim-camb-name" class="text-primary"></span></h4>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <input type="hidden" id="lim-camb-id">

                <div class="form-group">
                    <label class="font-weight-bold text-muted">Ação <i class="fas fa-info-circle text-primary ml-1" data-toggle="tooltip" title="AJUSTE SEGURO: Use esta tela para liberar saldo para o cambista trabalhar sem gerar dívidas financeiras na prestação de contas."></i></label>
                    <select id="lim-camb-tipo" class="form-control font-weight-bold shadow-sm">
                        <option value="aumentar">Aumentar Limite (+)</option>
                        <option value="diminuir">Reduzir Limite (-)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold text-muted">Valor a Ajustar (R$)</label>
                    <input type="number" id="lim-camb-valor" class="form-control form-control-lg font-weight-bold text-primary shadow-sm" step="0.01" value="0">
                </div>
                <div class="form-group">
                    <label class="font-weight-bold text-muted">Carteira</label>
                    <select id="lim-camb-carteira" class="form-control shadow-sm">
                        <option value="simples">Saldo Esportes (Simples/Geral)</option>
                        <option value="casadinha">Saldo Casadinha</option>
                        <option value="loto">Saldo Loteria (Seninha/Quininha)</option>
                    </select>
                </div>
                
                <button class="btn btn-primary btn-block btn-lg mt-4 font-weight-bold shadow-sm" onclick="salvarAjusteLimite()"><i class="fas fa-save"></i> SALVAR NOVO LIMITE</button>
            </div>
        </div>
    </div>
</div>

@stop
@section('css')
<style>.ativo{color:#008D4C;}.bloqueado{color:#D73925;}</style>
@stop
@section('js')
<script>
$(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip();
});

function fd(d){
    if(!d) return '';
    var date = new Date(d);
    var day = String(date.getDate()).padStart(2, '0');
    var month = String(date.getMonth() + 1).padStart(2, '0');
    var year = date.getFullYear();
    var hours = String(date.getHours()).padStart(2, '0');
    var minutes = String(date.getMinutes()).padStart(2, '0');
    return day + '/' + month + '/' + year + ' ' + hours + ':' + minutes;
}
$(document).ready(function(){
    loadCambistas();

    // Carregar lista de Gerentes para o formulário de cadastro
    $.get('/admin/list-gerentes-select', function(data){
        if (data && data.length > 0) {
            data.forEach(function(g){
                $('#cad-gerente').append('<option value="'+g.id+'">'+g.name+'</option>');
            });
        }
    });
});

function loadCambistas(){
    $('#cambistas-loading').show();
    $.get('/admin/list-cambistas',function(data){
        renderCambistas(data);
        window.storedCambistas = data; // guarda para filtros
    }).fail(function(){$('#cambistas-loading').hide();});
}

function filterCambistas() {
    var sit = $('#filter-situacao').val();
    var q = $('#search-cambista').val().toLowerCase();
    
    if(!window.storedCambistas) return;
    
    var filtered = window.storedCambistas.filter(function(c) {
        var matchName = !q || c.name.toLowerCase().includes(q) || c.username.toLowerCase().includes(q);
        var matchSit = sit === 'todos' || c.status == sit;
        return matchName && matchSit;
    });
    
    renderCambistas(filtered, true);
}

function renderCambistas(list, isFiltered){
    var tb=$('#cambistas-tbody');tb.empty();$('#cambistas-loading').hide();
    if(!list)return;
    list.forEach(function(c){
        var isAtivo = (c.status == 1);
        var safeName = (c.name||"").replace(/'/g, "\\'").replace(/"/g, '&quot;');
        
        var sit = isAtivo ? 
            '<button class="btn btn-warning btn-sm shadow-sm" title="Bloquear" onclick="toggleCamb('+c.id+', 0)"><i class="fas fa-lock"></i></button>' : 
            '<button class="btn btn-success btn-sm shadow-sm" title="Desbloquear" onclick="toggleCamb('+c.id+', 1)"><i class="fas fa-unlock"></i></button>';
        
        var lim = '<button class="btn btn-primary btn-sm shadow-sm ml-1" title="Ajustar Limite" onclick="abrirAjusteLimite('+c.id+',\''+safeName+'\')"><i class="fas fa-wallet"></i></button>';
        var lan = '<button class="btn btn-info btn-sm shadow-sm ml-1" title="Lançamento Financeiro" onclick="abrirLancamentoCambista('+c.id+',\''+safeName+'\')"><i class="fas fa-money-bill-wave"></i></button>';
        
        tb.append('<tr>' +
            '<td class="font-weight-bold">'+c.name+' <small class="text-muted d-block">('+c.username+')</small></td>' +
            '<td>'+(c.qtd_clientes||0)+'</td>' +
            '<td>'+fd(c.created_at)+'</td>' +
            '<td>'+(isAtivo ? '<span class="badge badge-success">Ativo</span>' : '<span class="badge badge-danger">Bloqueado</span>')+'</td>' +
            '<td>'+(c.contato||'<i class="text-muted">-</i>')+'</td>' +
            '<td>R$ '+(c.saldo_simples||'0.00')+'</td>' +
            '<td>R$ '+(c.saldo_casadinha||'0.00')+'</td>' +
            '<td>R$ '+(c.saldo_total||'0.00')+'</td>' +
            '<td>'+(c.em_uso||'Não')+'</td>' +
            '<td><button class="btn btn-secondary btn-sm shadow-sm" title="Editar" onclick="viewCamb('+c.id+')"><i class="fas fa-edit"></i></button></td>' +
            '<td>'+sit+' '+lim+' '+lan+'</td>' +
            '<td><button class="btn btn-danger btn-sm shadow-sm" title="Excluir" onclick="delCamb('+c.id+',\''+safeName+'\')"><i class="fas fa-trash-alt"></i></button></td>' +
        '</tr>');
    });
}

function abrirLancamentoCambista(id, name) {
    $('#lan-camb-id').val(id);
    $('#lan-camb-name').text(name);
    $('#lan-camb-valor').val('');
    $('#lan-camb-desc').val('');
    $('#modal-lancamento-cambista').modal('show');
}

function lancarCambista(){
    var v = $('#lan-camb-valor').val();
    if(!v || v<=0){ toastr.error('Valor inválido'); return; }
    $.post('/admin/add-lancamento',{
        _token: $('meta[name="csrf-token"]').attr('content'),
        user_id: $('#lan-camb-id').val(),
        tipo: $('#lan-camb-tipo').val(),
        carteira: $('#lan-camb-carteira').val(),
        valor: v,
        descricao: $('#lan-camb-desc').val()
    },function(){
        toastr.success('Lançamento realizado!');
        $('#modal-lancamento-cambista').modal('hide');
        loadCambistas();
    }).fail(function(xhr){
        toastr.error('Erro: ' + (xhr.responseJSON ? xhr.responseJSON.error : 'Desconhecido'));
    });
}

function abrirAjusteLimite(id, name) {
    $('#lim-camb-id').val(id);
    $('#lim-camb-name').text(name);
    $('#lim-camb-valor').val('');
    $('#modal-ajustar-limite').modal('show');
}

function salvarAjusteLimite() {
    var v = $('#lim-camb-valor').val();
    if(!v || v<=0){ toastr.error('Valor inválido'); return; }
    $.post('/admin/ajustar-limite',{
        _token: $('meta[name="csrf-token"]').attr('content'),
        user_id: $('#lim-camb-id').val(),
        tipo: $('#lim-camb-tipo').val(),
        carteira: $('#lim-camb-carteira').val(),
        valor: v
    },function(){
        toastr.success('Limite atualizado com sucesso!');
        $('#modal-ajustar-limite').modal('hide');
        loadCambistas();
    }).fail(function(xhr){
        toastr.error('Erro: ' + (xhr.responseJSON ? xhr.responseJSON.error : 'Desconhecido'));
    });
}

function viewCamb(id){
    $.get('/admin/list-cambistas',function(data){
        var c=data.find(function(x){return x.id==id;});
        if(!c){toastr.error('Cambista não encontrado');return;}
        $('#edit-camb-id').val(c.id);
        $('#edit-camb-name').text(c.name);
        $('#camb-name').val(c.name);
        $('#camb-username').val(c.username);
        $('#camb-contato').val(c.contato);
        $('#camb-endereco').val(c.address || c.endereco);
        $('#camb-saldo-simples').val('R$ ' + (c.saldo_simples || 0).toFixed(2));
        $('#camb-saldo-casadinha').val('R$ ' + (c.saldo_casadinha || 0).toFixed(2));
        $('#camb-saldo-total').val('R$ ' + (c.saldo_total || 0).toFixed(2));
        for(var i=1;i<=10;i++)$('#camb-comissao'+i).val(c['comissao'+i]||0);
        $('#camb-password').val('');
        $('#modal-editar-cambista').modal('show');
    });
}

function editCambista(){
    var id=$('#edit-camb-id').val();
    var d={
        _token:$('meta[name="csrf-token"]').attr('content'),
        name:$('#camb-name').val(),
        username:$('#camb-username').val(),
        password:$('#camb-password').val(),
        contato:$('#camb-contato').val(),
        endereco:$('#camb-endereco').val()
    };
    for(var i=1;i<=10;i++)d['comissao'+i]=$('#camb-comissao'+i).val();
    
    $.ajax({
        url:'/admin/cambistas/'+id,
        type:'PUT',
        data:d,
        success:function(){
            $('#modal-editar-cambista').modal('hide');
            loadCambistas();
            toastr.success('Dados alterados com sucesso!');
        },
        error:function(e){
            toastr.error('Erro ao editar cambista!');
        }
    });
}

function toggleCamb(id, statusValue){
    $.post('/admin/bloquear-user',{
        _token:$('meta[name="csrf-token"]').attr('content'),
        id:id,
        status:statusValue
    },function(){
        loadCambistas();
        toastr.info('Status do usuário atualizado!');
    }).fail(function(){
        toastr.error('Erro ao alterar status!');
    });
}

function delCamb(id,n){
    if(!confirm('Deseja realmente excluir o cambista: '+n+'?'))return;
    $.ajax({
        url:'/admin/cambistas/'+id,
        type:'DELETE',
        headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')},
        success:function(){
            loadCambistas();
            toastr.success('Cambista excluído!');
        },
        error:function(xhr){
            toastr.error(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Erro ao excluir cambista!');
        }
    });
}

// Toggle de visualização da senha no cadastro
function togglePassword() {
    var inp = document.getElementById('inp-password');
    var icon = document.getElementById('icon-eye');
    if (inp.type === 'password') {
        inp.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        inp.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Submit do Formulário de Cadastro
$('#form-cad-cambista').submit(function(e){
    e.preventDefault();
    var btn = $('#btn-submit-cambista');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Salvando...');

    $.ajax({
        url: '/admin/cadastrar-cambista',
        type: 'POST',
        data: $(this).serialize(),
        success: function(response) {
            toastr.success('Cambista cadastrado com sucesso!');
            $('#form-cad-cambista')[0].reset();
            btn.prop('disabled', false).html('<i class="fas fa-save"></i> Finalizar Cadastro');
            
            // Recarrega lista e muda de aba
            loadCambistas();
            $('#tab-lista-tab').tab('show');
        },
        error: function(xhr) {
            btn.prop('disabled', false).html('<i class="fas fa-save"></i> Finalizar Cadastro');
            var msg = 'Erro ao cadastrar cambista.';
            if (xhr.responseJSON) {
                if (xhr.responseJSON.errors) {
                    var errs = xhr.responseJSON.errors;
                    msg = '';
                    for (var k in errs) {
                        msg += errs[k][0] + '\n';
                    }
                } else if (xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
            }
            toastr.error(msg);
        }
    });
});
</script>
@stop