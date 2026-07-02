@extends('adminlte::page')

@section('title', 'Configurar Cash Out')

@section('content_header')
    <h1><i class="fas fa-hand-holding-usd me-2 text-warning"></i> Configurações de Cash Out</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-7">
        <div class="card card-warning card-outline shadow-sm">
            <div class="card-header border-0 bg-warning">
                <h3 class="card-title fw-bold">Regras de Segurança (Anti-Prejuízo)</h3>
            </div>
            <form action="{{ route('admin.settings.cashout.update') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" name="cashout_enabled" id="cashout_enabled" {{ $settings->cashout_enabled ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold" for="cashout_enabled">Ativar Cash Out no Site</label>
                    </div>

                    <div class="form-group mb-4">
                        <label class="form-label fw-bold">Taxa de Segurança (Margem da Casa)</label>
                        <div class="input-group">
                            <input type="number" name="cashout_tax" class="form-control" value="{{ $settings->cashout_tax }}" step="0.01">
                            <span class="input-group-text">%</span>
                        </div>
                        <small class="text-muted"><i class="fas fa-info-circle me-1"></i> Recomendado: 10% a 15%. Este valor é retido do cálculo proporcional para garantir o lucro da banca.</small>
                    </div>

                    <div class="form-group mb-4">
                        <label class="form-label fw-bold">Delay de Processamento (Segundos)</label>
                        <input type="number" name="cashout_delay_seconds" class="form-control" value="{{ $settings->cashout_delay_seconds }}">
                        <small class="text-muted"><i class="fas fa-shield-alt me-1"></i> Impede que o cliente encerre a aposta no exato momento de um gol ou lance de VAR.</small>
                    </div>
                </div>
                <div class="card-footer bg-light text-end">
                    <button type="submit" class="btn btn-warning px-5 fw-bold">
                        <i class="fas fa-check-circle me-2"></i> Salvar Regras de Cash Out
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card card-dark">
            <div class="card-header border-0">
                <h3 class="card-title"><i class="fas fa-calculator me-2"></i> Como o sistema calcula?</h3>
            </div>
            <div class="card-body">
                <div class="bg-light p-3 rounded mb-3">
                    <code>Valor = [(Odd Inicial / Odd Atual) * Aposta] * (1 - Taxa)</code>
                </div>
                <p class="small text-muted">Exemplo com Taxa de 10%:</p>
                <ul class="small text-muted ps-3">
                    <li>O cliente apostou R$ 100 com Odd 2.00 (Prêmio R$ 200).</li>
                    <li>O time dele está ganhando e a Odd caiu para 1.25.</li>
                    <li>O cálculo justo seria R$ 160. Com a sua taxa de 10%, o sistema oferecerá <strong>R$ 144,00</strong>.</li>
                    <li><strong>Resultado:</strong> O cliente sai feliz com lucro antecipado e a casa garante <strong>R$ 16,00 de lucro imediato</strong> pela conveniência.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@stop
