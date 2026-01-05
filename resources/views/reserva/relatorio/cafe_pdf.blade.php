<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Lista de Café da Manhã</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #ddd; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 18px; }
        .header p { margin: 5px 0 0; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .quarto { font-weight: bold; width: 15%; }
        .total { text-align: center; width: 10%; }
        .names { width: 75%; }
        .secundario { color: #555; font-style: italic; display: block; margin-left: 10px; font-size: 11px; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; color: #999; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Lista de Café da Manhã</h1>
        <p>Período: {{ \Carbon\Carbon::parse($dataInicio)->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($dataFim)->format('d/m/Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Quarto</th>
                <th>Hóspedes</th>
                <th>Qtd.</th>
            </tr>
        </thead>
        <tbody>
            @php $totalGeral = 0; @endphp
            @foreach($reservas as $reserva)
                @php 
                    $qtdSecundarios = $reserva->lista_secundarios ? $reserva->lista_secundarios->count() : 0;
                    $qtdPessoas = $reserva->n_adultos + $reserva->n_criancas;
                    $totalGeral += $qtdPessoas;
                @endphp
                <tr>
                    <td class="quarto">
                        {{ $reserva->quarto->nome ?? '-' }}
                        <br><span style="font-weight: normal; font-size: 10px;">{{ $reserva->quarto->categoria->titulo ?? '' }}</span>
                    </td>
                    <td class="names">
                        <strong>{{ $reserva->hospede->nome ?? 'Principal Removido' }}</strong>
                        
                        @if($qtdSecundarios > 0)
                            <div style="margin-top: 4px;">
                                @foreach($reserva->lista_secundarios as $sec)
                                    <span class="secundario">• {{ $sec->nome }}</span>
                                @endforeach
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