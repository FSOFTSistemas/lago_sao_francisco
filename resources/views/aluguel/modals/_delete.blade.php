<div class="modal fade" id="deleteAluguelModal{{ $aluguel->id }}" tabindex="-1"
    aria-labelledby="deleteAluguelModalLabel{{ $aluguel->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Cabeçalho do Modal -->
            <div class="modal-header">
                <h5 class="modal-title" id="deleteAluguelModalLabel{{ $aluguel->id }}">
                    <i class="fas fa-trash"></i> Confirmar Exclusão
                </h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                @if($aluguel->tipo === 'bloqueio')
                    Tem certeza que deseja remover o bloqueio de data do espaço
                    <strong>{{ $aluguel->espaco->nome ?? 'selecionado' }}</strong>
                    ({{ \Carbon\Carbon::parse($aluguel->data_inicio)->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($aluguel->data_fim)->format('d/m/Y') }})?
                @else
                    Tem certeza que deseja excluir o Aluguel de 
                    <strong>{{ $aluguel->cliente->nome_razao_social ?? 'Cliente' }}</strong>?
                @endif
            </div>

            <div class="modal-footer">
                <form action="{{ route('aluguel.destroy', $aluguel->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Excluir</button>
                </form>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>
