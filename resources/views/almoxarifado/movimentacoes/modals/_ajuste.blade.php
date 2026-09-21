<div class="modal fade" id="modalNovoAjuste" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="modalNovoAjusteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content text-dark" style="color: #212529;">
            <div class="modal-header border-bottom">
                <div>
                    <h5 class="modal-title font-weight-bold text-dark mb-0" id="modalNovoAjusteLabel">
                        <i class="fas fa-sliders-h text-info mr-2"></i>Ajuste de Estoque / Inventário Físico
                    </h5>
                    <small class="text-secondary font-weight-normal">Acerto de saldo físico por contagem de balanço, quebra, avaria ou diferença de estoque.</small>
                </div>
                <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form action="{{ route('almoxarifado.movimentacoes.store') }}" method="POST" id="formNovoAjuste">
                @csrf
                <input type="hidden" name="tipo" value="ajuste">

                <div class="modal-body text-dark">
                    <div class="row">
                        {{-- Seleção do Item --}}
                        <div class="col-12 col-md-7">
                            <div class="form-group">
                                <label for="ajuste_item_id" class="font-weight-bold text-dark">
                                    Item a Ajustar: <span class="text-danger">*</span>
                                </label>
                                <select name="item_id"
                                        id="ajuste_item_id"
                                        class="form-control text-dark @error('item_id') is-invalid @enderror"
                                        required
                                        onchange="atualizarInfoAjuste(this)">
                                    <option value="">Selecione o produto/material...</option>
                                    @foreach ($itens as $it)
                                        <option value="{{ $it->id }}"
                                                data-saldo="{{ (float)$it->estoque_atual }}"
                                                data-unidade="{{ $it->unidade_medida }}"
                                                data-formatado="{{ $it->estoque_formatado }}"
                                                {{ (string)old('item_id', request('item_id')) === (string)$it->id ? 'selected' : '' }}>
                                            {{ $it->nome }} (Saldo Sistema: {{ $it->estoque_formatado }} {{ $it->unidade_medida }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('item_id')
                                    <div class="invalid-feedback font-weight-bold">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Motivo do Ajuste --}}
                        <div class="col-12 col-md-5">
                            <div class="form-group">
                                <label for="ajuste_motivo" class="font-weight-bold text-dark">
                                    Motivo do Ajuste: <span class="text-danger">*</span>
                                </label>
                                <select name="motivo_ajuste" id="ajuste_motivo" class="form-control text-dark" required>
                                    <option value="Balanço / Inventário Físico" {{ old('motivo_ajuste') === 'Balanço / Inventário Físico' ? 'selected' : '' }}>Balanço / Inventário Físico</option>
                                    <option value="Quebra / Avaria" {{ old('motivo_ajuste') === 'Quebra / Avaria' ? 'selected' : '' }}>Quebra / Avaria de Material</option>
                                    <option value="Validade Vencida / Descarte" {{ old('motivo_ajuste') === 'Validade Vencida / Descarte' ? 'selected' : '' }}>Validade Vencida / Descarte</option>
                                    <option value="Perda / Extravio" {{ old('motivo_ajuste') === 'Perda / Extravio' ? 'selected' : '' }}>Perda / Extravio</option>
                                    <option value="Sobra de Contagem" {{ old('motivo_ajuste') === 'Sobra de Contagem' ? 'selected' : '' }}>Sobra de Contagem</option>
                                    <option value="Correção de Cadastro" {{ old('motivo_ajuste') === 'Correção de Cadastro' ? 'selected' : '' }}>Correção de Cadastro Inicial</option>
                                    <option value="Outro" {{ old('motivo_ajuste') === 'Outro' ? 'selected' : '' }}>Outro Motivo</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Abas / Seleção do Modo de Ajuste --}}
                    <div class="card card-outline card-secondary mb-3">
                        <div class="card-header p-2 bg-light">
                            <ul class="nav nav-pills" id="tabsModoAjuste">
                                <li class="nav-item">
                                    <a class="nav-link active font-weight-bold py-1 px-3" href="#abaSaldoFisico" data-toggle="pill" onclick="definirModoAjuste('saldo_fisico')">
                                        <i class="fas fa-clipboard-check mr-1"></i> 1. Digitar Novo Saldo Contado
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link font-weight-bold py-1 px-3" href="#abaQuantidadeDireta" data-toggle="pill" onclick="definirModoAjuste('quantidade_direta')">
                                        <i class="fas fa-plus-minus mr-1"></i> 2. Digitar Quantidade (+ ou -)
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body p-3">
                            <div class="tab-content">
                                {{-- Aba 1: Novo Saldo Físico Contado --}}
                                <div class="tab-pane fade show active" id="abaSaldoFisico">
                                    <div class="row align-items-center">
                                        <div class="col-12 col-md-6">
                                            <label for="ajuste_novo_estoque" class="font-weight-bold text-dark mb-1">
                                                Novo Saldo Físico (Contagem Real na Prateleira):
                                            </label>
                                            <div class="input-group">
                                                <input type="number"
                                                       step="any"
                                                       min="0"
                                                       name="novo_estoque"
                                                       id="ajuste_novo_estoque"
                                                       class="form-control text-dark font-weight-bold"
                                                       placeholder="0"
                                                       value="{{ old('novo_estoque') }}"
                                                       oninput="calcularDiferencaSaldoFisico()">
                                                <div class="input-group-append">
                                                    <span class="input-group-text font-weight-bold text-dark ajuste_unidade_label">UN</span>
                                                </div>
                                            </div>
                                            <small class="form-text text-secondary">
                                                Digite a quantidade exata encontrada fisicamente no hotel.
                                            </small>
                                        </div>
                                        <div class="col-12 col-md-6 mt-2 mt-md-0">
                                            <div class="p-2 border rounded bg-light text-center">
                                                <div class="text-muted small">Diferença a Lançar:</div>
                                                <div class="font-weight-bold h5 mb-0 text-dark" id="txt_diferenca_apurada">-</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Aba 2: Quantidade Direta (+ ou -) --}}
                                <div class="tab-pane fade" id="abaQuantidadeDireta">
                                    <div class="row align-items-center">
                                        <div class="col-12 col-md-5">
                                            <label class="font-weight-bold text-dark mb-1">Tipo de Ajuste:</label>
                                            <select name="tipo_ajuste" id="ajuste_tipo_operacao" class="form-control text-dark" onchange="calcularDiferencaQuantidadeDireta()">
                                                <option value="reducao">Redução (-) (Quebra, perda ou descarte)</option>
                                                <option value="acrescimo">Acréscimo (+) (Sobra ou achado)</option>
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-7 mt-2 mt-md-0">
                                            <label for="ajuste_quantidade_direta" class="font-weight-bold text-dark mb-1">
                                                Quantidade a Ajustar:
                                            </label>
                                            <div class="input-group">
                                                <input type="number"
                                                       step="any"
                                                       min="0.001"
                                                       name="quantidade"
                                                       id="ajuste_quantidade_direta"
                                                       class="form-control text-dark font-weight-bold"
                                                       placeholder="0"
                                                       value="{{ old('quantidade') }}"
                                                       oninput="calcularDiferencaQuantidadeDireta()">
                                                <div class="input-group-append">
                                                    <span class="input-group-text font-weight-bold text-dark ajuste_unidade_label">UN</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Projeção de Ajuste --}}
                    <div class="alert alert-light border mb-3 py-2 px-3" id="box_projecao_ajuste" style="display: none;">
                        <div class="d-flex align-items-center justify-content-between flex-wrap">
                            <span class="text-muted">
                                Saldo Anterior: <strong class="text-dark" id="txt_saldo_anterior_ajuste">0</strong>
                            </span>
                            <span class="font-weight-bold" id="txt_variacao_ajuste">
                                Variação: 0
                            </span>
                            <span class="text-dark">
                                Novo Saldo Final: <strong class="font-weight-bold text-info" id="txt_saldo_posterior_ajuste">0</strong>
                            </span>
                        </div>
                    </div>

                    <div class="row">
                        {{-- Data da Movimentação --}}
                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label for="ajuste_data_movimentacao" class="font-weight-bold text-dark">
                                    Data / Hora do Ajuste: <span class="text-danger">*</span>
                                </label>
                                <input type="datetime-local"
                                       name="data_movimentacao"
                                       id="ajuste_data_movimentacao"
                                       class="form-control text-dark @error('data_movimentacao') is-invalid @enderror"
                                       value="{{ old('data_movimentacao', now()->format('Y-m-d\TH:i')) }}"
                                       required>
                                @error('data_movimentacao')
                                    <div class="invalid-feedback font-weight-bold">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Observação / Justificativa --}}
                        <div class="col-12 col-md-8">
                            <div class="form-group mb-0">
                                <label for="ajuste_observacao" class="font-weight-bold text-dark">
                                    Justificativa / Detalhes: <span class="text-muted font-weight-normal">(Opcional)</span>
                                </label>
                                <input type="text"
                                       name="observacao"
                                       id="ajuste_observacao"
                                       class="form-control text-dark"
                                       placeholder="Ex: Frascos danificados na queda da caixa, balanço do fim de semana..."
                                       value="{{ old('observacao') }}"
                                       maxlength="1000">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-info font-weight-bold btn-submit-movimentacao" id="btn_confirmar_ajuste">
                        <i class="fas fa-check mr-1"></i> Salvar Ajuste de Estoque
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
