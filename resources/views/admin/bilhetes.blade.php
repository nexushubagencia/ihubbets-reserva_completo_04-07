@extends('adminlte::page')

@section('title', 'Gestão Profissional de Bilhetes | IHUB BETS')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-receipt"></i> Bilhetes <small class="text-muted">Gestão Avançada</small></h1>
        <button class="btn btn-info shadow-sm font-weight-bold" onclick="exportToExcel()">
            <i class="far fa-file-excel mr-2"></i> EXPORTAR EXCEL
        </button>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-dark card-outline shadow-sm mb-4 border-0">
            <!-- Cabeçalho de Filtros com Acordeão -->
            <div class="card-header bg-dark border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="card-title font-weight-bold text-white">
                        <i class="fas fa-filter text-primary mr-2"></i> Filtros de Pesquisa
                    </h3>
                    <button class="btn btn-link btn-sm text-muted" type="button" data-toggle="collapse" data-target="#collapseFiltros">
                        Ocultar/Mostrar Filtros <i class="fas fa-sliders-h ml-1"></i>
                    </button>
                </div>
            </div>

            <div id="collapseFiltros" class="collapse show">
                <div class="card-body bg-white pt-0">
                    <div class="row">
                        <div class="col-md-2 mb-3">
                            <label class="text-muted small font-weight-bold">CAMBISTA:</label>
                            <select id="filtro-cambista" class="form-control form-control-sm border-light-2">
                                <option value="Todos">Todos</option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="text-muted small font-weight-bold">ESPORTE:</label>
                            <select id="filtro-esporte" class="form-control form-control-sm border-light-2">
                                <option value="Todos">Todos</option>
                                <option value="Futebol">Futebol</option>
                                <option value="Basquete">Basquete</option>
                                <option value="Tenis">Tênis</option>
                                <option value="Volei">Vôlei</option>
                                <option value="Luta">Luta</option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="text-muted small font-weight-bold">STATUS:</label>
                            <select id="filtro-status" class="form-control form-control-sm border-light-2">
                                <option value="Todos">Todos</option>
                                <option value="Aberto">Aberto</option>
                                <option value="Ganhou">Ganhou</option>
                                <option value="Venceu">Venceu</option>
                                <option value="Perdeu">Perdeu</option>
                                <option value="Cancelado">Cancelado</option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="text-muted small font-weight-bold">VALOR MÍN:</label>
                            <input type="number" id="filtro-valor-min" class="form-control form-control-sm border-light-2" placeholder="R$ 10">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="text-muted small font-weight-bold">VALOR MAX:</label>
                            <input type="number" id="filtro-valor-max" class="form-control form-control-sm border-light-2" placeholder="R$ 1000">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="text-muted small font-weight-bold">CUPOM:</label>
                            <input type="text" id="filtro-cupom" class="form-control form-control-sm border-light-2" placeholder="Ex: ABC123">
                        </div>
                        
                        <div class="col-md-2 mb-3">
                            <label class="text-muted small font-weight-bold">DE:</label>
                            <input type="date" id="filtro-data1" class="form-control form-control-sm border-light-2">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="text-muted small font-weight-bold">ATÉ:</label>
                            <input type="date" id="filtro-data2" class="form-control form-control-sm border-light-2">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="text-muted small font-weight-bold">CLIENTE:</label>
                            <input type="text" id="filtro-cliente" class="form-control form-control-sm border-light-2" placeholder="Nome do cliente">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="text-muted small font-weight-bold">GERENTE:</label>
                            <select id="filtro-gerente" class="form-control form-control-sm border-light-2">
                                <option value="Todos">Todos</option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3 d-flex align-items-end">
                            <button class="btn btn-success btn-sm px-4 mr-2 font-weight-bold shadow-sm" onclick="searchBilhetes()">
                                <i class="fas fa-search mr-1"></i> PESQUISAR
                            </button>
                            <button class="btn btn-warning btn-sm px-4 font-weight-bold shadow-sm text-white" onclick="limparFiltros()">
                                <i class="fas fa-trash-alt mr-1"></i> LIMPAR
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabela de Bilhetes -->
            <div class="card-body p-0 mt-2">
                <div class="table-responsive">
                    <table class="table table-sm mb-0" id="tabela-bilhetes-export">
                        <thead class="text-white small font-weight-bold text-uppercase" style="background-color: #000;">
                            <tr>
                                <th class="border-0 px-3 py-2">Cupom</th>
                                <th class="border-0 py-2">Data</th>
                                <th class="border-0 py-2 text-center" style="width: 120px;">Status</th>
                                <th class="border-0 py-2">Apostado</th>
                                <th class="border-0 py-2">Retorno</th>
                                <th class="border-0 py-2 text-center">% Prem</th>
                                <th class="border-0 py-2">Vendedor</th>
                                <th class="border-0 py-2">Cliente</th>
                                <th class="border-0 py-2 text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody id="bilhetes-tbody" class="text-dark bg-white">
                            <!-- Injetado via AJAX -->
                        </tbody>
                        <tfoot id="bilhetes-tfoot" class="bg-light font-weight-bold" style="display:none;">
                            <tr class="text-dark border-top-2">
                                <td colspan="3" class="px-3 py-3">TOTAL PERÍODO (<span id="total-count">0</span>)</td>
                                <td id="total-apostado" class="py-3">R$ 0,00</td>
                                <td id="total-retorno" class="py-3">R$ 0,00</td>
                                <td colspan="4" class="text-right py-3 pr-4 text-muted small font-weight-bold">Monitorando Banca em Tempo Real</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <!-- Loading State -->
                <div id="loading-bilhetes" class="text-center py-5" style="display:none;">
                    <div class="v-spinner"><div class="v-scale v-scale1"></div><div class="v-scale v-scale2"></div><div class="v-scale v-scale3"></div><div class="v-scale v-scale4"></div><div class="v-scale v-scale5"></div></div>
                    <p class="text-muted mt-2 small font-weight-bold">Sincronizando Banco de Dados...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Bilhete -->
