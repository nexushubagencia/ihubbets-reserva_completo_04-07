@extends('adminlte::page')
@section('title', 'Editar Cambista | IHUB BETS')

@section('content_header')
    <h1><i class="fas fa-user-edit" style="color: #6366f1;"></i> Editar Cambista <small class="text-muted">(Ajuste de Operador)</small></h1>
    
@stop

@section('content')
<div class="container-fluid">
    <div id="edit-loading" class="text-center p-5"><i class="fas fa-spinner fa-spin fa-3x text-primary mb-2"></i><br><span class="text-muted text-sm font-weight-bold">Carregando dados do operador...</span></div>
    
    <form id="form-edit-cambista" style="display:none;" autocomplete="off">
        @csrf
        <input type="hidden" id="camb-id">

        {{-- ═══════════════════════════════════════════════════════ --}}
        {{-- SEÇÃO 1 — DADOS PESSOAIS --}}
        {{-- ═══════════════════════════════════════════════════════ --}}
        <div class="card card-outline card-primary mb-3 shadow-sm">
            <div class="card-header py-2">
                <h3 class="card-title text-sm font-weight-bold"><i class="fas fa-id-card text-primary"></i> DADOS PESSOAIS</h3>
            </div>
            <div class="card-body pb-2">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group mb-2">
                            <label class="text-xs font-weight-bold">Nome Completo</label>
                            <input type="text" id="camb-name" class="form-control form-control-sm" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-2">
                            <label class="text-xs font-weight-bold">Login (Usuário)</label>
                            <input type="text" id="camb-username" class="form-control form-control-sm" required autocomplete="new-password">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-2">
                            <label class="text-xs font-weight-bold">Nova Senha <span class="text-muted text-xs">(Em branco para manter)</span></label>
                            <div class="input-group input-group-sm">
                                <input type="password" id="camb-password" class="form-control form-control-sm" placeholder="Mín. 6 caracteres" autocomplete="new-password">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-secondary" onclick="togglePassword()"><i class="fas fa-eye" id="icon-eye"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-2">
                            <label class="text-xs font-weight-bold">Situação</label>
                            <select id="camb-situacao" class="form-control form-control-sm">
                                <option value="ativo">Ativo</option>
                                <option value="bloqueado">Bloqueado</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group mb-2">
                            <label class="text-xs font-weight-bold">Contato / Telefone</label>
                            <input type="text" id="camb-contato" class="form-control form-control-sm" placeholder="(XX) XXXXX-XXXX">
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group mb-2">
                            <label class="text-xs font-weight-bold">Endereço</label>
                            <input type="text" id="camb-endereco" class="form-control form-control-sm" placeholder="Rua, Bairro, Cidade...">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════ --}}
        {{-- SEÇÃO 2 — SALDOS OPERACIONAIS (RUA) --}}
        {{-- ═══════════════════════════════════════════════════════ --}}
        <div class="card card-outline card-success mb-3 shadow-sm">
            <div class="card-header py-2">
                <h3 class="card-title text-sm font-weight-bold"><i class="fas fa-wallet text-success"></i> SALDOS OPERACIONAIS (OPERAÇÃO DE RUA)</h3>
            </div>
            <div class="card-body pb-2">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group mb-2">
                            <label class="text-xs font-weight-bold">Saldo Simples (R$)</label>
                            <input type="number" id="camb-saldo-simples" class="form-control form-control-sm" step="0.01">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-2">
                            <label class="text-xs font-weight-bold">Saldo Casadinha (R$)</label>
                            <input type="number" id="camb-saldo-casadinha" class="form-control form-control-sm" step="0.01">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-2">
                            <label class="text-xs font-weight-bold">Saldo Loto (R$)</label>
                            <input type="number" id="camb-saldo-loto" class="form-control form-control-sm" step="0.01">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-2">
                            <label class="text-xs font-weight-bold">Comissão Loto (%)</label>
                            <input type="number" id="camb-comissao-loto" class="form-control form-control-sm" step="0.01">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════ --}}
        {{-- SEÇÃO 3 — COMISSÕES --}}
        {{-- ═══════════════════════════════════════════════════════ --}}
        <div class="row">
            {{-- COLUNA ESQUERDA: COMISSÃO RUA --}}
            <div class="col-md-7">
                <div class="card card-outline card-secondary mb-3 shadow-sm">
                    <div class="card-header py-2">
                        <h3 class="card-title text-sm font-weight-bold"><i class="fas fa-store text-secondary"></i> COMISSÃO DE RUA (por quantidade de jogos no bilhete)</h3>
                    </div>
                    <div class="card-body pb-2">
                        <div class="row">
                            @for($i = 1; $i <= 10; $i++)
                            <div class="col-md-4 col-6">
                                <div class="form-group mb-2">
                                    <label class="text-xs font-weight-bold">{{ $i }} {{ $i == 1 ? 'Jogo' : 'Jogos' }} (%)</label>
                                    <input type="number" id="camb-c{{ $i }}" class="form-control form-control-sm" step="0.01">
                                </div>
                            </div>
                            @endfor
                        </div>
                    </div>
                </div>
            </div>

            {{-- COLUNA DIREITA: COMISSÃO ONLINE --}}
            <div class="col-md-5">
                <div class="card card-outline card-info mb-3 shadow-sm">
                    <div class="card-header py-2">
                        <h3 class="card-title text-sm font-weight-bold"><i class="fas fa-globe text-info"></i> COMISSÃO ONLINE (AFILIADO)</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label class="text-sm font-weight-bold text-info">Comissão Fixa Online (%)</label>
                            <input type="number" id="camb-comissao-online" class="form-control" step="0.01" style="font-size: 1.4rem; font-weight: bold; color: #0dcaf0; border: 2px solid #0dcaf0; text-align: center;">
                        </div>

                        <div class="alert alert-light border shadow-none mb-0 text-xs text-muted">
                            <i class="fas fa-info-circle text-info"></i> Esta taxa percentual é calculada sobre o volume total apostado por novos clientes registrados diretamente pelo link de afiliado deste usuário.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════ --}}
        {{-- BOTÕES DE AÇÃO --}}
        {{-- ═══════════════════════════════════════════════════════ --}}
        <div class="text-right mb-4">
            <a href="/admin/cambistas" class="btn btn-secondary mr-2"><i class="fas fa-arrow-left"></i> Voltar</a>
            <button type="submit" class="btn btn-success px-5 shadow-sm" id="btn-submit-edit">
                <i class="fas fa-save"></i> Salvar Alterações
            </button>
        </div>
    </form>
