<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>{{ strtoupper($tipo) }}</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 10px;
            margin: 20px;
            color: #333;
        }
        .top-header {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }
        .top-header .title {
            display: table-cell;
            font-size: 20px;
            font-weight: bold;
            color: #1b2a4a;
            vertical-align: middle;
        }
        .top-header .numero {
            display: table-cell;
            text-align: right;
            font-size: 14px;
            font-weight: bold;
            vertical-align: middle;
        }
        .hotel-header {
            display: table;
            width: 100%;
            background-color: #f0f5fa;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #d0ddee;
            font-size: 10px;
            line-height: 1.5;
            color: #334;
            box-sizing: border-box;
        }
        .hotel-header .col {
            display: table-cell;
            vertical-align: top;
            width: 50%;
        }
        .hotel-header strong {
            font-size: 14px;
            font-weight: bold;
            color: #111;
            display: block;
            margin-bottom: 3px;
        }
        .hotel-header .emissao {
            text-align: right;
            font-weight: bold;
        }
        .section-title {
            font-size: 11px;
            font-weight: bold;
            color: #1b2a4a;
            padding: 4px 0;
            margin-top: 12px;
            margin-bottom: 6px;
            border-bottom: 1px solid #ccc;
        }
        table.info {
            width: 100%;
            border-collapse: collapse;
        }
        table.info td {
            padding: 3px 6px 3px 0;
            vertical-align: top;
            width: 25%;
        }
        .label {
            font-weight: bold;
            font-size: 9px;
            color: #555;
            display: block;
        }
        .value {
            font-size: 10px;
        }
        table.pagamentos {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }
        table.pagamentos th {
            text-align: left;
            font-size: 9px;
            background-color: #eef2f7;
            padding: 5px;
            border-bottom: 1px solid #ccc;
        }
        table.pagamentos td {
            font-size: 9px;
            padding: 5px;
            border-bottom: 1px solid #eee;
        }
        table.pagamentos tr:nth-child(even) td {
            background-color: #f7f9fb;
        }
        .totais {
            width: 100%;
            margin-top: 15px;
        }
        .totais table {
            margin-left: auto;
            border-collapse: collapse;
        }
        .totais td {
            padding: 2px 0 2px 15px;
            font-size: 10px;
            text-align: right;
        }
        .totais .rotulo {
            font-weight: bold;
        }
        .assinatura {
            display: table;
            width: 100%;
            margin-top: 50px;
        }
        .assinatura .col {
            display: table-cell;
            width: 50%;
            vertical-align: bottom;
        }
        .assinatura .linha {
            width: 80%;
            border-top: 1px solid #000;
            padding-top: 5px;
            text-align: center;
            font-size: 10px;
        }
        .assinatura .total-pagar {
            text-align: right;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="top-header">
        <div class="title">{{ strtoupper($tipo) }}</div>
        <div class="numero">Nº FA:{{ str_pad($reserva->id, 6, '0', STR_PAD_LEFT) }}</div>
    </div>

    <div class="hotel-header">
        <div class="col">
            <strong>Lago São Francisco</strong>
            CNPJ: 40.065.099/0001-24<br>
            gerencia@lagosaofrancisco.com<br>
            +55 (87) 98178-0808
        </div>
        <div class="col emissao">
            Data de emissão<br>
            {{ $dataEmissao }}
        </div>
    </div>

    <div class="section-title">Informações do hóspede</div>
    <table class="info">
        <tr>
            <td>
                <span class="label">Nome</span>
                <span class="value">{{ $hospede->nome }}</span>
            </td>
            <td>
                <span class="label">CPF</span>
                <span class="value">{{ $hospede->cpf ?? '-' }}</span>
            </td>
            <td>
                <span class="label">E-mail</span>
                <span class="value">{{ $hospede->email ?? '-' }}</span>
            </td>
            <td>
                <span class="label">Telefone</span>
                <span class="value">{{ $hospede->telefone ?? '-' }}</span>
            </td>
        </tr>
    </table>

    <div class="section-title">Informações da reserva</div>
    <table class="info">
        <tr>
            <td>
                <span class="label">Local</span>
                <span class="value">{{ $quarto->nome ?? '-' }}</span>
            </td>
            <td>
                <span class="label">Período</span>
                <span class="value">{{ $periodo }} ({{ $numDiarias }} {{ $numDiarias == 1 ? 'diária' : 'diárias' }})</span>
            </td>
            <td>
                <span class="label">Diária Média</span>
                <span class="value">R$ {{ number_format($valorDiaria, 2, ',', '.') }}</span>
            </td>
            <td>
                <span class="label">Nº adultos</span>
                <span class="value">{{ $reserva->n_adultos }}</span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="label">Nº crianças</span>
                <span class="value">{{ $reserva->n_criancas }}</span>
            </td>
            <td>
                <span class="label">Serviços</span>
                <span class="value">R$ {{ number_format($totalServicos, 2, ',', '.') }}</span>
            </td>
            <td>
                <span class="label">Produtos</span>
                <span class="value">R$ {{ number_format($totalProdutos, 2, ',', '.') }}</span>
            </td>
            <td>
                <span class="label">Diárias</span>
                <span class="value">R$ {{ number_format($totalDiarias, 2, ',', '.') }}</span>
            </td>
        </tr>
    </table>

    <div class="section-title">Pagamentos</div>
    <table class="pagamentos">
        <thead>
            <tr>
                <th>Descrição</th>
                <th>Pagamento em</th>
                <th>Data</th>
                <th>Custo</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($pagamentos as $pagamento)
                <tr>
                    <td>{{ $pagamento->descricao }}</td>
                    <td>{{ $pagamento->formaPagamento->descricao ?? '-' }}</td>
                    <td>{{ $pagamento->data_pagamento ? $pagamento->data_pagamento->format('d/m/Y') : '-' }}</td>
                    <td>R$ {{ number_format($pagamento->valor, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">Nenhum pagamento registrado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($reserva->observacoes)
        <div class="section-title">Observação</div>
        <div class="value">{{ $reserva->observacoes }}</div>
    @endif

    <div class="totais">
        <table>
            <tr>
                <td class="rotulo">Total reserva:</td>
                <td>R$ {{ number_format($totalReserva, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="rotulo">Total consumido:</td>
                <td>R$ {{ number_format($totalConsumido, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="rotulo">Total pago:</td>
                <td>R$ {{ number_format($totalPago, 2, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <div class="assinatura">
        <div class="col">
            <div class="linha">
                {{ $assinatura }}<br>
                (assinatura)
            </div>
        </div>
        <div class="col total-pagar">
            <strong>Total a pagar:</strong> R$ {{ number_format($totalAPagar, 2, ',', '.') }}
        </div>
    </div>
</body>
</html>
