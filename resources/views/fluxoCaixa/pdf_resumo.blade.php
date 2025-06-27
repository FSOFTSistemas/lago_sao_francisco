<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Resumo do Fluxo de Caixa</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .total {
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>{{ $empresa->nome_fantasia ?? 'Empresa' }}</h2>
        <p><strong>Data de Emissão:</strong> {{ $dataEmissao }}</p>
        <p><strong>Período:</strong> {{ $periodo }}</p>
        @if ($caixaSelecionado)
            <p><strong>Caixa:</strong> {{ $caixaSelecionado->descricao }}</p>
        @endif
        <h3>Resumo de Caixa por Movimento</h3>
    </div>

    <table>
        <thead>
            <tr>
                <th>Movimento</th>
                <th>Valor Total (R$)</th>
            </tr>
        </thead>
        <tbody>
            @php $totalGeral = 0; @endphp
            @foreach ($resumo as $movimento => $valor)
                <tr>
                    <td>{{ $movimento }}</td>
                    <td>R$ {{ number_format($valor, 2, ',', '.') }}</td>
                </tr>
                @php $totalGeral += $valor; @endphp
            @endforeach
            <tr class="total">
                <td>Total Geral</td>
                <td>R$ {{ number_format($totalGeral, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
</body>

</html>
