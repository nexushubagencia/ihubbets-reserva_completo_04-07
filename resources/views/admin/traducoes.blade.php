@extends('adminlte::page')

@section('title', 'Traduções de Ligas e Times')

@section('content_header')
    <h1><i class="fas fa-language text-info"></i> Traduções <small class="text-muted">Ligas e Times da API</small></h1>
@stop

@section('content')
<div class="row">
    <!-- FORMULÁRIO -->
    <div class="col-md-4">
        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-plus-circle mr-1"></i> Nova Tradução</h3>
            </div>
            <form action="{{ route('admin.traducoes.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
                        </div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger">
                            @foreach($errors->all() as $error)
                                <div><i class="fas fa-exclamation-triangle mr-1"></i> {{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <div class="form-group">
                        <label class="font-weight-bold">Tipo *</label>
                        <select name="tipo" class="form-control form-control-sm" required>
                            <option value="">Selecione...</option>
                            <option value="liga" {{ old('tipo') == 'liga' ? 'selected' : '' }}>Liga / Campeonato</option>
                            <option value="time" {{ old('tipo') == 'time' ? 'selected' : '' }}>Time / Equipe</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Nome Original (como vem da API) *</label>
                        <input type="text" name="texto_original" class="form-control form-control-sm"
                               placeholder="Ex: World Cup, Premier League" value="{{ old('texto_original') }}" required>
                        <small class="text-muted">Exato como aparece na API-Football</small>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Nome Traduzido (como deve aparecer) *</label>
                        <input type="text" name="texto_traduzido" class="form-control form-control-sm"
                               placeholder="Ex: Copa do Mundo, Premier League" value="{{ old('texto_traduzido') }}" required>
                        <small class="text-muted">Nome que o usuário verá no site</small>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary btn-block btn-sm">
                        <i class="fas fa-save mr-1"></i> Salvar Tradução
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- LISTA -->
    <div class="col-md-8">
        <div class="card card-outline card-success shadow-sm">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h3 class="card-title mt-1"><i class="fas fa-list mr-2"></i> Traduções Cadastradas</h3>
                <div class="d-flex">
                    <select id="filter-tipo" class="form-control form-control-sm mr-2" style="width: 130px;">
                        <option value="">Todos</option>
                        <option value="liga" {{ request('tipo') == 'liga' ? 'selected' : '' }}>Ligas</option>
                        <option value="time" {{ request('tipo') == 'time' ? 'selected' : '' }}>Times</option>
                    </select>
                    <input type="text" id="search-translations" class="form-control form-control-sm mr-2"
                           placeholder="Buscar..." style="width: 200px;">
                    <a href="{{ route('admin.traducoes') }}" class="btn btn-sm btn-secondary" title="Limpar filtros">
                        <i class="fas fa-sync"></i>
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="table-traducoes" class="table table-hover table-striped mb-0">
                        <thead class="bg-light text-muted text-uppercase" style="font-size: 0.75rem;">
                            <tr>
                                <th class="py-2 px-3">ID</th>
                                <th class="py-2">Tipo</th>
                                <th class="py-2">Nome Original</th>
                                <th class="py-2">Nome Traduzido</th>
                                <th class="py-2 text-center" style="width: 120px;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($traducoes as $trad)
                                <tr id="traducao-row-{{ $trad->id }}">
                                    <td class="text-muted">{{ $trad->id }}</td>
                                    <td>
                                        <span class="badge badge-{{ $trad->tipo == 'liga' ? 'primary' : 'warning' }} text-uppercase">
                                            {{ $trad->tipo == 'liga' ? 'Liga' : 'Time' }}
                                        </span>
                                    </td>
                                    <td><code class="bg-light p-1 px-2 rounded">{{ $trad->texto_original }}</code></td>
                                    <td class="font-weight-bold">{{ $trad->texto_traduzido }}</td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary btn-edit-trad"
                                                data-id="{{ $trad->id }}"
                                                data-original="{{ $trad->texto_original }}"
                                                data-traduzido="{{ $trad->texto_traduzido }}"
                                                title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="{{ route('admin.traducoes.destroy', $trad->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Excluir esta tradução?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Excluir">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="fas fa-language fa-3x mb-3 d-block opacity-50"></i>
                                        <p class="mb-0">Nenhuma tradução encontrada.</p>
                                        <small>Comece adicionando uma nova tradução no formulário ao lado.</small>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($traducoes->hasPages())
                <div class="card-footer">
                    {{ $traducoes->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Editar -->
<div class="modal fade" id="modalEditTrad" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-edit mr-2"></i> Editar Tradução</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="form-edit-trad">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Nome Original</label>
                        <input type="text" id="edit-original" class="form-control form-control-sm" readonly>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Nome Traduzido *</label>
                        <input type="text" id="edit-traduzido" class="form-control form-control-sm" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save mr-1"></i> Atualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .table td, .table th { vertical-align: middle; font-size: 0.85rem; }
    code { font-size: 0.82rem; }
    .badge { font-weight: 500; font-size: 0.7rem; }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    var table = $('#table-traducoes').DataTable({
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json' },
        pageLength: 50,
        order: [[0, 'desc']],
        responsive: true,
        columnDefs: [{ orderable: false, targets: [4] }]
    });

    $('#search-translations').on('keyup', function() {
        table.search($(this).val()).draw();
    });

    $('#filter-tipo').on('change', function() {
        var val = $(this).val();
        if (val === '') {
            table.column(1).search('').draw();
        } else {
            table.column(1).search(val === 'liga' ? 'Liga' : 'Time').draw();
        }
    });

    $(document).on('click', '.btn-edit-trad', function() {
        var id = $(this).data('id');
        var original = $(this).data('original');
        var traduzido = $(this).data('traduzido');
        $('#edit-original').val(original);
        $('#edit-traduzido').val(traduzido);
        $('#form-edit-trad').attr('action', '/admin/traducoes/' + id);
        $('#modalEditTrad').modal('show');
    });

    $('#form-edit-trad').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize() + '&_method=PUT',
            success: function(response) {
                toastr.success(response.message || 'Tradução atualizada!');
                $('#modalEditTrad').modal('hide');
                setTimeout(function() { location.reload(); }, 800);
            },
            error: function() {
                toastr.error('Erro ao atualizar tradução.');
            }
        });
    });
});
</script>
@stop
