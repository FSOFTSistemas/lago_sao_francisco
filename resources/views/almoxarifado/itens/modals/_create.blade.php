<div class="modal fade" id="modalCriarItem" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="modalCriarItemLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content text-dark" style="color: #212529;">
            <div class="modal-header border-bottom">
                <h5 class="modal-title font-weight-bold text-dark" id="modalCriarItemLabel">
                    <i class="fas fa-plus-circle text-success mr-1"></i> Cadastrar Item no Almoxarifado
                </h5>
                <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form action="{{ route('almoxarifado.itens.store') }}" method="POST" id="formCriarItem">
                @csrf

                <div class="modal-body text-dark">
                    <div class="row">
                        {{-- Nome do Item --}}
                        <div class="col-12 col-md-8">
                            <div class="form-group">
                                <label for="nome_novo_item" class="font-weight-bold text-dark">
                                    Nome / Descrição do Item: <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       class="form-control text-dark @error('nome') is-invalid @enderror"
                                       id="nome_novo_item"
                                       name="nome"
                                       placeholder="Ex: Detergente Neutro 5L, Lâmpada LED 9W, Toalha de Banho..."
                                       value="{{ old('nome') }}"
                                       required
                                       minlength="2"
                                       maxlength="255">
                                @error('nome')
                                    <div class="invalid-feedback font-weight-bold">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Categoria --}}
                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label for="categoria_id_novo_item" class="font-weight-bold text-dark">
                                    Categoria: <span class="text-danger">*</span>
                                </label>
                                <select class="form-control text-dark @error('categoria_id') is-invalid @enderror"
                                        id="categoria_id_novo_item"
                                        name="categoria_id"
                                        required>
                                    <option value="">Selecione...</option>
                                    @foreach ($categorias as $cat)
                                        <option value="{{ $cat->id }}" {{ old('categoria_id') == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->nome }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('categoria_id')
                                    <div class="invalid-feedback font-weight-bold">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        {{-- Unidade de Medida --}}
                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label for="unidade_medida_novo_item" class="font-weight-bold text-dark">
                                    Unidade de Medida: <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       class="form-control text-dark @error('unidade_medida') is-invalid @enderror"
                                       id="unidade_medida_novo_item"
                                       name="unidade_medida"
                                       list="listaUnidadesMedida"
                                       placeholder="Ex: UN, CX, PCT, L, KG"
                                       value="{{ old('unidade_medida', 'UN') }}"
                                       required
                                       maxlength="20">
                                <datalist id="listaUnidadesMedida">
                                    <option value="UN">Unidade (UN)</option>
                                    <option value="CX">Caixa (CX)</option>
                                    <option value="PCT">Pacote (PCT)</option>
                                    <option value="L">Litro (L)</option>
                                    <option value="KG">Quilo (KG)</option>
                                    <option value="GL">Galão (GL)</option>
                                    <option value="FD">Fardo (FD)</option>
                                    <option value="ROLO">Rolo (ROLO)</option>
                                    <option value="PAR">Par (PAR)</option>
                                    <option value="M">Metro (M)</option>
                                </datalist>
                                @error('unidade_medida')
                                    <div class="invalid-feedback font-weight-bold">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Saldo Inicial em Estoque --}}
                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label for="estoque_atual_novo_item" class="font-weight-bold text-dark">
                                    Saldo Inicial em Estoque:
                                </label>
                                <input type="number"
                                       step="any"
                                       min="0"
                                       class="form-control text-dark @error('estoque_atual') is-invalid @enderror"
                                       id="estoque_atual_novo_item"
                                       name="estoque_atual"
                                       placeholder="0"
                                       value="{{ old('estoque_atual', '0') }}">
                                <small class="form-text text-secondary mt-1 font-weight-normal">Quantidade física atual no almoxarifado.</small>
                                @error('estoque_atual')
                                    <div class="invalid-feedback font-weight-bold">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Estoque Mínimo (Alerta) --}}
                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label for="estoque_minimo_novo_item" class="font-weight-bold text-dark">
                                    Estoque Mínimo (Alerta):
                                </label>
                                <input type="number"
                                       step="any"
                                       min="0"
                                       class="form-control text-dark @error('estoque_minimo') is-invalid @enderror"
                                       id="estoque_minimo_novo_item"
                                       name="estoque_minimo"
                                       placeholder="0"
                                       value="{{ old('estoque_minimo', '0') }}">
                                <small class="form-text text-secondary mt-1 font-weight-normal">Avisa quando o estoque estiver baixo.</small>
                                @error('estoque_minimo')
                                    <div class="invalid-feedback font-weight-bold">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Status Ativo --}}
                    <div class="form-group mb-0">
                        <div class="custom-control custom-switch">
                            <input type="checkbox"
                                   class="custom-control-input"
                                   id="switch_ativo_novo_item"
                                   name="ativo"
                                   value="1"
                                   {{ old('ativo', '1') == '1' ? 'checked' : '' }}>
                            <label class="custom-control-label font-weight-bold text-dark" for="switch_ativo_novo_item">
                                Item ativo no almoxarifado
                            </label>
                        </div>
                        <small class="form-text text-secondary mt-1 font-weight-normal">Itens inativos não ficam disponíveis para novos usos ou recebimentos.</small>
                    </div>
                </div>

                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-success" id="btnSalvarNovoItem">
                        <i class="fas fa-save mr-1"></i> Salvar Item
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
