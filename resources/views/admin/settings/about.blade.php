@extends('adminlte::page')
@section('title', 'Sobre Nós')

@section('content_header')
    <h1><i class="fas fa-info-circle text-info"></i> Sobre Nós</h1>
@stop

@section('content')

{{-- Alertas de sucesso/erro da sessão (fallback para redirect) --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
    </div>
@endif

<div class="card card-info card-outline">
    <div class="card-body">
        <form id="aboutForm">
            @csrf
            <div class="form-group">
                <label><i class="fas fa-align-left mr-1"></i> Conteúdo da Página "Sobre Nós"</label>
                <small class="text-muted d-block mb-2">
                    Este texto aparece no rodapé da plataforma (seção "Sobre Nós").
                </small>
                <textarea name="about_us" id="aboutTextarea" class="form-control" rows="12"
                    placeholder="Escreva aqui a história ou descrição da sua banca...">{{ $settings->about_us ?? '' }}</textarea>
            </div>
            <div class="text-right">
                <button type="button" id="saveAboutBtn" class="btn btn-primary btn-lg shadow-sm">
                    <i class="fas fa-save mr-1"></i> Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>
@stop

@section('js')
<script>
document.getElementById('saveAboutBtn').addEventListener('click', function () {
    const btn = this;
    const text = document.getElementById('aboutTextarea').value;

    // Feedback visual imediato
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Salvando...';

    fetch('{{ route("admin.settings.about.update") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({ about_us: text })
    })
    .then(async res => {
        const data = await res.json();
        if (res.ok && (data.success || data.status === 'success')) {
            toastr.success('Sobre Nós atualizado com sucesso!', 'Salvo!', {
                positionClass: 'toast-top-right',
                timeOut: 4000,
                progressBar: true
            });
        } else {
            throw new Error(data.message || 'Erro ao salvar.');
        }
    })
    .catch(err => {
        toastr.error(err.message || 'Erro inesperado ao salvar.', 'Erro!', {
            positionClass: 'toast-top-right',
            timeOut: 5000
        });
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save mr-1"></i> Salvar Alterações';
    });
});
</script>
@stop
