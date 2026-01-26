<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relatório Detalhado de Vendas</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #444; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 16px; text-transform: uppercase; }
        .header p { margin: 5px 0 0; color: #666; font-size: 12px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; text-align: center; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        
        .row-vendedor { background-color: #ddd; font-weight: bold; }
        .row-subtotal { background-color: #f9f9f9; font-weight: bold; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #999; border-top: 1px solid #ddd; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Relatório Detalhado de Vendas</h1>
        <p>
            Período: {{ \Carbon\Carbon::parse($dataInicio)->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($dataFim)->format('d/m/Y') }}
            <br>
            Filtro: {{ $vendedorId == 'todos' ? 'Todos os Vendedores' : 'Vendedor Individual' }}
        </p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 15%">Data</th>
                <th style="width: 15%">Quarto</th>
                <th style="width: 30%">Hóspede</th>
                <th style="width: 12%">Check-in</th>
                <th style="width: 12%">Check-out</th>
                <th style="width: 16%">Total</th>
            </tr>
        </thead>
        <tbody>
            @php $totalGeral = 0; @endphp

            @foreach($vendasAgrupadas as $nomeVendedor => $reservas)
                <tr class="row-vendedor">
                    <td colspan="6">{{ $nomeVendedor }}</td>
                </tr>

                @php $subtotal = 0; @endphp

                @foreach($reservas as $reserva)
                    @php 
                        $subtotal += $reserva->valor_total;
                        $totalGeral += $reserva->valor_total;
                    @endphp
                    <tr>
                        <td class="text-center">{{ \Carbon\Carbon::parse($reserva->created_at)->format('d/m/Y H:i') }}</td>
                        <td>{{ $reserva->quarto->nome ?? '-' }}</td>
                        <td>{{ substr($reserva->hospede->nome ?? '-', 0, 30) }}</td>
                        <td class="text-center">{{ \Carbon\Carbon::parse($reserva->data_checkin)->format('d/m/Y') }}</td>
                        <td class="text-center">{{ \Carbon\Carbon::parse($reserva->data_checkout)->format('d/m/Y') }}</td>
                        <td class="text-right">R$ {{ number_format($reserva->valor_total, 2, ',', '.') }}</td>
                    </tr>
                @endforeach

                <tr class="row-subtotal">
                    <td colspan="5" class="text-right">Total {{ $nomeVendedor }}:</td>
                    <td class="text-right">R$ {{ number_format($subtotal, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        @if($vendedorId == 'todos')
        <tfoot>
            <tr style="background-color: #333; color: #fff; font-weight: bold;">
                <td colspan="5" class="text-right">TOTAL GERAL DO PERÍODO:</td>
                <td class="text-right">R$ {{ number_format($totalGeral, 2, ',', '.') }}</td>
            </tr>
        </tfoot>
        @endif
    </table>

    <div class="footer">
        Gerado em {{ date('d/m/Y H:i:s') }}
    </div>
</body>
</html>