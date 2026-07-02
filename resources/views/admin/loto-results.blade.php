@extends('adminlte::page')

@section('title', 'Resultados Loto')

@section('content_header')
    <h1><i class="fas fa-trophy"></i> Resultados Loto</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">Cadastrar Resultado</h3>
            </div>
            <div class="card-body">
                <form id="formResult">
                    @csrf
                    <div class="row">
                        <div class="col-md-2">
                            <label>Tipo</label>
                            <select name="tipo" class="form-control" required>
                                <option value="Quina">Quina</option>
                                <option value="Mega-Sena">Mega-Sena</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>Concurso</label>
                            <input type="text" name="concurso" class="form-control" placeholder="dd/mm/yyyy" required>
                        </div>
                        <div class="col-md-2">
                            <label>Data Sorteio</label>
                            <input type="date" name="data_sorteio" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label>Dezenas (separadas por virgula)</label>
                            <input type="text" name="dezenas" class="form-control"
                                   placeholder="Ex: 05,12,33,45,78" required>
                        </div>
                        <div class="col-md-2">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-save"></i> Salvar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card card-outline card-secondary">
            <div class="card-header">
                <h3 class="card-title">Historico de Resultados</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>Concurso</th>
                            <th>Data</th>
                            <th>Dezenas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($results as $result)
                        <tr>
                            <td>
                                <span class="badge badge-{{ $result->tipo === 'Quina' ? 'warning' : 'success' }}">
                                    {{ $result->tipo }}
                                </span>
                            </td>
                            <td>{{ $result->concurso }}</td>
                            <td>{{ $result->data_sorteio ? $result->data_sorteio->format('d/m/Y') : '-' }}</td>
                            <td>
                                @if(is_array($result->dezenas))
                                    @foreach($result->dezenas as $d)
                                        <span class="badge badge-dark">{{ $d }}</span>
                                    @endforeach
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">Nenhum resultado cadastrado</td>
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
$('#formResult').submit(function(e) {
    e.preventDefault();
    const dezenas = $('input[name="dezenas"]').val().split(',').map(d => d.trim());
    $.ajax({
        url: '{{ route("admin.loto") }}/results',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            tipo: $('select[name="tipo"]').val(),
            concurso: $('input[name="concurso"]').val(),
            data_sorteio: $('input[name="data_sorteio"]').val(),
            dezenas: dezenas
        },
        success: function(res) {
            toastr.success('Resultado cadastrado!');
            location.reload();
        },
        error: function(xhr) {
            toastr.error('Erro ao cadastrar resultado');
        }
    });
});
</script>
@stop
