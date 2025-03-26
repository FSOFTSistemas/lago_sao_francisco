<!-- resources/views/users/modals/_show.blade.php -->
<div class="modal fade" id="showFornecedor{{ $fornecedor->id }}" tabindex="-1" aria-labelledby="showFornecedorLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="showFornecedorLabel">Detalhes do Fornecedor</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Razão Social:</strong> <span id="razaoSocial">{{$fornecedor->razao_social}}</span></p>
                <p><strong>Nome Fantasia:</strong> <span id="nomeFantasia"></span>{{$fornecedor->nome_fantasia}}</p>
                <p><strong>CNPJ:</strong> <span id="cnpj"></span>{{$fornecedor->cnpj}}</p>
                <p><strong>Inscrição Estadual:</strong> <span id="inscricaoEstadual"></span>{{$fornecedor->inscricao_estadual}}</p>
            </div>
        </div>
    </div>
</div>
