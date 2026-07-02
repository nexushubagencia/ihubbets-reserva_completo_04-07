@extends('adminlte::page')

@section('title', 'Integrações PIX')

@section('content_header')
    <h1><i class="fas fa-plug me-2 text-primary"></i> Integrações de Pagamento (PIX)</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card card-success card-outline">
            <div class="card-header border-0 bg-success text-white">
                <h3 class="card-title">Configurar Gateway de Pagamento Automático</h3>
            </div>
            <form action="{{ route('admin.settings.integrations.update') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Escolha o Gateway</label>
                            <select name="pix_gateway" class="form-select">
                                <option value="suitpay" {{ $settings->pix_gateway == 'suitpay' ? 'selected' : '' }}>SuitPay (Recomendado)</option>
                                <option value="mercadopago" {{ $settings->pix_gateway == 'mercadopago' ? 'selected' : '' }}>Mercado Pago</option>
                                <option value="ezze" {{ $settings->pix_gateway == 'ezze' ? 'selected' : '' }}>EzzeBank</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Client ID / API Key</label>
                            <input type="text" name="pix_client_id" class="form-control" value="{{ $settings->pix_client_id }}" placeholder="Insira o Client ID fornecido pelo gateway">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Client Secret / Hash Key</label>
                            <input type="password" name="pix_client_secret" class="form-control" value="{{ $settings->pix_client_secret }}" placeholder="Insira o Client Secret secreto">
                        </div>
                        <div class="col-md-12 mt-3">
                            <div class="alert alert-warning py-2 mb-0">
                                <small><i class="fas fa-info-circle me-1"></i> <strong>Webhook:</strong> Use este link no seu painel da SuitPay para receber notificações: <br>
                                <code>{{ url('/api/webhooks/pix') }}</code></small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light text-end">
                    <button type="submit" class="btn btn-success px-5">
                        <i class="fas fa-save me-2"></i> Salvar Integração
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card card-dark card-outline">
            <div class="card-header border-0 bg-dark text-white py-2">
                <h3 class="card-title"><i class="fas fa-question-circle me-1"></i> Como funciona?</h3>
            </div>
            <div class="card-body">
                <p class="text-muted small">Ao configurar o PIX automático, seus clientes online poderão:</p>
                <ul class="text-muted small ps-3">
                    <li>Gerar QRCodes instantâneos de depósito.</li>
                    <li>O saldo é liberado na conta do cliente em segundos após o pagamento.</li>
                    <li>Você não precisa conferir comprovantes manualmente.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
    <style> .form-select { border-radius: 4px; border: 1px solid #ced4da; padding: .375rem 2.25rem .375rem .75rem; } </style>
@stop
