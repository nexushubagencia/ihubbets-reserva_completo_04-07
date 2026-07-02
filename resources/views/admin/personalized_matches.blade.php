@extends('adminlte::page')
@section('title', 'Partidas Personalizadas')
@section('content_header')
    <h1><i class="fas fa-edit"></i> Partidas Personalizadas</h1>
    
@stop
@section('content')
<div class="card card-primary card-outline">
    <div class="card-body">
        <div class="alert alert-info"><i class="fas fa-info-circle"></i> Crie partidas personalizadas com odds manuais para seu sistema de apostas.</div>
        <div class="row mb-3">
            <div class="col-md-3"><label>Liga:</label><input type="text" class="form-control" id="pm-liga" placeholder="Ex: Copa Local"></div>
            <div class="col-md-3"><label>Time Casa:</label><input type="text" class="form-control" id="pm-home" placeholder="Time casa"></div>
            <div class="col-md-3"><label>Time Fora:</label><input type="text" class="form-control" id="pm-away" placeholder="Time fora"></div>
            <div class="col-md-3"><label>Data:</label><input type="datetime-local" class="form-control" id="pm-date"></div>
        </div>
        <button class="btn btn-success mb-3" onclick="addPM()"><i class="fas fa-plus"></i> Criar Partida</button>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead style="background:#1E282C;color:#FFF;">
                    <tr><th>LIGA</th><th>CASA</th><th>FORA</th><th>DATA</th><th>AÇÃO</th></tr>
                </thead>
                <tbody id="pm-tbody"><tr><td colspan="5" class="text-center text-muted">Funcionalidade será conectada ao Frontend V1.2</td></tr></tbody>
            </table>
        </div>
    </div>
</div>
@stop
@section('js')
<script>
function addPM(){
    toastr.info('Funcionalidade de partidas personalizadas será conectada ao Frontend V1.2');
}
</script>
@stop
