<div class="modal fade" id="deleteFuncionarioModal{{ $funcionario->id }}" tabindex="-1"
    aria-labelledby="deleteFuncionarioModalLabel{{ $funcionario->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Cabeçalho do Modal -->
            <div class="modal-header">
                <h4 class="modal-title" id="deleteFuncionarioModalLabel{{ $funcionario->id }}">
                    <i class="fas fa-trash"></i> Confirmar Exclusão
                </h4>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Corpo do Modal -->
            <div class="modal-body">
                Tem certeza que deseja excluir o funcionário
                <strong>{{ $funcionario->nome }}</strong>?
            </div>

            <!-- Rodapé do Modal -->
            <div class="modal-footer">
                <form action="{{ route('funcionario.destroy', $funcionario->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Excluir</button>
                </form>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>
