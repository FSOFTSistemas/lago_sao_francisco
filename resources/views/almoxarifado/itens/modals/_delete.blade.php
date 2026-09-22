<div class="modal fade" id="modalExcluirItem{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="modalExcluirItemLabel{{ $item->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content text-dark" style="color: #212529;">
            <div class="modal-header border-bottom">
                <h5 class="modal-title font-weight-bold text-danger" id="modalExcluirItemLabel{{ $item->id }}">
                    <i class="fas fa-trash-alt mr-1"></i> Confirmar Exclusão de Item
                </h5>
                <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body text-dark">
                @if (($item->movimentacoes_count ?? 0) > 0)
                    <div class="alert alert-warning mb-3 text-dark font-weight-normal border">
                        <i class="fas fa-exclamation-triangle mr-1 text-warning"></i>
                        <strong class="text-dark">Atenção:</strong> Este item possui <strong class="text-dark">{{ $item->movimentacoes_count }}</strong> {{ $item->movimentacoes_count == 1 ? 'movimentação registrada' : 'movimentações registradas' }} no histórico do almoxarifado.
                    </div>
                    <p class="text-dark mb-0">Para manter a fidelidade contábil e o histórico físico de recebimentos e usos, itens com movimentação não podem ser excluídos. Recomendamos que você <strong>inative</strong> este item na edição.</p>
                @else
                    <p class="text-dark mb-2">Tem certeza que deseja excluir permanentemente o item <strong class="text-dark">"{{ $item->nome }}"</strong>?</p>
                    <p class="text-secondary small mb-0 font-weight-normal">Esta ação não poderá ser desfeita.</p>
                @endif
            </div>

            <div class="modal-footer border-top">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Cancelar
                </button>

                @if (($item->movimentacoes_count ?? 0) === 0)
                    <form action="{{ route('almoxarifado.itens.destroy', $item->id) }}" method="POST" class="d-inline form-excluir-item">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-confirmar-exclusao">
                            <i class="fas fa-trash-alt mr-1"></i> Confirmar Exclusão
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
