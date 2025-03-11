<!-- resources/views/users/modals/_show.blade.php -->
<div class="modal fade" id="showAdiantamento{{ $adiantamento->id }}" tabindex="-1" aria-labelledby="showAdiantamentoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="showAdiantamentoLabel">Detalhes das Contas A Pagar</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Funcionário:</strong> <span id="funcionario">{{$adiantamento->funcionario->nome}}</span></p>
                <p><strong>Valor:</strong> <span id="valor">{{$adiantamento->valor}}</span></p>
                <p><strong>Data do Adiantamento:</strong> <span id="data">{{$adiantamento->data}}</span></p>
                <p><strong>Descrição:</strong> <span id="descricao">{{$adiantamento->descricao}}</span></p>
                <p><strong>Situação:</strong> <span id="status">{{$adiantamento->status}}</span></p>
                <p><strong>Empresa:</strong> <span id="empresa">{{$adiantamento->empresa_id}}</span></p>
            </div>
        </div>
    </div>
</div>
