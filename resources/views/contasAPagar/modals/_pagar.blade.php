<!-- Modal de Pagamento -->
<div class="modal fade" id="pagarContasAPagarModal{{ $contasAPagar->id }}" tabindex="-1" role="dialog"
    aria-labelledby="pagarContasAPagarModalLabel{{ $contasAPagar->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form action="{{ route('contasAPagar.pagar', $contasAPagar->id) }}" method="POST">
            @csrf
            @method('POST')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pagarContasAPagarModalLabel{{ $contasAPagar->id }}">
                        Pagar Conta
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <p><strong>Descrição:</strong> {{ $contasAPagar->descricao }}</p>
                    <p><strong>Valor:</strong> R$ {{ number_format($contasAPagar->valor, 2, ',', '.') }}</p>

                    <div class="form-group">
                        <label for="data_pagamento">Data do Pagamento</label>
                        <input type="date" name="data_pagamento" class="form-control" required
                            value="{{ now()->toDateString() }}">
                    </div>

                    <div class="form-group">
                        <label for="valor_pago">Valor Pago</label>
                        <input type="number" step="0.01" min="0.01" name="valor_pago" class="form-control"
                            value="{{ $contasAPagar->valor }}" required>
                    </div>

                    <div class="form-group">
                        <label for="forma_pagamento">Forma de Pagamento</label>
                        <select name="forma_pagamento" class="form-control">
                            <option value="dinheiro">Dinheiro</option>
                            <option value="pix">PIX</option>
                            <option value="debito">Cartão Débito</option>
                            <option value="credito">Cartão Crédito</option>
                            <option value="boleto">Boleto</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Confirmar Pagamento</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </form>
    </div>
</div>
