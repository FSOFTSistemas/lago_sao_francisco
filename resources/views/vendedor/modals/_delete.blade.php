<div class="modal fade" id="deleteVendedorModal{{ $vendedor->id }}" tabindex="-1" role="dialog"
    aria-labelledby="deleteVendedorLabel{{ $vendedor->id }}" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('vendedor.destroy', $vendedor->id) }}" method="POST">
            @csrf
            @method('DELETE')

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteVendedorLabel{{ $vendedor->id }}">Confirmar Exclusão</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Tem certeza que deseja excluir o vendedor <strong>{{ $vendedor->nome }}</strong>?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Excluir</button>
                </div>
            </div>
        </form>
    </div>
</div>
