<div class="modal fade" id="modalExcluirCardapio{{ $cardapio->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('cardapios-excursao.destroy', $cardapio) }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-trash mr-1"></i> Excluir cardápio</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Deseja excluir o cardápio <strong>{{ $cardapio->nome }}</strong>?</p>
                    @if ($cardapio->almocos_excursao_count > 0)
                        <div class="alert alert-warning mt-3 mb-0">
                            Este cardápio já foi utilizado. Para preservar o histórico, edite-o e marque como inativo.
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger" @disabled($cardapio->almocos_excursao_count > 0)>Excluir</button>
                </div>
            </form>
        </div>
    </div>
</div>
