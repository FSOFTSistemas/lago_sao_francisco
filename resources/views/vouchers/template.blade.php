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
            font-size: 12px;
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
            font-size: 20px;
            font-weight: bold;
            color: #679A4C;
        }

        .voucher-number {
            font-size: 16px;
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
            font-size: 12px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .info-table td {
            padding: 4px;
            vertical-align: top;
            border: 1px solid #ddd;
        }

        /* ALTERAÇÃO AQUI: Ajuste do espaçamento do label */
        .info-label {
            font-weight: bold;
            display: inline-block;
            /* min-width removido para não criar espaço fixo grande */
            min-width: auto; 
            margin-right: 8px; /* Espaço pequeno entre o label e o texto */
        }

        .check-info {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .check-title {
            font-weight: bold;
            margin-bottom: 10px;
            color: #679A4C;
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
            margin-right: 5px; /* Adicionado para garantir separação no check-in/out */
        }
        
        .obs-box {
            margin-top: 15px;
            margin-bottom: 15px;
            padding: 10px;
            border: 1px dashed #ccc;
            background-color: #fff;
            font-size: 10px;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
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
            <div class="section-title">DADOS DO HÓSPEDE</div>
            <table class="info-table">
                <tr>
                    <td><span class="info-label">Nome:</span> {{ $reserva->hospede->nome ?? 'Não informado' }}</td>
                    <td><span class="info-label">Telefone:</span> {{ $reserva->hospede->telefone ?? 'Não informado' }}</td>
                </tr>
                <tr>
                    <td><span class="info-label">CPF:</span> {{ $reserva->hospede->cpf ?? 'Não informado' }}</td>
                    <td><span class="info-label">Email:</span> {{ $reserva->hospede->email ?? 'Não informado' }}</td>
                </tr>
            </table>
        </div>

        <div class="section">
            <div class="section-title">DADOS DA RESERVA</div>
            <table class="info-table">
                <tr>
                    <td><span class="info-label">Nº de Adultos:</span> {{ $reserva->n_adultos ?? '0' }}</td>
                    <td><span class="info-label">Nº de Crianças:</span> {{ $reserva->n_criancas ?? '0' }}</td>
                </tr>
                
                {{-- Exibe Crianças Não Pagantes apenas se houver --}}
                @if(($reserva->n_criancas_nao_pagantes ?? 0) > 0)
                <tr>
                    <td colspan="2">
                        <span class="info-label">Crianças (Não Pagantes):</span> 
                        {{ $reserva->n_criancas_nao_pagantes }}
                    </td>
                </tr>
                @endif

                {{-- Exibe Pets apenas se houver --}}
                @if($reserva->pets && $reserva->pets->count() > 0)
                <tr>
                    <td colspan="2">
                        <span class="info-label">Pets:</span>
                        @foreach($reserva->pets as $pet)
                            {{ $pet->quantidade }}x {{ ucfirst($pet->tamanho) }}
                            @if(!$loop->last), @endif
                        @endforeach
                    </td>
                </tr>
                @endif
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
                            <span class="check-label">Valor Unitário:</span> R$ {{ number_format($valorDiaria, 2, ',', '.') }}
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

            <table class="info-table" style="border: none;">
                <thead>
                    <tr style="background-color: #f2f2f2;">
                        <th>Forma de Pagamento</th>
                        <th>Descrição</th>
                        <th>Data</th>
                        <th>Valor</th>
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
                            <td colspan="4" style="text-align: center;">Nenhuma transação registrada.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if(!empty($reserva->observacoes))
            <div class="obs-box">
                <strong>Observações:</strong><br>
                {{ $reserva->observacoes }}
            </div>
            @endif

            <div class="totais" style="margin-top: 10px; text-align: right;">
                <p style="font-size: 11px; margin: 2px 0;">
                    <strong>Total da Reserva:</strong> R$ {{ number_format($totalReserva, 2, ',', '.') }}
                </p>
                <p style="font-size: 11px; margin: 2px 0;">
                    <strong>Total Pago:</strong> R$ {{ number_format($totalPago, 2, ',', '.') }}
                </p>

                <div style="
                        margin-top: 10px;
                        padding: 8px;
                        font-size: 14px;
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