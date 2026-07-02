@extends('adminlte::page')
@section('title', 'Em Breve')
@section('content_header')
    <h1><i class="fas fa-hammer"></i> Módulo em Atualização</h1>
@stop
@section('content')
<div class="alert alert-info">
    <h5><i class="icon fas fa-info"></i> Aviso!</h5>
    Esta funcionalidade (<b>{{ request()->path() }}</b>) está sendo reestruturada para a nova versão (v2.1).
    Ficará disponível muito em breve.
</div>
@stop