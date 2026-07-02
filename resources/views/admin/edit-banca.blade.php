@extends('adminlte::page')
@section('title', config('adminlte.title_adm_geral'))
@section('content_header')
    <h1><i class="fa fa-edit"></i> Editar Banca <small>Atualize os dados e imagens (Logo/Banners)</small></h1>
    
@stop
@section('content')
<div class="card card-primary card-outline">
    <div class="card-body">
        <form id="form-edit-banca" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Nome da Banca</label>
                        <input type="text" class="form-control" name="nome" placeholder="Insira o nome">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>E-mail de Contato</label>
                        <input type="email" class="form-control" name="email" placeholder="Insira o e-mail">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Logomarca (55x55px)</label>
                        <input type="file" class="form-control-file" name="logo" accept="image/*">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Banner 1 (1024x270px)</label>
                        <input type="file" class="form-control-file" name="banner1" accept="image/*">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Banner 2 (1024x270px)</label>
                        <input type="file" class="form-control-file" name="banner2" accept="image/*">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Banner 3 (1024x270px)</label>
                        <input type="file" class="form-control-file" name="banner3" accept="image/*">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <button type="button" class="btn btn-success" onclick="updateBanca()"><i class="fas fa-save"></i> Atualizar Dados</button>
                </div>
            </div>
        </form>
    </div>
</div>
@stop
@section('js')
<script>
function updateBanca(){
    // The original vue component simply fired an alert. We implement a stub here that can be connected to the backend.
    toastr.info("Função de upload de imagens e edição da banca em desenvolvimento/preparação para o novo Frontend.");
}
</script>
@stop