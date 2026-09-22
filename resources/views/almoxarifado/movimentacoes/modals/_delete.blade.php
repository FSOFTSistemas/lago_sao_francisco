<div class="modal fade" id="modalEstornarMovimentacao{{ $mov->id }}" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="modalEstornarLabel{{ $mov->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content text-dark" style="color: #212529;">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title font-weight-bold" id="modalEstornarLabel{{ $mov->id }}">
                    <i class="fas fa-undo-alt mr-2"></i>Confirmar Estorno de Movimentação
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form action="{{ route('almoxarifado.movimentacoes.destroy', $mov->id) }}" method="POST" class="form-estornar-movimentacao">
                @csrf
                @method('DELETE')

                <div class="modal-body text-dark">
                    <p class="mb-2">
                        Tem certeza de que deseja <strong>estornar/cancelar</strong> esta movimentação?
                    </p>

                    <div class="p-3 border rounded bg-light mb-3">
                        <div class="mb-1">
                            <span class="text-muted">Item:</span>
                            <strong class="text-dark">{{ $mov->item->nome ?? 'Item #' . $mov->item_id }}</strong>
                        </div>
                        <div class="mb-1">
                            <span class="text-muted">Tipo:</span>
                            <span class="badge {{ $mov->badge_class }}">{{ $mov->tipo_formatado }}</span>
                        </div>
                        <div class="mb-1">
                            <span class="text-muted">Quantidade Movimentada:</span>
                            <strong class="text-dark">{{ $mov->quantidade_formatada }} {{ $mov->item->unidade_medida ?? '' }}</strong>
                        </div>
                        <div class="mb-1">
                            <span class="text-muted">Data / Hora:</span>
                            <span class="text-dark">{{ $mov->data_movimentacao?->format('d/m/Y H:i') }}</span>
                        </div>
                        @if($mov->observacao)
                            <div class="small text-muted mt-2 border-top pt-1">
                                <em>Obs: {{ $mov->observacao }}</em>
                            </div>
                        @endif
                    </div>

                    <div class="alert alert-warning py-2 px-3 small mb-0">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        <strong>Atenção:</strong> Ao confirmar o estorno, o saldo físico do item no almoxarifado será recalculado automaticamente para reverter esta operação.
                    </div>
                </div>

                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Não, Manter
                    </button>
                    <button type="submit" class="btn btn-danger font-weight-bold btn-confirmar-estorno">
                        <i class="fas fa-undo-alt mr-1"></i> Sim, Estornar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
