@extends('adminlte::page')

@section('title', 'Regiões - IHUB V2')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-map-marked-alt text-info"></i> Regiões <small class="text-muted">Gestão Geográfica</small></h1>
        <button type="button" class="btn btn-info shadow-sm font-weight-bold" data-toggle="modal" data-target="#modalNovaRegiao">
            <i class="fas fa-plus-circle me-1"></i> Nova Região
        </button>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white border-0">
                    <h3 class="card-title fw-bold"><i class="fas fa-list me-2"></i> Regiões Cadastradas</h3>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>ID</th>
                                <th>Nome da Região</th>
                                <th>Gerente Responsável</th>
                                <th>Descrição</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($regions as $region)
                                <tr>
                                    <td>{{ $region->id }}</td>
                                    <td class="fw-bold">{{ $region->name }}</td>
                                    <td>
                                        @if($region->manager)
                                            <span class="badge badge-info">{{ $region->manager->username }}</span>
                                        @else
                                            <span class="text-muted italic">Sem Gerente</span>
                                        @endif
                                    </td>
                                    <td class="text-muted small">{{ $region->description ?? '---' }}</td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-info"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">Nenhuma região cadastrada.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nova Região -->
<div class="modal fade" id="modalNovaRegiao" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title font-weight-bold">Cadastrar Nova Região</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.regions.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nome da Região</label>
                        <input type="text" name="name" class="form-control" required placeholder="Ex: São Paulo Capital">
                    </div>
                    <div class="form-group">
                        <label>Gerente Responsável</label>
                        <select name="manager_id" class="form-control">
                            <option value="">Selecione um Gerente...</option>
                            @foreach(\App\Models\User::where('nivel', 'adm')->get() as $mgr)
                                <option value="{{ $mgr->id }}">{{ $mgr->name }} ({{ $mgr->username }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Descrição (Opcional)</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-info font-weight-bold">Salvar Região</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop
