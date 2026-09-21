<div class="modal fade" id="modalExcluirCategoria{{ $categoria->id }}" tabindex="-1" role="dialog" aria-labelledby="modalExcluirCategoriaLabel{{ $categoria->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content text-dark" style="color: #212529;">
            <div class="modal-header border-bottom">
                <h5 class="modal-title font-weight-bold text-danger" id="modalExcluirCategoriaLabel{{ $categoria->id }}">
                    <i class="fas fa-trash-alt mr-1"></i> Confirmar Exclusão
                </h5>
                <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body text-dark">
                @if (($categoria->itens_count ?? 0) > 0)
                    <div class="alert alert-warning mb-3 text-dark font-weight-normal border">
                        <i class="fas fa-exclamation-triangle mr-1 text-warning"></i>
                        <strong class="text-dark">Atenção:</strong> Esta categoria possui <strong class="text-dark">{{ $categoria->itens_count }}</strong> {{ $categoria->itens_count == 1 ? 'item vinculado' : 'itens vinculados' }} no almoxarifado.
                    </div>
                    <p class="text-dark mb-0">Por segurança e para preservar o histórico do estoque, categorias com itens não podem ser excluídas diretamente. Recomendamos que você <strong>inative</strong> esta categoria na edição.</p>
                @else
                    <p class="text-dark mb-2">Tem certeza que deseja excluir permanentemente a categoria <strong class="text-dark">"{{ $categoria->nome }}"</strong>?</p>
                    <p class="text-secondary small mb-0 font-weight-normal">Esta ação não poderá ser desfeita.</p>
                @endif
            </div>

            <div class="modal-footer border-top">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Cancelar
                </button>

                @if (($categoria->itens_count ?? 0) === 0)
                    <form action="{{ route('almoxarifado.categorias.destroy', $categoria->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash-alt mr-1"></i> Confirmar Exclusão
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
