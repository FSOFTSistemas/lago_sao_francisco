<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Contas a Pagar</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0 0;
            font-size: 10px;
            color: #777;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .total {
            font-weight: bold;
            text-align: right;
            font-size: 14px;
            padding-top: 15px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Relatório de Contas a Pagar</h1>
        <p>
            <strong>Período:</strong> {{ $filtros['periodo'] }} | 
            <strong>Situação:</strong> {{ $filtros['situacao'] }}
        </p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Fornecedor</th>
                <th>Data de Vencimento</th>
                <th>Forma de Pagamento</th>
                <th>Valor</th>
            </tr>
        </thead>
        <tbody>
            @php $valorTotal = 0; @endphp
            @forelse ($contas as $conta)
                <tr>
                    <td>{{ $conta->fornecedor->nome_fantasia ?? 'N/A' }}</td>
                    <td>{{ \Carbon\Carbon::parse($conta->data_vencimento)->format('d/m/Y') }}</td>
                    <td>
                        @php
                            $formas = explode("\n", $conta->forma_pagamento);
                            $formasFormatadas = [];
                            foreach ($formas as $forma) {
                                if (trim($forma) == 'conta_corrente') {
                                    $formasFormatadas[] = 'Conta Corrente';
                                } elseif (trim($forma) == 'caixa') {
                                    $formasFormatadas[] = 'Caixa';
                                }
                            }
                            echo implode(', ', $formasFormatadas);
                        @endphp
                    </td>
                    <td>R$ {{ number_format($conta->valor, 2, ',', '.') }}</td>
                </tr>
                @php $valorTotal += $conta->valor; @endphp
            @empty
                <tr>
                    <td colspan="4" style="text-align: center;">Nenhum dado encontrado para os filtros selecionados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="total">
        Valor Total: R$ {{ number_format($valorTotal, 2, ',', '.') }}
    </div>
</body>
</html>
