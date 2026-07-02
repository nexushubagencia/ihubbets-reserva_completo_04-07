@extends('adminlte::page')

@section('title', 'Bolao - Pool Betting')

@section('content_header')
    <h1><i class="fas fa-futbol text-primary"></i> Bolao - Pool Betting</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="info-box">
            <span class="info-box-icon bg-primary"><i class="fas fa-layer-group"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Rodadas</span>
                <span class="info-box-number">{{ $rodadas->count() }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box">
            <span class="info-box-icon bg-success"><i class="fas fa-ticket-alt"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Apostas</span>
                <span class="info-box-number">{{ $totalApostas }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box">
            <span class="info-box-icon bg-warning"><i class="fas fa-dollar-sign"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Arrecadado</span>
                <span class="info-box-number">R$ {{ number_format($totalArrecadado, 2, ',', '.') }}</span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-plus"></i> Criar Rodada</h3>
            </div>
            <div class="card-body">
                <form id="formRodada">
                    @csrf
                    <div class="row">
                        <div class="col-md-3">
                            <label>Nome da Rodada</label>
                            <input type="text" name="nome" class="form-control" placeholder="Ex: Rodada 10" required>
                        </div>
                        <div class="col-md-2">
                            <label>Premio Maximo</label>
                            <input type="number" name="premio_max" class="form-control" step="0.01" required>
                        </div>
                        <div class="col-md-2">
                            <label>1o Lugar</label>
                            <input type="number" name="premio_primeiro" class="form-control" step="0.01" required>
                        </div>
                        <div class="col-md-2">
                            <label>2o Lugar</label>
                            <input type="number" name="premio_segundo" class="form-control" step="0.01" value="0">
                        </div>
                        <div class="col-md-2">
                            <label>3o Lugar</label>
                            <input type="number" name="premio_terceiro" class="form-control" step="0.01" value="0">
                        </div>
                        <div class="col-md-2">
                            <label>Fechamento</label>
                            <input type="datetime-local" name="data_fechamento" class="form-control" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary mt-2"><i class="fas fa-save"></i> Criar Rodada</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card card-outline card-secondary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list"></i> Rodadas</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nome</th>
                            <th>Status</th>
                            <th>Premio Max</th>
                            <th>Apostas</th>
                            <th>Arrecadado</th>
                            <th>Fechamento</th>
                            <th>Acao</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rodadas as $rodada)
                        <tr>
                            <td>{{ $rodada->id }}</td>
                            <td>{{ $rodada->nome }}</td>
                            <td>
                                @if($rodada->status === 'Aberta')
                                    <span class="badge badge-success">Aberta</span>
                                @elseif($rodada->status === 'Fechada')
                                    <span class="badge badge-warning">Fechada</span>
                                @else
                                    <span class="badge badge-secondary">Finalizada</span>
                                @endif
                            </td>
                            <td>R$ {{ number_format($rodada->premio_max, 2, ',', '.') }}</td>
                            <td>{{ $rodada->quantidade }}</td>
                            <td>R$ {{ number_format($rodada->arrecadado, 2, ',', '.') }}</td>
                            <td>{{ $rodada->data_fechamento ? \Carbon\Carbon::parse($rodada->data_fechamento)->format('d/m/Y H:i') : '-' }}</td>
                            <td>
                                @if($rodada->status === 'Aberta')
                                    <button class="btn btn-sm btn-warning btn-fechar" data-id="{{ $rodada->id }}">
                                        <i class="fas fa-lock"></i>
                                    </button>
                                @endif
                                @if($rodada->status === 'Fechada')
                                    <button class="btn btn-sm btn-info btn-finalizar" data-id="{{ $rodada->id }}">
                                        <i class="fas fa-check"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">Nenhuma rodada criada</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    $('#formRodada').submit(function(e) {
        e.preventDefault();
        $.ajax({
            url: '{{ route("admin.bolao") }}/store-rodada',
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                toastr.success('Rodada criada!');
                location.reload();
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.error || 'Erro ao criar rodada';
                toastr.error(msg);
            }
        });
    });

    $('.btn-fechar').click(function() {
        const id = $(this).data('id');
        if (confirm('Fechar esta rodada? Apostas nao serao mais aceitas.')) {
            $.ajax({
                url: `/admin/bolao/${id}/fechar`,
                type: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function() {
                    toastr.success('Rodada fechada!');
                    location.reload();
                }
            });
        }
    });
});
</script>
@stop
