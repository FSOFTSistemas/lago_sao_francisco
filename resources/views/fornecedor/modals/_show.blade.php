<!-- resources/views/users/modals/_show.blade.php -->
<div class="modal fade" id="showFornecedor{{ $fornecedor->id }}" tabindex="-1" aria-labelledby="showFornecedorLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="showFornecedorLabel">Detalhes do Fornecedor</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Razão Social/Nome:</strong> <span id="razaoSocial">{{$fornecedor->razao_social}}</span></p>
                <p><strong>Nome Fantasia/Apelido:</strong> {{ $fornecedor->nome_fantasia ?? "Não informado" }}</p>
                <p><strong>CNPJ/CPF:</strong> {{ $fornecedor->cnpj ?? "Não informado" }}</p>
                <p><strong>Inscrição Estadual/RG:</strong> {{ $fornecedor->inscricao_estadual ?? "Não informado" }}</p>
                <p><strong>Forma de Pagamento:</strong> {{ $fornecedor->forma_pagamento ?? "Não informado" }}</p>
                <p><strong>Plano de Conta:</strong> {{ $fornecedor->planoDeConta->descricao ?? "Não informado" }}</p>
            </div>
        </div>
    </div>
</div>
