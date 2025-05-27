<div class="p-4 bg-white shadow rounded">
    <button class="btn btn-secondary mb-3">Voltar</button>

    {{-- Cabeçalho --}}
    <h5 class="mb-4 font-weight-bold text-center">Cabeçalho</h5>

    <div class="row mb-3">
       <div class="col-md-12">
            <label for="cliente">Cliente</label>
            <div class="input-group" style="gap: 8px; width: 100%;">
                <input type="text" class="form-control flex-grow-1" id="cliente" placeholder="Selecione o cliente" readonly>
                <button class="btn btn-outline-secondary" type="button" data-toggle="modal" data-target="#clienteModal">
                <i class="fas fa-search"></i>
                </button>
            </div>
       </div>

    </div>

    <div>
    @include('components.cliente-modal')
    </div>

    

    <div class="row mb-3">
        <div class="col-md-4">
            <label for="finalidade">Finalidade</label>
            <select class="form-control" id="finalidade">
                <option value="1">Venda</option>
                <option value="2">Devolução</option>
                <option value="3">Transferência</option>
                {{-- Adicione mais se necessário --}}
            </select>
        </div>

        <div class="col-md-4">
            <label for="cfop_id">CFOP</label>
            <select class="form-control" id="cfop_id">
                <option value="5101">5101</option>
                <option value="5102">5102</option>
                {{-- Popule dinamicamente conforme necessário --}}
            </select>
        </div>

        <div class="col-md-4">
            <label for="cfop_descricao">Descrição CFOP</label>
            <select class="form-control" id="cfop_descricao">
                <option>5101 | VENDA DE PRODUCAO DO ESTABELECIMENTO</option>
                <option>5102 | VENDA MERC. ADQ. DE TERCEIROS</option>
                {{-- Vinculado com o CFOP acima --}}
            </select>
        </div>
    </div>

    <!-- <hr class="my-4">
<h5 class="mb-3 text-center">Itens</h5>

{{-- Formulário de itens --}}
<div class="row mb-3">
    <div class="col-md-4">
        <label>Produto</label>
        <select class="form-control">
            <option>--Escolha um produto--</option>
            <option>FARDO BOLACHA MARISA</option>
            {{-- + outros produtos --}}
        </select>
    </div>
    <div class="col-md-2">
        <label>Qtd.</label>
        <input type="number" class="form-control" min="1" value="1">
    </div>
    <div class="col-md-2">
        <label>Valor</label>
        <input type="text" class="form-control" value="0">
    </div>
    <div class="col-md-2">
        <label>Dsct. (R$)</label>
        <input type="text" class="form-control" value="0">
    </div>
    <div class="col-md-2">
        <label>Total</label>
        <input type="text" class="form-control" value="0" readonly>
    </div>
</div> -->

<div class="mb-3 text-center">
    <button class="btn btn-primary px-5" type="button" data-toggle="modal" data-target="#produtoModal">+ Adicionar Itens</button>
</div>

<div>
    @include('components.produto-modal')
</div>

{{-- Tabela de Itens Adicionados --}}
<div class="table-responsive">
    <table class="table table-bordered table-striped text-center">
        <thead class="bg-primary text-white">
            <tr>
                <th>Id</th>
                <th>Produto</th>
                <th>Quantidade</th>
                <th>Unitário</th>
                <th>Desconto</th>
                <th>Total</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>#42</td>
                <td>FARDO BOLACHA MARISA</td>
                <td>1</td>
                <td>R$ 20.00</td>
                <td>R$ 0.00</td>
                <td>R$ 20.00</td>
                <td>
                    <button class="btn btn-sm btn-danger">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            </tr>
            {{-- + outros itens --}}
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="text-end"><strong>Soma produtos:</strong></td>
                <td colspan="2" class="text-start"><strong>R$ 20.00</strong></td>
            </tr>
        </tfoot>
    </table>
</div>

{{-- Informações Complementares --}}
<div class="mt-4">
    <label>Informações Complementares</label>
    <textarea class="form-control" placeholder="Opcional..." rows="4"></textarea>
</div>

</div>
