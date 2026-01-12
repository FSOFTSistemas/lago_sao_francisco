<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Voucher de Reserva - {{ $numeroVoucher }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
            font-size: 12px; /* Reduzido o tamanho base */
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #679A4C;
            padding: 20px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #679A4C;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .logo {
            font-size: 20px; /* Reduzido de 24px */
            font-weight: bold;
            color: #679A4C;
        }

        .voucher-number {
            font-size: 16px; /* Reduzido de 18px */
            color: #679A4C;
            margin-top: 10px;
        }

        .section {
            margin-bottom: 20px;
        }

        .section-title {
            font-weight: bold;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            margin-bottom: 10px;
            font-size: 12px; /* Definido tamanho levemente maior que o corpo */
        }

        .info-row {
            display: flex;
            margin-bottom: 5px;
        }

        .info-label {
            font-weight: bold;
            width: 200px;
        }

        .info-value {
            flex: 1;
        }

        .check-info {
            background-color: #f8f9fa;
            padding: 10px; /* Reduzido padding */
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .check-title {
            font-weight: bold;
            margin-bottom: 10px;
            color: #679A4C;
        }

        .check-row {
            display: flex;
            margin-bottom: 5px;
        }

        .check-label {
            width: 120px;
            font-weight: bold;
        }

        .check-value {
            flex: 1;
        }

        .payment-info {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
        }

        .total {
            font-size: 16px; /* Reduzido de 18px */
            font-weight: bold;
            margin-top: 10px;
            text-align: right;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px; /* Reduzido de 12px */
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }

        .highlight {
            color: #679A4C;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table,
        th,
        td {
            border: 1px solid #ddd;
        }

        th,
        td {
            padding: 6px; /* Reduzido padding */
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .info-table td {
            padding: 4px; /* Reduzido padding */
            vertical-align: top;
        }

        .info-label {
            font-weight: bold;
        }

        .check-table {
            width: 100%;
            border-collapse: collapse;
        }

        .check-table td {
            padding: 4px;
            border: none;
            vertical-align: top;
        }

        .check-label {
            font-weight: bold;
        }
        
        /* Estilo para caixa de observação */
        .obs-box {
            margin-top: 15px;
            margin-bottom: 15px;
            padding: 10px;
            border: 1px dashed #ccc;
            background-color: #fff;
            font-size: 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <div class="logo">Hotel Estação Chico</div>
            <div class="voucher-number">VOUCHER Nº {{ $numeroVoucher }}</div>
            <div>Data de Emissão: {{ $dataEmissao }}</div>
        </div>

        <div class="section">
            <div class="section">
                <div class="section-title">DADOS DO HÓSPEDE</div>
                <table class="info-table">
                    <tr>
                        <td><span class="info-label">Nome:</span> {{ $reserva->hospede->nome ?? 'Não informado' }}</td>
                        <td><span class="info-label">Telefone:</span>
                            {{ $reserva->hospede->telefone ?? 'Não informado' }}</td>
                        <td><span class="info-label">CPF:</span> {{ $reserva->hospede->cpf ?? 'Não informado' }}</td>
                    </tr>
                    <tr>
                        <td colspan="3"><span class="info-label">Email:</span>
                            {{ $reserva->hospede->email ?? 'Não informado' }}
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="section">
            <div class="section-title">DADOS DA RESERVA</div>
            <table class="info-table">
                {{-- Removido Quarto e Categoria --}}
                <tr>
                    <td><span class="info-label">Nº de Adultos:</span> {{ $reserva->n_adultos ?? '0' }}</td>
                    <td><span class="info-label">Nº de Crianças:</span> {{ $reserva->n_criancas ?? '0' }}</td>
                </tr>
                {{-- Removido Observações daqui --}}
            </table>
        </div>

        <div class="check-info">
            <div class="check-title">INFORMAÇÕES DE CHECK-IN E CHECK-OUT</div>

            <table class="check-table">
                <tr>
                    <td>
                        <span class="check-label">Check-in:</span>
                        {{ $dataCheckin }} a partir das {{ $horaCheckin }}
                    </td>
                    <td>
                        <span class="check-label">Check-out:</span>
                        {{ $dataCheckout }} até as {{ $horaCheckout }}
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <span class="check-label">Nº de Diárias:</span> {{ $numDiarias }}
                        <span style="margin-left: 20px;">
                            <span class="check-label">Valor Unitário:</span> R$
                            {{ number_format($valorDiaria, 2, ',', '.') }}
                        </span>
                        <span style="margin-left: 20px;">
                            <span class="check-label">Total:</span> R$ {{ number_format($valorTotal, 2, ',', '.') }}
                        </span>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="section">
            @php
                $totalReserva = $valorTotal;
                $totalPago = $reserva->transacoes->sum('valor');
                $totalRestante = max(0, $totalReserva - $totalPago);
            @endphp

            <div class="section-title">PAGAMENTOS</div>

            {{-- Tabela de transações --}}
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th>Forma de Pagamento</th>
                        <th>Descrição</th>
                        <th>Data de Pagamento</th>
                        <th>Valor Pago</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($reserva->transacoes as $transacao)
                        <tr>
                            <td>{{ $transacao->formaPagamento->descricao ?? '—' }}</td>
                            <td>{{ $transacao->descricao ?? '—' }}</td>
                            <td>{{ \Carbon\Carbon::parse($transacao->data_pagamento)->format('d/m/Y') ?? '—' }}</td>
                            <td>R$ {{ number_format($transacao->valor, 2, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">Nenhuma transação registrada.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Observações movidas para cá --}}
            @if(!empty($reserva->observacoes))
            <div class="obs-box">
                <strong>Observações:</strong><br>
                {{ $reserva->observacoes }}
            </div>
            @endif

            {{-- Total pago --}}
            <div class="totais" style="margin-top: 10px;">
                <p style="font-size: 11px; margin: 2px 0;">
                    <strong>Total da Reserva:</strong> R$ {{ number_format($totalReserva, 2, ',', '.') }}
                </p>
                <p style="font-size: 11px; margin: 2px 0;">
                    <strong>Total Pago:</strong> R$ {{ number_format($totalPago, 2, ',', '.') }}
                </p>

                <div
                    style="
                        margin-top: 10px;
                        padding: 8px;
                        font-size: 14px; /* Reduzido */
                        font-weight: bold;
                        border: 2px solid #679A4C;
                        text-align: center;
                        background-color: #f5f5f5;
                    ">
                    TOTAL A PAGAR: R$ {{ number_format($totalRestante, 2, ',', '.') }}
                </div>
            </div>
        </div>
        <p style="text-align: center; color:#679A4C; margin-top: 10px;">Este voucher deve ser apresentado no momento do check-in.</p>
        <div class="footer">
            <p>Hotel Estação Chico - Fazenda Lago São Francisco | Turistica & Lazer em Garanhuns 09, Garanhuns-PE</p>
            <p>Telefone: (87)9 8141-1088 - Email: hplagosaofrancisco@gmail.com</p>
            <p>CNPJ: 60.922.645/0001-03</p>
        </div>
    </div>
</body>

</html>