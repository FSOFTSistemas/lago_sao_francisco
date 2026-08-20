<div class="modal fade" id="modalVisualizarCardapio{{ $cardapio->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-eye mr-1"></i> Visualizar cardápio de excursão</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-8">
                        <small class="text-muted d-block">Nome</small>
                        <strong>{{ $cardapio->nome }}</strong>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">Status</small>
                        <span class="badge badge-{{ $cardapio->ativo ? 'success' : 'secondary' }}">
                            {{ $cardapio->ativo ? 'Ativo' : 'Inativo' }}
                        </span>
                    </div>
                </div>

                <div class="mb-3">
                    <small class="text-muted d-block mb-1">Itens do cardápio</small>
                    <div class="border rounded bg-light p-3">
                        <ul class="mb-0 pl-3">
                            @foreach (preg_split('/\r\n|\r|\n/', $cardapio->descricao_cardapio, -1, PREG_SPLIT_NO_EMPTY) as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div>
                    <small class="text-muted d-block">Valor por pessoa</small>
                    <strong class="h5 text-success">R$ {{ number_format((float) $cardapio->valor_por_pessoa, 2, ',', '.') }}</strong>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>
