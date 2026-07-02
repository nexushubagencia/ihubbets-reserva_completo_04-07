@component('mail::message')
# Alerta de Aposta {{ $bilhete->codigo_bilhete ?? $bilhete->id }}

Clique no botão e tenha acesso à aposta.

@component('mail::button', ['url' => config('app.url') . '/api/bilhete/' . $bilhete->id])
Acessar o Site
@endcomponent

Obrigado,
{{ config('app.name') }}
@endcomponent
