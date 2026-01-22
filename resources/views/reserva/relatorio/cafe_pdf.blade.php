<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Lista de Café da Manhã</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #ddd; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 18px; }
        .header p { margin: 5px 0 0; color: #666; }
        
        /* --- LAYOUT --- */
        .summary-table {
            width: 100%;
            border-collapse: separate; 
            border-spacing: 10px 0; 
            margin-bottom: 20px;
            margin-left: -10px;
            margin-right: -10px;
        }

        .summary-card {
            width: 33%;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            background-color: #f8f9fa;
            padding: 8px;
            vertical-align: middle;
        }

        .card-inner { width: 100%; }
        .card-icon { width: 40px; text-align: center; vertical-align: middle; }
        .card-text { text-align: left; padding-left: 5px; vertical-align: middle; }

        /* --- DESENHO DOS ÍCONES (CSS PURO) --- */
        .meeple-container { display: inline-block; margin: 0 auto; }
        
        /* Bonecos (Adulto/Criança) */
        .head { border-radius: 50%; margin: 0 auto 2px auto; }
        .body { border-radius: 8px 8px 0 0; margin: 0 auto; }

        /* Tamanhos */
        .icon-adulto .head { width: 10px; height: 10px; }
        .icon-adulto .body { width: 18px; height: 10px; }
        .icon-crianca .head { width: 8px; height: 8px; }
        .icon-crianca .body { width: 14px; height: 8px; }

        /* Ícone de TOTAL (Prancheta/Lista) */
        .icon-total-box {
            width: 18px;
            height: 22px;
            border: 2px solid #28a745; /* Borda Verde */
            border-radius: 3px;
            margin: 0 auto;
            position: relative;
            background-color: white;
        }
        /* Linhas dentro da prancheta */
        .icon-total-line {
            width: 10px;
            height: 2px;
            background-color: #28a745;
            margin: 3px auto 0 auto; /* Espaçamento entre as linhas */
        }

        /* Cores */
        .bg-blue { background-color: #17a2b8; }
        .bg-orange { background-color: #ffc107; }
        .bg-green { background-color: #28a745; }
        
        .text-blue { color: #17a2b8; }
        .text-orange { color: #ffc107; }
        .text-green { color: #28a745; }

        .summary-title { font-size: 9px; text-transform: uppercase; color: #666; font-weight: bold; display: block; }
        .summary-value { font-size: 18px; font-weight: bold; display: block; margin-top: 2px; }

        /* Tabela Principal */
        table.main-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 12px; }
        table.main-table th, table.main-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        table.main-table th { background-color: #f2f2f2; }
        
        .quarto { font-weight: bold; width: 15%; }
        .total { text-align: center; width: 10%; }
        .names { width: 75%; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; color: #999; }
    </style>
</head>
<body>
    @php
        $totalAdultos = $reservas->sum('n_adultos');
        // Alterado aqui: Soma pagantes + não pagantes
        $totalCriancas = $reservas->sum('n_criancas') + $reservas->sum('n_criancas_nao_pagantes');
        $totalGeral = $totalAdultos + $totalCriancas;
    @endphp

    <div class="header">
        <h1>Lista de Café da Manhã</h1>
        <p>Período: {{ \Carbon\Carbon::parse($dataInicio)->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($dataFim)->format('d/m/Y') }}</p>
    </div>

    <table class="summary-table">
        <tr>
            <td class="summary-card">
                <table class="card-inner">
                    <tr>
                        <td class="card-icon">
                            <div class="meeple-container icon-adulto">
                                <div class="head bg-blue"></div>
                                <div class="body bg-blue"></div>
                            </div>
                        </td>
                        <td class="card-text">
                            <span class="summary-title">Adultos</span>
                            <span class="summary-value text-blue">{{ $totalAdultos }}</span>
                        </td>
                    </tr>
                </table>
            </td>

            <td class="summary-card">
                <table class="card-inner">
                    <tr>
                        <td class="card-icon">
                            <div class="meeple-container icon-crianca">
                                <div class="head bg-orange"></div>
                                <div class="body bg-orange"></div>
                            </div>
                        </td>
                        <td class="card-text">
                            <span class="summary-title">Crianças</span>
                            <span class="summary-value text-orange">{{ $totalCriancas }}</span>
                        </td>
                    </tr>
                </table>
            </td>

            <td class="summary-card">
                <table class="card-inner">
                    <tr>
                        <td class="card-icon">
                            <div class="icon-total-box">
                                <div class="icon-total-line"></div>
                                <div class="icon-total-line"></div>
                                <div class="icon-total-line"></div>
                            </div>
                        </td>
                        <td class="card-text">
                            <span class="summary-title">Total Geral</span>
                            <span class="summary-value text-green">{{ $totalGeral }}</span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="main-table">
        <thead>
            <tr>
                <th>Quarto</th>
                <th>Hóspedes</th>
                <th>Qtd.</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reservas as $reserva)
                @php 
                    // Soma pagantes + não pagantes
                    $qtdPessoas = $reserva->n_adultos + $reserva->n_criancas + ($reserva->n_criancas_nao_pagantes ?? 0);
                @endphp
                <tr>
                    <td class="quarto">
                        {{ $reserva->quarto->nome ?? '-' }}
                        <br><span style="font-weight: normal; font-size: 10px;">{{ $reserva->quarto->categoria->titulo ?? '' }}</span>
                    </td>
                    <td class="names">
                        <strong>{{ $reserva->hospede->nome ?? 'Principal Removido' }}</strong>
                        
                        @if($reserva->nomes_hospedes_secundarios)
                            <div style="margin-top: 4px; font-size: 11px; color: #555;">
                                Secundários: {{ $reserva->nomes_hospedes_secundarios }}
                            </div>
                        @endif
                    </td>
                    <td class="total">
                        {{ $qtdPessoas }}
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #eee; font-weight: bold;">
                <td colspan="2" style="text-align: right;">Total de Pessoas:</td>
                <td class="total">{{ $totalGeral }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Gerado em {{ date('d/m/Y H:i') }}
    </div>
</body>
</html>