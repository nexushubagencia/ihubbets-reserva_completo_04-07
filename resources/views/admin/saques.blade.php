@extends('adminlte::page')

@section('title', 'Solicitações de Saque | IHUB BETS')

@section('content_header')
    <h1 class="text-dark font-weight-bold">
        <i class="fas fa-hand-holding-usd text-primary mr-2"></i> Solicitações de Saque
        <small class="text-muted text-sm font-weight-normal">Gestão de Pagamentos Pendentes</small>
    </h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-primary shadow-lg border-0">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h3 class="card-title text-lg font-weight-bold text-secondary">
                        <i class="fas fa-clock mr-2 text-warning"></i> Saques Aguardando Aprovação
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool text-primary" onclick="window.location.reload()"><i class="fas fa-sync-alt"></i></button>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="alert alert-warning border-0 shadow-sm mb-4 mt-3" style="background: linear-gradient(135deg, #fffbeb 0%, #fff7ed 100%); color: #92400e;">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-circle fa-lg mr-3"></i>
                            <div>
                                <h6 class="font-weight-bold mb-1">Atenção ao Processamento Manual</h6>
                                <p class="text-xs mb-0">Verifique os dados do PIX e a titularidade antes de aprovar. O estorno por rejeição devolve o saldo imediatamente ao cliente.</p>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="table-saques" class="table table-hover table-borderless table-striped align-middle" style="width:100%">
                            <thead class="bg-light text-muted text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.05em;">
                                <tr>
                                    <th class="py-3 px-4">ID</th>
                                    <th class="py-3">Usuário</th>
                                    <th class="py-3">Valor Líquido</th>
                                    <th class="py-3">Chave PIX</th>
                                    <th class="py-3">Data Solicitação</th>
                                    <th class="py-3 text-center">Status</th>
                                    <th class="py-3 text-right">Ações</th>
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

<!-- Modal Aprovar Saque -->
<div class="modal fade" id="modalAprovar" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <form id="formAprovar" enctype="multipart/form-data">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title font-weight-bold text-success"><i class="fas fa-check-circle mr-2"></i> Aprovar Pagamento</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="aprovar_id" name="id">
                    <div class="bg-light p-3 rounded mb-3">
                        <small class="text-muted d-block mb-1 text-uppercase font-weight-bold" style="font-size: 10px;">Anexe o comprovante abaixo</small>
                        <div class="form-group mb-0">
                            <input type="file" class="form-control-file" name="receipt" accept="image/*,application/pdf">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="text-xs font-weight-bold text-muted">Observação Interna</label>
                        <textarea class="form-control" name="admin_note" rows="3" placeholder="Opcional: Detalhes do pagamento..." style="border-radius: 8px;"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light font-weight-bold" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success px-4 font-weight-bold" style="border-radius: 8px;">Confirmar e Finalizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Rejeitar Saque -->
<div class="modal fade" id="modalRejeitar" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <form id="formRejeitar">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title font-weight-bold text-danger"><i class="fas fa-times-circle mr-2"></i> Rejeitar Solicitação</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="rejeitar_id" name="id">
                    <div class="alert alert-danger border-0 shadow-xs mb-3" style="background-color: #fef2f2; color: #991b1b;">
                        <small><i class="fas fa-undo mr-1"></i> <strong>Estorno Automático:</strong> O valor será devolvido para a conta do usuário imediatamente.</small>
                    </div>
                    <div class="form-group">
                        <label class="text-xs font-weight-bold text-muted">Motivo da Rejeição <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="admin_note" rows="3" required placeholder="Ex: CPF inválido ou inconsistente..." style="border-radius: 8px;"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light font-weight-bold" data-dismiss="modal">Voltar</button>
                    <button type="submit" class="btn btn-danger px-4 font-weight-bold" style="border-radius: 8px;">Confirmar Rejeição</button>
                </div>
            </form>
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
    
    .status-approved { background-color: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .status-pending { background-color: #fef9c3; color: #854d0e; border: 1px solid #fef08a; }
    .status-rejected { background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    
    .btn-action { width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; transition: all 0.2s; }
    .btn-action:hover { transform: translateY(-2px); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    const table = $('#table-saques').DataTable({
        language: { url: "//cdn.datatables.net/plug-ins/1.10.19/i18n/Portuguese-Brasil.json" },
        ajax: {
            url: "{{ route('admin.saques-list') }}",
            dataSrc: ''
        },
        order: [[4, 'desc']],
        columns: [
            { data: 'id', render: function(data) {
                return `<span class="text-muted font-weight-bold">#${data}</span>`;
            }},
            { data: null, render: function(data) {
                let badge = data.type === 'affiliate' 
                    ? '<span class="badge badge-warning text-xs mt-1"><i class="fas fa-handshake"></i> Afiliado</span>'
                    : '<span class="badge badge-info text-xs mt-1"><i class="fas fa-user"></i> Cliente</span>';
                
                return `
                    <div class="d-flex align-items-center">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mr-2" style="width: 32px; height: 32px; font-size: 12px;">
                            ${data.user_name.charAt(0)}
                        </div>
                        <div>
                            <div class="font-weight-bold text-dark">${data.user_name}</div>
                            <div class="text-xs text-muted">@${data.username}</div>
                            ${badge}
                        </div>
                    </div>
                `;
            }},
            { data: 'amount', render: function(data) {
                const val = parseFloat(data).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
                return `<span class="font-weight-bold text-dark" style="font-size: 1.1rem;">${val}</span>`;
            }},
            { data: null, render: function(data) {
                return `
                    <div class="text-sm font-weight-bold text-secondary"><code>${data.pix_key}</code></div>
                    <div class="text-xs text-muted uppercase font-weight-bold">${data.pix_key_type}</div>
                `;
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
                if(data === 'approved') { cls = 'status-approved'; txt = 'Pago'; icon = 'fa-check-circle'; }
                if(data === 'rejected') { cls = 'status-rejected'; txt = 'Rejeitado'; icon = 'fa-times-circle'; }
                return `<span class="badge-premium ${cls}"><i class="fas ${icon}"></i> ${txt}</span>`;
            }},
            { data: null, className: 'text-right', render: function(data) {
                if(data.status === 'pending') {
                    return `
                        <button class="btn btn-success btn-action btn-aprovar mr-1" data-id="${data.id}" title="Aprovar Pagamento"><i class="fas fa-check"></i></button>
                        <button class="btn btn-danger btn-action btn-rejeitar" data-id="${data.id}" title="Rejeitar e Estornar"><i class="fas fa-times"></i></button>
                    `;
                }
                return '<span class="badge badge-light border text-xs px-2 py-1">Processado</span>';
            }}
        ],
        drawCallback: function() {
            $('.dataTables_paginate > .paginate_button').addClass('btn btn-sm btn-light mx-1 shadow-sm');
        }
    });

    $(document).on('click', '.btn-aprovar', function() {
        $('#aprovar_id').val($(this).data('id'));
        $('#modalAprovar').modal('show');
    });

    $(document).on('click', '.btn-rejeitar', function() {
        $('#rejeitar_id').val($(this).data('id'));
        $('#modalRejeitar').modal('show');
    });

    $('#formAprovar').submit(function(e) {
        e.preventDefault();
        let id = $('#aprovar_id').val();
        let formData = new FormData(this);

        $.ajax({
            url: `/admin/approve-withdrawal/${id}`,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() { $('#formAprovar button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>'); },
            success: function(res) {
                toastr.success('Saque aprovado com sucesso!');
                $('#modalAprovar').modal('hide');
                table.ajax.reload();
            },
            error: function(err) {
                toastr.error(err.responseJSON.message || 'Erro ao processar');
            },
            complete: function() { $('#formAprovar button[type="submit"]').prop('disabled', false).html('Confirmar e Finalizar'); }
        });
    });

    $('#formRejeitar').submit(function(e) {
        e.preventDefault();
        let id = $('#rejeitar_id').val();
        
        $.ajax({
            url: `/admin/reject-withdrawal/${id}`,
            type: 'POST',
            data: $(this).serialize(),
            beforeSend: function() { $('#formRejeitar button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>'); },
            success: function(res) {
                toastr.success('Saque rejeitado e saldo estornado!');
                $('#modalRejeitar').modal('hide');
                table.ajax.reload();
            },
            error: function(err) {
                toastr.error(err.responseJSON.message || 'Erro ao processar');
            },
            complete: function() { $('#formRejeitar button[type="submit"]').prop('disabled', false).html('Confirmar Rejeição'); }
        });
    });
});
</script>
@stop
