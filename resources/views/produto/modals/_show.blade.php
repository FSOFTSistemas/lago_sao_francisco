<!-- resources/views/users/modals/_show.blade.php -->
<div class="modal fade" id="showProduto{{ $produto->id }}" tabindex="-1" aria-labelledby="showProdutoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="showProdutoLabel">Detalhes do Produto</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Descrição:</strong> <span id="descricao">{{$produto->descricao}}</span></p>
                <p><strong>Tipo:</strong> <span id="tipo">{{$produto->tipo}}</span></p>
                <p><strong>Situação:</strong> <span id="situacao">{{$produto->situacao}}</span></p>
                <p><strong>EAN:</strong> <span id="ean">R${{$produto->ean}}</span></p>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Preço de Custo:</strong> <span id="precoCusto">{{$produto->preco_custo}}</span></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Preço de Venda:</strong> <span id="precoVenda">{{$produto->preco_venda}}</span></p>
                    </div>
                </div>

                <p><strong>NCM:</strong> <span id="ncm">{{$produto->ncm}}</span></p>
                <p><strong>CST:</strong> <span id="cst">{{$produto->cst}}</span></p>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>CFOP Interno:</strong> <span id="cfopInterno">{{$produto->cfop_interno}}</span></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>CFOP Externo:</strong> <span id="cfopExterno">{{$produto->cfop_externo}}</span></p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Alíquota:</strong> <span id="aliquota">{{$produto->aliquota}}</span></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>CSOSN:</strong> <span id="csosn">{{$produto->csosn}}</span></p>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</div>
