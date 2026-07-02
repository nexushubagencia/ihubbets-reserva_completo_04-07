@extends('admin.layouts.app')

@section('title', 'Promoções | IHUB BETS')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-tags text-success"></i> Promoções <small class="text-muted">Campanhas & Bônus</small></h1>
        <a href="{{ route('admin.promocoes.create') }}" class="btn btn-success font-weight-bold shadow-sm">
            <i class="fas fa-plus mr-1"></i> Criar Promoção
        </a>
    </div>
@stop

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <h5><i class="icon fas fa-check"></i> Sucesso!</h5>
            {{ session('success') }}
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-success shadow-sm border-0">
                <div class="card-header bg-dark text-white">
                    <h3 class="card-title mt-1"><i class="fas fa-list mr-2"></i> Lista de Promoções</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="table-promocoes" class="table table-hover table-striped mb-0" style="width:100%">
                            <thead class="bg-light text-muted text-uppercase" style="font-size: 0.75rem;">
                                <tr>
                                    <th class="py-3 px-4">ID</th>
                                    <th class="py-3">Nome</th>
                                    <th class="py-3">Tipo</th>
                                    <th class="py-3">Valor</th>
                                    <th class="py-3">Dep. Mínimo</th>
                                    <th class="py-3">Rollover</th>
                                    <th class="py-3 text-center">Status</th>
                                    <th class="py-3 text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($promocoes as $promo)
                                    <tr>
                                        <td class="text-muted font-weight-bold">#{{ $promo->id }}</td>
                                        <td class="font-weight-bold">{{ $promo->name }}</td>
                                        <td>
                                            @php
                                                $typeClass = match($promo->type) {
                                                    'bonus' => 'primary',
                                                    'freebet' => 'info',
                                                    'cashback' => 'warning',
                                                    default => 'secondary'
                                                };
                                            @endphp
                                            <span class="badge badge-{{ $typeClass }} p-2 px-3">{{ ucfirst($promo->type) }}</span>
                                        </td>
                                        <td class="font-weight-bold text-success">
                                            @if($promo->type === 'bonus')
                                                {{ $promo->value }}%
                                            @else
                                                R$ {{ number_format($promo->value, 2, ',', '.') }}
                                            @endif
                                        </td>
                                        <td>R$ {{ number_format($promo->min_deposit ?? 0, 2, ',', '.') }}</td>
                                        <td class="text-center"><span class="badge badge-info">{{ $promo->rollover ?? 1 }}x</span></td>
                                        <td class="text-center">
                                            <form action="{{ route('admin.promocoes.toggle', $promo->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-xs btn-{{ $promo->is_active ? 'success' : 'secondary' }} rounded-pill px-3">
                                                    {{ $promo->is_active ? 'Ativo' : 'Inativo' }}
                                                </button>
                                            </form>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('admin.promocoes.edit', $promo->id) }}" class="btn btn-sm btn-outline-primary shadow-sm" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.promocoes.destroy', $promo->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger shadow-sm" title="Excluir" onclick="return confirm('Excluir esta promoção?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5 text-muted">
                                            <i class="fas fa-tags fa-2x mb-3 d-block"></i>
                                            Nenhuma promoção encontrada. Clique em "Criar Promoção" para começar.
                                        </td>
                                    </tr>
                                @endforelse
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
    .table td, .table th { vertical-align: middle; }
    .badge { font-weight: 500; }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    $('#table-promocoes').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json'
        },
        order: [[0, 'desc']],
        pageLength: 25,
        responsive: true
    });
});
</script>
@stop
