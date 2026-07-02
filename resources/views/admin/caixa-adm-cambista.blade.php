@extends('adminlte::page')
@section('title', config('adminlte.title_adm_geral'))
@section('content_header')
    <h1><i class="fas fa-university"></i> Caixa (Cambistas) <small>Relatorios</small></h1>
@stop
@section('content')
<div class="card card-primary card-outline">
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-3">
                <label>Selecione um Gerente:</label>
                <select id="select-gerente" class="form-control" onchange="searchCambista()">
                    <option value="Todos">Todos</option>
                </select>
            </div>
            <div class="col-md-3">
                <label>Filtro de Turno:</label>
                <select id="select-turno" class="form-control" onchange="searchCambista()">
                    <option value="integral">Integral</option>
                    <option value="manha">Manha</option>
                    <option value="tarde">Tarde</option>
                    <option value="noite">Noite</option>
                </select>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr style="background:#00466A;color:#fff;font-size:13px;text-align:center;">
                        <th>COLABORADOR</th>
                        <th>QTD</th>
                        <th>ENTRADAS</th>
                        <th>ENT. ABERTO</th>
                        <th>SAIDAS</th>
                        <th>LANCAMENTOS</th>
                        <th>COMISSOES</th>
                        <th>TOTAL</th>
                        <th>FECHAMENTO</th>
                    </tr>
                </thead>
                <tbody id="caixa-tbody" style="text-align:center;"></tbody>
                <tfoot id="caixa-tfoot">
                    <tr style="background:#00466A;color:#fff;font-size:13px;text-align:center;">
                        <td>TOTAL</td>
                        <td id="t-qtd">0</td>
                        <td id="t-ent" style="background:#009688;color:#fff;">R$ 0,00</td>
                        <td id="t-ab" style="background:#2C3B41;color:#fff;">R$ 0,00</td>
                        <td id="t-sa" style="background:#FF0000;color:#fff;">R$ 0,00</td>
                        <td id="t-la">R$ 0,00</td>
                        <td id="t-co">R$ 0,00</td>
                        <td id="t-to">R$ 0,00</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
            <div id="cc-loading" class="text-center p-3" style="display:none;"><i class="fas fa-spinner fa-spin fa-2x"></i></div>
        </div>
    </div>
</div>

