<div class="modal fade" id="produtoModal" tabindex="-1" aria-labelledby="produtoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-scrollable"> <!-- modal-md para largura média -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Selecionar Itens</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <input type="search" class="form-control mb-3" id="produtoSearch" placeholder="Pesquisar produto...">

                <hr class="my-3">
                <h5 class="mb-12 text-center">Itens</h5>

                {{-- Formulário de itens --}}
                <div class="mb-3">
                    <label for="produtoInput" class="form-label">Produto</label>
                    <div class="input-group px-2">
                        <input type="text" class="form-control" id="produtoInput" placeholder="Selecione um produto"
                            readonly>
                        <button class="btn btn-outline-secondary ms-2" type="button" data-toggle="modal"
                            data-target="#produtoModal">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>

                </div>


                <div class="row g-3 align-items-end">
                    <!-- g-3 para gap entre colunas, align-items-end para alinhar labels -->
                    <div class="col-3">
                        <label for="quantidade" class="form-label">Qtd.</label>
                        <input type="number" class="form-control" id="quantidade" min="1" value="1">
                    </div>
                    <div class="col-3">
                        <label for="valor" class="form-label">Valor (R$)</label>
                        <input type="text" class="form-control" id="valor" value="0">
                    </div>
                    <div class="col-3">
                        <label for="desconto" class="form-label">Dsct. (R$)</label>
                        <input type="text" class="form-control" id="desconto" value="0">
                    </div>
                    <div class="col-3">
                        <label for="total" class="form-label">Total (R$)</label>
                        <input type="text" class="form-control" id="total" value="0" readonly>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary">+ Adicionar</button>
            </div>
        </div>
    </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('produtoSearch');
        const produtoList = document.getElementById('produtoList');
        const produtoInput = document.getElementById('produto');

        searchInput.addEventListener('input', () => {
            const filter = searchInput.value.toLowerCase();
            const items = produtoList.querySelectorAll('li');

            items.forEach(item => {
                const nome = item.getAttribute('data-nome').toLowerCase();
                item.style.display = nome.includes(filter) ? '' : 'none';
            });
        });

        const liCliente = document.querySelectorAll('li.aqui')
        liCliente.forEach((produto) => {
            produto.addEventListener('click', (e) => {
                const nomeCliente = e.target.getAttribute('data-nome');
                produtoInput.value = nomeCliente;
                const modal = document.getElementById('produtoModal')
                modal.style.display = 'none';
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) {
                    backdrop.parentNode.removeChild(backdrop);
                }
            })
        })

    });
</script>