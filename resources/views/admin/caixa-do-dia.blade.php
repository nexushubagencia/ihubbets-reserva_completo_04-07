@extends('adminlte::page')
@section('title', config('adminlte.title_adm_geral') . ' - Caixa do Dia')
@section('content_header')
    <h1><i class="fas fa-calendar-day"></i> Caixa do Dia <small>Visao Financeira em Tempo Real</small></h1>
@stop
@section('content')
<div class="row">
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-success"><i class="fas fa-arrow-down"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Entradas Hoje</span>
                <span class="info-box-number" id="total-entradas">R$ 0,00</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-danger"><i class="fas fa-arrow-up"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Saidas Hoje</span>
                <span class="info-box-number" id="total-saidas">R$ 0,00</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-warning"><i class="fas fa-percentage"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Comissoes</span>
                <span class="info-box-number" id="total-comissoes">R$ 0,00</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-info"><i class="fas fa-chart-line"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Lucro Liquido</span>
                <span class="info-box-number" id="total-liquido">R$ 0,00</span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-secondary"><i class="fas fa-receipt"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Lancamentos</span>
                <span class="info-box-number" id="total-lancamentos">R$ 0,00</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-dark"><i class="fas fa-ticket-alt"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Apostas</span>
                <span class="info-box-number" id="total-apostas">0</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-primary"><i class="fas fa-lock"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Fechamentos Hoje</span>
                <span class="info-box-number" id="total-fechamentos">0</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box bg-gradient-info">
            <span class="info-box-icon text-white"><i class="fas fa-university"></i></span>
            <div class="info-box-content">
                <span class="info-box-text text-white">Liquido Fechado</span>
                <span class="info-box-number text-white" id="total-liquido-fechado">R$ 0,00</span>
            </div>
        </div>
    </div>
</div>

<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-users"></i> Cambistas e Gerentes</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" onclick="loadData()"><i class="fas fa-sync-alt"></i></button>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover table-bordered text-center" id="tabela-caixa">
            <thead>
                <tr style="background:#00466A;color:#fff;font-size:13px;">
                    <th>USUARIO</th>
                    <th>NIVEL</th>
                    <th>APOSTAS</th>
                    <th>ENTRADAS</th>
                    <th>SAIDAS</th>
                    <th>COMISSOES</th>
                    <th>LANCAMENTOS</th>
                    <th>LIQUIDO</th>
                    <th>ACOES</th>
                </tr>
            </thead>
            <tbody id="caixa-tbody">
                <tr><td colspan="9"><i class="fas fa-spinner fa-spin"></i> Carregando...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<div class="card card-info card-outline">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-history"></i> Fechamentos de Hoje</h3>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover table-bordered text-center">
            <thead>
                <tr style="background:#2C3B41;color:#fff;font-size:13px;">
                    <th>HORA</th>
                    <th>USUARIO</th>
                    <th>TURNO</th>
                    <th>ENTRADAS</th>
                    <th>SAIDAS</th>
                    <th>LIQUIDO</th>
                    <th>FECHADO POR</th>
                </tr>
            </thead>
            <tbody id="fechamentos-tbody">
                <tr><td colspan="7" class="text-muted">Nenhum fechamento hoje</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Preview Fechamento -->
