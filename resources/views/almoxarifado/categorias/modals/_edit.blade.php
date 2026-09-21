<div class="modal fade" id="modalEditarCategoria{{ $categoria->id }}" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="modalEditarCategoriaLabel{{ $categoria->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content text-dark" style="color: #212529;">
            <div class="modal-header border-bottom">
                <h5 class="modal-title font-weight-bold text-dark" id="modalEditarCategoriaLabel{{ $categoria->id }}">
                    <i class="fas fa-edit text-warning mr-1"></i> Editar Categoria: {{ $categoria->nome }}
                </h5>
                <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form action="{{ route('almoxarifado.categorias.update', $categoria->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="modal-body text-dark">
                    <div class="form-group">
                        <label for="nome_categoria_{{ $categoria->id }}" class="font-weight-bold text-dark">
                            Nome da Categoria: <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control text-dark"
                               id="nome_categoria_{{ $categoria->id }}"
                               name="nome"
                               value="{{ old('nome', $categoria->nome) }}"
                               required
                               minlength="2"
                               maxlength="255">
                        <small class="form-text text-secondary mt-1 font-weight-normal">Altere o nome descritivo da categoria.</small>
                    </div>

                    <div class="form-group mb-0">
                        <div class="custom-control custom-switch">
                            <input type="checkbox"
                                   class="custom-control-input"
                                   id="switch_ativo_{{ $categoria->id }}"
                                   name="ativo"
                                   value="1"
                                   {{ old('ativo', $categoria->ativo) ? 'checked' : '' }}>
                            <label class="custom-control-label font-weight-bold text-dark" for="switch_ativo_{{ $categoria->id }}">
                                {{ $categoria->ativo ? 'Categoria ativa' : 'Categoria inativa' }}
                            </label>
                        </div>
                        <small class="form-text text-secondary mt-1 font-weight-normal">Se inativada, novos produtos não poderão selecioná-la.</small>
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
