@component('mail::message')
# Alerta de Aposta #{{ $bet->id }}

Uma nova aposta foi realizada no sistema.

**Código:** {{ $bet->codigo_bilhete }}
**Valor:** R$ {{ number_format($bet->valor_total, 2, ',', '.') }}
**Cotação:** {{ $bet->cotacao_total }}
**Prêmio Potencial:** R$ {{ number_format($bet->premio_total, 2, ',', '.') }}
**Status:** {{ $bet->status }}
**Data:** {{ $bet->created_at }}

@component('mail::button', ['url' => config('app.url').'/admin/bilhetes'])
Ver Bilhete
@endcomponent

Obrigado,
{{ config('app.name') }}
@endcomponent