<!-- Modal Preview Fechamento -->
<div class="modal fade" id="modal-preview">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white border-0">
                <h4 class="modal-title font-weight-bold"><i class="fas fa-eye text-info mr-2"></i> Preview: <span id="preview-colaborador" class="text-info"></span></h4>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-4" id="printable-preview">
                <input type="hidden" id="preview-id">
                <input type="hidden" id="preview-name">

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="font-weight-bold">Turno do Fechamento:</label>
                        <select id="preview-turno" class="form-control">
                            <option value="integral">Integral</option>
                            <option value="manha">Manha</option>
                            <option value="tarde">Tarde</option>
                            <option value="noite">Noite</option>
                        </select>
                    </div>
                </div>

                <ul class="list-group list-group-flush mb-4">
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span class="font-weight-bold text-muted"><i class="fas fa-arrow-down text-success mr-2"></i> Entradas</span>
                        <span id="preview-entradas" class="badge badge-success badge-pill" style="font-size:1rem;"></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span class="font-weight-bold text-muted"><i class="fas fa-clock text-warning mr-2"></i> Entradas (Em Aberto)</span>
                        <span id="preview-abertas" class="badge badge-warning badge-pill text-dark" style="font-size:1rem;"></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span class="font-weight-bold text-muted"><i class="fas fa-arrow-up text-danger mr-2"></i> Saidas (Premios)</span>
                        <span id="preview-saidas" class="badge badge-danger badge-pill" style="font-size:1rem;"></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span class="font-weight-bold text-muted"><i class="fas fa-exchange-alt text-info mr-2"></i> Lancamentos</span>
                        <span id="preview-lancamentos" class="badge badge-info badge-pill" style="font-size:1rem;"></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span class="font-weight-bold text-muted"><i class="fas fa-percentage text-primary mr-2"></i> Comissoes</span>
                        <span id="preview-comissoes" class="badge badge-primary badge-pill" style="font-size:1rem;"></span>
                    </li>
                </ul>

                <div class="alert text-center mb-4 shadow-sm" id="preview-total-div" style="font-size:22px;font-weight:bold;border-radius:10px;">
                    Liquido: <span id="preview-liquido"></span>
                </div>

                <div id="preview-aviso" class="alert alert-warning text-sm text-center d-none">
                    <i class="fas fa-exclamation-triangle"></i> Ha apostas em aberto. O fechamento sera realizado mesmo assim.
                </div>

                <div class="d-print-none">
                    <button class="btn btn-danger btn-lg btn-block mb-2 font-weight-bold shadow-sm" id="btn-confirmar-fechamento" onclick="confirmarFechamento()">
                        <i class="fas fa-lock"></i> Confirmar Fechamento
                    </button>
                    <div class="row">
                        <div class="col-sm-6 mb-2">
                            <button class="btn btn-success btn-block font-weight-bold shadow-sm" onclick="shareWhatsApp()">
                                <i class="fab fa-whatsapp"></i> Enviar WhatsApp
                            </button>
                        </div>
                        <div class="col-sm-6 mb-2">
                            <button class="btn btn-secondary btn-block font-weight-bold shadow-sm" onclick="printPreview()">
                                <i class="fas fa-file-pdf"></i> Salvar PDF
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Historico de Fechamentos -->
<div class="modal fade" id="modal-historico">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-info text-white border-0">
                <h4 class="modal-title font-weight-bold"><i class="fas fa-history mr-2"></i> Historico: <span id="hist-colaborador" class="text-white"></span></h4>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm text-center mb-0">
                        <thead>
                            <tr style="background:#2C3B41;color:#fff;font-size:12px;">
                                <th>DATA</th>
                                <th>HORA</th>
                                <th>TURNO</th>
                                <th>ENTRADAS</th>
                                <th>SAIDAS</th>
                                <th>LIQUIDO</th>
                                <th>FECHADO POR</th>
                            </tr>
                        </thead>
                        <tbody id="hist-tbody">
                            <tr><td colspan="7" class="text-muted">Carregando...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .positivo{background:#009688!important;color:#FFF!important;}
    .negativo{background:#FF0000!important;color:#FFF!important;}
    .status-aberto{color:#009688;font-weight:bold;}
    .status-fechado{color:#FF0000;font-weight:bold;}
</style>
@stop

@section('js')
<script>
function fm(n){return "R$ "+parseFloat(n||0).toFixed(2).replace('.',',').replace(/(\d)(?=(\d{3})+\,)/g,"$1.");}

var currentCambista = null;

$(document).ready(function(){
    $.get('/admin/list-gerentes',function(d){
        if(d) d.forEach(function(g){
            $('#select-gerente').append('<option value="'+g.id+'">'+g.name+'</option>');
        });
    });
    loadCaixa();
});

function loadCaixa(){
    $('#cc-loading').show();
    $.get('/admin/list-caixa-adm-cambista',function(data){ renderCaixa(data); })
     .fail(function(){$('#cc-loading').hide();});
}

function searchCambista(){
    var g = $('#select-gerente').val();
    if(g === 'Todos'){
        $('#caixa-tfoot').show();
        loadCaixa();
        return;
    }
    $('#caixa-tfoot').hide();
    $('#cc-loading').show();
    $.post('/admin/search-caixa-adm-cambista',{
        _token: $('meta[name="csrf-token"]').attr('content'),
        gerente: g
    },function(data){ renderCaixa(data); });
}

function renderCaixa(data){
    var tb = $('#caixa-tbody');
    tb.empty();
    $('#cc-loading').hide();
    var tQ=0,tE=0,tA=0,tS=0,tL=0,tC=0,tG=0;
    if(data){
        data.forEach(function(c){
            var cls = parseFloat(c.total) >= 0 ? 'positivo' : 'negativo';
            var hasOpen = parseFloat(c.entradas_abertas) > 0;
            var statusHtml = hasOpen
                ? '<span class="status-aberto"><i class="fas fa-circle"></i> Aberto</span>'
                : '<span class="status-fechado"><i class="fas fa-circle"></i> Limpo</span>';

            tb.append(
                '<tr>'+
                '<td><strong>'+(c.colaborador||c.name||'')+'</strong></td>'+
                '<td>'+(c.quantidade||0)+'</td>'+
                '<td>'+fm(c.entradas)+'</td>'+
                '<td>'+fm(c.entradas_abertas)+'</td>'+
                '<td>'+fm(c.saidas)+'</td>'+
                '<td>'+fm(c.lancamentos)+'</td>'+
                '<td>'+fm(c.comissoes)+'</td>'+
                '<td class="'+cls+'">'+fm(c.total)+'</td>'+
                '<td>'+
                    '<div class="btn-group btn-group-sm">'+
                    '<button class="btn btn-outline-primary btn-sm" title="Preview" onclick=\'openPreview('+JSON.stringify(c).replace(/'/g,"&#39;")+')\'><i class="fas fa-eye"></i></button>'+
                    '<button class="btn btn-outline-info btn-sm" title="Historico" onclick=\'verHistorico('+JSON.stringify(c).replace(/'/g,"&#39;")+')\'><i class="fas fa-history"></i></button>'+
                    '<button class="btn btn-outline-success btn-sm" title="Fechar" onclick=\'openFechamento('+JSON.stringify(c).replace(/'/g,"&#39;")+')\'><i class="fas fa-paper-plane"></i></button>'+
                    '</div>'+
                '</td>'+
                '</tr>'
            );
            tQ+=parseFloat(c.quantidade||0);
            tE+=parseFloat(c.entradas||0);
            tA+=parseFloat(c.entradas_abertas||0);
            tS+=parseFloat(c.saidas||0);
            tL+=parseFloat(c.lancamentos||0);
            tC+=parseFloat(c.comissoes||0);
            tG+=parseFloat(c.total||0);
        });
    }
    $('#t-qtd').text(tQ);
    $('#t-ent').text(fm(tE));
    $('#t-ab').text(fm(tA));
    $('#t-sa').text(fm(tS));
    $('#t-la').text(fm(tL));
    $('#t-co').text(fm(tC));
    $('#t-to').text(fm(tG)).attr('class', tG >= 0 ? 'positivo' : 'negativo');
}

function openFechamento(c){
    openPreview(c);
}

function openPreview(c){
    currentCambista = c;
    $('#preview-id').val(c.id);
    $('#preview-name').val(c.colaborador||c.name);
    $('#preview-colaborador').text(c.colaborador||c.name);
    $('#preview-entradas').text('...');
    $('#preview-abertas').text('...');
    $('#preview-saidas').text('...');
    $('#preview-lancamentos').text('...');
    $('#preview-comissoes').text('...');
    $('#preview-liquido').text('...');
    $('#preview-aviso').addClass('d-none');
    $('#modal-preview').modal('show');

    $.get('/admin/preview-caixa/'+c.id, function(resp){
        if(resp.success){
            var d = resp.data;
            $('#preview-entradas').text(fm(d.totalEntradas));
            $('#preview-abertas').text(fm(d.totalAbertas));
            $('#preview-saidas').text(fm(d.totalSaidas));
            $('#preview-lancamentos').text(fm(d.totalLancamentos));
            $('#preview-comissoes').text(fm(d.totalComissoes));
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
    var name = $('#preview-name').val();

    if(!confirm('CONFIRMAR FECHAMENTO DO CAIXA DE '+name+'?\n\nEsta acao ira ZERAR todos os campos financeiros.')) return;

    $.ajax({
        url: '/admin/encerrar-caixa/'+id,
        type: 'PUT',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            turno: turno
        },
        success: function(resp){
            if(resp.success){
                toastr.success(resp.message || 'Caixa fechado com sucesso!');
                $('#modal-preview').modal('hide');
                loadCaixa();
            } else {
                toastr.error(resp.message || 'Erro ao fechar caixa.');
            }
        },
        error: function(){
            toastr.error('Erro ao fechar caixa.');
        }
    });
}

function verHistorico(c){
    var name = c.colaborador || c.name;
    $('#hist-colaborador').text(name);
    $('#hist-tbody').html('<tr><td colspan="7"><i class="fas fa-spinner fa-spin"></i> Carregando...</td></tr>');
    $('#modal-historico').modal('show');

    $.get('/admin/historico-fechamentos/'+c.id, function(data){
        var tb = $('#hist-tbody');
        tb.empty();
        if(!data || data.length === 0){
            tb.append('<tr><td colspan="7" class="text-muted">Nenhum fechamento encontrado</td></tr>');
            return;
        }
        data.forEach(function(h){
            var dt = new Date(h.created_at);
            var dataStr = String(dt.getDate()).padStart(2,'0')+'/'+String(dt.getMonth()+1).padStart(2,'0')+'/'+dt.getFullYear();
            var horaStr = String(dt.getHours()).padStart(2,'0')+':'+String(dt.getMinutes()).padStart(2,'0');
            var liq = parseFloat(h.total_liquido);
            var cls = liq >= 0 ? 'positivo' : 'negativo';
            tb.append(
                '<tr>'+
                '<td>'+dataStr+'</td>'+
                '<td>'+horaStr+'</td>'+
                '<td><span class="badge badge-secondary">'+(h.turno||'Integral')+'</span></td>'+
                '<td>'+fm(h.total_entradas)+'</td>'+
                '<td>'+fm(h.total_saidas)+'</td>'+
                '<td class="'+cls+'">'+fm(h.total_liquido)+'</td>'+
                '<td>'+(h.closed_by ? h.closed_by.name : 'N/A')+'</td>'+
                '</tr>'
            );
        });
    }).fail(function(){
        toastr.error('Erro ao carregar historico.');
    });
}

function shareWhatsApp(){
    if(!currentCambista) return;
    var msg = '*RESUMO DE FECHAMENTO - IHUB BETS*\n\n';
    msg += '*Colaborador:* '+currentCambista.colaborador+'\n';
    var d = new Date();
    msg += '*Data:* '+String(d.getDate()).padStart(2,'0')+'/'+String(d.getMonth()+1).padStart(2,'0')+'/'+d.getFullYear()+' '+String(d.getHours()).padStart(2,'0')+':'+String(d.getMinutes()).padStart(2,'0')+'\n\n';
    msg += '*Entradas:* '+fm(currentCambista.entradas)+'\n';
    msg += '*Saidas (Premios):* '+fm(currentCambista.saidas)+'\n';
    msg += '*Comissoes:* '+fm(currentCambista.comissoes)+'\n';
    msg += '*Lancamentos:* '+fm(currentCambista.lancamentos)+'\n';
    msg += '----------------------------\n';
    msg += '*TOTAL A PAGAR/RECEBER:* *'+fm(currentCambista.total)+'*\n\n';
    msg += '_Fechamento realizado via painel administrativo IHUB._';
    window.open('https://api.whatsapp.com/send?text='+encodeURIComponent(msg), '_blank');
}

function printPreview(){
    var printContents = document.getElementById('printable-preview').innerHTML;
    var originalContents = document.body.innerHTML;
    document.body.innerHTML = '<html><head><title>Fechamento</title></head><body style="padding:20px;">'+printContents+'</body></html>';
    window.print();
    document.body.innerHTML = originalContents;
    location.reload();
}
</script>
@stop
