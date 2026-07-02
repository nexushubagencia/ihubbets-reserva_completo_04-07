@extends('adminlte::page')

@section('title', 'Regulamento da Banca - IHUB BETS')

@section('content_header')
    <h1><i class="fas fa-file-alt"></i> Regulamento <small class="text-muted">Termos e condições de uso</small></h1>
@stop

@section('content')
    <div class="card card-outline card-primary shadow-sm">
        <form action="{{ route('admin.settings.layout.update') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="alert alert-info py-2">
                    <i class="fas fa-info-circle"></i> O texto abaixo será exibido na página de "Regulamento" do site principal e também poderá ser impresso no rodapé de alguns bilhetes.
                </div>
                
                <div class="form-group">
                    <textarea name="regulation" class="form-control" rows="25" style="font-family: 'Courier New', Courier, monospace; font-size: 14px;" placeholder="Digite aqui as regras da sua banca...">{{ $settings->regulation }}</textarea>
                </div>
            </div>
            <div class="card-footer text-right bg-white">
                <button type="submit" class="btn btn-success px-5"><i class="fas fa-save"></i> Salvar Regulamento</button>
            </div>
        </form>
    </div>
@stop

@section('css')
    <style>
        .card { border-radius: 8px; }
        textarea:focus { border-color: #28a745; box-shadow: 0 0 5px rgba(40,167,69,0.5); }
    </style>
@stop
