@extends('layouts.app')

@section('content')
<style>
    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
        font-family: 'Source Sans Pro', sans-serif;
    }
    body {
        background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('/img/login_bg.png') no-repeat center center fixed;
        background-size: cover;
    }
    main {
        padding: 0 !important;
    }
    .reset-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .reset-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        padding: 40px;
        width: 100%;
        max-width: 450px;
        text-align: center;
        transition: all 0.3s ease;
    }
    @media (max-width: 576px) {
        .reset-card {
            padding: 30px 20px;
            border-radius: 12px;
        }
        .reset-logo {
            width: 130px;
            margin-bottom: 20px;
        }
        .reset-title {
            font-size: 20px;
        }
    }
    @media (min-width: 1200px) {
        .reset-card {
            max-width: 500px;
            padding: 50px;
        }
    }
    .reset-logo {
        width: 160px;
        margin-bottom: 30px;
        filter: drop-shadow(0 4px 8px rgba(0,0,0,0.1));
    }
    .reset-title {
        font-size: 22px;
        font-weight: 700;
        color: #333;
        margin-bottom: 10px;
    }
    .reset-subtitle {
        font-size: 14px;
        color: #777;
        margin-bottom: 30px;
    }
    .form-control {
        height: 50px;
        border-radius: 8px;
        border: 1px solid #ddd;
        padding: 10px 15px;
        font-size: 15px;
        transition: all 0.3s;
    }
    .form-control:focus {
        border-color: #3ca569;
        box-shadow: 0 0 0 3px rgba(60, 165, 105, 0.1);
    }
    .btn-reset {
        background-color: #3ca569;
        border: none;
        color: #fff;
        font-weight: 700;
        height: 50px;
        border-radius: 8px;
        font-size: 16px;
        width: 100%;
        margin-top: 10px;
        transition: all 0.3s;
    }
    .btn-reset:hover {
        background-color: #2d8d57;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(60, 165, 105, 0.3);
    }
    .back-link {
        display: inline-block;
        margin-top: 25px;
        color: #777;
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
    }
    .back-link:hover {
        color: #3ca569;
    }
    .alert-success {
        border-radius: 8px;
        font-size: 14px;
        margin-bottom: 25px;
    }
</style>

<div class="reset-container">
    <div class="reset-card">
        @php
            $site = \App\Models\Site::first();
            $logoPath = $site->logo_path ?? '';
            $logoUrl = null;
            if ($logoPath) {
                $logoUrl = str_starts_with($logoPath, 'http') ? $logoPath : asset($logoPath);
            }
        @endphp
        
        @if($logoUrl)
            <img src="{{ $logoUrl }}" class="reset-logo" alt="Logo">
        @endif

        <h1 class="reset-title">Recuperar Senha</h1>
        <p class="reset-subtitle">Insira seu e-mail para receber o link de redefinição.</p>

        @if (session('status'))
            <div class="alert alert-success" role="alert">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="mb-4 text-start">
                <label for="email" class="form-label" style="font-weight: 600; font-size: 13px; color: #555; text-transform: uppercase; letter-spacing: 0.5px;">E-mail Cadastrado</label>
                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="exemplo@email.com">

                @error('email')
                    <span class="invalid-feedback" role="alert" style="font-size: 12px;">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <button type="submit" class="btn btn-reset">
                Enviar Link de Recuperação
            </button>
        </form>

        <a href="{{ url('/') }}" class="back-link">
            <i class="fa fa-arrow-left"></i> Voltar para o Site
        </a>
    </div>
</div>
@endsection
