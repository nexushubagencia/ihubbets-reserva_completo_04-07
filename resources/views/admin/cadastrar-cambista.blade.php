@extends('adminlte::page')
@section('title', 'Cadastrar Cambista | IHUB BETS')

@section('content_header')
    <h1><i class="fas fa-user-plus" style="color: #10b981;"></i> Cadastrar Cambista <small class="text-muted">(Novo Operador)</small></h1>
    
@stop

@section('content')
<div class="container-fluid">
    <form id="form-cad-cambista" autocomplete="off">
        @csrf

        {{-- ═══════════════════════════════════════════════════════ --}}
        {{-- SEÇÃO 1 — DADOS PESSOAIS --}}
        {{-- ═══════════════════════════════════════════════════════ --}}
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
                                <input type="password" name="password" id="inp-password" class="form-control form-control-sm" placeholder="Mín. 6 caracteres" required autocomplete="new-password">
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

        {{-- ═══════════════════════════════════════════════════════ --}}
        {{-- SEÇÃO 2 — SALDOS INICIAIS (OPERAÇÃO DE RUA) --}}
        {{-- ═══════════════════════════════════════════════════════ --}}
        <div class="card card-outline card-primary mb-3 shadow-sm">
            <div class="card-header py-2">
                <h3 class="card-title text-sm font-weight-bold"><i class="fas fa-wallet text-primary"></i> SALDOS INICIAIS (OPERAÇÃO DE RUA) <i class="fas fa-info-circle text-primary ml-1" data-toggle="tooltip" title="Defina os créditos iniciais que este Cambista terá para operar no modo Rua (bilhetes físicos). Se operar apenas Online, pode deixar zerado."></i></h3>
            </div>
            <div class="card-body pb-2">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group mb-2">
                            <label class="text-xs font-weight-bold">Saldo Simples (R$)</label>
                            <input type="number" name="saldo_simples" class="form-control form-control-sm" step="0.01" value="0">
                            <small class="text-muted text-xs">Crédito para bilhetes simples.</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-2">
                            <label class="text-xs font-weight-bold">Saldo Casadinha (R$)</label>
                            <input type="number" name="saldo_casadinha" class="form-control form-control-sm" step="0.01" value="0">
                            <small class="text-muted text-xs">Crédito para apostas múltiplas.</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-2">
                            <label class="text-xs font-weight-bold">Saldo Loto (R$)</label>
                            <input type="number" name="saldo_loto" class="form-control form-control-sm" step="0.01" value="0">
                            <small class="text-muted text-xs">Crédito p/ modalidade loteria.</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-2">
                            <label class="text-xs font-weight-bold">Comissão Loto (%)</label>
                            <input type="number" name="comissao_loto" class="form-control form-control-sm" step="0.01" value="0">
                            <small class="text-muted text-xs">Taxa de comissão sobre loteria.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════ --}}
        {{-- SEÇÃO 3 — COMISSÕES --}}
        {{-- ═══════════════════════════════════════════════════════ --}}
        <div class="row">
            {{-- COLUNA ESQUERDA: COMISSÃO RUA (por quantidade de jogos) --}}
            <div class="col-md-7">
                <div class="card card-outline card-secondary mb-3 shadow-sm">
                    <div class="card-header py-2">
                        <h3 class="card-title text-sm font-weight-bold"><i class="fas fa-store text-secondary"></i> COMISSÃO DE RUA (por quantidade de jogos no bilhete) <i class="fas fa-info-circle text-secondary ml-1" data-toggle="tooltip" title="Percentual de comissão que o Cambista ganha quando um cliente faz uma aposta presencial (bilhete físico). A taxa depende da quantidade de jogos no bilhete."></i></h3>
                    </div>
                    <div class="card-body pb-2">
                        <div class="row">
                            @for($i = 1; $i <= 10; $i++)
                            <div class="col-md-{{ $i <= 5 ? '4' : '4' }} col-6">
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

            {{-- COLUNA DIREITA: COMISSÃO ONLINE (AFILIADO) --}}
            <div class="col-md-5">
                <div class="card card-outline card-info mb-3 shadow-sm">
                    <div class="card-header py-2">
                        <h3 class="card-title text-sm font-weight-bold"><i class="fas fa-globe text-info"></i> COMISSÃO ONLINE (AFILIADO) <i class="fas fa-info-circle text-info ml-1" data-toggle="tooltip" title="Taxa fixa que este Cambista/Afiliado ganha sobre cada aposta feita por usuários que ele indicou via link de afiliado no site."></i></h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label class="text-sm font-weight-bold text-info">Comissão Fixa Online (%)</label>
                            <input type="number" name="comissao_online" class="form-control" step="0.01" value="0" style="font-size: 1.4rem; font-weight: bold; color: #0dcaf0; border: 2px solid #0dcaf0; text-align: center;">
                        </div>

                        <div class="alert alert-light border mb-0 shadow-none">
                            <h6 class="font-weight-bold text-dark text-xs mb-1"><i class="fas fa-lightbulb text-warning"></i> Como funciona:</h6>
                            <ul class="text-xs text-muted pl-3 mb-0" style="line-height: 1.8;">
                                <li>Um apostador se cadastra usando o <strong>link de indicação</strong> deste cambista.</li>
                                <li>Toda vez que esse apostador fizer uma aposta, este cambista ganha <strong>X%</strong> do valor apostado.</li>
                                <li>O <strong>Gerente</strong> vinculado a ele também ganha sua própria taxa (configurada no perfil do Gerente).</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════ --}}
        {{-- BOTÃO DE AÇÃO --}}
        {{-- ═══════════════════════════════════════════════════════ --}}
        <div class="text-right mb-4">
            <a href="/admin/cambistas" class="btn btn-secondary mr-2"><i class="fas fa-arrow-left"></i> Voltar</a>
            <button type="submit" class="btn btn-success px-5 shadow-sm" id="btn-submit-cambista">
                <i class="fas fa-save"></i> Finalizar Cadastro
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
    $('[data-toggle="tooltip"]').tooltip();
    // Carregar lista de Gerentes
    $.get('/admin/list-gerentes', function(data){
        if (data && data.length > 0) {
            data.forEach(function(g){
                $('#cad-gerente').append('<option value="'+g.id+'">'+g.name+'</option>');
            });
        }
    });
});

// Toggle de visualização da senha
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

// Submit do Formulário
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
            setTimeout(function(){ window.location.href = '/admin/cambistas'; }, 1200);
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
