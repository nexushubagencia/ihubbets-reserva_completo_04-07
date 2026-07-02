<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - IHUB BETS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background: #0b1120; color: #fff; min-height: 100vh; padding: 40px 0; }
        .reg-container { max-width: 800px; margin: auto; background: #111827; border: 1px solid rgba(16, 185, 129, 0.2); border-radius: 20px; padding: 40px; box-shadow: 0 20px 50px rgba(0,0,0,0.5); }
        .logo { font-size: 1.8rem; font-weight: 800; text-align: left; margin-bottom: 30px; color: #10b981; border-bottom: 2px solid #10b981; padding-bottom: 10px; }
        .logo span { color: #fff; text-transform: uppercase; margin-left: 10px; font-size: 1.2rem; }
        .form-label { color: #9ca3af; font-weight: 600; font-size: 0.85rem; text-transform: uppercase; margin-bottom: 8px; }
        .input-group-text { background: #1f2937; border: 1px solid #374151; color: #9ca3af; border-radius: 12px 0 0 12px; }
        .form-control, .form-select { background: #1f2937; border: 1px solid #374151; color: #fff; padding: 12px; border-radius: 12px; }
        .input-group .form-control { border-radius: 0 12px 12px 0; }
        .form-control:focus, .form-select:focus { background: #1f2937; border-color: #10b981; color: #fff; box-shadow: none; }
        .btn-register { background: #10b981; color: #fff; font-weight: 700; padding: 15px; border-radius: 12px; border: none; width: 100%; transition: 0.3s; margin-top: 20px; }
        .btn-register:hover { background: #059669; transform: translateY(-3px); }
        .form-check-label { color: #9ca3af; font-size: 0.9rem; margin-left: 10px; }
        .form-check-input { background-color: #1f2937; border-color: #374151; }
        .form-check-input:checked { background-color: #10b981; border-color: #10b981; }
        .bonus-box { background: rgba(16, 185, 129, 0.1); border-left: 4px solid #10b981; padding: 15px; border-radius: 8px; margin-bottom: 30px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="reg-container">
            <div class="logo">
                <i class="fas fa-shield-halved"></i> <span>Cadastro de Usuário</span>
            </div>

            <div class="bonus-box">
                <h6 class="mb-1 text-white fw-bold"><i class="fas fa-gift me-2 text-warning"></i> Promoção de Boas-Vindas!</h6>
                <p class="mb-0 small text-muted">Complete seu cadastro e ganhe 100% de bônus no seu primeiro depósito para começar a ganhar!</p>
            </div>

            <form action="{{ route('register.client.submit') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label class="form-label">Nome Completo *</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" name="name" class="form-control" placeholder="Seu nome completo" required>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <label class="form-label">Nome de usuário *</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-at"></i></span>
                            <input type="text" name="username" class="form-control" placeholder="Ex: apostador_pro" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label class="form-label">Criar Senha *</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-key"></i></span>
                            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <label class="form-label">Confirmar Senha *</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" name="password_confirmation" class="form-control" placeholder="••••••••" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label class="form-label">CPF * (Seu CPF será sua chave Pix)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                            <input type="text" name="cpf" class="form-control" placeholder="000.000.000-00" required>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <label class="form-label">Celular / Contato *</label>
                        <div class="input-group">
                            <select class="input-group-text form-select" style="max-width: 90px; border-radius: 12px 0 0 12px !important;">
                                <option value="+55">+55</option>
                            </select>
                            <input type="text" name="phone" class="form-control" placeholder="(00) 00000-0000" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label class="form-label">Data de Nascimento *</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                            <input type="date" name="birth_date" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <label class="form-label">Email *</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" name="email" class="form-control" placeholder="seu@email.com" required>
                        </div>
                    </div>
                </div>

                <div class="form-check mb-4 mt-2">
                    <input class="form-check-input" type="checkbox" value="1" id="termsCheck" required>
                    <label class="form-check-label" for="termsCheck">
                        Certifico que tenho mais de 18 anos de idade e declaro que li e concordo com os <a href="#">termos de uso do site</a>
                    </label>
                </div>

                <button type="submit" class="btn btn-register shadow-lg">
                    REGISTRAR-SE <i class="fas fa-check-circle ms-2"></i>
                </button>
            </form>

            <div class="text-center mt-4">
                <span class="text-muted">Já tem uma conta?</span> <a href="{{ route('login') }}" class="fw-bold ms-2">Entrar agora</a>
            </div>
        </div>
    </div>
</body>
</html>
