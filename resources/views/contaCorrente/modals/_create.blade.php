  <!-- Modal -->
  <div class="modal fade" id="createContaCorrenteModal" data-backdrop="static" tabindex="-1" role="dialog"
      aria-labelledby="createContaCorrenteModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
          <div class="modal-content">
              <div class="modal-header">
                  <h5 class="modal-title" id="createContaCorrenteModalLabel">Cadastro de Contas Correntes</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                  </button>
              </div>
              <div class="modal-body">
                  <form id="createContaCorrenteForm" action="{{ route('contaCorrente.store') }}" method="POST">
                      @csrf
                      <div class="mb-3">
                          <label for="numeroConta">Número da Conta:</label>
                          <input type="text" class="form-control" id="numeroConta" name="numero_conta" required>
                      </div>

                      <div class="row">
                          <div class="mb-3">
                              <label for="titular">Titular:</label>
                              <input type="text" class="form-control" id="titular" name="titular" required>
                          </div>

                      </div>
                      <div class="mb-3">
                          <label for="saldo">Saldo:</label>
                          <input type="text" class="form-control" id="saldo" name="saldo" required>
                      </div>

                      <div class="mb-3">
                          <label for="descricao">Descrição:</label>
                          <input type="text" class="form-control" id="descricao" name="descricao" required>
                      </div>


                      <div class="mb-3">
                          <label for="banco">Banco:</label>
                          <select class="form-control" id="banco" name="banco_id" required>
                              <option value="">Selecione</option>
                              @foreach ($banco as $banco)
                                  <option value="{{ $banco->id }}">{{ $banco->descricao }}</option>
                              @endforeach
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
