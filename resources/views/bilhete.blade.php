<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Bilhete Online</title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <meta name="csrf-token" content="{{ csrf_token()}}">
  <link rel="stylesheet" href="{{ asset('bower_components/bootstrap/dist/css/bootstrap.min.css')}}">
  <link rel="stylesheet" href="{{ asset('bower_components/font-awesome/css/font-awesome.min.css')}}">
  <link rel="stylesheet" href="{{ asset('bower_components/Ionicons/css/ionicons.min.css')}}">
  <link rel="stylesheet" href="{{ asset('dist/css/skins/_all-skins.min.css')}}">
  <link rel="stylesheet" href="{{ asset('bower_components/morris.js/morris.css')}}">
  <link rel="stylesheet" href="{{ asset('bower_components/jvectormap/jquery-jvectormap.css')}}">
  <link rel="stylesheet" href="{{ asset('bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css')}}">
  <link rel="stylesheet" href="{{ asset('bower_components/bootstrap-daterangepicker/daterangepicker.css')}}">
  <link rel="stylesheet" href="{{ asset('plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css')}}">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
  <style>
    @media print {
      .no-print { display: none !important; }
      body { margin: 0; padding: 15px; }
    }
    .bilhete-container { max-width: 400px; margin: 0 auto; padding: 20px; font-family: 'Courier New', monospace; }
    .bilhete-header { text-align: center; margin-bottom: 20px; }
    .bilhete-header img { max-width: 180px; max-height: 140px; }
    .bilhete-cupom { font-size: 18px; font-weight: bold; text-align: center; margin: 15px 0; letter-spacing: 2px; }
    .bilhete-info { font-size: 11px; margin-bottom: 10px; }
    .bilhete-info table { width: 100%; }
    .bilhete-info td { padding: 2px 5px; }
    .bilhete-palpites { margin: 10px 0; }
    .bilhete-palpites table { width: 100%; font-size: 11px; }
    .bilhete-palpites th { text-align: left; padding: 3px 5px; font-size: 10px; text-transform: uppercase; }
    .bilhete-palpites td { padding: 3px 5px; }
    .bilhete-total { font-weight: bold; font-size: 13px; text-align: right; margin-top: 10px; }
    .bilhete-status { text-align: center; font-size: 14px; font-weight: bold; margin: 15px 0; padding: 5px; }
    .bilhete-footer { text-align: center; font-size: 10px; margin-top: 20px; color: #666; }
  </style>
</head>
<body>
  <div class="bilhete-container">
    <div class="bilhete-header">
      <img src="{{ asset('img/logo.png') }}" alt="Logo" onerror="this.style.display='none'">
      <h4>{{ config('app.name', 'IHUB BETS') }}</h4>
    </div>

    <div class="bilhete-cupom">{{ $bilhete->codigo_bilhete ?? $bilhete->cupom }}</div>

    <div class="bilhete-info">
      <table>
        <tr><td><strong>Cliente:</strong></td><td>{{ $bilhete->cliente ?? $bilhete->cliente_nome ?? '-' }}</td></tr>
        <tr><td><strong>Vendedor:</strong></td><td>{{ $bilhete->vendedor ?? 'Sistema' }}</td></tr>
        <tr><td><strong>Data:</strong></td><td>{{ \Carbon\Carbon::parse($bilhete->created_at)->format('d/m/Y H:i') }}</td></tr>
        <tr><td><strong>Tipo:</strong></td><td>{{ $bilhete->tipo ?? ($bilhete->total_palpites > 1 ? 'Múltipla' : 'Simples') }}</td></tr>
        <tr><td><strong>Modalidade:</strong></td><td>{{ $bilhete->modalidade ?? 'Esporte' }}</td></tr>
        <tr><td><strong>Cotacão:</strong></td><td>{{ number_format($bilhete->cotacao ?? 1, 2) }}</td></tr>
      </table>
    </div>

    <div class="bilhete-palpites">
      <table>
        <thead>
          <tr>
            <th>Partida</th>
            <th>Mercado</th>
            <th>Palpite</th>
            <th>Cot.</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          @forelse($bilhete->palpites as $palpite)
          <tr>
            <td>{{ $palpite->home_team ?? '' }} x {{ $palpite->away_team ?? '' }}</td>
            <td>{{ $palpite->market_name ?? '-' }}</td>
            <td>{{ $palpite->selection_label ?? $palpite->palpite ?? '-' }}</td>
            <td>{{ number_format($palpite->selection_odd ?? 1, 2) }}</td>
            <td>{{ $palpite->status ?? 'Aberto' }}</td>
          </tr>
          @empty
          <tr><td colspan="5">Nenhum palpite encontrado</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="bilhete-total">
      <p>Valor: R$ {{ number_format($bilhete->valor_apostado, 2) }}</p>
      <p>Retorno: R$ {{ number_format($bilhete->retorno_possivel, 2) }}</p>
    </div>

    <div class="bilhete-status" style="color: {{ $bilhete->status == 'Ganhou' ? 'green' : ($bilhete->status == 'Perdeu' || $bilhete->status == 'Cancelado' ? 'red' : '#333') }}">
      Status: {{ $bilhete->status }}
    </div>

    <div class="bilhete-footer no-print">
      <button onclick="window.print()" class="btn btn-primary btn-sm">
        <i class="fa fa-print"></i> Imprimir
      </button>
      <button onclick="window.close()" class="btn btn-default btn-sm">
        <i class="fa fa-times"></i> Fechar
      </button>
    </div>

    <div class="bilhete-footer">
      {{ config('app.name', 'IHUB BETS') }} - Apostas Esportivas<br>
      Este bilhete é um comprovante de aposta.
    </div>
  </div>

  <script src="{{ asset('bower_components/jquery/dist/jquery.min.js')}}"></script>
  <script src="{{ asset('bower_components/bootstrap/dist/js/bootstrap.min.js')}}"></script>
</body>
</html>
