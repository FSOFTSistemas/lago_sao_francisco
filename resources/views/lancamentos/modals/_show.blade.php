<!-- Modal de Visualização de Lançamento -->
<div class="modal fade" id="showLancamentoModal{{ $lancamento->id }}" tabindex="-1" role="dialog" aria-labelledby="showLancamentoModalLabel{{ $lancamento->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="showLancamentoModalLabel{{ $lancamento->id }}">
                    Detalhes do Lançamento #{{ $lancamento->id }}
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <dl class="dl-horizontal">
                            <dt>ID do Lançamento</dt>
                            <dd>{{ $lancamento->id }}</dd>

                            <dt>Descrição</dt>
                            <dd>{{ $lancamento->descricao }}</dd>

                            <dt>Data</dt>
                            <dd>{{ \Carbon\Carbon::parse($lancamento->data)->format('d/m/Y') }}</dd>

                            <dt>Valor</dt>
                            <dd>R$ {{ number_format($lancamento->valor, 2, ',', '.') }}</dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <dl class="dl-horizontal">
                            <dt>Tipo de Movimento</dt>
                            <dd>
                                @if ($lancamento->tipo == 'entrada')
                                    <span class="badge badge-success">Entrada</span>
                                @else
                                    <span class="badge badge-danger">Saída</span>
                                @endif
                            </dd>

                            <dt>Status</dt>
                            <dd>
                                @if ($lancamento->status == 'finalizado')
                                    <span class="badge badge-primary">Finalizado</span>
                                @else
                                    <span class="badge badge-warning">Pendente</span>
                                @endif
                            </dd>

                            <dt>ID da Conta Corrente</dt>
                            <dd>{{ $lancamento->conta_corrente_id }}</dd>

                            <dt>ID do Banco</dt>
                            <dd>{{ $lancamento->banco_id }}</dd>
                        </dl>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-12">
                         <dl class="dl-horizontal">
                            <dt>ID da Empresa</dt>
                            <dd>{{ $lancamento->empresa_id }}</dd>

                            <dt>Criado em</dt>
                            <dd>{{ \Carbon\Carbon::parse($lancamento->created_at)->format('d/m/Y H:i:s') }}</dd>

                            <dt>Atualizado em</dt>
                            <dd>{{ \Carbon\Carbon::parse($lancamento->updated_at)->format('d/m/Y H:i:s') }}</dd>
                         </dl>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>
