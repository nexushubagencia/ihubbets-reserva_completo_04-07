@extends('adminlte::page')

@section('title', 'Partidas Ao Vivo')

@section('content_header')
    <h1><i class="fas fa-broadcast-tower me-2 text-danger"></i> Gerenciar Ao Vivo</h1>
@stop

@section('content')
<div id="app">
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i> Integrando motor de monitoramento em tempo real...
    </div>
    <!-- Componente de jogos ao vivo será montado aqui -->
</div>
@stop

@section('css')
    @vite(['resources/sass/app.scss'])
@stop

@section('js')
    @vite(['resources/js/app.js'])
@stop
