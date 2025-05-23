<div class="modal fade" id="editCategoriaModal{{$categoria->id}}" tabindex="-1" aria-labelledby="editCategoriaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCategoriaModalLabel">Atualizar Categorias</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editCategoriaForm" action="{{ route('categoriasCardapio.update',$categoria->id) }}" method="POST" >
                    @csrf
                    @method('PUT')
                   <div class="form-group">
                            <label for="nome">Nome da Categoria</label>
                            <input type="text" name="nome" id="nome" class="form-control" value="{{$categoria->nome}}" required>
                        </div>

                         <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                          <button type="submit" class="btn btn-primary">Atualizar</button>
                      </div>
                    </form>
            </div>

        </div>
    </div>
</div>

