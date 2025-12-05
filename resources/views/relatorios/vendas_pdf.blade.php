<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relatório de Vendas</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #444; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 18px; text-transform: uppercase; }
        .header p { margin: 5px 0 0; color: #666; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: right; }
        th { background-color: #f2f2f2; font-weight: bold; text-align: center; }
        
        /* Alinhamento específico das colunas */
        .col-nome { text-align: left; width: 40%; }
        
        /* Totais */
        .total-row td { background-color: #e9ecef; font-weight: bold; border-top: 2px solid #444; }
        .grand-total { background-color: #333; color: #3f7322; }

        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; color: #999; border-top: 1px solid #ddd; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Relatório de Vendas por Vendedor</h1>
        <p>Período de apuração: {{ \Carbon\Carbon::parse($dataInicio)->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($dataFim)->format('d/m/Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="col-nome">Vendedor</th>
                <th>Total Reservas</th>
                <th>Total Day Use</th>
                <th>Total Geral</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $somaReservas = 0;
                $somaDayUse = 0;
                $somaGeral = 0;
            @endphp

            @foreach($relatorio as $venda)
                @php 
                    $somaReservas += $venda['total_reserva'];
                    $somaDayUse += $venda['total_dayuse'];
                    $somaGeral += $venda['total_geral'];
                @endphp
                <tr>
                    <td class="col-nome">{{ $venda['nome'] }}</td>
                    <td>R$ {{ number_format($venda['total_reserva'], 2, ',', '.') }}</td>
                    <td>R$ {{ number_format($venda['total_dayuse'], 2, ',', '.') }}</td>
                    <td style="font-weight: bold;">R$ {{ number_format($venda['total_geral'], 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td class="col-nome">TOTAIS</td>
                <td>R$ {{ number_format($somaReservas, 2, ',', '.') }}</td>
                <td>R$ {{ number_format($somaDayUse, 2, ',', '.') }}</td>
                <td class="grand-total">R$ {{ number_format($somaGeral, 2, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Relatório gerado em {{ date('d/m/Y H:i:s') }}
    </div>
</body>
</html>