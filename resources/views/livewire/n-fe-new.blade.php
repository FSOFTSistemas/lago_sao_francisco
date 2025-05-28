<div>
    <!-- Cabeçalho da nota -->
    <div class="border-bottom pb-3 mb-4">
        <div class="row g-3">
            <div class="col-md-3">
                <label for="empresa" class="form-label">Empresa</label>
                <input type="text" id="empresa" wire:model="empresa" disabled class="form-control" />
            </div>
            <div class="col-md-3">
                <label for="cliente" class="form-label">Cliente</label>
                <input type="text" id="cliente" wire:model="cliente.nome_razao_social" readonly
                    class="form-control" placeholder="Clique para selecionar" style="cursor: pointer;"
                    wire:click="abrirModalCliente" />
            </div>
            <div class="col-md-3">
                <label for="numero" class="form-label">Número da Nota</label>
                <input type="text" id="numero" wire:model="numero"
                    class="form-control fw-bold text-danger fs-3" />
            </div>
            <div class="col-md-2">
                <label for="serie" class="form-label">Série</label>
                <input type="text" id="serie" wire:model="serie" class="form-control" />
            </div>
            <div class="col-md-2">
                <label for="data_emissao" class="form-label">Data de Emissão</label>
                <input type="date" id="data_emissao" wire:model="data_emissao" class="form-control" />
            </div>
            <div class="col-md-2">
                <label for="data_saida" class="form-label">Data de Saída</label>
                <input type="date" id="data_saida" wire:model="data_saida" class="form-control" />
            </div>
            <div class="col-md-2">
                <label for="tipo_nota" class="form-label">Tipo de Nota</label>
                <select id="tipo_nota" wire:model="tipo_nota" class="form-select form-control">
                    <option value="entrada">Entrada</option>
                    <option value="saida">Saída</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="finalidade" class="form-label">Finalidade da Nota</label>
                <select id="finalidade" wire:model="finalidade" class="form-select form-control">
                    <option value="1">NF-e normal</option>
                    <option value="2">NF-e complementar</option>
                    <option value="3">NF-e de ajuste</option>
                    <option value="4">Devolução de mercadoria</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="forma_pagamento" class="form-label">Forma de Pagamento</label>
                <select id="forma_pagamento" wire:model="forma_pagamento" class="form-select form-control">
                    <option value="0">Pagamento à Vista</option>
                    <option value="1">Pagamento a Prazo</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Botão para adicionar novo item -->
    <button wire:click="openModal" class="btn btn-primary mb-3">
        Adicionar Novo Item
    </button>

    <!-- Abas do body -->
    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $aba == 'itens' ? 'active' : '' }}" wire:click="$set('aba', 'itens')"
                type="button" role="tab">Itens</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $aba == 'faturamento' ? 'active' : '' }}" wire:click="$set('aba', 'faturamento')"
                type="button" role="tab">Faturamento</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $aba == 'complementares' ? 'active' : '' }}"
                wire:click="$set('aba', 'complementares')" type="button" role="tab">Informações
                Complementares</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $aba == 'referenciada' ? 'active' : '' }}"
                wire:click="$set('aba', 'referenciada')" type="button" role="tab">NFe Referenciada</button>
        </li>
    </ul>

    <div class="tab-content">
        @if ($aba == 'itens')
            <div role="tabpanel" class="tab-pane active">
                @if (count($itens) > 0)
                    @component('components.data-table', [
                        'responsive' => [
                            ['responsivePriority' => 1, 'targets' => 0],
                            ['responsivePriority' => 2, 'targets' => 1],
                            ['responsivePriority' => 3, 'targets' => 2],
                            ['responsivePriority' => 4, 'targets' => -1],
                        ],
                        'itemsPerPage' => 10,
                        'showTotal' => false,
                        'valueColumnIndex' => 4,
                    ])
                        <thead class="bg-primary text-white">
                            <tr>
                                <th>Produto</th>
                                <th>Qtde</th>
                                <th>CST</th>
                                <th>CFOP</th>
                                <th>Valor Unitário</th>
                                <th>SubTotal</th>
                                <th>Desconto</th>
                                <th>Acrescimo</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($itens as $item)
                                <tr>
                                    <td>{{ $item['produto'] }}</td>
                                    <td>{{ $item['quantidade'] }}</td>
                                    <td>{{ $item['cst'] ?? '' }}</td>
                                    <td>{{ $item['cfop'] ?? '' }}</td>
                                    <td>{{ number_format($item['valor_unitario'], 2, ',', '.') }}</td>
                                    <td>{{ number_format($item['subtotal'] ?? 0, 2, ',', '.') }}</td>
                                    <td>{{ number_format($item['desconto'] ?? 0, 2, ',', '.') }}</td>
                                    <td>{{ number_format($item['acrescimo'] ?? 0, 2, ',', '.') }}</td>
                                    <td>{{ number_format($item['total'], 2, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    @endcomponent
                    <div class="mt-3 p-3 border rounded bg-light">
                        <div class="row text-end">
                            <div class="col-md-3 offset-md-6">
                                <strong>Subtotal:</strong> R$ {{ number_format(collect($itens)->sum('subtotal'), 2, ',', '.') }}
                            </div>
                            <div class="col-md-3">
                                <strong>Descontos:</strong> R$ {{ number_format(collect($itens)->sum('desconto'), 2, ',', '.') }}
                            </div>
                            <div class="col-md-3 offset-md-6">
                                <strong>Acréscimos:</strong> R$ {{ number_format(collect($itens)->sum('acrescimo'), 2, ',', '.') }}
                            </div>
                            <div class="col-md-3">
                                <strong>Total da Nota:</strong>
                                R$
                                {{ number_format(collect($itens)->sum(fn($i) => $i['subtotal'] - $i['desconto'] + $i['acrescimo']), 2, ',', '.') }}
                            </div>
                        </div>
                    </div>
                @else
                    <p>Nenhum item adicionado.</p>
                @endif
            </div>
        @elseif($aba == 'faturamento')
            <div role="tabpanel" class="tab-pane active">
                <p>Conteúdo de faturamento aqui...</p>
            </div>
        @elseif($aba == 'complementares')
            <div role="tabpanel" class="tab-pane active">
                <p>Informações complementares aqui...</p>
            </div>
        @elseif($aba == 'referenciada')
            <div role="tabpanel" class="tab-pane active">
                <p>NFe referenciada aqui...</p>
            </div>
        @endif
    </div>

    <!-- Modal para adicionar item -->
    <div class="modal fade @if ($modalAberto) show d-block @endif" tabindex="-1"
        style="@if ($modalAberto) background-color: rgba(0,0,0,0.5); @else display:none; @endif"
        aria-modal="true" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Adicionar Item</h5>
                    <button type="button" class="btn-close" wire:click="fecharModal"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-3">
                            <label for="produto" class="form-label">Produto</label>
                            <input type="text" id="produto" wire:model="novoItem.produto" readonly
                                class="form-control" placeholder="Clique para selecionar" style="cursor: pointer;"
                                wire:click="abrirModalProduto" />
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="quantidade" class="form-label">Quantidade</label>
                                <input type="number" id="quantidade" wire:model="novoItem.quantidade" wire:blur="atualizarTotaisItem"
                                    class="form-control" min="1" />
                            </div>
                            <div class="col-md-4">
                                <label for="valor_unitario" class="form-label">Valor Unitário</label>
                                <input type="number" id="valor_unitario" wire:model="novoItem.valor_unitario" wire:blur="atualizarTotaisItem"
                                    class="form-control" step="0.01" />
                            </div>
                            <div class="col-md-4">
                                <label for="subtotal" class="form-label">Subtotal</label>
                                <input type="text" id="subtotal" class="form-control" value="{{ number_format($novoItem['subtotal'], 2, ',', '.') }}" readonly />
                            </div>
                        </div>

                        <hr>

                        <button class="btn btn-link" type="button" wire:click="$toggle('mostrarTributaria')">
                            Dados Tributários @if ($mostrarTributaria)
                                &#9650;
                            @else
                                &#9660;
                            @endif
                        </button>

                        @if ($mostrarTributaria)
                            <div class="row g-3 mt-2">
                                <div class="col-md-3">
                                    <label for="cst" class="form-label">CST</label>
                                    <input type="text" id="cst" wire:model="novoItem.cst"
                                        class="form-control" />
                                </div>
                                <div class="col-md-3">
                                    <label for="cfop" class="form-label">CFOP</label>
                                    <input type="text" id="cfop" wire:model="novoItem.cfop"
                                        class="form-control" />
                                </div>
                                <div class="col-md-3">
                                    <label for="csosn" class="form-label">CSOSN</label>
                                    <input type="text" id="csosn" wire:model="novoItem.csosn"
                                        class="form-control" />
                                </div>
                                <div class="col-md-3">
                                    <label for="aliquota" class="form-label">Alíquota (%)</label>
                                    <input type="number" id="aliquota" wire:model="novoItem.aliquota"
                                        class="form-control" step="0.01" />
                                </div>
                                <div class="col-md-4">
                                    <label for="valor_icms" class="form-label">Valor do ICMS</label>
                                    <input type="number" id="valor_icms" wire:model="novoItem.valor_icms"
                                        class="form-control" step="0.01" />
                                </div>
                                <div class="col-md-4">
                                    <label for="base_calculo" class="form-label">Base de Cálculo</label>
                                    <input type="number" id="base_calculo" wire:model="novoItem.base_calculo"
                                        class="form-control" step="0.01" />
                                </div>
                            </div>
                        @endif
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" wire:click="salvarItem" class="btn btn-success">Salvar</button>
                    <button type="button" wire:click="fecharModal" class="btn btn-secondary">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para selecionar produto -->
    <div class="modal fade @if ($modalProdutoAberto) show d-block @endif" tabindex="-1"
        style="@if ($modalProdutoAberto) background-color: rgba(0,0,0,0.5); @else display:none; @endif"
        aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Selecionar Produto</h5>
                    <button type="button" class="btn-close" wire:click="fecharModalProduto"></button>
                </div>
                <div class="modal-body">
                    <input type="text" wire:model="buscaProduto" placeholder="Buscar produto..."
                        class="form-control mb-3" />

                    <div style="max-height: 300px; overflow-y: auto;">
                        @foreach ($produtos as $produto)
                            <div wire:click="selecionarProduto('{{ $produto['id'] }}')" class="p-2 border-bottom"
                                style="cursor:pointer;">
                                {{ $produto['descricao'] }}
                            </div>
                        @endforeach
                        @if (count($produtos) === 0)
                            <p>Nenhum produto encontrado.</p>
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" wire:click="fecharModalProduto" class="btn btn-secondary">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para selecionar cliente -->
    <div class="modal fade @if ($modalClienteAberto) show d-block @endif" tabindex="-1"
        style="@if ($modalClienteAberto) background-color: rgba(0,0,0,0.5); @else display:none; @endif"
        aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Selecionar Cliente</h5>
                    <button type="button" class="btn-close" wire:click="fecharModalCliente"></button>
                </div>
                <div class="modal-body">
                    <input type="text" wire:model="buscaCliente" placeholder="Buscar cliente..."
                        class="form-control mb-3" />
                    <div style="max-height: 300px; overflow-y: auto;">
                        @foreach ($clientes as $cliente)
                            <div wire:click="selecionarCliente('{{ $cliente['id'] }}')" class="p-2 border-bottom"
                                style="cursor:pointer;">
                                {{ $cliente['nome_razao_social'] }}
                            </div>
                        @endforeach
                        @if (count($clientes) === 0)
                            <p>Nenhum cliente encontrado.</p>
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" wire:click="fecharModalCliente" class="btn btn-secondary">Fechar</button>
                </div>
            </div>
        </div>
    </div>
</div>
