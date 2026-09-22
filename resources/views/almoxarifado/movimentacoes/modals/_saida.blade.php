<div class="modal fade" id="modalNovaSaida" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="modalNovaSaidaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content text-dark" style="color: #212529;">
            <div class="modal-header border-bottom">
                <div>
                    <h5 class="modal-title font-weight-bold text-dark mb-0" id="modalNovaSaidaLabel">
                        <i class="fas fa-arrow-circle-up text-danger mr-2"></i>Registrar Saída do Almoxarifado
                    </h5>
                    <small class="text-secondary font-weight-normal">Requisição para uso interno da governança, manutenção, limpeza ou setores do hotel.</small>
                </div>
                <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form action="{{ route('almoxarifado.movimentacoes.store') }}" method="POST" id="formNovaSaida">
                @csrf
                <input type="hidden" name="tipo" value="saida">

                <div class="modal-body text-dark">
                    <div class="row">
                        {{-- Seleção do Item --}}
                        <div class="col-12 col-md-8">
                            <div class="form-group">
                                <label for="saida_item_id" class="font-weight-bold text-dark">
                                    Item a Retirar: <span class="text-danger">*</span>
                                </label>
                                <select name="item_id"
                                        id="saida_item_id"
                                        class="form-control text-dark @error('item_id') is-invalid @enderror"
                                        required
                                        onchange="atualizarInfoSaida(this)">
                                    <option value="">Selecione o produto/material...</option>
                                    @foreach ($itens as $it)
                                        <option value="{{ $it->id }}"
                                                data-saldo="{{ (float)$it->estoque_atual }}"
                                                data-unidade="{{ $it->unidade_medida }}"
                                                data-formatado="{{ $it->estoque_formatado }}"
                                                {{ (string)old('item_id', request('item_id')) === (string)$it->id ? 'selected' : '' }}>
                                            {{ $it->nome }} (Saldo Atual: {{ $it->estoque_formatado }} {{ $it->unidade_medida }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('item_id')
                                    <div class="invalid-feedback font-weight-bold">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Quantidade a Sair --}}
                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label for="saida_quantidade" class="font-weight-bold text-dark">
                                    Quantidade a Retirar: <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="number"
                                           step="any"
                                           min="0.001"
                                           name="quantidade"
                                           id="saida_quantidade"
                                           class="form-control text-dark font-weight-bold @error('quantidade') is-invalid @enderror"
                                           placeholder="0"
                                           value="{{ old('quantidade') }}"
                                           required
                                           oninput="calcularSaldoPosteriorSaida()">
                                    <div class="input-group-append">
                                        <span class="input-group-text font-weight-bold text-dark" id="saida_unidade_label">UN</span>
                                    </div>
                                    @error('quantidade')
                                        <div class="invalid-feedback font-weight-bold">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Projeção de saldo e aviso de estoque insuficiente --}}
                    <div class="alert alert-light border mb-3 py-2 px-3" id="box_projecao_saida" style="display: none;">
                        <div class="d-flex align-items-center justify-content-between flex-wrap">
                            <span class="text-muted">
                                Saldo Atual: <strong class="text-dark" id="txt_saldo_anterior_saida">0</strong>
                            </span>
                            <span class="text-danger font-weight-bold">
                                - Retirando: <span id="txt_qtd_saida">0</span>
                            </span>
                            <span class="text-dark">
                                = Saldo Restante: <strong class="font-weight-bold" id="txt_saldo_posterior_saida">0</strong>
                            </span>
                        </div>
                        <div id="alerta_estoque_insuficiente" class="text-danger font-weight-bold mt-1" style="display: none;">
                            <i class="fas fa-exclamation-triangle mr-1"></i> Atenção: A quantidade informada é maior do que o saldo físico disponível!
                        </div>
                    </div>

                    <div class="row">
                        {{-- Setor de Destino --}}
                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label for="saida_setor" class="font-weight-bold text-dark">
                                    Setor Solicitante / Destino: <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       name="setor"
                                       id="saida_setor"
                                       list="listaSetoresSaida"
                                       class="form-control text-dark @error('setor') is-invalid @enderror"
                                       placeholder="Ex: Governança, Manutenção..."
                                       value="{{ old('setor') }}"
                                       required
                                       maxlength="255">
                                <datalist id="listaSetoresSaida">
                                    <option value="Governança">
                                    <option value="Limpeza e Higienização">
                                    <option value="Manutenção Predial">
                                    <option value="Piscina e Área Externa">
                                    <option value="Recepção">
                                    <option value="Restaurante / Cozinha">
                                    <option value="Lavanderia">
                                    <option value="Eventos">
                                    <option value="Administrativo">
                                    @if(isset($setoresExistentes))
                                        @foreach($setoresExistentes as $s)
                                            <option value="{{ $s }}">
                                        @endforeach
                                    @endif
                                </datalist>
                                @error('setor')
                                    <div class="invalid-feedback font-weight-bold">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Retirado por --}}
                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label for="saida_retirado_por" class="font-weight-bold text-dark">
                                    Retirado por (Funcionário): <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       name="retirado_por"
                                       id="saida_retirado_por"
                                       class="form-control text-dark @error('retirado_por') is-invalid @enderror"
                                       placeholder="Ex: Maria (Camareira), José (Manut.)..."
                                       value="{{ old('retirado_por') }}"
                                       required
                                       maxlength="255">
                                @error('retirado_por')
                                    <div class="invalid-feedback font-weight-bold">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Data / Hora --}}
                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label for="saida_data_movimentacao" class="font-weight-bold text-dark">
                                    Data / Hora da Saída: <span class="text-danger">*</span>
                                </label>
                                <input type="datetime-local"
                                       name="data_movimentacao"
                                       id="saida_data_movimentacao"
                                       class="form-control text-dark @error('data_movimentacao') is-invalid @enderror"
                                       value="{{ old('data_movimentacao', now()->format('Y-m-d\TH:i')) }}"
                                       required>
                                @error('data_movimentacao')
                                    <div class="invalid-feedback font-weight-bold">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Observações --}}
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group mb-0">
                                <label for="saida_observacao" class="font-weight-bold text-dark">Observações / Motivo da Requisição:</label>
                                <input type="text"
                                       name="observacao"
                                       id="saida_observacao"
                                       class="form-control text-dark @error('observacao') is-invalid @enderror"
                                       placeholder="Ex: Reposição dos chalés 01 a 10, reparo elétrico no bloco B..."
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
                    <button type="submit" class="btn btn-danger font-weight-bold btn-submit-movimentacao" id="btn_confirmar_saida">
                        <i class="fas fa-arrow-circle-up mr-1"></i> Confirmar Saída
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
