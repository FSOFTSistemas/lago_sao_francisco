<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Previsão de Movimentação</title>
    <style>
        body { font-family: sans-serif; font-size: 9px; color: #333; }
        .header { text-align: center; margin-bottom: 14px; border-bottom: 2px solid #ddd; padding-bottom: 8px; }
        .header h1 { margin: 0; font-size: 16px; }
        .header p { margin: 4px 0 0; color: #666; }

        table.main-table { width: 100%; border-collapse: collapse; }
        table.main-table th, table.main-table td { border: 1px solid #ddd; padding: 4px 3px; text-align: center; }
        table.main-table td:first-child, table.main-table th:first-child { text-align: left; }
        table.main-table thead tr:first-child th { background-color: #eef3ea; color: #3e7222; font-size: 9px; }
        table.main-table thead tr:last-child th { background-color: #f2f2f2; font-size: 8px; }
        table.main-table tfoot td { background-color: #eee; font-weight: bold; }

        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 8px; color: #999; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Previsão de Movimentação</h1>
        <p>Período: {{ $dataInicio }} a {{ $dataFim }}</p>
    </div>

    <table class="main-table">
        <thead>
            <tr>
                <th rowspan="2">Data</th>
                <th colspan="2">Saídas</th>
                <th colspan="2">Entradas</th>
                <th colspan="2">Ocupação</th>
                <th colspan="3">Crianças</th>
                <th colspan="2">Café</th>
                <th colspan="2">Reservas Bloqueio</th>
                <th rowspan="2">Disp.</th>
                <th colspan="2">% Ocupação</th>
            </tr>
            <tr>
                <th>Apto</th><th>Pax</th>
                <th>Apto</th><th>Pax</th>
                <th>Apto</th><th>Pax</th>
                <th>Pag</th><th>Free</th><th>Total</th>
                <th>Adl</th><th>CHD</th>
                <th>Apto</th><th>Pax</th>
                <th>Apto</th><th>Pax</th>
            </tr>
        </thead>
        <tbody>
            @forelse($linhas as $linha)
                <tr>
                    <td>{{ ucfirst($linha['data']->translatedFormat('D')) }} {{ $linha['data']->format('d/m/Y') }}</td>
                    <td>{{ $linha['saidas_apto'] }}</td>
                    <td>{{ $linha['saidas_pax'] }}</td>
                    <td>{{ $linha['entradas_apto'] }}</td>
                    <td>{{ $linha['entradas_pax'] }}</td>
                    <td>{{ $linha['ocupacao_apto'] }}</td>
                    <td>{{ $linha['ocupacao_pax'] }}</td>
                    <td>{{ $linha['criancas_pag'] }}</td>
                    <td>{{ $linha['criancas_free'] }}</td>
                    <td>{{ $linha['criancas_total'] }}</td>
                    <td>{{ $linha['cafe_adl'] }}</td>
                    <td>{{ $linha['cafe_chd'] }}</td>
                    <td>{{ $linha['bloqueio_apto'] }}</td>
                    <td>{{ $linha['bloqueio_pax'] }}</td>
                    <td>{{ $linha['disp_apto'] }}</td>
                    <td>{{ $linha['pct_ocupacao_apto'] }}%</td>
                    <td>{{ $linha['pct_ocupacao_pax'] }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="17">Nenhum dado para este período.</td>
                </tr>
            @endforelse
        </tbody>
        @if(count($linhas) > 0)
            <tfoot>
                <tr>
                    <td>Total geral</td>
                    <td>{{ $totais['saidas_apto'] }}</td>
                    <td>{{ $totais['saidas_pax'] }}</td>
                    <td>{{ $totais['entradas_apto'] }}</td>
                    <td>{{ $totais['entradas_pax'] }}</td>
                    <td>{{ $totais['ocupacao_apto'] }}</td>
                    <td>{{ $totais['ocupacao_pax'] }}</td>
                    <td>{{ $totais['criancas_pag'] }}</td>
                    <td>{{ $totais['criancas_free'] }}</td>
                    <td>{{ $totais['criancas_total'] }}</td>
                    <td>{{ $totais['cafe_adl'] }}</td>
                    <td>{{ $totais['cafe_chd'] }}</td>
                    <td>{{ $totais['bloqueio_apto'] }}</td>
                    <td>{{ $totais['bloqueio_pax'] }}</td>
                    <td>{{ $totais['disp_apto'] }}</td>
                    <td>{{ $totais['pct_ocupacao_apto'] }}%</td>
                    <td>{{ $totais['pct_ocupacao_pax'] }}%</td>
                </tr>
            </tfoot>
        @endif
    </table>

    <div class="footer">
        Gerado em {{ date('d/m/Y H:i') }}
    </div>
</body>
</html>
