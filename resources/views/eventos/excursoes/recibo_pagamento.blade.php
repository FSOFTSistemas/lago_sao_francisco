<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Recibo de pagamento</title>
    <style>
        @page { margin: 18px; }
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #26332a;
            font-size: 9px;
            line-height: 1.45;
            background: #fff;
        }
        .recibo {
            border: 1px solid #dce6d7;
            background: #fff;
            padding: 0 18px 16px;
            box-shadow: none;
        }
        .cabecalho {
            margin: 0 -18px 16px;
            background: #679A4C;
            color: #fff;
            padding: 14px 18px;
        }
        .cabecalho table {
            width: 100%;
            border-collapse: collapse;
        }
        .cabecalho td {
            vertical-align: middle;
        }
        .cabecalho h1 {
            margin: 0;
            font-size: 20px;
            letter-spacing: 1px;
        }
        .empresa {
            margin-top: 2px;
            font-size: 8px;
            color: #fff;
        }
        .numero {
            text-align: right;
            font-size: 8px;
            color: #fff;
        }
        .confirmado {
            display: inline-block;
            margin-top: 5px;
            padding: 2px 6px;
            background: #fff;
            color: #3e7222;
            font-size: 7px;
            font-weight: bold;
            letter-spacing: .4px;
        }
        .valor {
            margin: 14px 0;
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
            margin: 8px 0;
        }
        .detalhes {
            width: 100%;
            margin-top: 14px;
            border-collapse: collapse;
            background: #f8faf7;
        }
        .detalhes td {
            width: 50%;
            padding: 8px 10px;
            border: 1px solid #e1e9dd;
        }
        .rotulo {
            display: block;
            color: #71806f;
            font-size: 7px;
            text-transform: uppercase;
            letter-spacing: .3px;
        }
        .dado {
            display: block;
            margin-top: 2px;
            color: #26332a;
            font-size: 9px;
            font-weight: bold;
        }
        .data {
            margin-top: 14px;
            text-align: right;
            color: #71806f;
        }
        .assinatura {
            width: 65%;
            margin: 42px auto 0;
            border-top: 1px solid #526250;
            padding-top: 5px;
            text-align: center;
        }
        .observacao {
            margin-top: 20px;
            padding-top: 8px;
            border-top: 1px solid #edf1eb;
            text-align: center;
            color: #7b8878;
            font-size: 7px;
        }
    </style>
</head>
<body>
    <div class="recibo">
        <div class="cabecalho">
            <table>
                <tr>
                    <td>
                        <h1>RECIBO</h1>
                        <div class="empresa">
                            <strong>{{ $empresa?->nome_fantasia ?? $empresa?->razao_social ?? 'Empresa não informada' }}</strong>
                            @if ($empresa?->cnpj)<br>CNPJ: {{ $empresa->cnpj }}@endif
                        </div>
                    </td>
                    <td class="numero">
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
