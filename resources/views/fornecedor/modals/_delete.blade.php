<div class="modal fade" id="deleteFornecedorModal{{ $fornecedor->id }}" tabindex="-1"
    aria-labelledby="deleteFornecedorModalLabel{{ $fornecedor->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Cabeçalho do Modal -->
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteFornecedorModalLabel{{ $fornecedor->id }}">
                    <i class="fas fa-trash"></i> Confirmar Exclusão
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Corpo do Modal -->
            <div class="modal-body">
                Tem certeza que deseja excluir o fornecedor
                <strong>{{ $fornecedor->nome_fantasia }}</strong>?
            </div>

            <!-- Rodapé do Modal -->
            <div class="modal-footer">
                <form action="{{ route('fornecedor.destroy', $fornecedor->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Excluir</button>
                </form>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>
