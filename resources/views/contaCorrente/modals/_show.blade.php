<!-- resources/views/users/modals/_show.blade.php -->
<div class="modal fade" id="showContaCorrente{{ $contaCorrente->id }}" tabindex="-1" aria-labelledby="showContaCorrenteLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="showContaCorrenteLabel">Detalhes das Contas Correntes</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Titular:</strong> <span id="titular">{{$contaCorrente->titular}}</span></p>
                <p><strong>Número da Conta:</strong> <span id="numeroConta">{{$contaCorrente->numero_conta}}</span></p>
                <p><strong>Descrição:</strong> <span id="descricao">{{$contaCorrente->descricao}}</span></p>
                <p><strong>Banco:</strong> <span id="banco">{{$contaCorrente->banco->descricao}}</span></p>
                <p><strong>Saldo:</strong> <span id="saldo">R${{$contaCorrente->saldo}}</span></p>
            </div>
        </div>
    </div>
</div>
