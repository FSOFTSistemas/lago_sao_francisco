@php
    $almoco = $excursao->almoco;
    $possuiAlmoco = $almoco !== null || $excursao->qtd_almoco > 0;
    $nomeCardapio = $almoco?->nome_cardapio ?? 'Almoço da excursão';
    $descricaoCardapio = $almoco?->descricao_cardapio;
    $quantidadeAlmoco = $almoco?->quantidade ?? $excursao->qtd_almoco;
    $valorUnitarioAlmoco = $almoco?->valor_unitario ?? $excursao->valor_almoco;
    $totalAlmoco = $almoco?->total ?? $excursao->total_almoco;
    $statusModalClass = match ($excursao->status) {
        'REALIZADO' => 'success',
        'CANCELADO' => 'danger',
        'EM_ANDAMENTO' => 'primary',
        default => 'warning',
    };
@endphp

<div class="modal fade" id="modalVisualizarExcursao{{ $excursao->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-{{ $statusModalClass }} {{ $excursao->status === 'AGENDADO' ? '' : 'text-white' }}">
                <div>
                    <h5 class="modal-title">
                        <i class="fas fa-bus mr-2"></i>Excursão #{{ $excursao->id }}
                    </h5>
                    <small>{{ $excursao->data->format('d/m/Y') }} — {{ $excursao->responsavel }}</small>
                </div>
                <button type="button" class="close {{ $excursao->status === 'AGENDADO' ? '' : 'text-white' }}" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-7">
                        <div class="card card-outline card-primary shadow-none">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Informações gerais</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <small class="text-muted d-block">Status</small>
                                        <span class="badge badge-{{ $statusModalClass }}">
                                            {{ ucfirst(strtolower(str_replace('_', ' ', $excursao->status))) }}
                                        </span>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <small class="text-muted d-block">Quantidade de pessoas</small>
                                        <strong>{{ number_format($excursao->qtd_pessoas, 0, ',', '.') }}</strong>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <small class="text-muted d-block">Valor por pessoa</small>
                                        <strong>R$ {{ number_format((float) $excursao->valor_pessoa, 2, ',', '.') }}</strong>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <small class="text-muted d-block">Responsável</small>
                                        <strong>{{ $excursao->responsavel }}</strong>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <small class="text-muted d-block">Telefone</small>
                                        <strong>{{ $excursao->telefone_responsavel }}</strong>
                                    </div>
                                </div>
                                <small class="text-muted d-block">Descrição</small>
                                <p class="mb-0 text-break">{{ $excursao->descricao }}</p>
                            </div>
                        </div>

                        @if ($possuiAlmoco)
                            <div class="card card-outline card-warning shadow-none">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fas fa-utensils mr-2"></i>Almoço</h3>
                                </div>
                                <div class="card-body">
                                    <h5>{{ $nomeCardapio }}</h5>
                                    @if ($descricaoCardapio)
                                        <ul class="pl-3 mb-3">
                                            @foreach (preg_split('/\r\n|\r|\n/', $descricaoCardapio, -1, PREG_SPLIT_NO_EMPTY) as $item)
                                                <li>{{ $item }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                    <div class="row">
                                        <div class="col-sm-4 mb-2">
                                            <small class="text-muted d-block">Quantidade</small>
                                            <strong>{{ number_format($quantidadeAlmoco, 0, ',', '.') }}</strong>
                                        </div>
                                        <div class="col-sm-4 mb-2">
                                            <small class="text-muted d-block">Valor unitário</small>
                                            <strong>R$ {{ number_format((float) $valorUnitarioAlmoco, 2, ',', '.') }}</strong>
                                        </div>
                                        <div class="col-sm-4 mb-2">
                                            <small class="text-muted d-block">Total do almoço</small>
                                            <strong>R$ {{ number_format((float) $totalAlmoco, 2, ',', '.') }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="card card-outline card-success shadow-none mb-lg-0">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-receipt mr-2"></i>Recebimentos</h3>
                            </div>
                            <div class="card-body p-0">
                                @if ($excursao->recebimentos->isEmpty())
                                    <p class="text-muted text-center py-4 mb-0">Nenhum recebimento registrado.</p>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th class="pl-3">Data</th>
                                                    <th>Forma</th>
                                                    <th class="text-right">Valor</th>
                                                    <th class="text-center">Comprovante</th>
                                                    <th class="text-center pr-3">Recibo</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($excursao->recebimentos as $recebimento)
                                                    <tr>
                                                        <td class="pl-3">{{ $recebimento->data_recebimento?->format('d/m/Y') ?? '—' }}</td>
                                                        <td>{{ $recebimento->formaPagamento?->descricao ?? 'Não informada' }}</td>
                                                        <td class="text-right">R$ {{ number_format((float) $recebimento->valor, 2, ',', '.') }}</td>
                                                        <td class="text-center">
                                                            @if ($recebimento->comprovante_path)
                                                                <a href="{{ route('eventos.recebimentos.comprovante', $recebimento) }}"
                                                                    class="btn btn-xs btn-outline-primary" target="_blank" title="Abrir comprovante">
                                                                    <i class="fas fa-paperclip"></i>
                                                                </a>
                                                            @else
                                                                <span class="text-muted">—</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-center pr-3">
                                                            @if ($recebimento->fluxo_caixa_id && ! $recebimento->fluxo_cancelamento_id)
                                                                <a href="{{ route('eventos.recebimentos.recibo', $recebimento) }}"
                                                                    class="btn btn-xs btn-outline-danger" target="_blank"
                                                                    title="Emitir recibo deste pagamento">
                                                                    <i class="fas fa-file-invoice-dollar"></i>
                                                                </a>
                                                            @else
                                                                <span class="text-muted">—</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="card card-outline card-success shadow-none">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-calculator mr-2"></i>Resumo financeiro</h3>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Valor das pessoas</span>
                                    <strong>R$ {{ number_format((float) $excursao->valor_pessoas, 2, ',', '.') }}</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Total do almoço</span>
                                    <strong>R$ {{ number_format((float) $totalAlmoco, 2, ',', '.') }}</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotal</span>
                                    <strong>R$ {{ number_format((float) $excursao->subtotal, 2, ',', '.') }}</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Acréscimo</span>
                                    <strong>R$ {{ number_format((float) $excursao->acrescimo, 2, ',', '.') }}</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Desconto</span>
                                    <strong>R$ {{ number_format((float) $excursao->desconto, 2, ',', '.') }}</strong>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between h5">
                                    <span>Total</span>
                                    <strong>R$ {{ number_format((float) $excursao->total, 2, ',', '.') }}</strong>
                                </div>
                                <div class="d-flex justify-content-between text-danger mb-2">
                                    <span>Comissão ({{ number_format((float) $excursao->percentual_comissao, 2, ',', '.') }}%)</span>
                                    <strong>R$ {{ number_format((float) $excursao->valor_comissao, 2, ',', '.') }}</strong>
                                </div>
                                <div class="d-flex justify-content-between text-success">
                                    <span>Receita líquida</span>
                                    <strong>R$ {{ number_format((float) $excursao->receita_liquida, 2, ',', '.') }}</strong>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Valor pago</span>
                                    <strong>R$ {{ number_format((float) $excursao->valor_pago, 2, ',', '.') }}</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Valor restante</span>
                                    <strong>R$ {{ number_format((float) $excursao->valor_restante, 2, ',', '.') }}</strong>
                                </div>
                            </div>
                        </div>

                        @if ($excursao->iniciada_em || $excursao->finalizada_em || $excursao->cancelada_em)
                            <div class="card card-outline card-secondary shadow-none mb-0">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fas fa-history mr-2"></i>Histórico</h3>
                                </div>
                                <div class="card-body">
                                    @if ($excursao->iniciada_em)
                                        <div class="mb-2"><small class="text-muted d-block">Iniciada em</small>{{ $excursao->iniciada_em->format('d/m/Y H:i') }}</div>
                                    @endif
                                    @if ($excursao->finalizada_em)
                                        <div class="mb-2"><small class="text-muted d-block">Finalizada em</small>{{ $excursao->finalizada_em->format('d/m/Y H:i') }}</div>
                                    @endif
                                    @if ($excursao->cancelada_em)
                                        <div><small class="text-muted d-block">Cancelada em</small>{{ $excursao->cancelada_em->format('d/m/Y H:i') }}</div>
                                        @if ($excursao->motivo_cancelamento)
                                            <div class="mt-2"><small class="text-muted d-block">Motivo</small>{{ $excursao->motivo_cancelamento }}</div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                @if ($excursao->recebimentos->whereNotNull('fluxo_caixa_id')->isNotEmpty())
                    <a href="{{ route('eventos.excursoes.demonstrativo', $excursao) }}" target="_blank"
                        class="btn btn-outline-primary" title="Emitir demonstrativo de todos os pagamentos">
                        <i class="fas fa-list-alt mr-1"></i>Demonstrativo de pagamentos
                    </a>
                @else
                    <button type="button" class="btn btn-outline-secondary" disabled
                        title="Nenhum pagamento registrado">
                        <i class="fas fa-list-alt mr-1"></i>Demonstrativo de pagamentos
                    </button>
                @endif
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>
