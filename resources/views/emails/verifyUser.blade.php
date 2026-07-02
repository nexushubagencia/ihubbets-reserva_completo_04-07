@component('mail::message')
# Olá,

Você foi cadastrado com sucesso em nossa plataforma!
Por favor, clique no link abaixo para confirmar seu endereço de e-mail.

<br>
<p><strong>Usuário:</strong> {{ $user->username ?? $user->email }}</p>
<p><strong>Senha:</strong> Utilize a senha informada no momento do cadastro.</p>

@component('mail::button', ['actionText' => $actionText, 'url' => $actionUrl, 'color' => 'red'])
{{ $actionText }}
@endcomponent

@if (! empty($salutation))
{{ $salutation }}
@else
Atenciosamente,<br>{{ config('app.name') }}
@endif

@isset($actionText)
@component('mail::subcopy')
Se você estiver com problemas para clicar no botão "{{ $actionText }}, copie e cole a URL abaixo em seu navegador: {{ $actionUrl }}
@endcomponent
@endisset
@endcomponent
