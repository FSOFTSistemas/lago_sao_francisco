  <!-- Modal -->
  <div class="modal fade" id="createAdiantamentoModal" data-backdrop="static" tabindex="-1" role="dialog"
      aria-labelledby="createAdiantamentoModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
              <div class="modal-header">
                  <h5 class="modal-title" id="createAdiantamentoModalLabel">Cadastro de Adiantamentos</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                  </button>
              </div>
              <div class="modal-body">
                  <form id="createAdiantamentoForm" action="{{ route('adiantamento.store') }}" method="POST">
                      @csrf
                      <div class="mb-3">
                          <label for="nome">Descrição:</label>
                          <input type="text" class="form-control" id="descricao" name="descricao" required>
                      </div>

                      <div class="row">
                            <div class="mb-3">
                                <label for="cpf">Funcionário:</label>
                                <select class="form-control" id="funcionario_id" name="funcionario_id" required>
                                    @foreach ($funcionarios as $funcionario)
                                        <option value="{{ $funcionario->id }}">{{ $funcionario->nome }}</option>
                                    @endforeach
                                </select>
                            </div>

                      </div>
                      <div class="mb-3">
                          <label for="valor">Valor:</label>
                          <input type="text" class="form-control" id="valor" name="valor" required>
                      </div>
                      <div class="mb-3">
                          <label for="dataContratacao">Data do Adiantamento:</label>
                          <input type="date" class="form-control" id="data" name="data" required>
                      </div>

                      <div class="mb-3">
                          <label for="tipo">Situação:</label>
                          <select class="form-control" id="status" name="status" required>
                              <option value="pendente">Pendente</option>
                              <option value="finalizado">Finalizado</option>
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
  </div>
