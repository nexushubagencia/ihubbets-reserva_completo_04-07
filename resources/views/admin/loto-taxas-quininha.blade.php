@extends('adminlte::page')

@section('title', 'Taxas Quininha')

@section('content_header')
    <h1><i class="fas fa-star text-warning"></i> Taxas Quininha</h1>
@stop

@section('content')
<div class="card card-warning card-outline">
    <div class="card-header">
        <h3 class="card-title">Cotacoes por Numero (01-80)</h3>
        <a href="{{ route('admin.loto') }}" class="btn btn-secondary btn-sm float-right">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover table-striped">
            <thead>
                <tr>
                    <th>Dezena</th>
                    <th>Taxa (x)</th>
                    <th>Status</th>
                    <th>Acao</th>
                </tr>
            </thead>
            <tbody>
                @foreach($taxas as $taxa)
                <tr>
                    <td><span class="badge badge-warning">{{ $taxa->dezena }}</span></td>
                    <td>
                        <input type="number" step="0.01" min="0" value="{{ $taxa->taxa }}"
                               class="form-control form-control-sm taxa-input"
                               data-id="{{ $taxa->id }}" style="width: 100px;">
                    </td>
                    <td>
                        @if($taxa->status)
                            <span class="badge badge-success">Ativo</span>
                        @else
                            <span class="badge badge-danger">Bloqueado</span>
                        @endif
                    </td>
                    <td>
                        <button class="btn btn-sm btn-primary update-taxa" data-id="{{ $taxa->id }}">
                            <i class="fas fa-save"></i>
                        </button>
                        <button class="btn btn-sm btn-{{ $taxa->status ? 'danger' : 'success' }} toggle-status"
                                data-id="{{ $taxa->id }}">
                            <i class="fas fa-{{ $taxa->status ? 'lock' : 'unlock' }}"></i>
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    $('.update-taxa').click(function() {
        const id = $(this).data('id');
        const taxa = $(`input[data-id="${id}"]`).val();
        $.ajax({
            url: `/admin/loto/taxa-quina/${id}`,
            type: 'PUT',
            data: { taxa: taxa, _token: '{{ csrf_token() }}' },
            success: function(res) {
                toastr.success('Taxa atualizada!');
            }
        });
    });

    $('.toggle-status').click(function() {
        const id = $(this).data('id');
        $.ajax({
            url: `/admin/loto/status-quina/${id}`,
            type: 'PUT',
            data: { _token: '{{ csrf_token() }}' },
            success: function(res) {
                location.reload();
            }
        });
    });
});
</script>
@stop
