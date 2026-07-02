@extends('adminlte::page')

@section('title', 'Cambistas')

@section('content_header')
    <h1><i class="fas fa-walking me-2 text-warning"></i> Equipe de Cambistas</h1>
@stop

@section('content')
<div id="app">
    <colaborators-management-component role="seller"></colaborators-management-component>
</div>
@stop

@section('css')
    @vite(['resources/sass/app.scss'])
@stop

@section('js')
    @vite(['resources/js/app.js'])
@stop
