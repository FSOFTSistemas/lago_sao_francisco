<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Hóspedes por Período</title>
    <style>
        @page {
            margin: 30px;
        }

        body {
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 14px;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .page-border {
            border: 1px solid #28a745;
            border-radius: 5px;
            padding: 30px;
            min-height: 920px;
            position: relative;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
        }

        .header h1 {
            font-size: 22px;
            margin: 0;
            color: #28a745;
            text-transform: uppercase;
        }

        .header p {
            font-size: 13px;
            color: #666;
            margin-top: 5px;
        }

        /* Tabela de métricas */
        .metrics-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 15px;
            margin-bottom: 20px;
        }

        .card {
            background-color: #fff;
            border-bottom: 3px solid #eee;
            padding: 20px;
            text-align: center;
            width: 50%;
        }

        .card-label {
            font-size: 12px;
            text-transform: uppercase;
            color: #888;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .card-value {
            font-size: 32px;
            font-weight: bold;
            color: #333;
        }

        /* Seção de Totais Refinada */
        .summary-section {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            text-align: right;
            padding-right: 20px;
        }

        .total-hospedes {
            font-size: 20px;
            color: #28a745;
            font-weight: bold;
        }

        .total-pets {
            font-size: 15px;
            color: #666;
            margin-top: 4px;
        }

        .footer {
            position: absolute;
            bottom: 20px;
            width: calc(100% - 60px);
            text-align: center;
            font-size: 10px;
            color: #aaa;
            border-top: 1px solid #f9f9f9;
            padding-top: 10px;
        }
    </style>
</head>

<body>
    <div class="page-border">

        <div class="header">
            <h1>Relatório de Estadia</h1>
            <p>Período: <strong>{{ $data_inicio }}</strong> a <strong>{{ $data_fim }}</strong></p>
        </div>

        <table class="metrics-table">
            <tr>
                <td class="card" style="border-color: #28a745;">
                    <div class="card-label">Adultos</div>
                    <div class="card-value">{{ $hospedes->adultos }}</div>
                </td>
                <td class="card" style="border-color: #17a2b8;">
                    <div class="card-label">Crianças (Pagantes)</div>
                    <div class="card-value">{{ $hospedes->criancas }}</div>
                </td>
            </tr>
            <tr>
                <td class="card" style="border-color: #ffc107;">
                    <div class="card-label">Crianças (Não Pagantes)</div>
                    <div class="card-value">{{ $hospedes->criancas_np }}</div>
                </td>
                <td class="card" style="border-color: #6f42c1;">
                    <div class="card-label">Pets Registrados</div>
                    <div class="card-value">{{ $hospedes->pets }}</div>
                </td>
            </tr>
        </table>

        <div class="summary-section">
            <div class="total-hospedes">
                Total Hóspedes: {{ $hospedes->total_geral }}
            </div>

            @if($hospedes->pets > 0)
            <div class="total-pets">
                Total Pets: {{ $hospedes->pets }}
            </div>
            @endif
        </div>

        <div class="footer">
            Gerado automaticamente pelo sistema em {{ date('d/m/Y H:i') }}
        </div>

    </div>
</body>

</html>