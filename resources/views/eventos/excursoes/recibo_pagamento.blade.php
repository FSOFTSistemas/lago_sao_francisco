<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Recibo de pagamento</title>
    <style>
        @page { margin: 9px; }
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #26332a;
            font-size: 10px;
            line-height: 1.4;
            background: #fff;
            margin: 0;
        }
        .recibo {
            border: 1px solid #dce6d7;
            background: #fff;
            padding: 0 20px 16px;
            box-shadow: none;
            height: 135mm;
            box-sizing: border-box;
            overflow: hidden;
        }
        .cabecalho {
            margin: 0 -20px 15px;
            background: #679A4C;
            color: #fff;
            padding: 13px 20px;
        }
        .cabecalho table {
            width: 100%;
            border-collapse: collapse;
        }
        .cabecalho td {
            vertical-align: middle;
        }
        .cabecalho .empresa-coluna {
            width: 44%;
        }
        .cabecalho .numero-coluna {
            width: 56%;
        }
        .cabecalho h1 {
            margin: 0;
            font-size: 21px;
            letter-spacing: 1px;
        }
        .empresa {
            margin-top: 2px;
            font-size: 9px;
            color: #fff;
        }
        .numero {
            text-align: right;
            font-size: 9px;
            color: #fff;
            white-space: nowrap;
        }
        .confirmado {
            display: inline-block;
            margin-top: 5px;
            padding: 3px 8px;
            background: #fff;
            color: #3e7222;
            font-size: 8px;
            font-weight: bold;
            letter-spacing: .4px;
        }
        .valor {
            margin: 13px 0;
            padding: 10px;
            border: 1px solid #cbdcc3;
            background: #f1f7ee;
            text-align: center;
            color: #3e7222;
            font-size: 22px;
            font-weight: bold;
        }
        .texto {
            text-align: justify;
            margin: 9px 0;
        }
        .detalhes {
            width: 100%;
            margin-top: 12px;
            border-collapse: collapse;
            background: #f8faf7;
        }
        .detalhes td {
            width: 50%;
            padding: 9px 12px;
            border: 1px solid #e1e9dd;
        }
        .rotulo {
            display: block;
            color: #71806f;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: .3px;
        }
        .dado {
            display: block;
            margin-top: 2px;
            color: #26332a;
            font-size: 10px;
            font-weight: bold;
        }
        .data {
            margin-top: 12px;
            text-align: right;
            color: #71806f;
        }
        .assinatura {
            width: 65%;
            margin: 34px auto 0;
            border-top: 1px solid #526250;
            padding-top: 7px;
            text-align: center;
        }
        .observacao {
            margin-top: 14px;
            padding-top: 8px;
            border-top: 1px solid #edf1eb;
            text-align: center;
            color: #7b8878;
            font-size: 8px;
        }
    </style>
</head>
<body>
    <div class="recibo">
        <div class="cabecalho">
            <table>
                <tr>
                    <td class="empresa-coluna">
                        <h1>RECIBO</h1>
                        <div class="empresa">
                            <strong>{{ $empresa?->nome_fantasia ?? $empresa?->razao_social ?? 'Empresa não informada' }}</strong>
                            @if ($empresa?->cnpj)<br>CNPJ: {{ $empresa->cnpj }}@endif
                        </div>
                    </td>
                    <td class="numero numero-coluna">
                        Nº REC-EXC-{{ str_pad((string) $excursao->id, 6, '0', STR_PAD_LEFT) }}-{{ str_pad((string) $recebimento->id, 6, '0', STR_PAD_LEFT) }}<br>
                        <span class="confirmado">PAGAMENTO CONFIRMADO</span>
                    </td>
                </tr>
            </table>
        </div>

        <p class="texto">
            A empresa <strong>{{ $empresa?->nome_fantasia ?? $empresa?->razao_social ?? 'não informada' }}</strong>
            declara que recebeu de <strong>{{ $excursao->responsavel }}</strong> o pagamento referente à
            excursão <strong>#{{ $excursao->id }}</strong>, agendada para
            <strong>{{ $excursao->data->format('d/m/Y') }}</strong> na quantia de:
        </p>

        <div class="valor">R$ {{ number_format((float) $recebimento->valor, 2, ',', '.') }}</div>

        <table class="detalhes">
            <tr>
                <td>
                    <span class="rotulo">Data do pagamento</span>
                    <span class="dado">{{ $recebimento->data_recebimento?->format('d/m/Y') ?? 'Não informada' }}</span>
                </td>
                <td>
                    <span class="rotulo">Forma de pagamento</span>
                    <span class="dado">{{ $recebimento->formaPagamento?->descricao ?? 'Não informada' }}</span>
                </td>
            </tr>
        </table>

        <div class="data">Emitido em {{ $emitidoEm->format('d/m/Y') }}.</div>

        <div class="assinatura">
            <strong>{{ $empresa?->nome_fantasia ?? $empresa?->razao_social ?? 'Empresa' }}</strong><br>
            Assinatura do responsável
        </div>

        <div class="observacao">Este recibo confirma exclusivamente o pagamento descrito acima e não substitui documento fiscal.</div>
    </div>
</body>
</html>
