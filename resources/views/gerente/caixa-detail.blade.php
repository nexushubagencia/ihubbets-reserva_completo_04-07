@extends('gerente.layouts.app')
@section('title', 'Caixa - Detalhe Cambista')
@section('content_header')
    <h1>
        <i class="fas fa-university text-primary mr-2"></i> Caixa de {{ $cambista->name }}
    </h1>
@stop

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <h5 class="font-weight-bold mb-0"><i class="fas fa-history mr-2"></i> Últimos Lançamentos</h5>
        <a href="{{ route('gerente.caixa') }}" class="btn btn-sm btn-outline-light">
            <i class="fas fa-arrow-left mr-1"></i> Voltar
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="bg-light">
                    <tr style="font-size: 0.78rem; text-transform: uppercase;">
                        <th class="px-3 py-2">Data</th>
                        <th class="py-2">Tipo</th>
                        <th class="py-2">Descrição</th>
                        <th class="py-2 text-right">Valor</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lancamentos as $l)
                        <tr style="font-size: 0.82rem;">
                            <td class="px-3 text-muted">{{ $l->created_at ? $l->created_at->format('d/m/Y H:i') : '-' }}</td>
                            <td>
                                @if(strtolower($l->tipo) === 'crédito' || strtolower($l->tipo) === 'credito')
                                    <span class="badge badge-success">Crédito</span>
                                @else
                                    <span class="badge badge-danger">Débito</span>
                                @endif
                            </td>
                            <td>{{ $l->descricao ?? '-' }}</td>
                            <td class="text-right font-weight-bold {{ strtolower($l->tipo) === 'crédito' || strtolower($l->tipo) === 'credito' ? 'text-success' : 'text-danger' }}">
                                {{ strtolower($l->tipo) === 'crédito' || strtolower($l->tipo) === 'credito' ? '+' : '-' }}R$ {{ number_format($l->valor, 2, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">Nenhum lançamento encontrado</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop
