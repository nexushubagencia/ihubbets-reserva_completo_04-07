@extends('adminlte::page')

@section('title', 'Financeiro / PIX (Usuários) | IHUB BETS')

@section('content_header')
    <h1><i class="fas fa-users-cog" style="color: #6366f1;"></i> Financeiro / PIX <small class="text-muted">(Dados dos Usuários)</small></h1>
    
@stop

@section('content')
<div class="container-fluid">
    <!-- FILTRO RÁPIDO -->
    <div class="card shadow-sm mb-3">
        <div class="card-body p-2 bg-light d-flex justify-content-between align-items-center">
            <div class="btn-group" role="group" aria-label="Filtro de Cargos">
                <button type="button" class="btn btn-sm btn-outline-secondary px-3 active" onclick="filterRole('all')">Todos</button>
                <button type="button" class="btn btn-sm btn-outline-secondary px-3" onclick="filterRole('manager')">Gerentes</button>
                <button type="button" class="btn btn-sm btn-outline-secondary px-3" onclick="filterRole('seller')">Cambistas</button>
                <button type="button" class="btn btn-sm btn-outline-secondary px-3" onclick="filterRole('user')">Clientes</button>
            </div>
            <div>
                <input type="text" id="user-search-input" class="form-control form-control-sm shadow-none" placeholder="Buscar por Nome ou Usuário..." style="width: 250px;" onkeyup="filterText()">
            </div>
        </div>
    </div>

    <!-- TABELA PRINCIPAL -->
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover table-striped mb-0 text-center align-middle" id="pix-table">
                <thead class="bg-dark text-white text-xs">
                    <tr>
                        <th>NOME / USUÁRIO</th>
                        <th>CARGO</th>
                        <th>SALDO ATUAL</th>
                        <th>TIPO CHAVE PIX</th>
                        <th>CHAVE PIX</th>
                        <th>CPF</th>
                        <th>AÇÕES</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse($usuarios as $user)
                        <tr class="user-row" data-role="{{ $user->role }}" data-text="{{ strtolower($user->name . ' ' . $user->username) }}">
                            <td class="text-left pl-4 font-weight-bold">
                                {{ $user->name }} <br>
                                <span class="text-muted text-xs"><i class="fas fa-user"></i> {{ $user->username }}</span>
                            </td>
                            <td>
                                @if($user->role == 'manager')
                                    <span class="badge badge-primary px-2 py-1">Gerente</span>
                                @elseif($user->role == 'seller')
                                    <span class="badge badge-success px-2 py-1">Cambista</span>
                                @else
                                    <span class="badge badge-info px-2 py-1">Cliente</span>
                                @endif
                            </td>
                            <td class="font-weight-bold text-success">
                                R$ {{ number_format($user->balance, 2, ',', '.') }}
                            </td>
                            <td>
                                <span class="text-uppercase text-xs font-weight-bold text-secondary">
                                    {{ $user->pix_key_type ?? 'Não Cadastrado' }}
                                </span>
                            </td>
                            <td class="text-muted font-weight-bold text-xs">
                                {{ $user->pix_key ?? '---' }}
                            </td>
                            <td class="text-xs">
                                {{ $user->cpf ?? '---' }}
                            </td>
                            <td>
                                <button class="btn btn-xs btn-primary shadow-sm px-2 py-1" onclick="openPixModal({{ json_encode($user) }})">
                                    <i class="fas fa-edit"></i> Editar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-muted p-4"><i class="fas fa-info-circle"></i> Nenhum usuário elegível encontrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL EDIÇÃO PIX -->
<div class="modal fade" id="modalPixEdit" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content shadow">
            <div class="modal-header bg-primary text-white py-2">
                <h5 class="modal-title text-md font-weight-bold"><i class="fas fa-edit"></i> Alterar Dados Financeiros</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="form-edit-pix">
                    <input type="hidden" id="edit-user-id">
                    <div class="form-group">
                        <label class="text-sm font-weight-bold">Nome do Usuário:</label>
                        <input type="text" id="edit-user-name" class="form-control form-control-sm" disabled>
                    </div>

                    <div class="form-group">
                        <label class="text-sm font-weight-bold">Saldo Atual (R$):</label>
                        <input type="number" id="edit-user-balance" class="form-control form-control-sm" step="0.01">
                    </div>

                    <div class="form-group">
                        <label class="text-sm font-weight-bold">Tipo de Chave PIX:</label>
                        <select id="edit-user-pix-type" class="form-control form-control-sm">
                            <option value="">Nenhum</option>
                            <option value="cpf">CPF</option>
                            <option value="cnpj">CNPJ</option>
                            <option value="email">E-Mail</option>
                            <option value="telefone">Telefone</option>
                            <option value="aleatoria">Chave Aleatória</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="text-sm font-weight-bold">Chave PIX:</label>
                        <input type="text" id="edit-user-pix-key" class="form-control form-control-sm" placeholder="Ex: 12345678910">
                    </div>

                    <div class="form-group">
                        <label class="text-sm font-weight-bold">CPF Cadastrado:</label>
                        <input type="text" id="edit-user-cpf" class="form-control form-control-sm" placeholder="Ex: 000.000.000-00">
                    </div>
                </form>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-sm btn-secondary shadow-sm" data-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-sm btn-success shadow-sm" onclick="saveUserPixData()"><i class="fas fa-save"></i> Atualizar Dados</button>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
    // Filtros visuais de tabela (JS Client-side para agilidade)
    function filterRole(role) {
        $('.btn-group .btn').removeClass('active');
        $(event.target).addClass('active');

        if (role === 'all') {
            $('.user-row').show();
        } else {
            $('.user-row').hide();
            $('.user-row[data-role="'+role+'"]').show();
        }
    }

    function filterText() {
        var query = $('#user-search-input').val().toLowerCase();
        $('.user-row').each(function() {
            var text = $(this).data('text');
            if (text.includes(query)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }

    // Modal de Edição
    function openPixModal(user) {
        $('#edit-user-id').val(user.id);
        $('#edit-user-name').val(user.name + ' (' + user.username + ')');
        $('#edit-user-balance').val(user.balance);
        $('#edit-user-pix-type').val(user.pix_key_type || '');
        $('#edit-user-pix-key').val(user.pix_key || '');
        $('#edit-user-cpf').val(user.cpf || '');
        $('#modalPixEdit').modal('show');
    }

    function saveUserPixData() {
        var id = $('#edit-user-id').val();
        var payload = {
            _token: $('meta[name="csrf-token"]').attr('content'),
            balance: $('#edit-user-balance').val(),
            pix_key_type: $('#edit-user-pix-type').val(),
            pix_key: $('#edit-user-pix-key').val(),
            cpf: $('#edit-user-cpf').val()
        };

        toastr.info('Enviando dados...');

        // Vamos criar uma rota de atualização simples ou usar o UserApiController
        $.ajax({
            url: '/admin/user-financial-update/' + id,
            type: 'POST',
            data: payload,
            success: function(response) {
                toastr.success(response.message || 'Dados atualizados!');
                $('#modalPixEdit').modal('hide');
                setTimeout(() => { location.reload(); }, 1000);
            },
            error: function() {
                toastr.error('Falha ao atualizar dados financeiros do usuário.');
            }
        });
    }
</script>
@stop