<div class="modal fade" id="modal-preview" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white">
                <h4 class="modal-title font-weight-bold"><i class="fas fa-eye mr-2"></i> Preview Fechamento: <span id="preview-colaborador" class="text-info"></span></h4>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="preview-id">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="font-weight-bold">Turno:</label>
                        <select id="preview-turno" class="form-control">
                            <option value="integral">Integral</option>
                            <option value="manha">Manha</option>
                            <option value="tarde">Tarde</option>
                            <option value="noite">Noite</option>
                        </select>
                    </div>
                </div>
                <ul class="list-group list-group-flush mb-3">
                    <li class="list-group-item d-flex justify-content-between">
                        <span><i class="fas fa-arrow-down text-success mr-2"></i> Entradas</span>
                        <span id="preview-entradas" class="font-weight-bold text-success"></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span><i class="fas fa-arrow-up text-danger mr-2"></i> Saidas (Premios)</span>
                        <span id="preview-saidas" class="font-weight-bold text-danger"></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span><i class="fas fa-percentage text-warning mr-2"></i> Comissoes</span>
                        <span id="preview-comissoes" class="font-weight-bold text-warning"></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span><i class="fas fa-exchange-alt text-info mr-2"></i> Lancamentos</span>
                        <span id="preview-lancamentos" class="font-weight-bold text-info"></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span><i class="fas fa-percentage text-primary mr-2"></i> Comissao Gerente</span>
                        <span id="preview-comissao-gerente" class="font-weight-bold text-primary"></span>
                    </li>
                </ul>
                <div class="alert text-center shadow-sm" id="preview-total-div" style="font-size:22px;font-weight:bold;border-radius:10px;">
                    Liquido: <span id="preview-liquido"></span>
                </div>
                <div id="preview-aviso" class="alert alert-warning text-center d-none">
                    <i class="fas fa-exclamation-triangle"></i> Ha apostas em aberto. O fechamento sera feito mesmo assim.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger font-weight-bold" id="btn-confirmar-fechamento" onclick="confirmarFechamento()">
                    <i class="fas fa-lock"></i> Confirmar Fechamento
                </button>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .positivo{background:#009688!important;color:#FFF!important;}
    .negativo{background:#FF0000!important;color:#FFF!important;}
    .info-box-number{font-size:1.1rem;}
    .table td,.table th{vertical-align:middle;}
    .btn-fakechar{opacity:0.85;}
    .btn-fakechar:hover{opacity:1;}
</style>
@stop

@section('js')
<script>
function fm(n){return "R$ "+parseFloat(n||0).toFixed(2).replace('.',',').replace(/(\d)(?=(\d{3})+\,)/g,"$1.");}

$(document).ready(function(){
    loadData();
    setInterval(loadData, 30000);
});

function loadData(){
    $.get('/admin/caixa-do-dia/geral', function(data){
        renderResumo(data);
        renderTabela(data.usuarios || []);
    }).fail(function(){
        toastr.error('Erro ao carregar dados do caixa.');
    });
}

function renderResumo(d){
    $('#total-entradas').text(fm(d.total_entradas));
    $('#total-saidas').text(fm(d.total_saidas));
    $('#total-comissoes').text(fm(d.total_comissoes));
    $('#total-lancamentos').text(fm(d.total_lancamentos));
    $('#total-liquido').text(fm(d.total_liquido));
    $('#total-apostas').text(d.total_apostas || 0);
    $('#total-fechamentos').text(d.fechamentos_hoje || 0);
    
    let liq = parseFloat(d.total_liquido||0);
    $('#total-liquido').css('color', liq >= 0 ? '#009688' : '#FF0000');
}

function renderTabela(users){
    var tb = $('#caixa-tbody');
    tb.empty();
    if(!users || users.length === 0){
        tb.append('<tr><td colspan="9" class="text-muted">Nenhum usuario encontrado</td></tr>');
        return;
    }
    users.forEach(function(u){
        var cls = parseFloat(u.total) >= 0 ? 'positivo' : 'negativo';
        var nivelBadge = u.nivel === 'gerente' ? 'badge badge-warning' : 'badge badge-info';
        tb.append(
            '<tr>'+
            '<td><strong>'+u.name+'</strong></td>'+
            '<td><span class="'+nivelBadge+'">'+(u.nivel === 'gerente' ? 'GERENTE' : 'CAMBISTA')+'</span></td>'+
            '<td>'+u.quantidade+'</td>'+
            '<td class="text-success font-weight-bold">'+fm(u.entradas)+'</td>'+
            '<td class="text-danger font-weight-bold">'+fm(u.saidas)+'</td>'+
            '<td class="text-warning font-weight-bold">'+fm(u.comissoes)+'</td>'+
            '<td class="text-info font-weight-bold">'+fm(u.lancamentos)+'</td>'+
            '<td class="'+cls+' font-weight-bold">'+fm(u.total)+'</td>'+
            '<td>'+
                '<div class="btn-group btn-group-sm">'+
                '<button class="btn btn-outline-primary" title="Preview" onclick="openPreview('+u.id+', \''+u.name.replace(/'/g,"\\'")+'\')"><i class="fas fa-eye"></i></button>'+
                '<button class="btn btn-outline-info" title="Historico" onclick="verHistorico('+u.id+', \''+u.name.replace(/'/g,"\\'")+'\')"><i class="fas fa-history"></i></button>'+
                '</div>'+
            '</td>'+
            '</tr>'
        );
    });
}

function openPreview(id, name){
    $('#preview-id').val(id);
    $('#preview-colaborador').text(name);
    $('#preview-entradas').text('...');
    $('#preview-saidas').text('...');
    $('#preview-comissoes').text('...');
    $('#preview-lancamentos').text('...');
    $('#preview-comissao-gerente').text('...');
    $('#preview-liquido').text('...');
    $('#preview-aviso').addClass('d-none');
    $('#modal-preview').modal('show');

    $.get('/admin/preview-caixa/'+id, function(resp){
        if(resp.success){
            var d = resp.data;
            $('#preview-entradas').text(fm(d.totalEntradas));
            $('#preview-saidas').text(fm(d.totalSaidas));
            $('#preview-comissoes').text(fm(d.totalComissoes));
            $('#preview-lancamentos').text(fm(d.totalLancamentos));
            $('#preview-comissao-gerente').text(fm(d.comissaoGerente));
            $('#preview-liquido').text(fm(d.liquido));

            var isPositive = parseFloat(d.liquido) >= 0;
            $('#preview-total-div').removeClass('alert-success alert-danger text-success text-danger')
                .addClass(isPositive ? 'alert-success text-success' : 'alert-danger text-danger');

            if(d.totalAbertas > 0){
                $('#preview-aviso').removeClass('d-none');
            }
        } else {
            toastr.error(resp.message || 'Erro ao gerar preview.');
        }
    }).fail(function(){
        toastr.error('Erro ao carregar preview.');
    });
}

function confirmarFechamento(){
    var id = $('#preview-id').val();
    var turno = $('#preview-turno').val();
    if(!confirm('CONFIRMAR FECHAMENTO DO CAIXA?\n\nEsta acao ira ZERAR todos os campos financeiros deste usuario.')) return;

    $.ajax({
        url: '/admin/encerrar-caixa/'+id,
        type: 'PUT',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            turno: turno
        },
        success: function(resp){
            if(resp.success){
                toastr.success(resp.message);
                $('#modal-preview').modal('hide');
                loadData();
            } else {
                toastr.error(resp.message || 'Erro ao fechar caixa.');
            }
        },
        error: function(){
            toastr.error('Erro ao fechar caixa.');
        }
    });
}

function verHistorico(id, name){
    var url = '/admin/historico-fechamentos/'+id;
    window.open(url, '_blank');
}
</script>
@stop
