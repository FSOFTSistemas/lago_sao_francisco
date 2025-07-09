  <!-- Modal -->
  <div class="modal fade" id="createCategoriaParceiroModal" data-backdrop="static" tabindex="-1" role="dialog"
      aria-labelledby="createCategoriaParceiroModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
              <div class="modal-header">
                  <h5 class="modal-title" id="createCategoriaParceiroModalLabel">Cadastro de Categoria de Parceiros</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                  </button>
              </div>
              <div class="modal-body">
                  <form id="createCategoriaParceiroForm" action="{{ route('categoriasParceiro.store') }}" method="POST">
                      @csrf
                      <div class="mb-3">
                          <label for="descricao">Descrição:</label>
                          <input type="text" class="form-control" id="descricao" name="descricao" required>
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

  
