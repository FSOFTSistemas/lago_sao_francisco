<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relatório de Day Use</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #28a745; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 16px; text-transform: uppercase; color: #28a745; }
        .header p { margin: 5px 0 0; color: #666; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #28a745; color: #fff; font-weight: bold; text-align: center; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .row-vendedor { background-color: #ddd; font-weight: bold; }
        .row-subtotal { background-color: #f9f9f9; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Relatório Detalhado - Day Use</h1>
        <p>Período: {{ \Carbon\Carbon::parse($dataInicio)->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($dataFim)->format('d/m/Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Data Venda</th>
                <th>Cliente</th>
                <th>Data Uso</th>
                <th>Valor</th>
            </tr>
        </thead>
        <tbody>
            @php $totalGeral = 0; @endphp
            @foreach($vendasDayUseAgrupadas as $nomeVendedor => $dayUses)
                <tr class="row-vendedor"><td colspan="4">{{ $nomeVendedor }}</td></tr>
                @php $sub = 0; @endphp
                @foreach($dayUses as $du)
                    @php 
                        $valor = $du->total ?? $du->valor_total ?? $du->valor ?? 0;
                        $sub += $valor; $totalGeral += $valor;
                        $cliente = $du->hospede->nome ?? $du->cliente_nome ?? $du->nome_cliente ?? 'N/D';
                    @endphp
                    <tr>
                        <td class="text-center">{{ \Carbon\Carbon::parse($du->created_at)->format('d/m/Y H:i') }}</td>
                        <td>{{ $cliente }}</td>
                        <td class="text-center">{{ isset($du->data) ? \Carbon\Carbon::parse($du->data)->format('d/m/Y') : '-' }}</td>
                        <td class="text-right">R$ {{ number_format($valor, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
                <tr class="row-subtotal"><td colspan="3" class="text-right">Total {{ $nomeVendedor }}:</td><td class="text-right">R$ {{ number_format($sub, 2, ',', '.') }}</td></tr>
            @endforeach
        </tbody>
        @if($vendedorId == 'todos')
        <tfoot>
            <tr style="background-color: #333; color: #fff;"><td colspan="3" class="text-right">TOTAL GERAL:</td><td class="text-right">R$ {{ number_format($totalGeral, 2, ',', '.') }}</td></tr>
        </tfoot>
        @endif
    </table>
</body>
</html>