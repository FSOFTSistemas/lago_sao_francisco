<!-- Modal -->
<div class="modal fade" id="createContasAPagarModal" data-backdrop="static" tabindex="-1" role="dialog"
    aria-labelledby="createContasAPagarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cadastro de Contas A Pagar</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <form id="createContasAPagarForm" action="{{ route('contasAPagar.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="descricao">Descrição:</label>
                        <input type="text" class="form-control" id="descricao" name="descricao" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="valor">Valor:</label>
                            <input type="number" class="form-control" id="valor" name="valor" step="0.01"
                                min="0.01" required oninput="validarValor(this)">
                            <div class="invalid-feedback">
                                Por favor, insira um valor válido (mínimo R$ 0,01).
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="valorPago">Valor Pago:</label>
                            <input type="number" class="form-control" id="valorPago" name="valor_pago" step="0.01"
                                min="0" value="0" oninput="validarValorPago(this)">
                            <div class="invalid-feedback">
                                O valor pago não pode ser maior que o valor da conta.
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="dataVencimento">Data de Vencimento:</label>
                            <input type="date" class="form-control" id="dataVencimento" name="data_vencimento"
                                required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="dataPagamento">Data do Pagamento:</label>
                            <input type="date" class="form-control" id="dataPagamento" name="data_pagamento">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="status">Situação</label>
                        <select class="form-control" id="status" name="status" required>
                            <option value="pendente">Pendente</option>
                            <option value="finalizado">Finalizado</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="parcelas">Parcelas</label>
                        <select class="form-control" id="parcelas" name="parcelas">
                            @for ($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}">{{ $i }}x</option>
                            @endfor
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="periodo">Período entre vencimentos (em dias)</label>
                        <input type="number" name="periodo" id="periodo" class="form-control" min="1"
                            value="30">
                    </div>


                    <div class="mb-3">
                        <label for="planoDeConta">Plano de Contas</label>
                        <select class="form-control" id="planoDeConta" name="plano_de_contas_id" required>
                            <option value="">Selecione</option>
                            @foreach ($planoDeContas as $planoDeConta)
                                <option value="{{ $planoDeConta->id }}">{{ $planoDeConta->descricao }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="fornecedor">Fornecedor</label>
                        <select class="form-control" id="fornecedor" name="fornecedor_id">
                            <option value="">Selecione</option>
                            @foreach ($fornecedores as $fornecedor)
                                <option value="{{ $fornecedor->id }}">{{ $fornecedor->nome_fantasia }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                        <button type="submit" class="btn btn-primary">Criar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function validarValorPago(input) {
        const valorInput = document.getElementById('valor');
        const valorPago = parseFloat(input.value);
        const valor = parseFloat(valorInput.value);

        if (!isNaN(valorPago) && !isNaN(valor) && valorPago > valor) {
            input.classList.add('is-invalid');
        } else {
            input.classList.remove('is-invalid');
        }
    }

    function validarValor(input) {
        const valorPagoInput = document.getElementById('valorPago');
        const valor = parseFloat(input.value);
        const valorPago = parseFloat(valorPagoInput.value);

        if (!isNaN(valorPago) && !isNaN(valor) && valorPago > valor) {
            valorPagoInput.classList.add('is-invalid');
        } else {
            valorPagoInput.classList.remove('is-invalid');
        }
    }
</script>
