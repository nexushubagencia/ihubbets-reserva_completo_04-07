@extends('adminlte::page')

@section('title', 'Cash Out - Antecipar Aposta')

@section('content_header')
    <h1><i class="fas fa-hand-holding-usd text-success"></i> Cash Out - Antecipar Aposta</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-success card-outline">
            <div class="card-header">
                <h3 class="card-title">Apostas Abertas elegiveis para Cash Out</h3>
                <span class="badge badge-success ml-2">Taxa: {{ $taxa }}%</span>
            </div>
            <div class="card-body">
                <form method="GET" class="form-inline mb-3">
                    <input type="text" name="busca" class="form-control mr-2" placeholder="Buscar por codigo, cliente..." value="{{ request('busca') }}">
                    <select name="modalidade" class="form-control mr-2">
                        <option value="">Todas modalidades</option>
                        <option value="Simples" {{ request('modalidade') == 'Simples' ? 'selected' : '' }}>Simples</option>
                        <option value="Casadinha" {{ request('modalidade') == 'Casadinha' ? 'selected' : '' }}>Casadinha</option>
                        <option value="Loto" {{ request('modalidade') == 'Loto' ? 'selected' : '' }}>Loto</option>
                    </select>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filtrar</button>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Codigo</th>
                                <th>Cliente</th>
                                <th>Vendedor</th>
                                <th>Modalidade</th>
                                <th>Valor Apostado</th>
                                <th>Retorno Possivel</th>
                                <th>Cash Out</th>
                                <th>Acao</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($apostas as $aposta)
                            <tr>
                                <td>{{ $aposta->id }}</td>
                                <td><code>{{ $aposta->codigo_bilhete }}</code></td>
                                <td>{{ $aposta->cliente ?? 'N/D' }}</td>
                                <td>{{ $aposta->vendedor }}</td>
                                <td><span class="badge badge-{{ $aposta->modalidade === 'Loto' ? 'warning' : 'info' }}">{{ $aposta->modalidade }}</span></td>
                                <td>R$ {{ number_format($aposta->valor_apostado, 2, ',', '.') }}</td>
                                <td>R$ {{ number_format($aposta->retorno_possivel, 2, ',', '.') }}</td>
                                <td>
                                    @php
                                        $cashOutValor = ($aposta->valor_apostado * $taxa) / 100;
                                    @endphp
                                    <span class="text-success font-weight-bold">
                                        R$ {{ number_format($cashOutValor, 2, ',', '.') }}
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-warning btn-cashout"
                                            data-id="{{ $aposta->id }}"
                                            data-valor="{{ number_format($cashOutValor, 2, ',', '.') }}"
                                            data-original="{{ number_format($aposta->retorno_possivel, 2, ',', '.') }}">
                                        <i class="fas fa-hand-holding-usd"></i> Cash Out
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted">Nenhuma aposta aberta encontrada</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $apostas->withQueryString()->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmacao -->
<div class="modal fade" id="cashoutModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Confirmar Cash Out</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja antecipar esta aposta?</p>
                <div class="alert alert-info">
                    <p><strong>Valor Apostado:</strong> R$ <span id="modal-valor-apostado"></span></p>
                    <p><strong>Retorno Original:</strong> R$ <span id="modal-valor-original"></span></p>
                    <p><strong>Valor Cash Out:</strong> <span class="text-success font-weight-bold">R$ <span id="modal-valor-cashout"></span></span></p>
                </div>
                <div class="form-group">
                    <label>Senha para confirmar:</label>
                    <input type="password" id="cashout-senha" class="form-control" placeholder="Digite sua senha">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" id="btn-confirm-cashout">
                    <i class="fas fa-check"></i> Confirmar Cash Out
                </button>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
let currentBetId = null;

$(document).ready(function() {
    $('.btn-cashout').click(function() {
        currentBetId = $(this).data('id');
        $('#modal-valor-cashout').text($(this).data('valor'));
        $('#modal-valor-original').text($(this).data('original'));
        $('#modal-valor-apostado').text(
            $(this).closest('tr').find('td:nth-child(6)').text().replace('R$ ', '')
        );
        $('#cashoutModal').modal('show');
    });

    $('#btn-confirm-cashout').click(function() {
        const senha = $('#cashout-senha').val();
        if (!senha) {
            toastr.error('Digite sua senha');
            return;
        }

        $.ajax({
            url: `/admin/cashout/executar/${currentBetId}`,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                senha: senha
            },
            success: function(res) {
                toastr.success(res.message);
                $('#cashoutModal').modal('hide');
                setTimeout(() => location.reload(), 1500);
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.error || 'Erro ao processar';
                toastr.error(msg);
            }
        });
    });
});
</script>
@stop
