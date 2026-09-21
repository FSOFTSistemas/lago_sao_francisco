<div class="modal fade" id="modalCriarCategoria" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="modalCriarCategoriaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content text-dark" style="color: #212529;">
            <div class="modal-header border-bottom">
                <h5 class="modal-title font-weight-bold text-dark" id="modalCriarCategoriaLabel">
                    <i class="fas fa-plus-circle text-success mr-1"></i> Nova Categoria do Almoxarifado
                </h5>
                <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form action="{{ route('almoxarifado.categorias.store') }}" method="POST">
                @csrf

                <div class="modal-body text-dark">
                    <div class="form-group">
                        <label for="nome_nova_categoria" class="font-weight-bold text-dark">
                            Nome da Categoria: <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control text-dark @error('nome') is-invalid @enderror"
                               id="nome_nova_categoria"
                               name="nome"
                               placeholder="Ex: Limpeza e Higiene, Manutenção, Escritório..."
                               value="{{ old('nome') }}"
                               required
                               minlength="2"
                               maxlength="255">
                        @error('nome')
                            <div class="invalid-feedback font-weight-bold">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-secondary mt-1 font-weight-normal">Exemplos: Limpeza, Manutenção, Rouparia, Descartáveis, Escritório.</small>
                    </div>

                    <div class="form-group mb-0">
                        <div class="custom-control custom-switch">
                            <input type="checkbox"
                                   class="custom-control-input"
                                   id="switch_ativo_nova_categoria"
                                   name="ativo"
                                   value="1"
                                   {{ old('ativo', '1') == '1' ? 'checked' : '' }}>
                            <label class="custom-control-label font-weight-bold text-dark" for="switch_ativo_nova_categoria">
                                Categoria ativa
                            </label>
                        </div>
                        <small class="form-text text-secondary mt-1 font-weight-normal">Categorias inativas não aparecem para novos produtos.</small>
                    </div>
                </div>

                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save mr-1"></i> Salvar Categoria
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
