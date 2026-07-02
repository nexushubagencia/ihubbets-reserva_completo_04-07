@extends('adminlte::page')

@section('title', 'Painel do Afiliado')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0 text-dark"><i class="fas fa-bullhorn mr-2 text-primary"></i> Área do Afiliado</h1>
        </div>
    </div>
@stop

@section('content')
<div class="row">
    <!-- Link de Divulgação -->
    <div class="col-md-12">
        <div class="card card-primary card-outline shadow-sm">
            <div class="card-body">
                <h5 class="font-weight-bold">Seu Link de Convite Único</h5>
                <p class="text-muted">Divulgue este link para começar a ganhar comissões agora mesmo!</p>
                <div class="input-group input-group-lg">
                    <input type="text" class="form-control bg-light" id="referralLink" value="{{ $stats['referral_link'] }}" readonly>
                    <div class="input-group-append">
                        <button class="btn btn-primary" onclick="copyLink()">
                            <i class="fas fa-copy mr-1"></i> COPIAR LINK
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Métricas -->
    <div class="col-lg-4 col-12">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-success elevation-1"><i class="fas fa-wallet"></i></span>
            <div class="info-box-content">
                <span class="info-box-text text-uppercase small text-muted font-weight-bold">Saldo Disponível</span>
                <span class="info-box-number h3 mb-0">R$ {{ number_format($affiliate->pending_balance, 2, ',', '.') }}</span>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-12">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-info elevation-1"><i class="fas fa-users"></i></span>
            <div class="info-box-content">
                <span class="info-box-text text-uppercase small text-muted font-weight-bold">Total de Indicados</span>
                <span class="info-box-number h3 mb-0">{{ $stats['total_referrals'] }}</span>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-12">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-bolt"></i></span>
            <div class="info-box-content">
                <span class="info-box-text text-uppercase small text-muted font-weight-bold">Jogadores Ativos</span>
                <span class="info-box-number h3 mb-0">{{ $stats['active_players'] }}</span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Tabela de Indicados -->
    <div class="col-md-8">
        <div class="card card-dark shadow-sm h-100">
            <div class="card-header border-0">
                <h3 class="card-title text-white font-weight-bold"><i class="fas fa-user-plus mr-2"></i> Indicações Recentes</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-valign-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Jogador</th>
                            <th>Data Cadastro</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentReferrals as $referral)
                        <tr>
                            <td>{{ $referral->name }} <span class="badge badge-light border ml-1">ID {{ $referral->id }}</span></td>
                            <td>{{ $referral->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <span class="badge badge-success">Ativo</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-5 text-muted">Ainda não há indicações. Divulgue seu link!</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Solicitar Saque -->
    <div class="col-md-4">
        <div class="card bg-gradient-success shadow-sm">
            <div class="card-body text-center py-4">
                <h2 class="font-weight-bold mb-3"><i class="fas fa-coins mr-2"></i> Receber Comissões</h2>
                <p>Solicite o resgate das suas comissões acumuladas diretamente para o seu PIX.</p>
                <button class="btn btn-light btn-lg btn-block font-weight-bold text-success mt-4">
                    <i class="fas fa-paper-plane mr-2"></i> SOLICITAR SAQUE
                </button>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
    function copyLink() {
        var copyText = document.getElementById("referralLink");
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        document.execCommand("copy");
        
        Toast.fire({
            icon: 'success',
            title: 'Link copiado para a área de transferência!'
        });
    }
</script>
@stop
