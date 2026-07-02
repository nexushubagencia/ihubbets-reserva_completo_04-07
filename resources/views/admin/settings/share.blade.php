@extends('adminlte::page')
@section('title', 'Compartilhamentos')
@section('content_header')
    <h1><i class="fas fa-share-alt text-info"></i> Links de Compartilhamento</h1>
@stop
@section('content')
<div class="card card-info card-outline">
    <div class="card-body">
        <form action="{{ route('admin.settings.share.update') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Links de Redes Sociais / Compartilhamento</label>
                <textarea name="share_links" class="form-control" rows="10" placeholder="Exemplo: 
WhatsApp: https://wa.me/55...
Instagram: https://instagram.com/...
Telegram: https://t.me/...">{{ $settings->share_links }}</textarea>
                <small class="text-muted"><i class="fas fa-info-circle me-1"></i> Insira os links que aparecerão para os usuários compartilharem ou acessarem suas redes.</small>
            </div>
            <div class="text-right">
                <button type="submit" class="btn btn-primary btn-lg shadow-sm"><i class="fas fa-save me-2"></i> Salvar Links</button>
            </div>
        </form>
    </div>
</div>
@stop
