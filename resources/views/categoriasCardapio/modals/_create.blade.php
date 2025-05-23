  <!-- Modal -->
  <div class="modal fade" id="createCategoriaModal" data-backdrop="static" tabindex="-1" role="dialog"
      aria-labelledby="createCategoriaModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
              <div class="modal-header">
                  <h5 class="modal-title" id="createCategoriaModalLabel">Cadastro de Categorias</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                  </button>
              </div>
              <div class="modal-body">
                  <form action="{{ route('categoriasCardapio.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="nome">Nome da Categoria</label>
                            <input type="text" name="nome" id="nome" class="form-control" required>
                        </div>

                         <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                          <button type="submit" class="btn btn-primary">Criar</button>
                      </div>
                    </form>
              </div>

          </div>
      </div>
  </div>
  </div>
