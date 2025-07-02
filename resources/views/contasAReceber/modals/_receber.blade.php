<div class="modal fade" id="modalReceberConta" tabindex="-1" role="dialog" aria-labelledby="modalReceberContaLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h4 class="modal-title w-100 text-center">Confirmar recebimento</h4>
                <button type="button" class="close text-white" data-bs-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body text-center">
                <p>Tem certeza que deseja marcar esta conta como <strong>Recebida</strong>?</p>
                <p id="valorReceberTexto" class="font-weight-bold text-success"></p>

                <form action="{{ route('contasAReceber.receber') }}" method="POST">
                    @csrf
                    <input type="hidden" name="pagamento_id" id="pagamento_id" value="">

                    <div class="form-group text-left">
                        <label for="valor_pago">Valor a Receber</label>
                        <input type="number" name="valor_pago" id="valor_pago" class="form-control" required
                            step="0.01" min="0.01">
                        <small class="text-muted">Valor restante: <span id="valor_restante_texto"
                                class="text-success"></span></small>
                    </div>

                    <div class="form-group text-left mt-2">
                        <label for="forma_pagamento">Forma de Pagamento</label>
                        <select name="forma_pagamento" id="forma_pagamento" class="form-control" required>
                            <option value="">Selecione</option>
                            <option value="dinheiro">Dinheiro</option>
                            <option value="pix">Pix</option>
                            <option value="cartão-crédito">Cartão de Crédito</option>
                            <option value="cartão-debito">Cartão de Débito</option>
                            <option value="transferência-bancária">Transferência</option>
                            <option value="carteira">Carteira</option>
                        </select>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-success">Confirmar</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
