<div class="modal fade" id="editCategoriaModal{{ $categoria->id }}" tabindex="-1" role="dialog" aria-labelledby="editCategoriaProdutoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCategoriaProdutoModalLabel">Editar Categoria de Produto: {{ $categoria->descricao }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form action="{{ route('categoriaProduto.update', $categoria->id) }}" method="POST" class="edit-categoria-form">
                @csrf
                @method('PUT')

                <div class="modal-body">
                    <div class="row">
                        <!-- Descrição -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_nome_categoria">Descrição</label>
                                <input type="text" class="form-control" id="edit_nome_categoria"
                                       name="descricao" value="{{ old('descricao', $categoria->descricao) }}"
                                       required minlength="3" maxlength="100">
                                @error('descricao')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <!-- Ativo -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_categoria_ativo{{ $categoria->id }}">Ativo</label>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="edit_categoria_ativo{{ $categoria->id }}"
                                           name="ativo" value="1" {{ $categoria->ativo ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="edit_categoria_ativo{{ $categoria->id }}">
                                        {{ $categoria->ativo ? 'Ativo' : 'Inativo' }}
                                    </label>
                                </div>
                                @error('ativo')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
