<div class="modal fade" id="deleteContasAPagarModal{{ $contasAPagar->id }}" tabindex="-1"
    aria-labelledby="deleteContasAPagarModalLabel{{ $contasAPagar->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Cabeçalho do Modal -->
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteContasAPagarModalLabel{{ $contasAPagar->id }}">
                    <i class="fas fa-trash"></i> Confirmar Exclusão
                </h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Corpo do Modal -->
            <div class="modal-body">
                Tem certeza que deseja excluir a conta
                <strong>{{ $contasAPagar->nome_fantasia }}</strong>?
            </div>

            <!-- Rodapé do Modal -->
            <div class="modal-footer">
                <form action="{{ route('contasAPagar.destroy', $contasAPagar->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Excluir</button>
                </form>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>
