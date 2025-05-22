<div class="modal fade" id="deleteItemModal{{ $itens->id }}" tabindex="-1"
    aria-labelledby="deleteItemModalLabel{{ $itens->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Cabeçalho do Modal -->
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteItemModalLabel{{ $itens->id }}">
                    <i class="fas fa-trash"></i> Confirmar Exclusão
                </h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                Tem certeza que deseja excluir o item
                <strong>{{ $itens->nome }}</strong>?
            </div>

            <div class="modal-footer">
                <form action="{{ route('buffet.destroy', $itens->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Excluir</button>
                </form>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>
