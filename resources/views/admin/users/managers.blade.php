@extends('adminlte::page')

@section('title', 'Gerentes')

@section('content_header')
    <h1><i class="fas fa-user-shield me-2 text-primary"></i> Admin de Região / Gerentes</h1>
@stop

@section('content')
<div id="app">
    <colaborators-management-component role="manager"></colaborators-management-component>
</div>
@stop

@section('css')
    @vite(['resources/sass/app.scss'])
@stop

@section('js')
    @vite(['resources/js/app.js'])
@stop