<div class="modal fade" id="modal-editar-bilhete">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title font-weight-bold">Editar Bilhete <span id="edit-cupom"></span></h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="edit-id">
                <div class="form-group">
                    <label class="small font-weight-bold">NOME DO CLIENTE:</label>
                    <input type="text" id="edit-cliente" class="form-control">
                </div>
                <div class="form-group">
                    <label class="small font-weight-bold">STATUS DO BILHETE:</label>
                    <select id="edit-status" class="form-control">
                        <option value="Aberto">Aberto</option>
                        <option value="Ganhou">Ganhou</option>
                        <option value="Venceu">Venceu</option>
                        <option value="Perdeu">Perdeu</option>
                        <option value="Cancelado">Cancelado</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">CANCELAR</button>
                <button type="button" class="btn btn-success font-weight-bold" onclick="salvarEdicao()">SALVAR ALTERAÇÕES</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Bilhete Premiado -->
<div class="modal fade" id="modal-banner-ganhou">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; background: #1a1a1a;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title text-white font-weight-bold"><i class="fas fa-trophy text-warning mr-2"></i> Banner de Vitória</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body text-center p-4">
                <div id="banner-canvas" class="p-4 rounded-lg mb-4 shadow-2xl" style="background: linear-gradient(135deg, #1d976c 0%, #93f9b9 100%); border: 4px solid #fff;">
                    <div class="text-white mb-2 small font-weight-bold text-uppercase tracking-widest">Aposta Vencedora</div>
                    <h1 id="banner-cupom" class="text-white font-weight-black mb-3" style="font-size: 3rem; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);"></h1>
                    <div class="bg-white rounded-pill d-inline-block px-4 py-2 mb-3 shadow-sm">
                        <span id="banner-retorno" class="text-success h3 font-weight-black mb-0"></span>
                    </div>
                    <div class="text-white-50 small font-weight-bold uppercase">IHUB BETS - SEMPRE VENCENDO!</div>
                </div>
                <button class="btn btn-warning btn-block btn-lg font-weight-bold shadow-sm" onclick="downloadBanner()">
                    <i class="fas fa-download mr-2"></i> BAIXAR BANNER (JPG)
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ver Bilhete (Recibo Premium) -->
<div class="modal fade" id="modal-ver-bilhete">
    <div class="modal-dialog" style="max-width: 400px; margin: 30px auto;">
        <div class="modal-content" style="border: none; border-radius: 0; background-color: #FDF5D2; color: #333; font-family: 'Montserrat', sans-serif;">
            <div class="modal-body p-0">
                <!-- 🚀 CABEÇALHO TEAL -->
                <div id="detalhe-header-status" style="background-color: #00ADEF; padding: 15px; text-align: center; position: relative;">
                    <button type="button" class="close" data-dismiss="modal" style="color: #fff; opacity: 1; font-size: 20px; position: absolute; right: 15px; top: 15px;">&times;</button>
                    <h4 id="detalhe-status-label" style="color: #fff; font-weight: 900; text-transform: uppercase; margin: 0; letter-spacing: 1.5px; font-size: 22px;">
                        -
                    </h4>                          
                </div>

                <div style="padding: 20px 0; text-align: center;">
                    <img width="180" src="{{ asset('img/logo.png') }}" style="max-width: 200px; height: auto;">
                    <h3 style="margin: 10px 0 0; font-weight: 900; color: #000; text-transform: uppercase; font-size: 18px; letter-spacing: 1px;">
                      IHUB BETS
                    </h3>
                </div>

                <div style="border-top: 1.5px dashed #BDB76B; width: 90%; margin: 0 auto 20px;"></div>

                <div style="padding: 0 20px;">
                    <h4 id="detalhe-tipo-aposta" style="text-align: center; font-weight: 800; text-transform: uppercase; margin-bottom: 20px; color: #444; letter-spacing: 1px;">
                        APOSTA
                    </h4>

                    <div style="font-size: 13px; line-height: 1.8; margin-bottom: 20px; color: #555;">
                        <div style="display: flex; justify-content: space-between;">
                            <span>DATA</span>
                            <b id="detalhe-data" style="color: #000;">-</b>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span>VENDEDOR</span>
                            <b id="detalhe-vendedor" style="color: #000;">-</b>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span>CLIENTE</span>
                            <b id="detalhe-cliente-nome" style="color: #000;">-</b>
                        </div>
                    </div>

                    <div style="border-top: 1.5px dashed #BDB76B; width: 100%; margin-bottom: 15px;"></div>

                    <div style="display: flex; justify-content: space-between; font-size: 11px; font-weight: 700; color: #888; text-transform: uppercase; margin-bottom: 10px;">
                        <span>EVENTO / MERCADO</span>
                        <span>COTAÇÃO</span>
                    </div>

                    <div id="container-jogos-termico">
                        <!-- Jogos injetados aqui -->
                    </div>

                    <div style="margin: 30px 0; text-align: center; border-top: 2px solid #000; border-bottom: 2px solid #000; padding: 15px 0;">
                        <h1 id="detalhe-cupom-text" style="font-weight: 900; font-size: 42px; margin: 0; color: #000; letter-spacing: 2px;">
                            -
                        </h1>
                    </div>

                    <div style="padding-bottom: 20px;">
                        <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid rgba(0,0,0,0.05);">
                            <span>Quantidade de Jogos</span>
                            <b id="detalhe-qtd-jogos">-</b>
                        </div>
                        <div id="row-acertos-admin" style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid rgba(0,0,0,0.05); display: none;">
                            <span>Acertos</span>
                            <b id="detalhe-acertos-admin">-</b>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid rgba(0,0,0,0.05);">
                            <span>Cotação Total</span>
                            <b id="detalhe-cotacao-termico">-</b>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid rgba(0,0,0,0.05);">
                            <span>Valor Apostado</span>
                            <b id="detalhe-valor-apostado-termico">-</b>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; padding: 15px 0; align-items: center;">
                            <span style="font-weight: 800; font-size: 16px;">Retorno Possível</span>
                            <b id="detalhe-retorno-termico" style="font-size: 22px; color: #28A745; font-weight: 900;">R$ 0,00</b>
                        </div>
                    </div>

                    <div style="border-top: 1.5px solid #000; width: 100%; margin-bottom: 15px;"></div>

                    <div style="padding-bottom: 30px;">
                        <b style="font-size: 10px; text-transform: uppercase; color: #000;">REGRAS:</b>
                        <p style="font-size: 10px; color: #666; margin-top: 5px; line-height: 1.4;">
                            Aposte com confiança e segurança.
                        </p>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 p-2" style="background-color: rgba(0,0,0,0.05); flex-wrap: wrap; justify-content: center; gap: 10px;">
                <a id="btn-whatsapp-admin" href="#" target="_blank" class="btn btn-success" style="background-color: #25D366; border: none; font-weight: 700;">
                    <i class="fab fa-whatsapp"></i> WhatsApp
                </a>
                <button class="btn btn-dark" onclick="window.print()"><i class="fas fa-print"></i> Imprimir</button>
                <button class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .border-light-2 { border: 1.5px solid #edf2f7 !important; border-radius: 6px; }
    .st-ganhou, .st-venceu { background-color: #008D4C !important; color: #fff !important; font-weight: 800; text-transform: uppercase; padding: 2px 8px; border-radius: 4px; }
    .st-perdeu { background-color: #FF0000 !important; color: #fff !important; font-weight: 800; text-transform: uppercase; padding: 2px 8px; border-radius: 4px; }
    .st-aberto { background-color: #00C0EF !important; color: #fff !important; font-weight: 800; text-transform: uppercase; padding: 2px 8px; border-radius: 4px; }
    .st-cancelado { background-color: #E69222 !important; color: #fff !important; font-weight: 800; text-transform: uppercase; padding: 2px 8px; border-radius: 4px; }
    
    #bilhetes-tbody td { vertical-align: middle; padding: 12px 8px; font-size: 13px; border-bottom: 1px solid #f1f5f9; }
    .border-top-2 { border-top: 3px solid #000 !important; }

    /* Legacy Styles for Modal */
    .body-bilhete { background:#F8ECC2; color: #000; }
    .score { color:#b12323; }
    .body-palpite { width: 100%; height: auto; margin-bottom: 20px; border-bottom: 1px #000 dashed; font-size: 15px; padding-bottom: 9px; }
    .body-palpite p { margin-bottom: 0px; }
    
    /* Spinner Animado */
    .v-spinner { display: flex; justify-content: center; align-items: center; }
    .v-scale { background-color: #28a745; height: 35px; width: 4px; margin: 2px; border-radius: 2px; display: inline-block; animation: v-scaleStretchDelay 1s infinite cubic-bezier(0.2, 0.68, 0.18, 1.08) both; }
    @keyframes v-scaleStretchDelay { 0%, 100% { transform: scaleY(1); } 50% { transform: scaleY(0.4); } }
</style>
@stop

@section('js')
<script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
<script>
    $(document).ready(function() {
        const today = new Date().toISOString().split('T')[0];
        $('#filtro-data1, #filtro-data2').val(today);
        loadCombos();
        searchBilhetes();
    });

    function fm(n) { return "R$ " + parseFloat(n||0).toFixed(2).replace('.', ',').replace(/(\d)(?=(\d{3})+\,)/g, "$1."); }
    function fc(n) { return parseFloat(n||0).toFixed(2).replace('.', ','); }
    function fd(ds) { if(!ds) return '-'; const d = new Date(ds); return d.getDate().toString().padStart(2,'0') + '/' + (d.getMonth()+1).toString().padStart(2,'0') + ' ' + d.getHours().toString().padStart(2,'0') + ':' + d.getMinutes().toString().padStart(2,'0'); }

    function getStatusClass(s) {
        if (!s) return 'st-aberto';
        s = s.toString().toLowerCase().trim();
        if (s === 'ganhou' || s === 'venceu') return 'st-ganhou';
        if (s === 'perdeu') return 'st-perdeu';
        if (s === 'cancelado') return 'st-cancelado';
        return 'st-aberto';
    }

    function loadCombos() {
        $.get('/admin/list-cambistas', function(res) {
            res.forEach(c => $('#filtro-cambista').append(`<option value="${c.id}">${c.name}</option>`));
        });
        $.get('/admin/list-gerentes', function(res) {
            res.forEach(g => $('#filtro-gerente').append(`<option value="${g.id}">${g.name}</option>`));
        });
    }

    let currentBilhetes = [];

    function searchBilhetes() {
        $('#bilhetes-tbody').empty();
        $('#bilhetes-tfoot').hide();
        $('#loading-bilhetes').show();

        $.post('/admin/bilhetes-search', {
            _token: $('meta[name="csrf-token"]').attr('content'),
            cambista: $('#filtro-cambista').val(),
            gerente: $('#filtro-gerente').val(),
            esporte: $('#filtro-esporte').val(),
            status: $('#filtro-status').val(),
            valor_min: $('#filtro-valor-min').val(),
            valor_max: $('#filtro-valor-max').val(),
            cliente: $('#filtro-cliente').val(),
            cupom: $('#filtro-cupom').val(),
            date1: $('#filtro-data1').val(),
            date2: $('#filtro-data2').val()
        }, function(res) {
            $('#loading-bilhetes').hide();
            const list = res.bilhetes;
            currentBilhetes = list;
            const totals = res.totals;

            if (list.length === 0) { $('#empty-bilhetes').show(); return; }

            list.forEach((b, index) => {
                const perc = b.retorno_possivel > 0 ? ((b.valor_apostado / b.retorno_possivel) * 100).toFixed(1) : '0.0';
                const row = `
                    <tr>
                        <td class="px-3"><strong>${b.cupom}</strong></td>
                        <td class="small text-muted">${fd(b.created_at)}</td>
                        <td class="text-center"><span class="${getStatusClass(b.status)}">${b.status}</span></td>
                        <td class="font-weight-bold text-muted">${fm(b.valor_apostado)}</td>
                        <td class="text-dark font-weight-bold">${fm(b.retorno_possivel)}</td>
                        <td class="text-center small text-muted">${perc}%</td>
                        <td>${b.vendedor}</td>
                        <td>${b.cliente}</td>
                        <td class="text-center">
                            <div class="btn-group shadow-sm">
                                <button title="Ver Detalhes" class="btn btn-xs btn-primary px-2" onclick="verBilhete(${b.id})"><i class="fas fa-eye"></i></button>
                                <button title="Editar" class="btn btn-xs btn-secondary px-2" onclick='editarBilheteByIndex(${index})'><i class="fas fa-edit"></i></button>
                                ${getStatusClass(b.status) === 'st-ganhou' ? `<button title="Gerar Banner" class="btn btn-xs btn-success px-2" onclick="gerarBanner('${b.cupom}', '${fm(b.retorno_possivel)}')"><i class="fas fa-trophy"></i></button>` : ''}
                                ${b.status !== 'Cancelado' ? `<button title="Excluir" class="btn btn-xs btn-danger px-2" onclick='cancelarBilheteByIndex(${index})'><i class="fas fa-times"></i></button>` : ''}
                            </div>
                        </td>
                    </tr>
                `;
                $('#bilhetes-tbody').append(row);
            });

            $('#total-count').text(totals.count);
            $('#total-apostado').text(fm(totals.apostado));
            $('#total-retorno').text(fm(totals.retorno));
            $('#bilhetes-tfoot').show();

        }).fail(function(xhr) {
            $('#loading-bilhetes').hide();
            toastr.error('Erro na busca.');
        });
    }

    function editarBilheteByIndex(index) {
        const b = currentBilhetes[index];
        $('#edit-id').val(b.id);
        $('#edit-cupom').text(b.cupom);
        $('#edit-cliente').val(b.cliente);
        $('#edit-status').val(b.status);
        $('#modal-editar-bilhete').modal('show');
    }

    function salvarEdicao() {
        const id = $('#edit-id').val();
        $.ajax({
            url: '/admin/bilhetes/' + id,
            type: 'PUT',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                cliente: $('#edit-cliente').val(),
                status: $('#edit-status').val()
            },
            success: function() {
                $('#modal-editar-bilhete').modal('hide');
                toastr.success('Bilhete atualizado com sucesso!');
                searchBilhetes();
            },
            error: function() { toastr.error('Erro ao salvar edição'); }
        });
    }

    function cancelarBilheteByIndex(index) {
        const b = currentBilhetes[index];
        Swal.fire({
            title: 'Deseja excluir?',
            text: `Confirmar cancelamento do bilhete ${b.cupom}? Isso estornará o saldo se necessário.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Sim, excluir!',
            cancelButtonText: 'Não'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/admin/bilhetes/' + b.id,
                    type: 'PUT',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        status: 'Cancelado'
                    },
                    success: function() {
                        toastr.success('Bilhete cancelado/excluído com sucesso!');
                        searchBilhetes();
                    },
                    error: function() { toastr.error('Erro ao cancelar bilhete'); }
                });
            }
        });
    }

    function gerarBanner(cupom, retorno) {
        $('#banner-cupom').text(cupom);
        $('#banner-retorno').text(retorno);
        $('#modal-banner-ganhou').modal('show');
    }

    function downloadBanner() {
        html2canvas(document.querySelector("#banner-canvas")).then(canvas => {
            let link = document.createElement('a');
            link.download = 'vitoria-' + $('#banner-cupom').text() + '.jpg';
            link.href = canvas.toDataURL("image/jpeg", 0.9);
            link.click();
        });
    }

    function exportToExcel() {
        let table = document.getElementById("tabela-bilhetes-export");
        let html = table.outerHTML;
        window.open('data:application/vnd.ms-excel,' + encodeURIComponent(html));
    }

    function verBilhete(id) {
        $('#container-jogos-termico').empty();
        $('#modal-ver-bilhete').modal('show');
        $.get('/admin/palpites-bilhete/' + id, function(res) {
            if(!res || res.length === 0) return;
            const b = res[0];
            
            // Preenche Header
            const statusClass = b.status.toLowerCase();
            $('#detalhe-status-label').text(b.status).css({
                'background-color': statusClass === 'ganhou' ? '#008D4C' : (statusClass === 'perdeu' ? '#FF0000' : '#00C0EF'),
                'color': '#fff'
            });
            $('#detalhe-tipo-aposta').text(b.tipo || 'SIMPLES');
            $('#detalhe-data').text(fd(b.created_at));
            $('#detalhe-vendedor').text(b.vendedor || '-');
            $('#detalhe-cliente-nome').text(b.cliente || '-');
            
            // Preenche Jogos
            b.palpites.forEach(p => {
                const ps = p.status;
                const psClass = ps.toLowerCase();
                const statusColor = '#fff';
                const statusBarColor = '#00ADEF';

                $('#container-jogos-termico').append(`
                    <div style="margin-bottom: 25px; font-family: 'Montserrat', sans-serif;">
                        <div style="font-size: 11px; color: #777; margin-bottom: 3px;">
                            ${p.sport || 'Futebol'} • ${fd(p.match_temp)}
                        </div>
                        <div style="color: #D37D2A; font-weight: 800; font-size: 13px; text-transform: uppercase; margin-bottom: 4px;">
                            ${p.league || ''}
                        </div>
                        <div style="font-weight: 900; font-size: 16px; color: #000; margin-bottom: 4px;">
                            ${p.home_team} X ${p.away_team}
                        </div>
                        <div style="font-size: 12px; color: #666; font-style: italic; margin-bottom: 8px;">
                            ${p.market_name || ''}
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 5px;">
                            <b style="font-size: 16px; color: #000;">${p.selection_label}</b>
                            <b style="font-size: 18px; color: #000;">${fc(p.selection_odd)}</b>
                        </div>

                        <!-- 🚀 BARRA DE STATUS DO JOGO -->
                        <div style="background-color: ${statusBarColor}; color: ${statusColor}; text-align: center; font-weight: 900; font-size: 12px; padding: 4px 0; text-transform: uppercase; border-radius: 4px;">
                            ${ps}
                        </div>
                    </div>
                `);
            });

            // Preenche Footer
            $('#detalhe-cupom-text').text(b.cupom);
            $('#detalhe-qtd-jogos').text(b.total_palpites);
            
            if(b.status.toLowerCase() !== 'aberto') {
                $('#row-acertos-admin').show();
                $('#detalhe-acertos-admin').text(b.acertos_palpites);
            } else {
                $('#row-acertos-admin').hide();
            }

            $('#detalhe-cotacao-termico').text(fc(b.cotacao || b.total_cotacao || (b.retorno_possivel / b.valor_apostado)));
            $('#detalhe-valor-apostado-termico').text(fm(b.valor_apostado));
            $('#detalhe-retorno-termico').text(fm(b.retorno_possivel));

            // 🚀 LINK WHATSAPP
            let shareLink = "https://api.whatsapp.com/send?text=" + encodeURIComponent("Confira meu bilhete: " + window.location.origin + "/acompanhar?c=" + b.cupom);
            $('#btn-whatsapp-admin').attr('href', shareLink);
        });
    }
</script>
@stop