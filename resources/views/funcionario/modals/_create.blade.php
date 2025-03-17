  <!-- Modal -->
  <div class="modal fade" id="createFuncionarioModal" data-backdrop="static" tabindex="-1" role="dialog"
      aria-labelledby="createFuncionarioModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
          <div class="modal-content">
              <div class="modal-header">
                  <h5 class="modal-title" id="createFuncionarioModalLabel">Cadastro de Funcionário</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                  </button>
              </div>
              <div class="modal-body">
                  <form id="createFuncionarioForm" action="{{ route('funcionario.store') }}" method="POST">
                      @csrf
                      <div class="mb-3">
                          <label for="nome">Nome:</label>
                          <input type="text" class="form-control" id="nome" name="nome" required>
                      </div>

                      <div class="row">
                          <div class="mb-3">
                              <label for="cpf">CPF:</label>
                              <input type="text" class="form-control" id="cpf" name="cpf" required>
                          </div>

                      </div>
                      <div class="mb-3">
                          <label for="salario">Salário:</label>
                          <input type="text" class="form-control" id="salario" name="salario" required>
                      </div>
                      <div class="mb-3">
                          <label for="dataContratacao">Data de Contratação:</label>
                          <input type="date" class="form-control" id="dataContratacao" name="data_contratacao" required>
                      </div>

                      <div class="mb-3">
                          <label for="setor">Setor:</label>
                          <input type="text" class="form-control" id="setor" name="setor" required>
                      </div>

                      <div class="mb-3">
                          <label for="cargo">Cargo:</label>
                          <input type="text" class="form-control" id="cargo" name="cargo" required>
                      </div>

                      <div class="mb-3">
                          <label for="tipo">Situação:</label>
                          <select class="form-control" id="status" name="status" required>
                              <option value="ativo">Ativo</option>
                              <option value="inativo">Inativo</option>
                          </select>
                      </div>

                      <div class="mb-3">
                          <label for="empresa">Empresa:</label>
                          <select class="form-control" id="empresa" name="empresa_id" required>
                              <option value="">Selecione</option>
                              @foreach ($empresas as $empresa)
                                  <option value="{{ $empresa->id }}">{{ $empresa->razao_social }}</option>
                              @endforeach
                          </select>
                      </div>

                      <div class="mb-3">
                          <label for="endereco">Endereço:</label>
                          <select class="form-control" id="endereco" name="endereco_id">
                              <option value="">Selecione</option>
                              @foreach ($enderecos as $endereco)
                                  <option value="{{ $endereco->id }}">{{ $endereco->logradouro }}</option>
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
