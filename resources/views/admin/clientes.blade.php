@extends('adminlte::page')

@section('title', 'Clientes')

@section('content_header')
    <h1><i class="fa fa-users"></i> Clientes <small>Cadastrados no sistema</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('home') }}"><i class="fa fa-dashboard"></i> Página Inicial</a></li>
        <li class="active">Clientes</li>
    </ol>
@stop

@section('content')
<div class="box">
    <div class="box-header with-border">
        <h3 class="box-title">Lista de Clientes</h3>
        <div class="box-tools pull-right">
            <div class="input-group input-group-sm" style="width: 200px;">
                <input type="text" id="searchCliente" class="form-control pull-right" placeholder="Buscar cliente...">
                <div class="input-group-btn">
                    <button type="submit" class="btn btn-primary" onclick="loadClientes()"><i class="fa fa-search"></i></button>
                </div>
            </div>
        </div>
    </div>
    <div class="box-body no-padding">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Username</th>
                    <th>Saldo</th>
                    <th>Crédito</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody id="clientesList">
                <tr><td colspan="7" class="text-center">Carregando...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Editar Cliente -->
<div class="modal fade" id="editClienteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title">Editar Cliente</h4>
            </div>
            <form id="editClienteForm">
                <div class="modal-body">
                    <input type="hidden" id="edit_id">
                    <div class="form-group">
                        <label>Nome</label>
                        <input type="text" class="form-control" id="edit_name" required>
                    </div>
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" class="form-control" id="edit_username">
                    </div>
                    <div class="form-group">
                        <label>Senha (deixe vazio para manter)</label>
                        <input type="password" class="form-control" id="edit_password">
                    </div>
                    <div class="form-group">
                        <label>CPF</label>
                        <input type="text" class="form-control" id="edit_cpf">
                    </div>
                    <div class="form-group">
                        <label>Telefone</label>
                        <input type="text" class="form-control" id="edit_phone">
                    </div>
                    <div class="form-group">
                        <label>Chave PIX</label>
                        <input type="text" class="form-control" id="edit_pix_key">
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select class="form-control" id="edit_situacao">
                            <option value="ativo">Ativo</option>
                            <option value="inativo">Inativo</option>
                            <option value="bloqueado">Bloqueado</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        loadClientes();

        $('#searchCliente').on('keyup', function() {
            loadClientes($(this).val());
        });

        $('#editClienteForm').on('submit', function(e) {
            e.preventDefault();
            var id = $('#edit_id').val();
            $.ajax({
                url: '/admin/editar-cliente/' + id,
                type: 'PUT',
                data: {
                    _token: '{{ csrf_token() }}',
                    name: $('#edit_name').val(),
                    username: $('#edit_username').val(),
                    password: $('#edit_password').val(),
                    cpf: $('#edit_cpf').val(),
                    phone: $('#edit_phone').val(),
                    pix_key: $('#edit_pix_key').val(),
                    situacao: $('#edit_situacao').val()
                },
                success: function(res) {
                    $('#editClienteModal').modal('hide');
                    loadClientes();
                    toastr.success('Cliente atualizado com sucesso!');
                },
                error: function() {
                    toastr.error('Erro ao atualizar cliente.');
                }
            });
        });
    });

    function loadClientes(search) {
        var url = search ? '/admin/clientes/search/' + search : '/admin/clientes/list';
        $.get(url, function(data) {
            var html = '';
            if (data.length === 0) {
                html = '<tr><td colspan="7" class="text-center">Nenhum cliente encontrado</td></tr>';
            } else {
                $.each(data, function(i, c) {
                    var statusLabel = c.situacao === 'ativo' ? '<span class="label label-success">Ativo</span>' :
                                      c.situacao === 'bloqueado' ? '<span class="label label-danger">Bloqueado</span>' :
                                      '<span class="label label-warning">' + (c.situacao || 'N/A') + '</span>';
                    html += '<tr>';
                    html += '<td>' + c.id + '</td>';
                    html += '<td>' + (c.name || '') + '</td>';
                    html += '<td>' + (c.username || '') + '</td>';
                    html += '<td>R$ ' + parseFloat(c.balance || 0).toFixed(2) + '</td>';
                    html += '<td>R$ ' + parseFloat(c.credito || 0).toFixed(2) + '</td>';
                    html += '<td>' + statusLabel + '</td>';
                    html += '<td>';
                    html += '<button class="btn btn-xs btn-info" onclick="editCliente(' + c.id + ', \'' + (c.name||'').replace(/'/g,"\\'") + '\', \'' + (c.username||'') + '\', \'' + (c.cpf||'') + '\', \'' + (c.phone||'') + '\', \'' + (c.pix_key||'') + '\', \'' + (c.situacao||'') + '\')"><i class="fa fa-edit"></i></button> ';
                    html += '<button class="btn btn-xs btn-danger" onclick="deleteCliente(' + c.id + ')"><i class="fa fa-trash"></i></button>';
                    html += '</td>';
                    html += '</tr>';
                });
            }
            $('#clientesList').html(html);
        });
    }

    function editCliente(id, name, username, cpf, phone, pix_key, situacao) {
        $('#edit_id').val(id);
        $('#edit_name').val(name);
        $('#edit_username').val(username);
        $('#edit_cpf').val(cpf);
        $('#edit_phone').val(phone);
        $('#edit_pix_key').val(pix_key);
        $('#edit_situacao').val(situacao);
        $('#edit_password').val('');
        $('#editClienteModal').modal('show');
    }

    function deleteCliente(id) {
        if (confirm('Tem certeza que deseja excluir este cliente?')) {
            $.ajax({
                url: '/admin/deletar-cliente/' + id,
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    loadClientes();
                    toastr.success('Cliente excluído com sucesso!');
                },
                error: function(xhr) {
                    var msg = xhr.responseJSON ? xhr.responseJSON.error : 'Erro ao excluir.';
                    toastr.error(msg);
                }
            });
        }
    }
</script>
@stop
