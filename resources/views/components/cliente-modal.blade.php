<div class="modal fade" id="clienteModal" tabindex="-1" aria-labelledby="clienteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Selecionar Cliente</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <input type="search" class="form-control mb-3" id="clienteSearch" placeholder="Pesquisar cliente...">

                <ul class="list-group" id="clienteList" style="max-height: 400px; overflow-y:auto;">
                    @foreach($clientes as $cliente)
                    <li class="list-group-item list-group-item-action aqui" data-nome="{{ $cliente->nome_razao_social }} | {{ $cliente->cpf_cnpj }}">
                        {{ $cliente->nome_razao_social }} | {{ $cliente->cpf_cnpj }}
                    </li>
                    @endforeach
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('clienteSearch');
        const clienteList = document.getElementById('clienteList');
        const clienteInput = document.getElementById('cliente');

        searchInput.addEventListener('input', () => {
            const filter = searchInput.value.toLowerCase();
            const items = clienteList.querySelectorAll('li');

            items.forEach(item => {
                const nome = item.getAttribute('data-nome').toLowerCase();
                item.style.display = nome.includes(filter) ? '' : 'none';
            });
        });

        // clienteList.addEventListener('click', (e) => {
        //     if(e.target && e.target.nodeName === "LI") {
        //         const nomeCliente = e.target.getAttribute('data-nome');
        //         clienteInput.value = nomeCliente;
        //         const modalEl = document.getElementById('clienteModal');
        //         const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        //         modal.hide();
        //     }
        // });

        const liCliente = document.querySelectorAll('li.aqui')
        liCliente.forEach((cliente) => {
            cliente.addEventListener('click', (e) => {
                 const nomeCliente = e.target.getAttribute('data-nome');
                 clienteInput.value = nomeCliente;
                 const modal = document.getElementById('clienteModal')
                 modal.style.display = 'none';
                 const backdrop = document.querySelector('.modal-backdrop');
                 if (backdrop) {
                    backdrop.parentNode.removeChild(backdrop);
                 }
            })
        })
        
    });
</script>
