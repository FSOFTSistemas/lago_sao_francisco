  <!-- Modal -->
  <div class="modal fade" id="createCaixaModal" data-backdrop="static" tabindex="-1" role="dialog"
      aria-labelledby="createCaixaModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
          <div class="modal-content">
              <div class="modal-header">
                  <h5 class="modal-title" id="createCaixaModalLabel">Cadastro de Contas A Pagar</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                  </button>
              </div>
              <div class="modal-body">
                  <form id="createCaixaForm" action="{{ route('caixa.store') }}" method="POST">
                      @csrf
                      <div class="mb-3">
                          <label for="descricao">Descrição:</label>
                          <input type="text" class="form-control" id="descricao" name="descricao" required>
                      </div>
                      <div class="mb-3">
                          <label for="dataAbertura">Data de Abertura:</label>
                          <input type="date" class="form-control" id="dataAbertura" name="data_abertura" required>
                      </div>
                      <div class="mb-3">
                          <label for="dataFechamento">Data de Fechamento:</label>
                          <input type="date" class="form-control" id="dataFechamento" name="data_fechamento" required>
                      </div>

                      <div class="mb-3">
                          <label for="valorInicial">Valor Inicial:</label>
                          <input type="text" class="form-control" id="valorInicial" name="valor-inicial" required>
                      </div>

                      <div class="mb-3">
                          <label for="valorFinal">Valor Final:</label>
                          <input type="text" class="form-control" id="valorFinal" name="valor_final">
                      </div>

                      <div class="mb-3">
                          <label for="tipo">Situação:</label>
                          <select class="form-control" id="status" name="status" required>
                              <option value="aberto">Aberto</option>
                              <option value="fechado">Fechado</option>
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
                        <label for="observacoes">Observações:</label>
                        <input type="text" class="form-control" id="observacoes" name="observacoes">
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
