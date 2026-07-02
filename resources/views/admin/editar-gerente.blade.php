@extends('adminlte::page')
@section('title', config('adminlte.title_adm_geral'))
@section('content_header')
    <h1><i class="fas fa-user-edit"></i> Gerente <small>Editar</small></h1>
    
@stop
@section('content')
<div class="card card-primary card-outline">
    <div class="card-body">
        <div id="edit-loading" class="text-center p-4"><i class="fas fa-spinner fa-spin fa-2x"></i> Carregando...</div>
        <form id="form-edit-gerente" style="display:none;">
            @csrf
            <input type="hidden" id="ger-id">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group"><label>Nome</label><input type="text" class="form-control" id="ger-name" placeholder="Insira o nome"></div>
                    <div class="form-group"><label>Login</label><input type="text" class="form-control" id="ger-username" placeholder="Insira o login"></div>
                    <div class="form-group"><label>Senha (deixe em branco para manter)</label><input type="password" class="form-control" id="ger-password" placeholder="Nova senha"></div>
                </div>
                <div class="col-md-4">
                    <div class="form-group"><label>Contato</label><input type="text" class="form-control" id="ger-contato" placeholder="Insira o contato"></div>
                    <div class="form-group"><label>Endereço</label><input type="text" class="form-control" id="ger-endereco" placeholder="Insira o endereço"></div>
                    <div class="form-group"><label>Saldo</label><input type="number" class="form-control" id="ger-saldo" step="0.01"></div>
                </div>
                <div class="col-md-4">
                    <div class="form-group"><label>Comissão Líquida % (Geral Rua)</label><input type="number" class="form-control" id="ger-comissao" step="0.01"></div>
                    <div class="form-group bg-info p-2 rounded">
                        <label class="text-white">Comissão Gestão Online %</label>
                        <input type="number" class="form-control" id="ger-comissao-online" step="0.01" style="font-weight: bold;">
                        <small class="text-white">Ganha esta % sobre o volume total dos seus afiliados no online.</small>
                    </div>
                    <div class="form-group"><label>Situação</label>
                        <select class="form-control" id="ger-situacao">
                            <option value="ativo">Ativo</option>
                            <option value="bloqueado">Bloqueado</option>
                        </select>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Editar</button>
        </form>
    </div>
</div>
@stop
@section('js')
<script>
$(document).ready(function(){
    var params = new URLSearchParams(window.location.search);
    var id = params.get('id');
    if(!id){ toastr.error('ID do gerente não informado!'); return; }

    $.get('/admin/gerentes-list', function(data){
        var g = data.find(function(u){ return u.id == id; });
        if(!g){ toastr.error('Gerente não encontrado!'); return; }
        $('#ger-id').val(g.id);
        $('#ger-name').val(g.name);
        $('#ger-username').val(g.username);
        $('#ger-contato').val(g.contato);
        $('#ger-endereco').val(g.endereco);
        $('#ger-saldo').val(g.saldo_gerente);
        $('#ger-comissao').val(g.comissao_gerente);
        $('#ger-comissao-online').val(g.comissao_gerente_online || 0); // 🚀 Carrega comissão fixa online
        $('#ger-situacao').val(g.situacao);
        $('#edit-loading').hide();
        $('#form-edit-gerente').show();
    });

    $('#form-edit-gerente').submit(function(e){
        e.preventDefault();
        var data = {
            _token: $('meta[name="csrf-token"]').attr('content'),
            name: $('#ger-name').val(),
            username: $('#ger-username').val(),
            contato: $('#ger-contato').val(),
            endereco: $('#ger-endereco').val(),
            saldo_gerente: $('#ger-saldo').val(),
            comissao_gerente: $('#ger-comissao').val(),
            comissao_gerente_online: $('#ger-comissao-online').val(), // 🚀 Envia comissão fixa online
            situacao: $('#ger-situacao').val()
        };
        var pw = $('#ger-password').val();
        if(pw) data.password = pw;

        $.ajax({
            url: '/admin/editar-gerente/'+$('#ger-id').val(),
            type: 'PUT',
            data: data,
            success: function(){ toastr.info('Gerente atualizado!'); window.location.href='/admin/gerentes'; },
            error: function(){ toastr.error('Erro ao atualizar!'); }
        });
    });
});
</script>
@stop