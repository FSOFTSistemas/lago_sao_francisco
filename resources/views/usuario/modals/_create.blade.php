  <!-- Modal -->
  <div class="modal fade" id="createUsuarioModal" data-backdrop="static" tabindex="-1" role="dialog"
      aria-labelledby="createUsuarioModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
          <div class="modal-content">
              <div class="modal-header">
                  <h5 class="modal-title" id="createUsuarioModalLabel">Cadastro de Usuário</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
              </div>
              <div class="modal-body">
                  <form id="createUsuarioForm" action="{{ route('usuarios.store') }}" method="POST">
                      @csrf
                      <div class="mb-3">
                          <label for="name">Nome</label>
                          <input type="text" class="form-control" id="name" name="name" required>
                      </div>
                      <div class="mb-3">
                          <label for="email">Email</label>
                          <input type="email" class="form-control" id="email" name="email" required>
                      </div>
                      <div class="mb-3">
                          <label for="password">Senha</label>
                          <input type="password" class="form-control" id="password" name="password" required>
                      </div>
                      <div class="mb-3">
                          <label for="role">Permissão</label>
                          <select class="form-control" id="role" name="role" required>
                              <option value="financeiro">Financeiro</option>
                              <option value="funcionario">Funcionário</option>
                          </select>
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
