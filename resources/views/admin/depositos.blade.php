@extends('adminlte::page')

@section('title', 'Histórico de Depósitos | IHUB BETS')

@section('content_header')
    <h1 class="text-dark font-weight-bold">
        <i class="fas fa-university text-primary mr-2"></i> Histórico de Depósitos
        <small class="text-muted text-sm font-weight-normal">Gestão de Recebimentos via PIX</small>
    </h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-primary shadow-lg border-0">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h3 class="card-title text-lg font-weight-bold text-secondary">
                        <i class="fas fa-list-ul mr-2 text-primary"></i> Relatório de Depósitos (Mercado Pago)
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool text-primary" onclick="window.location.reload()"><i class="fas fa-sync-alt"></i></button>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="alert alert-info border-0 shadow-sm mb-4 mt-3" style="background: linear-gradient(135deg, #e0f2fe 0%, #f0f9ff 100%); color: #0369a1;">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-info-circle fa-lg mr-3"></i>
                            <div>
                                <h6 class="font-weight-bold mb-1">Monitoramento em Tempo Real</h6>
                                <p class="text-xs mb-0">Esta listagem exibe todos os depósitos processados automaticamente pelo gateway Mercado Pago. O saldo é creditado instantaneamente na conta do cliente.</p>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="table-depositos" class="table table-hover table-borderless table-striped align-middle" style="width:100%">
                            <thead class="bg-light text-muted text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.05em;">
                                <tr>
                                    <th class="py-3 px-4">ID</th>
                                    <th class="py-3">Cliente</th>
                                    <th class="py-3">Valor Bruto</th>
                                    <th class="py-3">Ref. Gateway</th>
                                    <th class="py-3">Data Processamento</th>
                                    <th class="py-3 text-center">Status Final</th>
                                </tr>
                            </thead>
                            <tbody class="text-secondary" style="font-size: 0.9rem;">
                                <!-- Carregado via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .card { border-radius: 12px; }
    .table thead th { border-bottom: 2px solid #f8f9fa !important; }
    .table td { vertical-align: middle !important; padding: 1rem 0.75rem !important; }
    .badge-premium { padding: 0.5em 0.8em; border-radius: 6px; font-weight: 600; font-size: 0.75rem; display: inline-flex; align-items: center; }
    .badge-premium i { margin-right: 4px; }
    
    .status-completed { background-color: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .status-pending { background-color: #fef9c3; color: #854d0e; border: 1px solid #fef08a; }
    .status-failed { background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    
    #table-depositos_wrapper .dataTables_paginate .paginate_button.current {
        background: #3b82f6 !important;
        border-color: #3b82f6 !important;
        color: white !important;
        border-radius: 6px;
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    const table = $('#table-depositos').DataTable({
        language: { url: "//cdn.datatables.net/plug-ins/1.10.19/i18n/Portuguese-Brasil.json" },
        ajax: {
            url: "{{ route('admin.depositos-list') }}",
            dataSrc: ''
        },
        order: [[4, 'desc']],
        columns: [
            { data: 'id', render: function(data) {
                return `<span class="text-muted font-weight-bold">#${data}</span>`;
            }},
            { data: null, render: function(data) {
                return `
                    <div class="d-flex align-items-center">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mr-2" style="width: 32px; height: 32px; font-size: 12px;">
                            ${data.user_name.charAt(0)}
                        </div>
                        <div>
                            <div class="font-weight-bold text-dark">${data.user_name}</div>
                            <div class="text-xs text-muted">@${data.username}</div>
                        </div>
                    </div>
                `;
            }},
            { data: 'amount', render: function(data) {
                const val = parseFloat(data).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
                return `<span class="font-weight-bold text-success" style="font-size: 1.1rem;">${val}</span>`;
            }},
            { data: 'gateway_ref', render: function(data) {
                return `<code class="bg-light px-2 py-1 rounded" style="font-size: 0.8rem; color: #6366f1;">${data || 'N/A'}</code>`;
            }},
            { data: 'created_at', render: function(data) {
                const date = new Date(data);
                return `
                    <div class="text-dark">${date.toLocaleDateString('pt-BR')}</div>
                    <div class="text-xs text-muted"><i class="far fa-clock mr-1"></i>${date.toLocaleTimeString('pt-BR')}</div>
                `;
            }},
            { data: 'status', className: 'text-center', render: function(data) {
                let cls = 'status-pending';
                let txt = 'Pendente';
                let icon = 'fa-clock';
                
                if(data === 'completed' || data === 'approved') { 
                    cls = 'status-completed'; txt = 'Aprovado'; icon = 'fa-check-circle';
                } else if(data === 'failed' || data === 'rejected') { 
                    cls = 'status-failed'; txt = 'Falha'; icon = 'fa-times-circle';
                }
                
                return `<span class="badge-premium ${cls}"><i class="fas ${icon}"></i> ${txt}</span>`;
            }}
        ],
        drawCallback: function() {
            $('.dataTables_paginate > .paginate_button').addClass('btn btn-sm btn-light mx-1 shadow-sm');
        }
    });
});
</script>
@stop

