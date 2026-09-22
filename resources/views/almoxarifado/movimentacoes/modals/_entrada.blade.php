<div class="modal fade" id="modalNovaEntrada" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="modalNovaEntradaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content text-dark" style="color: #212529;">
            <div class="modal-header border-bottom">
                <div>
                    <h5 class="modal-title font-weight-bold text-dark mb-0" id="modalNovaEntradaLabel">
                        <i class="fas fa-arrow-circle-down text-success mr-2"></i>Registrar Entrada no Almoxarifado
                    </h5>
                    <small class="text-secondary font-weight-normal">Recebimento de mercadorias, compras e reposição de estoque.</small>
                </div>
                <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form action="{{ route('almoxarifado.movimentacoes.store') }}" method="POST" id="formNovaEntrada">
                @csrf
                <input type="hidden" name="tipo" value="entrada">

                <div class="modal-body text-dark">
                    <div class="row">
                        {{-- Seleção do Item --}}
                        <div class="col-12 col-md-8">
                            <div class="form-group">
                                <label for="entrada_item_id" class="font-weight-bold text-dark">
                                    Item do Almoxarifado: <span class="text-danger">*</span>
                                </label>
                                <select name="item_id"
                                        id="entrada_item_id"
                                        class="form-control text-dark @error('item_id') is-invalid @enderror"
                                        required
                                        onchange="atualizarInfoEntrada(this)">
                                    <option value="">Selecione o produto/material...</option>
                                    @foreach ($itens as $it)
                                        <option value="{{ $it->id }}"
                                                data-saldo="{{ (float)$it->estoque_atual }}"
                                                data-unidade="{{ $it->unidade_medida }}"
                                                data-formatado="{{ $it->estoque_formatado }}"
                                                {{ (string)old('item_id', request('item_id')) === (string)$it->id ? 'selected' : '' }}>
                                            {{ $it->nome }} (Saldo: {{ $it->estoque_formatado }} {{ $it->unidade_medida }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('item_id')
                                    <div class="invalid-feedback font-weight-bold">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Quantidade a Entrar --}}
                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label for="entrada_quantidade" class="font-weight-bold text-dark">
                                    Quantidade Recebida: <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="number"
                                           step="any"
                                           min="0.001"
                                           name="quantidade"
                                           id="entrada_quantidade"
                                           class="form-control text-dark font-weight-bold @error('quantidade') is-invalid @enderror"
                                           placeholder="0"
                                           value="{{ old('quantidade') }}"
                                           required
                                           oninput="calcularSaldoPosteriorEntrada()">
                                    <div class="input-group-append">
                                        <span class="input-group-text font-weight-bold text-dark" id="entrada_unidade_label">UN</span>
                                    </div>
                                    @error('quantidade')
                                        <div class="invalid-feedback font-weight-bold">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Card informativo de projeção de saldo --}}
                    <div class="alert alert-light border mb-3 py-2 px-3" id="box_projecao_entrada" style="display: none;">
                        <div class="d-flex align-items-center justify-content-between flex-wrap">
                            <span class="text-muted">
                                Saldo Atual: <strong class="text-dark" id="txt_saldo_anterior_entrada">0</strong>
                            </span>
                            <span class="text-success font-weight-bold">
                                + Entrando: <span id="txt_qtd_entrada">0</span>
                            </span>
                            <span class="text-dark">
                                = Novo Saldo: <strong class="text-success font-weight-bold" id="txt_saldo_posterior_entrada">0</strong>
                            </span>
                        </div>
                    </div>

                    <div class="row">
                        {{-- Data da Movimentação --}}
                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label for="entrada_data_movimentacao" class="font-weight-bold text-dark">
                                    Data / Hora de Recebimento: <span class="text-danger">*</span>
                                </label>
                                <input type="datetime-local"
                                       name="data_movimentacao"
                                       id="entrada_data_movimentacao"
                                       class="form-control text-dark @error('data_movimentacao') is-invalid @enderror"
                                       value="{{ old('data_movimentacao', now()->format('Y-m-d\TH:i')) }}"
                                       required>
                                @error('data_movimentacao')
                                    <div class="invalid-feedback font-weight-bold">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Fornecedor --}}
                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label for="entrada_fornecedor" class="font-weight-bold text-dark">Fornecedor / Origem:</label>
                                <input type="text"
                                       name="fornecedor"
                                       id="entrada_fornecedor"
                                       class="form-control text-dark @error('fornecedor') is-invalid @enderror"
                                       placeholder="Ex: Atacadão, Distribuidora XYZ..."
                                       value="{{ old('fornecedor') }}"
                                       maxlength="255">
                                @error('fornecedor')
                                    <div class="invalid-feedback font-weight-bold">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Recebido por --}}
                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label for="entrada_recebido_por" class="font-weight-bold text-dark">Recebido por:</label>
                                <input type="text"
                                       name="recebido_por"
                                       id="entrada_recebido_por"
                                       class="form-control text-dark @error('recebido_por') is-invalid @enderror"
                                       placeholder="Ex: Carlos (Almoxarife)..."
                                       value="{{ old('recebido_por', Auth::user()?->name) }}"
                                       maxlength="255">
                                @error('recebido_por')
                                    <div class="invalid-feedback font-weight-bold">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        {{-- Número de Documento / NF --}}
                        <div class="col-12 col-md-4">
                            <div class="form-group mb-md-0">
                                <label for="entrada_numero_documento" class="font-weight-bold text-dark">Nº NF / Cupom / Recibo:</label>
                                <input type="text"
                                       name="numero_documento"
                                       id="entrada_numero_documento"
                                       class="form-control text-dark @error('numero_documento') is-invalid @enderror"
                                       placeholder="Ex: NF 12345..."
                                       value="{{ old('numero_documento') }}"
                                       maxlength="100">
                                @error('numero_documento')
                                    <div class="invalid-feedback font-weight-bold">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Observações --}}
                        <div class="col-12 col-md-8">
                            <div class="form-group mb-0">
                                <label for="entrada_observacao" class="font-weight-bold text-dark">Observações / Detalhes:</label>
                                <input type="text"
                                       name="observacao"
                                       id="entrada_observacao"
                                       class="form-control text-dark @error('observacao') is-invalid @enderror"
                                       placeholder="Ex: Lote 456, entrega parcial, mercadoria conferida..."
                                       value="{{ old('observacao') }}"
                                       maxlength="1000">
                                @error('observacao')
                                    <div class="invalid-feedback font-weight-bold">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-success font-weight-bold btn-submit-movimentacao">
                        <i class="fas fa-arrow-circle-down mr-1"></i> Confirmar Entrada
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