</div>
@stop

@section('css')
<style>
    .card-title { font-size: 0.9rem !important; }
    .form-control-sm { border-radius: 4px; }
    label { color: #495057; }
    .card-header { border-bottom: none; }
</style>
@stop

@section('js')
<script>
$(document).ready(function(){
    var params = new URLSearchParams(window.location.search);
    var id = params.get('id');
    if (!id) { 
        toastr.error('ID do cambista não informado!'); 
        $('#edit-loading').html('<span class="text-danger font-weight-bold"><i class="fas fa-times-circle"></i> Erro: ID não fornecido.</span>');
        return; 
    }

    // Carregar dados
    $.get('/admin/list-cambistas', function(data){
        var c = data.find(function(u){ return u.id == id; });
        if (!c) { 
            toastr.error('Cambista não encontrado!'); 
            $('#edit-loading').html('<span class="text-danger font-weight-bold"><i class="fas fa-times-circle"></i> Operador não localizado.</span>');
            return; 
        }

        // Popular campos
        $('#camb-id').val(c.id);
        $('#camb-name').val(c.name);
        $('#camb-username').val(c.username);
        $('#camb-contato').val(c.contato);
        $('#camb-endereco').val(c.address || c.endereco);
        $('#camb-saldo-simples').val(c.saldo_simples);
        $('#camb-saldo-casadinha').val(c.saldo_casadinha);
        $('#camb-saldo-loto').val(c.saldo_loto);
        $('#camb-comissao-loto').val(c.comissao_loto);
        $('#camb-situacao').val(c.situacao || (c.status == 1 ? 'ativo' : 'bloqueado'));
        $('#camb-comissao-online').val(c.comissao_online || 0);

        for (var i = 1; i <= 10; i++) {
            $('#camb-c' + i).val(c['comissao' + i]);
        }

        $('#edit-loading').hide();
        $('#form-edit-cambista').show();
    });

    // Enviar formulário
    $('#form-edit-cambista').submit(function(e){
        e.preventDefault();
        var btn = $('#btn-submit-edit');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Salvando...');

        var d = {
            _token: $('meta[name="csrf-token"]').attr('content'),
            name: $('#camb-name').val(),
            username: $('#camb-username').val(),
            contato: $('#camb-contato').val(),
            address: $('#camb-endereco').val(),
            saldo_simples: $('#camb-saldo-simples').val(),
            saldo_casadinha: $('#camb-saldo-casadinha').val(),
            saldo_loto: $('#camb-saldo-loto').val(),
            comissao_loto: $('#camb-comissao-loto').val(),
            situacao: $('#camb-situacao').val(),
            comissao_online: $('#camb-comissao-online').val()
        };

        var pw = $('#camb-password').val();
        if (pw) d.password = pw;

        for (var i = 1; i <= 10; i++) {
            d['comissao' + i] = $('#camb-c' + i).val();
        }

        $.ajax({
            url: '/admin/editar-cambista/' + $('#camb-id').val(),
            type: 'PUT',
            data: d,
            success: function() { 
                Swal.fire('Sucesso!', 'Os dados do operador foram atualizados!', 'success').then(() => {
                    window.location.href = '/admin/cambistas';
                });
            },
            error: function(xhr) { 
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Salvar Alterações');
                toastr.error('Erro ao atualizar os dados do operador.'); 
            }
        });
    });
});

function togglePassword() {
    var inp = document.getElementById('camb-password');
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
</script>
@stop