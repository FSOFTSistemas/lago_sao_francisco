<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Demonstrativo de pagamentos da excursão #{{ $excursao->id }}</title>
    <style>
        @page { margin: 24px 30px; }
        body { font-family: DejaVu Sans, sans-serif; color: #263238; font-size: 10px; line-height: 1.4; }
        .cabecalho { border-bottom: 3px solid #6f42c1; padding-bottom: 10px; margin-bottom: 16px; }
        .cabecalho table, .dados, .resumo { width: 100%; border-collapse: collapse; }
        .titulo { color: #6f42c1; font-size: 20px; font-weight: bold; text-transform: uppercase; }
        .numero { text-align: right; font-size: 12px; font-weight: bold; }
        .empresa { margin-top: 7px; color: #455a64; }
        .emissao { text-align: right; color: #455a64; }
        .status { display: inline-block; padding: 3px 8px; border: 1px solid #6f42c1; color: #6f42c1; font-weight: bold; }
        .secao { margin-top: 15px; margin-bottom: 6px; color: #6f42c1; font-size: 12px; font-weight: bold; border-bottom: 1px solid #d6c8e8; padding-bottom: 3px; }
        .dados td { width: 25%; padding: 4px 8px 4px 0; vertical-align: top; }
        .rotulo { display: block; color: #607d8b; font-size: 8px; text-transform: uppercase; }
        .valor { font-weight: bold; }
        .descricao { padding: 7px; background: #f7f4fb; border: 1px solid #e3d9ef; }
        table.pagamentos { width: 100%; border-collapse: collapse; }
        .pagamentos th { background: #6f42c1; color: #fff; padding: 6px 5px; font-size: 8px; text-align: left; }
        .pagamentos td { padding: 6px 5px; border-bottom: 1px solid #ddd; vertical-align: top; font-size: 8px; }
        .pagamentos tr:nth-child(even) td { background: #fafafa; }
        .direita { text-align: right !important; }
        .centro { text-align: center !important; }
        .estornado { color: #b71c1c; font-weight: bold; }
        .confirmado { color: #1b5e20; font-weight: bold; }
        .resumo { margin-top: 13px; }
        .resumo td { padding: 3px 5px; }
        .resumo .espaco { width: 55%; }
        .resumo .total td { border-top: 2px solid #6f42c1; padding-top: 6px; font-size: 12px; font-weight: bold; }
        .saldo { color: #b45309; }
        .quitado { color: #1b5e20; }
        .observacao { margin-top: 18px; padding: 8px; background: #f4f5f6; color: #546e7a; font-size: 8px; }
        .assinatura { margin-top: 42px; width: 45%; border-top: 1px solid #455a64; text-align: center; padding-top: 5px; }
        .rodape { position: fixed; bottom: -12px; left: 0; right: 0; text-align: center; color: #90a4ae; font-size: 7px; }
    </style>
</head>
<body>
    @php
        $titulo = 'Demonstrativo de pagamentos da excursão';
        $status = ucfirst(strtolower(str_replace('_', ' ', $excursao->status)));
        $almoco = $excursao->almoco;
    @endphp

    <div class="cabecalho">
        <table>
            <tr>
                <td class="titulo">{{ $titulo }}</td>
                <td class="numero">Nº REC-EXC-{{ str_pad((string) $excursao->id, 6, '0', STR_PAD_LEFT) }}</td>
            </tr>
            <tr>
                <td class="empresa">
                    <strong>{{ $empresa?->nome_fantasia ?? $empresa?->razao_social ?? 'Lago São Francisco' }}</strong><br>
                    @if ($empresa?->cnpj) CNPJ: {{ $empresa->cnpj }}<br> @endif
                    @if ($empresa?->endereco) {{ $empresa->endereco }} @endif
                </td>
                <td class="emissao">
                    Emitido em {{ $emitidoEm->format('d/m/Y \à\s H:i') }}<br>
                    <span class="status">{{ $status }}</span>
                </td>
            </tr>
        </table>
    </div>

    <div class="secao">Dados da excursão</div>
    <table class="dados">
        <tr>
            <td><span class="rotulo">Código</span><span class="valor">#{{ $excursao->id }}</span></td>
            <td><span class="rotulo">Data agendada</span><span class="valor">{{ $excursao->data->format('d/m/Y') }}</span></td>
            <td><span class="rotulo">Responsável</span><span class="valor">{{ $excursao->responsavel }}</span></td>
            <td><span class="rotulo">Telefone</span><span class="valor">{{ $excursao->telefone_responsavel }}</span></td>
        </tr>
        <tr>
            <td><span class="rotulo">Pessoas</span><span class="valor">{{ number_format($excursao->qtd_pessoas, 0, ',', '.') }}</span></td>
            <td><span class="rotulo">Valor por pessoa</span><span class="valor">R$ {{ number_format((float) $excursao->valor_pessoa, 2, ',', '.') }}</span></td>
            <td><span class="rotulo">Valor das pessoas</span><span class="valor">R$ {{ number_format((float) $excursao->valor_pessoas, 2, ',', '.') }}</span></td>
            <td><span class="rotulo">Situação financeira</span><span class="valor {{ $quitada ? 'quitado' : 'saldo' }}">{{ $quitada ? 'Quitada' : 'Saldo pendente' }}</span></td>
        </tr>
    </table>
    <div class="descricao"><strong>Descrição:</strong> {{ $excursao->descricao }}</div>

    @if ($almoco || $excursao->qtd_almoco > 0)
        <div class="secao">Almoço</div>
        <table class="dados">
            <tr>
                <td><span class="rotulo">Cardápio</span><span class="valor">{{ $almoco?->nome_cardapio ?? 'Almoço da excursão' }}</span></td>
                <td><span class="rotulo">Quantidade</span><span class="valor">{{ number_format($almoco?->quantidade ?? $excursao->qtd_almoco, 0, ',', '.') }}</span></td>
                <td><span class="rotulo">Valor unitário</span><span class="valor">R$ {{ number_format((float) ($almoco?->valor_unitario ?? $excursao->valor_almoco), 2, ',', '.') }}</span></td>
                <td><span class="rotulo">Total</span><span class="valor">R$ {{ number_format((float) ($almoco?->total ?? $excursao->total_almoco), 2, ',', '.') }}</span></td>
            </tr>
        </table>
        @if ($almoco?->descricao_cardapio)
            <div class="descricao"><strong>Itens:</strong> {{ implode(' · ', preg_split('/\r\n|\r|\n/', $almoco->descricao_cardapio, -1, PREG_SPLIT_NO_EMPTY)) }}</div>
        @endif
    @endif

    <div class="secao">Pagamentos registrados</div>
    <table class="pagamentos">
        <thead>
            <tr>
                <th>#</th>
                <th>Data</th>
                <th>Forma</th>
                <th>Movimentação / caixa</th>
                <th>Registrado por</th>
                <th class="centro">Comprovante</th>
                <th class="centro">Situação</th>
                <th class="direita">Valor</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($recebimentos as $recebimento)
                <tr>
                    <td>{{ $recebimento->id }}</td>
                    <td>{{ $recebimento->data_recebimento?->format('d/m/Y') ?? '—' }}</td>
                    <td>{{ $recebimento->formaPagamento?->descricao ?? 'Não informada' }}</td>
                    <td>
                        Fluxo #{{ $recebimento->fluxo_caixa_id }}<br>
                        Caixa #{{ $recebimento->fluxoCaixa?->caixa_id ?? '—' }}
                    </td>
                    <td>{{ $recebimento->fluxoCaixa?->usuario?->name ?? 'Não informado' }}</td>
                    <td class="centro">{{ $recebimento->comprovante_path ? 'Anexado' : '—' }}</td>
                    <td class="centro {{ $recebimento->fluxo_cancelamento_id ? 'estornado' : 'confirmado' }}">
                        {{ $recebimento->fluxo_cancelamento_id ? 'Estornado' : 'Confirmado' }}
                    </td>
                    <td class="direita">R$ {{ number_format((float) $recebimento->valor, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="resumo">
        <tr><td class="espaco"></td><td>Valor das pessoas</td><td class="direita">R$ {{ number_format((float) $excursao->valor_pessoas, 2, ',', '.') }}</td></tr>
        <tr><td></td><td>Total do almoço</td><td class="direita">R$ {{ number_format((float) $excursao->total_almoco, 2, ',', '.') }}</td></tr>
        <tr><td></td><td>Subtotal</td><td class="direita">R$ {{ number_format((float) $excursao->subtotal, 2, ',', '.') }}</td></tr>
        <tr><td></td><td>Acréscimo</td><td class="direita">R$ {{ number_format((float) $excursao->acrescimo, 2, ',', '.') }}</td></tr>
        <tr><td></td><td>Desconto</td><td class="direita">- R$ {{ number_format((float) $excursao->desconto, 2, ',', '.') }}</td></tr>
        <tr class="total"><td></td><td>Total da excursão</td><td class="direita">R$ {{ number_format((float) $excursao->total, 2, ',', '.') }}</td></tr>
        <tr><td></td><td>Total recebido</td><td class="direita quitado">R$ {{ number_format($totalRecebido, 2, ',', '.') }}</td></tr>
        <tr><td></td><td>Saldo restante</td><td class="direita {{ $quitada ? 'quitado' : 'saldo' }}">R$ {{ number_format($saldoRestante, 2, ',', '.') }}</td></tr>
    </table>

    @if ($excursao->status === \App\Models\Excursao::STATUS_CANCELADO)
        <div class="observacao"><strong>Excursão cancelada.</strong> Os valores acima permanecem recebidos enquanto não houver estorno registrado.</div>
    @else
        <div class="observacao">Este documento comprova os pagamentos registrados no sistema e não substitui documento fiscal.</div>
    @endif

    <div class="assinatura">
        {{ Auth::user()?->name ?? 'Responsável pela emissão' }}<br>
        Responsável pela emissão
    </div>

    <div class="rodape">Demonstrativo gerado eletronicamente pelo sistema Lago São Francisco.</div>
</body>
</html>
