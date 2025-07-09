

<div class="modal fade" id="showVendedor{{ $vendedor->id }}" tabindex="-1" role="dialog" aria-labelledby="showVendedorLabel{{ $vendedor->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="showVendedorLabel{{ $vendedor->id }}">Detalhes do Vendedor</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p><strong>Nome:</strong> {{ $vendedor->nome }}</p>
                <p><strong>Email:</strong> {{ $vendedor->email }}</p>
                <p><strong>Telefone:</strong> {{ $vendedor->telefone }}</p>
                <p><strong>CPF:</strong> {{ $vendedor->cpf }}</p>
                <p><strong>Endereço:</strong> {{ $vendedor->endereco }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>