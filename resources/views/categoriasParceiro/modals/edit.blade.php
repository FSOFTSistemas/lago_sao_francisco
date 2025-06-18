<div class="modal fade" id="editCategoriaParceiroModal{{ $categoria->id }}" tabindex="-1" role="dialog" aria-labelledby="editCategoriaParceiroModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCategoriaParceiroModalLabel">Editar Categoria de Parceiro: {{ $categoria->descricao }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form action="{{ route('categoriasParceiro.update', $categoria->id) }}" method="POST" class="edit-categoria-form">
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
                                       required>
                                @error('descricao')
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
