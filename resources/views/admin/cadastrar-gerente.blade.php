@extends('adminlte::page')
@section('title', 'Cadastrar Gerente | IHUB BETS')

@section('content_header')
    <h1><i class="fas fa-user-tie" style="color: #10b981;"></i> Cadastrar Gerente <small class="text-muted">(Novo Gestor)</small></h1>
@stop

@section('content')
<div class="container-fluid">
    <form id="form-cad-gerente" autocomplete="off">
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
                            <input type="text" name="name" class="form-control form-control-sm" placeholder="Ex: Carlos Silva" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-2">
                            <label class="text-xs font-weight-bold">Login (Usuário) <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control form-control-sm" placeholder="Ex: carlos.silva" required autocomplete="new-password">
                        </div>
                    </div>
                    <div class="col-md-4">
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
        {{-- SEÇÃO 2 — SALDOS E COMISSÕES --}}
        {{-- ═══════════════════════════════════════════════════════ --}}
        <div class="row">
            <div class="col-md-4">
                <div class="card card-outline card-primary mb-3 shadow-sm h-100">
                    <div class="card-header py-2">
                        <h3 class="card-title text-sm font-weight-bold"><i class="fas fa-wallet text-primary"></i> SALDO INICIAL</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-0">
                            <label class="text-xs font-weight-bold">Saldo do Gerente (R$) <i class="fas fa-info-circle text-primary ml-1" data-toggle="tooltip" title="Defina o saldo (limite de crédito) que este gerente terá para distribuir aos seus cambistas."></i></label>
                            <input type="number" name="saldo_gerente" class="form-control form-control-lg text-primary font-weight-bold" step="0.01" value="0">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card card-outline card-secondary mb-3 shadow-sm h-100">
                    <div class="card-header py-2">
                        <h3 class="card-title text-sm font-weight-bold"><i class="fas fa-store text-secondary"></i> COMISSÃO OPERAÇÃO RUA</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-0">
                            <label class="text-xs font-weight-bold">Comissão Sobre Lucro (%) <i class="fas fa-info-circle text-secondary ml-1" data-toggle="tooltip" title="Percentual sobre o lucro líquido (Prejuízo da Casa) gerado pelos cambistas sob gestão deste gerente nas apostas físicas."></i></label>
                            <input type="number" name="comissao_gerente" class="form-control form-control-lg font-weight-bold" step="0.01" value="0">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card card-outline card-info mb-3 shadow-sm h-100">
                    <div class="card-header py-2 bg-info text-white">
                        <h3 class="card-title text-sm font-weight-bold"><i class="fas fa-globe"></i> COMISSÃO GESTÃO ONLINE</h3>
                    </div>
                    <div class="card-body bg-light">
                        <div class="form-group mb-0">
                            <label class="text-xs font-weight-bold text-info">Taxa de Gestão Online (%) <i class="fas fa-info-circle ml-1" data-toggle="tooltip" title="O gerente ganha esta % sobre o volume total de apostas feitas pelos clientes diretos e clientes de seus cambistas online."></i></label>
                            <input type="number" name="comissao_gerente_online" class="form-control form-control-lg text-info font-weight-bold border-info" step="0.01" value="0">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4 mb-5">
            <div class="col-md-12">
                <button type="submit" class="btn btn-success btn-lg btn-block shadow-sm font-weight-bold">
                    <i class="fas fa-save mr-2"></i> CADASTRAR GERENTE
                </button>
            </div>
        </div>
    </form>
</div>
@stop

@section('js')
<script>
$(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip();
});

function togglePassword() {
    var inp = document.getElementById('inp-password');
    var icon = document.getElementById('icon-eye');
    if (inp.type === "password") {
        inp.type = "text";
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        inp.type = "password";
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

$('#form-cad-gerente').submit(function(e){
    e.preventDefault();
    var btn = $(this).find('button[type="submit"]');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> SALVANDO...');

    $.post('/admin/cadastrar-gerente', $(this).serialize(), function(){
        toastr.success('Gerente cadastrado com sucesso!');
        setTimeout(function(){ window.location.href='/admin/gerentes'; }, 1000);
    }).fail(function(xhr){
        btn.prop('disabled', false).html('<i class="fas fa-save mr-2"></i> CADASTRAR GERENTE');
        var msg = 'Erro ao cadastrar!';
        if(xhr.responseJSON && xhr.responseJSON.errors){
            var errs = xhr.responseJSON.errors;
            for(var k in errs) msg += '<br>' + errs[k][0];
        }
        toastr.error(msg);
    });
});
</script>
@stop
