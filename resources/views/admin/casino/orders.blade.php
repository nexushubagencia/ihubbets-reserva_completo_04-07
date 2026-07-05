@extends('adminlte::page')

@section('title', 'Apostas do Cassino | IHUB BETS')

@section('content_header')
    <h1 class="text-dark font-weight-bold">
        <i class="fas fa-history text-primary mr-2"></i> Apostas do Cassino
        <small class="text-muted text-sm font-weight-normal">Histórico de transações dos jogos</small>
    </h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-primary shadow-lg border-0">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h3 class="card-title text-lg font-weight-bold text-secondary">
                        <i class="fas fa-list mr-2 text-primary"></i> Últimas Apostas
                    </h3>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-borderless table-striped align-middle">
                            <thead class="bg-light text-muted text-uppercase" style="font-size: 0.75rem;">
                                <tr>
                                    <th>ID</th>
                                    <th>Usuário</th>
                                    <th>Jogo</th>
                                    <th>Tipo</th>
                                    <th>Valor</th>
                                    <th>Provedor</th>
                                    <th>TxID</th>
                                    <th>Data</th>
                                </tr>
                            </thead>
                            <tbody class="text-secondary" style="font-size: 0.9rem;">
                                @forelse($orders as $order)
                                <tr>
                                    <td>{{ $order->id }}</td>
                                    <td>{{ $order->user?->name ?? $order->user?->email ?? 'N/A' }}</td>
                                    <td>{{ $order->game ?? '-' }}</td>
                                    <td><span class="badge badge-{{ $order->type === 'win' ? 'success' : 'primary' }}">{{ strtoupper($order->type) }}</span></td>
                                    <td>R$ {{ number_format($order->amount, 2, ',', '.') }}</td>
                                    <td><span class="badge badge-info">{{ $order->providers ?? '-' }}</span></td>
                                    <td><code>{{ $order->transaction_id ?? '-' }}</code></td>
                                    <td>{{ $order->created_at_formatted }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">Nenhuma aposta registrada.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center">
                        {{ $orders->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
