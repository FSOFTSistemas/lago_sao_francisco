<div class="modal fade" id="modalEditarItem{{ $item->id }}" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="modalEditarItemLabel{{ $item->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content text-dark" style="color: #212529;">
            <div class="modal-header border-bottom">
                <h5 class="modal-title font-weight-bold text-dark" id="modalEditarItemLabel{{ $item->id }}">
                    <i class="fas fa-edit text-warning mr-1"></i> Editar Item: {{ $item->nome }}
                </h5>
                <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form action="{{ route('almoxarifado.itens.update', $item->id) }}" method="POST" id="formEditarItem{{ $item->id }}">
                @csrf
                @method('PUT')

                <div class="modal-body text-dark">
                    <div class="row">
                        {{-- Nome do Item --}}
                        <div class="col-12 col-md-8">
                            <div class="form-group">
                                <label for="nome_item_{{ $item->id }}" class="font-weight-bold text-dark">
                                    Nome / Descrição do Item: <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       class="form-control text-dark"
                                       id="nome_item_{{ $item->id }}"
                                       name="nome"
                                       value="{{ old('nome', $item->nome) }}"
                                       required
                                       minlength="2"
                                       maxlength="255">
                            </div>
                        </div>

                        {{-- Categoria --}}
                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label for="categoria_id_item_{{ $item->id }}" class="font-weight-bold text-dark">
                                    Categoria: <span class="text-danger">*</span>
                                </label>
                                <select class="form-control text-dark"
                                        id="categoria_id_item_{{ $item->id }}"
                                        name="categoria_id"
                                        required>
                                    @foreach ($categorias as $cat)
                                        <option value="{{ $cat->id }}" {{ old('categoria_id', $item->categoria_id) == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->nome }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        {{-- Unidade de Medida --}}
                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label for="unidade_medida_item_{{ $item->id }}" class="font-weight-bold text-dark">
                                    Unidade de Medida: <span class="text-danger">*</span>
                                </label>
                                @php
                                    $unidadesMedidaDisponiveis = [
                                        'UN'  => 'UN - Unidade',
                                        'PCT' => 'PCT - Pacote',
                                        'CX'  => 'CX - Caixa',
                                        'L'   => 'L - Litro',
                                        'KG'  => 'KG - Quilo',
                                        'M'   => 'M - Metro',
                                        'PAR' => 'PAR - Par',
                                    ];
                                    $unidadeItemAtual = strtoupper(trim((string) old('unidade_medida', $item->unidade_medida ?? 'UN')));
                                    $ehUnidadePersonalizada = !empty($unidadeItemAtual) && !array_key_exists($unidadeItemAtual, $unidadesMedidaDisponiveis);
                                @endphp
                                <select class="form-control text-dark"
                                        id="unidade_medida_item_{{ $item->id }}"
                                        name="unidade_medida"
                                        onchange="tratarTrocaUnidade(this, 'wrapper_unidade_custom_{{ $item->id }}', 'input_unidade_custom_{{ $item->id }}')"
                                        required>
                                    @foreach ($unidadesMedidaDisponiveis as $sigla => $descricao)
                                        <option value="{{ $sigla }}" {{ $unidadeItemAtual === $sigla ? 'selected' : '' }}>
                                            {{ $descricao }}
                                        </option>
                                    @endforeach
                                    @if ($ehUnidadePersonalizada)
                                        <option value="{{ $unidadeItemAtual }}" selected>{{ $unidadeItemAtual }} (Personalizado)</option>
                                    @endif
                                    <option value="__OUTRO__">Outro (digitar manualmente...)</option>
                                </select>
                                <div id="wrapper_unidade_custom_{{ $item->id }}" class="mt-2" style="display: none;">
                                    <input type="text"
                                           class="form-control text-dark text-uppercase"
                                           id="input_unidade_custom_{{ $item->id }}"
                                           placeholder="Digite a sigla (ex: TON, RESMA)..."
                                           maxlength="20"
                                           oninput="this.value = this.value.toUpperCase()">
                                    <small class="form-text text-secondary">Digite a sigla ou descrição curta da unidade.</small>
                                </div>
                            </div>
                        </div>

                        {{-- Estoque Atual (Informativo) --}}
                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold text-dark">Estoque Atual Físico:</label>
                                <div class="form-control bg-light text-dark font-weight-bold">
                                    {{ $item->estoque_formatado }} {{ $item->unidade_medida }}
                                </div>
                                <small class="form-text text-secondary mt-1 font-weight-normal">O saldo é atualizado via recebimentos e usos.</small>
                            </div>
                        </div>

                        {{-- Estoque Mínimo (Alerta) --}}
                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label for="estoque_minimo_item_{{ $item->id }}" class="font-weight-bold text-dark">
                                    Estoque Mínimo (Alerta):
                                </label>
                                <input type="number"
                                       step="any"
                                       min="0"
                                       class="form-control text-dark"
                                       id="estoque_minimo_item_{{ $item->id }}"
                                       name="estoque_minimo"
                                       value="{{ old('estoque_minimo', (float) $item->estoque_minimo) }}">
                                <small class="form-text text-secondary mt-1 font-weight-normal">Avisa quando o estoque estiver baixo.</small>
                            </div>
                        </div>
                    </div>

                    {{-- Status Ativo --}}
                    <div class="form-group mb-0">
                        <div class="custom-control custom-switch">
                            <input type="checkbox"
                                   class="custom-control-input"
                                   id="switch_ativo_{{ $item->id }}"
                                   name="ativo"
                                   value="1"
                                   {{ old('ativo', $item->ativo) ? 'checked' : '' }}>
                            <label class="custom-control-label font-weight-bold text-dark" for="switch_ativo_{{ $item->id }}">
                                {{ $item->ativo ? 'Item ativo no almoxarifado' : 'Item inativo' }}
                            </label>
                        </div>
                        <small class="form-text text-secondary mt-1 font-weight-normal">Itens inativos não ficam disponíveis para novos usos ou recebimentos.</small>
                    </div>
                </div>

                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
