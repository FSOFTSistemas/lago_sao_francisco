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
                        <input type="text" id="produto" wire:model="novoItem.produto" readonly class="form-control"
                            placeholder="Clique para selecionar" style="cursor: pointer;"
                            wire:click="abrirModalProduto" />
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="quantidade" class="form-label">Quantidade</label>
                            <input type="number" id="quantidade" wire:model="novoItem.quantidade"
                                wire:blur="atualizarTotaisItem" class="form-control" min="1" />
                        </div>
                        <div class="col-md-4">
                            <label for="valor_unitario" class="form-label">Valor Unitário</label>
                            <input type="number" id="valor_unitario" wire:model="novoItem.valor_unitario"
                                wire:blur="atualizarTotaisItem" class="form-control" step="0.01" />
                        </div>
                        <div class="col-md-4">
                            <label for="subtotal" class="form-label">Subtotal</label>
                            <input type="text" id="subtotal" class="form-control"
                                value="{{ number_format($novoItem['subtotal'], 2, ',', '.') }}" readonly />
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
                                <input type="text" id="cst" wire:model="novoItem.cst" class="form-control" />
                            </div>
                            <div class="col-md-3">
                                <label for="cfop" class="form-label">CFOP</label>
                                <input type="text" id="cfop" wire:model="novoItem.cfop" class="form-control" />
                            </div>
                            <div class="col-md-3">
                                <label for="csosn" class="form-label">CSOSN</label>
                                <input type="text" id="csosn" wire:model="novoItem.csosn" class="form-control" />
                            </div>
                            <div class="col-md-3">
                                <label for="aliquota" class="form-label">Alíquota (%)</label>
                                <input type="number" id="aliquota" wire:model="novoItem.aliquota" class="form-control"
                                    step="0.01" />
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
