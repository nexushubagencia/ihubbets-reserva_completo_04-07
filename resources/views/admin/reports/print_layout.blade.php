<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Prestação de Contas - IHUB V2</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; line-height: 1.6; margin: 40px; }
        .header { text-align: center; border-bottom: 2px solid #28a745; padding-bottom: 10px; margin-bottom: 30px; }
        .header h1 { margin: 0; color: #1a5c28; text-transform: uppercase; font-size: 24px; }
        .info-section { margin-bottom: 30px; display: flex; justify-content: space-between; background: #f8f9fa; padding: 15px; border-radius: 8px; }
        .info-box { flex: 1; }
        .info-box span { font-weight: bold; color: #555; display: block; font-size: 12px; }
        .info-box strong { font-size: 16px; color: #222; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th { background: #28a745; color: white; text-align: left; padding: 12px; font-size: 14px; }
        td { padding: 12px; border-bottom: 1px solid #dee2e6; font-size: 14px; }
        .total-row { background: #f1f3f5; font-weight: bold; }
        .footer { text-align: center; margin-top: 50px; font-size: 12px; color: #777; border-top: 1px solid #eee; padding-top: 10px; }
        .money { text-align: right; font-family: 'Courier New', Courier, monospace; }
        @media print {
            .no-print { display: none; }
            body { margin: 0; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer;">
            Salvar como PDF / Imprimir
        </button>
    </div>

    <div class="header">
        <h1>IHUB BETS - Relatório de Acerto</h1>
    </div>

    <div class="info-section">
        <div class="info-box">
            <span>COLABORADOR</span>
            <strong>{{ $data['name'] }}</strong>
        </div>
        <div class="info-box" style="text-align: right;">
            <span>PERÍODO</span>
            <strong>{{ $data['period'] }}</strong>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Descrição Item</th>
                <th style="text-align: right;">Valores (R$)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Total de Entradas (Apostas Realizadas)</td>
                <td class="money">+ {{ number_format($data['entradas'], 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Total de Saídas (Prêmios Concedidos)</td>
                <td class="money">- {{ number_format($data['saidas'], 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Comissão {{ $data['role'] == 'manager' ? 'Gerente' : 'Cambista' }}</td>
                <td class="money">- {{ number_format($data['comissao_gerente'] ?? $data['comissao_cambista'] ?? 0, 2, ',', '.') }}</td>
            </tr>
            @if(isset($data['comissao_cambistas_total']))
            <tr>
                <td>Total Pago aos Cambistas (Equipe)</td>
                <td class="money">- {{ number_format($data['comissao_cambistas_total'], 2, ',', '.') }}</td>
            </tr>
            @endif
            <tr class="total-row">
                <td style="font-size: 18px;">SALDO FINAL A PRESTAR</td>
                <td class="money" style="font-size: 18px; color: {{ $data['saldo'] >= 0 ? '#1a5c28' : '#c82333' }};">
                    R$ {{ number_format($data['saldo'], 2, ',', '.') }}
                </td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top: 40px; border: 1px dashed #ccc; padding: 20px; text-align: center;">
        <p style="font-size: 14px; color: #666;">Assinatura do Responsável</p>
        <div style="margin-top: 40px; border-top: 1px solid #333; width: 300px; margin-left: auto; margin-right: auto;"></div>
    </div>

    <div class="footer">
        Relatório gerado automaticamente pelo IHUB V2 em {{ date('d/m/Y H:i') }}
    </div>
</body>
</html>
