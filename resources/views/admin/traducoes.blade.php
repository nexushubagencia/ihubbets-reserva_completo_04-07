@extends('admin.layouts.app')

@section('title', 'Traduções | IHUB BETS')

@section('content_header')
    <h1><i class="fas fa-language text-info"></i> Traduções <small class="text-muted">Gestão de Idiomas</small></h1>
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

    <div class="card card-outline card-info shadow-sm border-0">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h3 class="card-title mt-1"><i class="fas fa-globe mr-2"></i> Chaves de Tradução</h3>
            <div class="d-flex">
                <input type="text" id="search-translations" class="form-control form-control-sm mr-2" placeholder="Buscar chave ou valor..." style="width: 300px;">
                <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#modalAddTranslation">
                    <i class="fas fa-plus mr-1"></i> Adicionar
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="table-traducoes" class="table table-hover table-striped mb-0" style="width:100%">
                    <thead class="bg-light text-muted text-uppercase" style="font-size: 0.75rem;">
                        <tr>
                            <th class="py-3 px-4">Chave</th>
                            <th class="py-3">Português (PT)</th>
                            <th class="py-3">Inglês (EN)</th>
                            <th class="py-3 text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($traducoes as $traducao)
                            <tr id="traducao-row-{{ $traducao->id }}">
                                <td class="text-muted font-weight-bold">
                                    <code class="bg-light p-1 px-2 rounded">{{ $traducao->key }}</code>
                                </td>
                                <td>
                                    <span class="translation-pt" data-id="{{ $traducao->id }}">{{ $traducao->pt ?? '' }}</span>
                                </td>
                                <td>
                                    <span class="translation-en" data-id="{{ $traducao->id }}">{{ $traducao->en ?? '' }}</span>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary edit-translation" data-id="{{ $traducao->id }}"
                                            data-key="{{ $traducao->key }}" data-pt="{{ $traducao->pt }}" data-en="{{ $traducao->en }}"
                                            title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('admin.traducoes.destroy', $traducao->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Excluir" onclick="return confirm('Excluir esta tradução?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="fas fa-language fa-2x mb-3 d-block"></i>
                                    Nenhuma tradução encontrada.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Adicionar Tradução -->
<div class="modal fade" id="modalAddTranslation" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-plus-circle mr-2"></i> Nova Tradução</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="{{ route('admin.traducoes.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Chave *</label>
                        <input type="text" name="key" class="form-control form-control-sm"
                               placeholder="Ex: home.welcome" required>
                        <small class="text-muted">Use notação pontuada (ex: auth.login)</small>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Português (PT) *</label>
                        <input type="text" name="pt" class="form-control form-control-sm"
                               placeholder="Texto em português" required>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Inglês (EN) *</label>
                        <input type="text" name="en" class="form-control form-control-sm"
                               placeholder="English text" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success px-4"><i class="fas fa-save mr-1"></i> Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Tradução -->
<div class="modal fade" id="modalEditTranslation" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-edit mr-2"></i> Editar Tradução</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="" method="POST" id="form-edit-translation">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Chave</label>
                        <input type="text" name="key" id="edit-key" class="form-control form-control-sm" readonly>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Português (PT) *</label>
                        <input type="text" name="pt" id="edit-pt" class="form-control form-control-sm" required>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Inglês (EN) *</label>
                        <input type="text" name="en" id="edit-en" class="form-control form-control-sm" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save mr-1"></i> Atualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .table td, .table th { vertical-align: middle; }
    code { font-size: 0.85rem; }
    .badge { font-weight: 500; }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    $('#table-traducoes').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json'
        },
        pageLength: 50,
        order: [[0, 'asc']],
        responsive: true
    });

    $('#search-translations').on('keyup', function() {
        $('#table-traducoes').DataTable().search($(this).val()).draw();
    });

    $(document).on('click', '.edit-translation', function() {
        var id = $(this).data('id');
        var key = $(this).data('key');
        var pt = $(this).data('pt');
        var en = $(this).data('en');

        $('#edit-key').val(key);
        $('#edit-pt').val(pt);
        $('#edit-en').val(en);
        $('#form-edit-translation').attr('action', '/admin/traducoes/' + id);
        $('#modalEditTranslation').modal('show');
    });

    $('#form-edit-translation').submit(function(e) {
        e.preventDefault();
        var form = $(this);
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize() + '&_method=PUT',
            success: function(response) {
                toastr.success(response.message || 'Tradução atualizada!');
                $('#modalEditTranslation').modal('hide');
                setTimeout(function() { location.reload(); }, 1000);
            },
            error: function() {
                toastr.error('Erro ao atualizar tradução.');
            }
        });
    });
});
</script>
@stop
