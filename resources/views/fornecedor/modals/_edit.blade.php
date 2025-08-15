<div class="modal fade" id="editFornecedorModal{{ $fornecedor->id }}" tabindex="-1"
    aria-labelledby="editFornecedorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editFornecedorModalLabel">Editar Fornecedor</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editFornecedorForm" action="{{ route('fornecedor.update', $fornecedor->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="razao_social" class="form-label">Razão Social</label>
                        <input type="text" class="form-control" name="razao_social"
                            value={{ $fornecedor->razao_social }} required>
                    </div>
                    <div class="mb-3">
                        <label for="nomeFantasia">Nome Fantasia:</label>
                        <input type="text" class="form-control" id="nomeFantasia" name="nome_fantasia"
                            value="{{ $fornecedor->nome_fantasia }}">
                    </div>
                    <div class="mb-3">
                        <label for="cnpj">CNPJ:</label>
                        <input type="text" class="form-control" id="cnpj" name="cnpj"
                            value="{{ $fornecedor->cnpj }}">
                    </div>
                    <div class="mb-3">
                        <label for="inscricaoEstadual">Inscrição Estadual:</label>
                        <input type="text" class="form-control" id="inscricaoEstadual" name="inscricao_estadual"
                            value="{{ $fornecedor->inscricao_estadual }}">
                    </div>
                    <div class="mb-3">
                        <label for="formaPagamento">Forma de Pagamento:</label>
                        <input type="text" class="form-control" id="formaPagamento" name="forma_pagamento"
                            value="{{ $fornecedor->forma_pagamento }}">
                    </div>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </form>
            </div>
        </div>
    </div>
</div>
