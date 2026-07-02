@extends('adminlte::page')

@section('title', 'Relatório de Transações | IHUB BETS')

@section('content_header')
    <h1 class="text-dark font-weight-bold">
        <i class="fas fa-file-invoice-dollar text-primary mr-2"></i> Auditoria de Transações
        <small class="text-muted text-sm font-weight-normal">Fluxo de Caixa Detalhado</small>
    </h1>
@stop

@section('content')
<div class="container-fluid">
    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 bg-white" style="border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mr-3" style="width: 45px; height: 45px;">
                            <i class="fas fa-arrow-down fa-lg"></i>
                        </div>
                        <div>
                            <small class="text-muted font-weight-bold text-uppercase" style="font-size: 10px;">Total Entradas (Depósitos)</small>
                            <h4 class="mb-0 font-weight-bold" id="sum-deposits">R$ 0,00</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 bg-white" style="border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center mr-3" style="width: 45px; height: 45px;">
                            <i class="fas fa-trophy fa-lg"></i>
                        </div>
                        <div>
                            <small class="text-muted font-weight-bold text-uppercase" style="font-size: 10px;">Total Saídas (Prêmios)</small>
                            <h4 class="mb-0 font-weight-bold" id="sum-payouts">R$ 0,00</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 bg-white" style="border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center mr-3" style="width: 45px; height: 45px;">
                            <i class="fas fa-percentage fa-lg"></i>
                        </div>
                        <div>
                            <small class="text-muted font-weight-bold text-uppercase" style="font-size: 10px;">Total Comissões</small>
                            <h4 class="mb-0 font-weight-bold" id="sum-commissions">R$ 0,00</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 bg-white" style="border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center mr-3" style="width: 45px; height: 45px;">
                            <i class="fas fa-balance-scale fa-lg"></i>
                        </div>
                        <div>
                            <small class="text-muted font-weight-bold text-uppercase" style="font-size: 10px;">GGR (Net Profit)</small>
                            <h4 class="mb-0 font-weight-bold" id="sum-ggr">R$ 0,00</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card card-outline card-primary shadow-lg border-0 mb-4">
        <div class="card-body p-3">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <div class="form-group mb-0">
                        <label class="text-xs font-weight-bold text-muted uppercase">Usuário</label>
                        <select class="form-control select2" id="filter-user">
                            <option value="Todos">Todos os Usuários</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} (@{{ $user->username }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-0">
                        <label class="text-xs font-weight-bold text-muted uppercase">Categoria</label>
                        <select class="form-control" id="filter-type">
                            <option value="Todos">Todos os Tipos</option>
                            <option value="bet_placed">Aposta Realizada</option>
                            <option value="bet_payout">Prêmio Pago</option>
                            <option value="commission">Comissão</option>
                            <option value="deposit">Depósito PIX</option>
                            <option value="withdrawal">Saque solicitado</option>
                            <option value="withdrawal_refund">Saque Rejeitado</option>
                            <option value="adjustment">Ajuste Manual</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-0">
                        <label class="text-xs font-weight-bold text-muted uppercase">Período De</label>
                        <input type="date" class="form-control" id="filter-date1">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-0">
                        <label class="text-xs font-weight-bold text-muted uppercase">Período Até</label>
                        <input type="date" class="form-control" id="filter-date2">
                    </div>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary btn-block font-weight-bold" onclick="fetchTransactions()" style="height: 38px;">
                        <i class="fas fa-search mr-1"></i> FILTRAR RESULTADOS
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Table -->
    <div class="card shadow-lg border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="transactions-table" class="table table-hover align-middle m-0" style="width:100%">
                    <thead class="bg-light text-muted text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.05em;">
                        <tr>
                            <th class="py-3 px-4">Momento</th>
                            <th class="py-3">Beneficiário</th>
                            <th class="py-3">Tipo</th>
                            <th class="py-3 text-right">Valor</th>
                            <th class="py-3">Ref.</th>
                            <th class="py-3 text-center">Status</th>
                            <th class="py-3 px-4">Descrição Detalhada</th>
                        </tr>
                    </thead>
                    <tbody id="transactions-tbody" style="font-size: 0.85rem;">
                        <!-- Data will be injected here -->
                    </tbody>
                </table>
                <div id="loading-spinner" class="text-center p-5" style="display:none;">
                    <i class="fas fa-spinner fa-spin fa-3x text-primary mb-2"></i>
                    <div class="text-muted">Gerando relatório de auditoria...</div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .card { border-radius: 12px; }
    .table td { padding: 0.9rem 0.75rem !important; vertical-align: middle !important; }
    .badge-premium { padding: 0.4em 0.6em; border-radius: 4px; font-weight: 700; font-size: 0.7rem; text-transform: uppercase; }
    
    .badge-bet_placed { background-color: #e0f2fe; color: #0369a1; }
    .badge-bet_payout { background-color: #dcfce7; color: #166534; }
    .badge-commission { background-color: #fef9c3; color: #854d0e; }
    .badge-deposit { background-color: #e0e7ff; color: #3730a3; }
    .badge-withdrawal { background-color: #f3f4f6; color: #374151; }
    .badge-withdrawal_refund { background-color: #fee2e2; color: #991b1b; }
    .badge-adjustment { background-color: #f5f3ff; color: #5b21b6; }
</style>
@stop

@section('js')
<script>
    function fm(n){ return parseFloat(n||0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }); }

    $(document).ready(function() {
        $('.select2').select2({ theme: 'default' });
        const today = new Date().toISOString().split('T')[0];
        $('#filter-date1').val(today);
        $('#filter-date2').val(today);
        fetchTransactions();
    });

    function fetchTransactions() {
        $('#transactions-tbody').empty();
        $('#loading-spinner').show();

        const params = {
            user_id: $('#filter-user').val(),
            type: $('#filter-type').val(),
            date1: $('#filter-date1').val(),
            date2: $('#filter-date2').val()
        };

        $.get('/admin/transactions-list', params, function(data) {
            $('#loading-spinner').hide();
            
            let totalDep = 0, totalPay = 0, totalCom = 0;

            if (data.length === 0) {
                $('#transactions-tbody').append('<tr><td colspan="7" class="text-center p-5 text-muted">Nenhuma movimentação para o período selecionado.</td></tr>');
                updateSummary(0, 0, 0);
                return;
            }

            data.forEach(function(t) {
                const isPos = t.amount > 0;
                const valCls = isPos ? 'text-success' : 'text-danger';
                
                // Stats
                if(t.type === 'deposit') totalDep += parseFloat(t.amount);
                if(t.type === 'bet_payout') totalPay += Math.abs(parseFloat(t.amount));
                if(t.type === 'commission') totalCom += Math.abs(parseFloat(t.amount));

                let statusBadge = '';
                switch(t.status) {
                    case 'completed': statusBadge = '<span class="badge badge-success px-2 py-1">Concluído</span>'; break;
                    case 'pending': statusBadge = '<span class="badge badge-warning px-2 py-1">Pendente</span>'; break;
                    case 'cancelled': statusBadge = '<span class="badge badge-danger px-2 py-1">Cancelado</span>'; break;
                    default: statusBadge = `<span class="badge badge-secondary px-2 py-1">${t.status}</span>`;
                }

                $('#transactions-tbody').append(`
                    <tr>
                        <td class="px-4"><strong>${t.date_formatted}</strong></td>
                        <td><strong>${t.user_name}</strong><br><small class="text-muted text-uppercase">${t.user_role}</small></td>
                        <td><span class="badge-premium badge-${t.type}">${t.type_label}</span></td>
                        <td class="text-right"><strong class="${valCls}">${fm(t.amount)}</strong></td>
                        <td><code class="text-xs">${t.gateway_ref || '-'}</code></td>
                        <td class="text-center">${statusBadge}</td>
                        <td class="px-4"><small class="text-muted font-italic">${t.description || '-'}</small></td>
                    </tr>
                `);
            });

            updateSummary(totalDep, totalPay, totalCom);
        }).fail(function() {
            $('#loading-spinner').hide();
            toastr.error('Erro ao gerar relatório.');
        });
    }

    function updateSummary(dep, pay, com) {
        $('#sum-deposits').text(fm(dep));
        $('#sum-payouts').text(fm(pay));
        $('#sum-commissions').text(fm(com));
        $('#sum-ggr').text(fm(dep - pay - com));
    }
</script>
@stop
